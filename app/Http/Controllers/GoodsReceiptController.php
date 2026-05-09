<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoodsReceipt\StoreGoodsReceiptRequest;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\PurchaseOrder;
use App\Services\FifoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class GoodsReceiptController extends Controller
{
    public function __construct(private FifoService $fifoService)
    {
        $this->middleware('can:goods_receipts.view')->only(['index', 'show']);
        $this->middleware('can:goods_receipts.create')->only(['create', 'store']);
        $this->middleware('can:goods_receipts.confirm')->only(['confirm']);
    }

    public function index(Request $request)
    {
        $query = GoodsReceipt::with(['purchaseOrder.supplier', 'user'])
            ->withCount('items');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('gr_number', 'like', "%{$search}%")
                    ->orWhere('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas(
                        'purchaseOrder.supplier',
                        fn($s) =>
                        $s->where('name', 'like', "%{$search}%")
                    );
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from')) {
            $query->whereDate('receipt_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('receipt_date', '<=', $request->input('to'));
        }

        $goodsReceipts = $query->latest('receipt_date')->paginate(15)->withQueryString();

        return view('goods-receipts.index', compact('goodsReceipts'));
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $goodsReceipt->load([
            'purchaseOrder.supplier',
            'user',
            'items.product.unit',
            'items.stockLot',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    public function create(Request $request)
    {
        // Bisa dibuka dari halaman PO (sudah ada ?po=id) atau pilih manual
        $purchaseOrder = null;
        $poItems       = collect();

        if ($request->filled('po')) {
            $purchaseOrder = PurchaseOrder::with('items.product.unit', 'supplier')
                ->whereIn('status', ['sent', 'partial'])
                ->findOrFail($request->input('po'));

            // Hanya tampilkan item yang belum fully received
            $poItems = $purchaseOrder->items
                ->filter(fn($i) => !$i->isFullyReceived())
                ->values();
        }

        // PO yang bisa di-GR (status sent atau partial)
        $availablePOs = PurchaseOrder::with('supplier')
            ->whereIn('status', ['sent', 'partial'])
            ->latest('po_date')
            ->get();

        $grNumber = $this->generateGrNumber();

        return view('goods-receipts.create', compact(
            'purchaseOrder',
            'poItems',
            'availablePOs',
            'grNumber'
        ));
    }

    public function store(StoreGoodsReceiptRequest $request)
    {
        DB::transaction(function () use ($request) {
            // 1. Buat header GR
            $gr = GoodsReceipt::create([
                'purchase_order_id' => $request->purchase_order_id,
                'user_id'           => auth()->id(),
                'gr_number'         => $this->generateGrNumber(),
                'receipt_date'      => $request->receipt_date,
                'invoice_number'    => $request->invoice_number,
                'invoice_date'      => $request->invoice_date,
                'status'            => 'confirmed',
                'notes'             => $request->notes,
            ]);

            // 2. Buat GR items (stock_lot_id masih null dulu)
            foreach ($request->items as $item) {
                GoodsReceiptItem::create([
                    'goods_receipt_id'       => $gr->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'product_id'             => $item['product_id'],
                    'qty_received'           => $item['qty_received'],
                    'unit_cost'              => str_replace(['.', ','], ['', '.'], $item['unit_cost']),
                    'subtotal'               => $item['qty_received'] * str_replace(['.', ','], ['', '.'], $item['unit_cost']),
                ]);
            }

            // 3. Reload items lalu jalankan FifoService
            // FifoService::processStockIn() akan:
            //   - Buat StockLot per item
            //   - Update stock_lot_id di GR item
            //   - Update qty_received di PO item
            //   - Catat StockMovement
            //   - Update status PO
            $gr->load('items');
            $this->fifoService->processStockIn($gr);
        });

        return redirect()->route('goods-receipts.index')
            ->with('success', 'Penerimaan barang berhasil dicatat dan stok telah diperbarui.');
    }

    private function generateGrNumber(): string
    {
        $prefix = 'GR-' . now()->format('Ymd') . '-';
        $last   = GoodsReceipt::where('gr_number', 'like', $prefix . '%')
            ->orderByDesc('gr_number')
            ->value('gr_number');
        $next   = $last ? (int) substr($last, -4) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
