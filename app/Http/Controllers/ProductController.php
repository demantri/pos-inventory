<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:products.view')->only(['index', 'show']);
        $this->middleware('can:products.create')->only(['create', 'store']);
        $this->middleware('can:products.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:products.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Product::with(['category', 'unit'])
            ->withCount('stockLots');

        if ($search = $request->input('search')) {
            $query->search($search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        if ($request->input('low_stock')) {
            // Filter produk dengan stok di bawah minimum
            $query->whereHas('stockLots', function ($q) {
                $q->where('is_exhausted', false);
            }, '<', 1)->orWhereColumn(
                DB::raw('(SELECT COALESCE(SUM(qty_remaining),0) FROM stock_lots WHERE product_id = products.id AND is_exhausted = 0)'),
                '<=',
                'min_stock'
            );
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::active()->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'unit']);
        $activeLots  = $product->stockLots()->available()->get();
        $totalStock  = $activeLots->sum('qty_remaining');

        $recentMovements = $product->stockMovements()
            ->with('user')
            ->latest('movement_date')
            ->take(10)
            ->get();

        return view('products.show', compact('product', 'activeLots', 'totalStock', 'recentMovements'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();
        $units      = Unit::active()->orderBy('name')->get();
        $code       = $this->generateCode();

        return view('products.create', compact('categories', 'units', 'code'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sale_price'] = str_replace(['.', ','], ['', '.'], $request->sale_price);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$request->name}\" berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        $categories = Category::active()->orderBy('name')->get();
        $units      = Unit::active()->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['is_active']  = $request->boolean('is_active', true);
        $data['sale_price'] = str_replace(['.', ','], ['', '.'], $request->sale_price);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        if ($product->stockLots()->where('qty_remaining', '>', 0)->exists()) {
            return back()->with('error',
                "Produk \"{$product->name}\" tidak bisa dihapus karena masih memiliki stok.");
        }

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', "Produk \"{$product->name}\" berhasil dihapus.");
    }

    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Produk \"{$product->name}\" berhasil {$status}.");
    }

    private function generateCode(): string
    {
        $last = Product::withTrashed()->orderByDesc('id')->value('code');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'PRD' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}