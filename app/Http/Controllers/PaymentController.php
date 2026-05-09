<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FifoService;
use App\Services\HppService;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        private MidtransService $midtrans,
        private FifoService     $fifo,
        private HppService      $hpp,
    ) {
        $this->middleware('auth');
        $this->middleware('can:pos.access');
    }

    public function createSnapToken(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id'              => ['nullable', 'exists:customers,id'],
            'payment_method'           => ['required', 'in:qris,transfer,debit_card,credit_card'],
            'discount_amount'          => ['nullable', 'numeric', 'min:0'],
            'tax_percent'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                    => ['nullable', 'string', 'max:500'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.qty'              => ['required', 'numeric', 'min:1'],
            'items.*.sale_price'       => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.name'             => ['required', 'string'],
        ]);

        try {
            $result = DB::transaction(function () use ($request) {
                $subtotal       = 0;
                $discountAmount = (float) ($request->discount_amount ?? 0);
                $taxPercent     = (float) ($request->tax_percent ?? 0);

                foreach ($request->items as $item) {
                    $itemSubtotal = $item['sale_price'] * $item['qty'];
                    $itemDiscount = $itemSubtotal * (($item['discount_percent'] ?? 0) / 100);
                    $subtotal    += $itemSubtotal - $itemDiscount;
                }

                $taxAmount = round($subtotal * ($taxPercent / 100), 2);
                $total     = round($subtotal - $discountAmount + $taxAmount, 2);

                $sale = Sale::create([
                    'user_id'         => auth()->id(),
                    'customer_id'     => $request->customer_id,
                    'sale_number'     => $this->generateSaleNumber(),
                    'sale_date'       => now(),
                    'payment_method'  => $request->payment_method,
                    'subtotal'        => $subtotal,
                    'discount_amount' => $discountAmount,
                    'tax_percent'     => $taxPercent,
                    'tax_amount'      => $taxAmount,
                    'total_amount'    => $total,
                    'paid_amount'     => $total,
                    'change_amount'   => 0,
                    'status'          => Sale::STATUS_PENDING,
                    'notes'           => $request->notes,
                ]);

                foreach ($request->items as $item) {
                    $itemSubtotal = $item['sale_price'] * $item['qty'];
                    $discPct      = (float) ($item['discount_percent'] ?? 0);
                    $discAmt      = round($itemSubtotal * ($discPct / 100), 2);

                    SaleItem::create([
                        'sale_id'          => $sale->id,
                        'product_id'       => $item['product_id'],
                        'qty'              => (int) $item['qty'],
                        'sale_price'       => $item['sale_price'],
                        'discount_percent' => $discPct,
                        'discount_amount'  => $discAmt,
                        'subtotal'         => round($itemSubtotal - $discAmt, 2),
                    ]);
                }

                $customer = $request->customer_id ? Customer::find($request->customer_id) : null;

                $itemDetails = collect($request->items)->map(function ($item) {
                    $discPct  = (float) ($item['discount_percent'] ?? 0);
                    $price    = round($item['sale_price'] * (1 - $discPct / 100));
                    return [
                        'id'       => (string) $item['product_id'],
                        'price'    => (int) $price,
                        'quantity' => (int) $item['qty'],
                        'name'     => substr($item['name'], 0, 50),
                    ];
                })->toArray();

                if ($discountAmount > 0) {
                    $itemDetails[] = [
                        'id'       => 'DISC',
                        'price'    => -(int) round($discountAmount),
                        'quantity' => 1,
                        'name'     => 'Diskon',
                    ];
                }

                if ($taxAmount > 0) {
                    $itemDetails[] = [
                        'id'       => 'TAX',
                        'price'    => (int) round($taxAmount),
                        'quantity' => 1,
                        'name'     => "Pajak {$taxPercent}%",
                    ];
                }

                $customerDetails = [
                    'first_name' => $customer ? $customer->name : 'Pelanggan Umum',
                    'email'      => $customer?->email ?? 'customer@pos.local',
                    'phone'      => $customer?->phone ?? '',
                ];

                // order_id Midtrans harus selalu unik per percobaan bayar,
                // terpisah dari sale_number agar tidak konflik walau sale di-cancel.
                $midtransOrderId = 'PAY-' . now()->format('YmdHis') . '-' . $sale->id;

                $params = $this->midtrans->buildSnapParams(
                    $midtransOrderId,
                    $total,
                    $customerDetails,
                    $itemDetails
                );

                $snapToken = $this->midtrans->getSnapToken($params);

                $transaction = PaymentTransaction::create([
                    'sale_id'        => $sale->id,
                    'order_id'       => $midtransOrderId,
                    'snap_token'     => $snapToken,
                    'gross_amount'   => $total,
                    'status'         => PaymentTransaction::STATUS_PENDING,
                    'payment_type'   => $request->payment_method,
                ]);

                return compact('snapToken', 'transaction', 'sale');
            });

            return response()->json([
                'success'        => true,
                'snap_token'     => $result['snapToken'],
                'transaction_id' => $result['transaction']->id,
                'sale_number'    => $result['sale']->sale_number,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat token pembayaran: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function complete(Request $request, PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->status !== PaymentTransaction::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah diproses.'], 422);
        }

        $request->validate([
            'payment_type'          => ['nullable', 'string'],
            'transaction_id'        => ['nullable', 'string'],
            'transaction_status'    => ['nullable', 'string'],
        ]);

        try {
            $sale = DB::transaction(function () use ($request, $transaction) {
                $transaction->update([
                    'status'                  => PaymentTransaction::STATUS_SUCCESS,
                    'payment_type'            => $request->input('payment_type', $transaction->payment_type),
                    'midtrans_transaction_id' => $request->input('transaction_id'),
                    'payment_detail'          => $request->all(),
                    'paid_at'                 => now(),
                ]);

                $sale = $transaction->sale;
                $sale->update([
                    'payment_method' => $this->mapPaymentType($request->input('payment_type', $transaction->payment_type)),
                    'status'         => Sale::STATUS_PENDING,
                ]);

                $this->hpp->processSale($sale);

                $sale->refresh();
                $sale->update(['status' => Sale::STATUS_COMPLETED]);

                if ($sale->customer_id) {
                    $sale->customer->increment('total_transaction', $sale->total_amount);
                    $sale->customer->increment('total_visit');
                }

                return $sale;
            });

            return response()->json([
                'success'     => true,
                'message'     => 'Pembayaran berhasil dikonfirmasi',
                'sale_number' => $sale->sale_number,
                'total'       => $sale->total_amount,
                'paid'        => $sale->paid_amount,
                'change'      => $sale->change_amount,
                'receipt_url' => route('pos.receipt', $sale),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyelesaikan pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancel(PaymentTransaction $transaction): JsonResponse
    {
        if ($transaction->status !== PaymentTransaction::STATUS_PENDING) {
            return response()->json(['success' => false, 'message' => 'Transaksi sudah diproses.'], 422);
        }

        DB::transaction(function () use ($transaction) {
            $sale = $transaction->sale;
            $transaction->update(['status' => PaymentTransaction::STATUS_CANCEL]);
            if ($sale) {
                // Soft-delete saja agar sale_number tidak dipakai ulang.
                // FIFO belum diproses (sale masih PENDING), jadi tidak ada stok yang perlu dikembalikan.
                $sale->update(['status' => Sale::STATUS_VOIDED]);
                $sale->delete();
            }
        });

        return response()->json(['success' => true, 'message' => 'Pembayaran dibatalkan.']);
    }

    public function notification(Request $request): JsonResponse
    {
        try {
            $notification  = $this->midtrans->getNotification();
            $orderId       = $notification->order_id;
            $statusCode    = $notification->status_code;
            $grossAmount   = $notification->gross_amount;
            $signatureKey  = $notification->signature_key;
            $transStatus   = $notification->transaction_status;
            $paymentType   = $notification->payment_type;
            $fraudStatus   = $notification->fraud_status ?? null;

            $serverKey = \App\Models\Setting::get('midtrans.server_key', config('midtrans.server_key'));
            $expected  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($signatureKey !== $expected) {
                return response()->json(['status' => 'invalid signature'], 403);
            }

            $paymentTrx = PaymentTransaction::where('order_id', $orderId)->first();
            if (!$paymentTrx) {
                return response()->json(['status' => 'not found'], 404);
            }

            $status = match (true) {
                in_array($transStatus, ['capture', 'settlement']) && $fraudStatus !== 'deny'
                    => PaymentTransaction::STATUS_SUCCESS,
                $transStatus === 'pending'  => PaymentTransaction::STATUS_PENDING,
                $transStatus === 'cancel'   => PaymentTransaction::STATUS_CANCEL,
                $transStatus === 'expire'   => PaymentTransaction::STATUS_EXPIRE,
                default                     => PaymentTransaction::STATUS_FAILURE,
            };

            if ($status === PaymentTransaction::STATUS_SUCCESS && $paymentTrx->status === PaymentTransaction::STATUS_PENDING) {
                DB::transaction(function () use ($paymentTrx, $paymentType, $notification, $status) {
                    $paymentTrx->update([
                        'status'                  => $status,
                        'payment_type'            => $paymentType,
                        'midtrans_transaction_id' => $notification->transaction_id ?? null,
                        'payment_detail'          => (array) $notification,
                        'paid_at'                 => now(),
                    ]);

                    $sale = $paymentTrx->sale;
                    if ($sale && $sale->status === Sale::STATUS_PENDING) {
                        $this->hpp->processSale($sale);
                        $sale->update([
                            'status'         => Sale::STATUS_COMPLETED,
                            'payment_method' => $this->mapPaymentType($paymentType),
                        ]);
                        if ($sale->customer_id) {
                            $sale->customer->increment('total_transaction', $sale->total_amount);
                            $sale->customer->increment('total_visit');
                        }
                    }
                });
            } elseif (in_array($status, [PaymentTransaction::STATUS_CANCEL, PaymentTransaction::STATUS_EXPIRE, PaymentTransaction::STATUS_FAILURE])) {
                $paymentTrx->update(['status' => $status]);
            }

            return response()->json(['status' => 'ok']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Konversi payment_type dari Midtrans ke nilai ENUM payment_method di tabel sales.
     * Midtrans: bank_transfer, credit_card, qris, gopay, shopeepay, cstore, echannel, dll.
     */
    private function mapPaymentType(string $paymentType): string
    {
        return match (true) {
            $paymentType === 'credit_card'                              => 'credit_card',
            $paymentType === 'debit_card'                               => 'debit_card',
            $paymentType === 'qris'                                     => 'qris',
            in_array($paymentType, ['bank_transfer', 'echannel', 'permata', 'bca_klikpay', 'bca_klikbca', 'cimb_clicks', 'danamon_online', 'mandiri_clickpay']) => 'transfer',
            in_array($paymentType, ['gopay', 'shopeepay', 'dana', 'ovo', 'linkaja', 'akulaku', 'kredivo']) => 'qris',
            default                                                     => 'transfer',
        };
    }

    private function generateSaleNumber(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        // withTrashed() agar nomor dari sale yang di-cancel (soft-deleted) tidak dipakai ulang
        $last   = Sale::withTrashed()
            ->where('sale_number', 'like', $prefix . '%')
            ->orderByDesc('sale_number')
            ->value('sale_number');
        $next   = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
