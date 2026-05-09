<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\FifoService;
use App\Services\HppService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function __construct(
        private FifoService $fifoService,
        private HppService  $hppService,
    ) {
        $this->middleware('can:pos.access');
    }

    public function index()
    {
        $products  = Product::active()
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->get()
            ->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'code'       => $p->code,
                    'barcode'    => $p->barcode,
                    'name'       => $p->name,
                    'price'      => (float) $p->sale_price,
                    'stock'      => (float) $p->current_stock,
                    'unit'       => $p->unit->symbol,
                    'category'   => $p->category->name,
                    'image'      => $p->image
                        ? asset('storage/' . $p->image)
                        : null,
                ];
            });

        $customers = Customer::active()
            ->orderBy('name')
            ->get()
            ->map(function ($c) {
                return [
                    'id'    => $c->id,
                    'code'  => $c->code,
                    'name'  => $c->name,
                    'phone' => $c->phone,
                ];
            });

        $lastSaleNumber = Sale::latest()->value('sale_number');

        return view('pos.index', compact('products', 'customers', 'lastSaleNumber'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id'              => ['nullable', 'exists:customers,id'],
            'payment_method'           => ['required', 'in:cash,qris,transfer,debit_card,credit_card,mixed'],
            'paid_amount'              => ['required', 'numeric', 'min:0'],
            'discount_amount'          => ['nullable', 'numeric', 'min:0'],
            'tax_percent'              => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'                    => ['nullable', 'string', 'max:500'],
            'items'                    => ['required', 'array', 'min:1'],
            'items.*.product_id'       => ['required', 'exists:products,id'],
            'items.*.qty'              => ['required', 'numeric', 'min:1'],
            'items.*.sale_price'       => ['required', 'numeric', 'min:0'],
            'items.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {

                // ── Validasi stok SEBELUM proses apapun ──────────────
                $stockErrors = [];

                foreach ($request->items as $item) {
                    $product   = Product::findOrFail($item['product_id']);
                    $available = (float) $product->current_stock;
                    $needed    = (float) $item['qty'];

                    if ($available <= 0) {
                        $stockErrors[] = "Stok {$product->name} sudah habis.";
                    } elseif ($needed > $available) {
                        $stockErrors[] = "Stok {$product->name} tidak cukup. "
                            . "Dibutuhkan: {$needed} {$product->unit->symbol}, "
                            . "Tersedia: {$available} {$product->unit->symbol}.";
                    }
                }

                if (!empty($stockErrors)) {
                    throw new \Exception(implode("\n", $stockErrors));
                }

                // ── Hitung subtotal ───────────────────────────────────
                $subtotal       = 0;
                $discountAmount = (float) ($request->discount_amount ?? 0);
                $taxPercent     = (float) ($request->tax_percent ?? 0);

                foreach ($request->items as $item) {
                    $itemSubtotal = $item['sale_price'] * $item['qty'];
                    $itemDiscount = $itemSubtotal * (($item['discount_percent'] ?? 0) / 100);
                    $subtotal    += $itemSubtotal - $itemDiscount;
                }

                $taxAmount  = round($subtotal * ($taxPercent / 100), 2);
                $total      = round($subtotal - $discountAmount + $taxAmount, 2);
                $paidAmount = (float) $request->paid_amount;
                $change     = max(0, $paidAmount - $total);

                // ── Buat header sale ──────────────────────────────────
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
                    'paid_amount'     => $paidAmount,
                    'change_amount'   => $change,
                    'status'          => Sale::STATUS_COMPLETED,
                    'notes'           => $request->notes,
                ]);

                // ── Buat sale items ───────────────────────────────────
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

                // ── Proses FIFO + HPP ─────────────────────────────────
                // FifoService juga melakukan lockForUpdate() di dalam consumeStock()
                // sebagai lapisan validasi terakhir di level database (race condition safe)
                $this->hppService->processSale($sale);

                // ── Update statistik customer ─────────────────────────
                if ($sale->customer_id) {
                    $sale->customer->increment('total_transaction', $total);
                    $sale->customer->increment('total_visit');
                }

                return $sale;
            });

            return response()->json([
                'success'     => true,
                'message'     => 'Transaksi berhasil',
                'sale_number' => $sale->sale_number,
                'total'       => $sale->total_amount,
                'paid'        => $sale->paid_amount,
                'change'      => $sale->change_amount,
                'receipt_url' => route('pos.receipt', $sale),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items.product.unit', 'customer', 'user']);

        return view('pos.receipt', compact('sale'));
    }

    public function checkStock(Product $product)
    {
        return response()->json([
            'product_id' => $product->id,
            'stock'      => (float) $product->current_stock,
            'price'      => (float) $product->sale_price,
        ]);
    }

    private function generateSaleNumber(): string
    {
        $prefix = 'TRX-' . now()->format('Ymd') . '-';
        $last   = Sale::withTrashed()
            ->where('sale_number', 'like', $prefix . '%')
            ->orderByDesc('sale_number')
            ->value('sale_number');
        $next   = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
