<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockLot;
use App\Models\StockMovement;
use App\Services\FifoService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class InventoryController extends Controller
{
    public function __construct(private FifoService $fifoService)
    {
        $this->middleware('can:inventory.view');
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])->active();

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->input('low_stock')) {
            $query->whereRaw('
                (SELECT COALESCE(SUM(qty_remaining), 0)
                 FROM stock_lots
                 WHERE stock_lots.product_id = products.id
                 AND stock_lots.is_exhausted = 0
                 AND stock_lots.deleted_at IS NULL
                ) <= products.min_stock
            ');
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $productIds = $products->pluck('id');

        // Satu query untuk stok + lot count + nilai — tidak ada N+1
        $stockSummary = StockLot::selectRaw('
                product_id,
                SUM(qty_remaining)              AS total_stock,
                COUNT(*)                         AS lot_count,
                SUM(qty_remaining * unit_cost)   AS total_value
            ')
            ->where('is_exhausted', false)
            ->whereNull('deleted_at')
            ->whereIn('product_id', $productIds)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        $categories = Category::active()->orderBy('name')->get();

        $stats = [
            'total_products'  => Product::active()->count(),
            'total_lots'      => StockLot::where('is_exhausted', false)
                ->whereNull('deleted_at')
                ->count(),
            'low_stock_count' => Product::active()->whereRaw('
                (SELECT COALESCE(SUM(qty_remaining), 0)
                 FROM stock_lots
                 WHERE stock_lots.product_id = products.id
                 AND stock_lots.is_exhausted = 0
                 AND stock_lots.deleted_at IS NULL
                ) <= products.min_stock
            ')->count(),
            'inventory_value' => StockLot::where('is_exhausted', false)
                ->whereNull('deleted_at')
                ->selectRaw('SUM(qty_remaining * unit_cost) as total')
                ->value('total') ?? 0,
        ];

        return view('inventory.index', compact('products', 'stockSummary', 'categories', 'stats'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit']);

        $activeLots = StockLot::forProduct($product->id)
            ->available()
            ->with('goodsReceipt.purchaseOrder')
            ->get();

        $exhaustedLots = StockLot::forProduct($product->id)
            ->where('is_exhausted', true)
            ->latest('received_date')
            ->take(10)
            ->get();

        $movements = StockMovement::byProduct($product->id)
            ->with(['user', 'stockLot'])
            ->latest('movement_date')
            ->paginate(15);

        $totalStock = (float) $activeLots->sum('qty_remaining');
        $totalValue = $activeLots->sum(fn($l) => $l->qty_remaining * $l->unit_cost);
        $avgCost    = $totalStock > 0 ? $totalValue / $totalStock : 0;

        return view('inventory.show', compact(
            'product',
            'activeLots',
            'exhaustedLots',
            'movements',
            'totalStock',
            'totalValue',
            'avgCost'
        ));
    }

    public function stockCard(Product $product, Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->format('Y-m-d'));
        $to   = $request->input('to', now()->format('Y-m-d'));

        $movements = StockMovement::byProduct($product->id)
            ->with(['user', 'stockLot'])
            ->whereBetween('movement_date', [
                $from . ' 00:00:00',
                $to   . ' 23:59:59',
            ])
            ->oldest('movement_date')
            ->get();

        $product->load('unit');

        return view('inventory.stock-card', compact('product', 'movements', 'from', 'to'));
    }
}
