<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrder\UpdatePurchaseOrderRequest;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:purchase_orders.view')->only(['index', 'show']);
        $this->middleware('can:purchase_orders.create')->only(['create', 'store']);
        $this->middleware('can:purchase_orders.edit')->only(['edit', 'update', 'send']);
        $this->middleware('can:purchase_orders.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'user'])
            ->withCount('items');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('po_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('po_date', '<=', $request->input('to'));
        }

        $purchaseOrders = $query->latest('po_date')->paginate(15)->withQueryString();

        return view('purchase-orders.index', compact('purchaseOrders'));
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'user', 'items.product.unit', 'goodsReceipts.user']);

        return view('purchase-orders.show', compact('purchaseOrder'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('unit')->orderBy('name')->get();
        $poNumber  = $this->generatePoNumber();

        $productsJson = $products->map(function ($p) {
            return [
                'id'     => $p->id,
                'name'   => $p->name,
                'code'   => $p->code,
                'symbol' => $p->unit->symbol,
            ];
        })->values();

        return view('purchase-orders.create', compact('suppliers', 'products', 'poNumber', 'productsJson'));
    }

    public function store(StorePurchaseOrderRequest $request)
    {
        DB::transaction(function () use ($request) {
            // Hitung total
            $totalAmount = 0;
            $items = [];

            foreach ($request->items as $item) {
                $unitCost  = (float) str_replace(['.', ','], ['', '.'], $item['unit_cost']);
                $qty       = (float) $item['qty_ordered'];
                $discount  = (float) ($item['discount_percent'] ?? 0);
                $subtotal  = $qty * $unitCost * (1 - $discount / 100);

                $items[] = [
                    'product_id'       => $item['product_id'],
                    'qty_ordered'      => $qty,
                    'qty_received'     => 0,
                    'unit_cost'        => $unitCost,
                    'discount_percent' => $discount,
                    'subtotal'         => round($subtotal, 2),
                ];

                $totalAmount += $subtotal;
            }

            $po = PurchaseOrder::create([
                'supplier_id'     => $request->supplier_id,
                'user_id'         => auth()->id(),
                'po_number'       => $this->generatePoNumber(),
                'po_date'         => $request->po_date,
                'expected_date'   => $request->expected_date,
                'status'          => PurchaseOrder::STATUS_DRAFT,
                'total_amount'    => round($totalAmount, 2),
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'grand_total'     => round($totalAmount, 2),
                'notes'           => $request->notes,
            ]);

            $po->items()->createMany($items);
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dibuat.');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'PO dengan status "' . $purchaseOrder->status . '" tidak dapat diedit.');
        }

        $purchaseOrder->load('items.product.unit');
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('unit')->orderBy('name')->get();

        $productsJson = $products->map(function ($p) {
            return [
                'id'     => $p->id,
                'name'   => $p->name,
                'code'   => $p->code,
                'symbol' => $p->unit->symbol,
            ];
        })->values();

        $existingItemsJson = $purchaseOrder->items->map(function ($i) {
            return [
                'product_id'       => $i->product_id,
                'qty_ordered'      => $i->qty_ordered,
                'unit_cost'        => number_format($i->unit_cost, 0, ',', '.'),
                'discount_percent' => $i->discount_percent,
                'symbol'           => $i->product->unit->symbol,
            ];
        })->values();

        return view('purchase-orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'products',
            'productsJson',
            'existingItemsJson'
        ));
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'PO ini tidak dapat diedit.');
        }

        DB::transaction(function () use ($request, $purchaseOrder) {
            $totalAmount = 0;
            $items = [];

            foreach ($request->items as $item) {
                $unitCost  = (float) str_replace(['.', ','], ['', '.'], $item['unit_cost']);
                $qty       = (float) $item['qty_ordered'];
                $discount  = (float) ($item['discount_percent'] ?? 0);
                $subtotal  = $qty * $unitCost * (1 - $discount / 100);

                $items[] = [
                    'product_id'       => $item['product_id'],
                    'qty_ordered'      => $qty,
                    'qty_received'     => 0,
                    'unit_cost'        => $unitCost,
                    'discount_percent' => $discount,
                    'subtotal'         => round($subtotal, 2),
                ];

                $totalAmount += $subtotal;
            }

            $purchaseOrder->update([
                'supplier_id'   => $request->supplier_id,
                'po_date'       => $request->po_date,
                'expected_date' => $request->expected_date,
                'total_amount'  => round($totalAmount, 2),
                'grand_total'   => round($totalAmount, 2),
                'notes'         => $request->notes,
            ]);

            // Replace semua items
            $purchaseOrder->items()->delete();
            $purchaseOrder->items()->createMany($items);
        });

        return redirect()->route('purchase-orders.show', $purchaseOrder)
            ->with('success', 'Purchase Order berhasil diperbarui.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'PO yang sudah diproses tidak bisa dihapus.');
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Purchase Order berhasil dihapus.');
    }

    // Ubah status PO menjadi "sent" (sudah dikirim ke supplier)
    public function send(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== PurchaseOrder::STATUS_DRAFT) {
            return back()->with('error', 'Hanya PO berstatus draft yang bisa dikirim.');
        }

        $purchaseOrder->update(['status' => PurchaseOrder::STATUS_SENT]);

        return back()->with('success', 'PO berhasil ditandai sebagai terkirim ke supplier.');
    }

    private function generatePoNumber(): string
    {
        $prefix = 'PO-' . now()->format('Ymd') . '-';
        $last   = PurchaseOrder::where('po_number', 'like', $prefix . '%')
            ->orderByDesc('po_number')
            ->value('po_number');
        $next   = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
