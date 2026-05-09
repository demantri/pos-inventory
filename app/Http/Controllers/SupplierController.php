<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class SupplierController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:suppliers.view')->only(['index', 'show']);
        $this->middleware('can:suppliers.create')->only(['create', 'store']);
        $this->middleware('can:suppliers.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:suppliers.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function show(Supplier $supplier)
    {
        $supplier->loadCount('purchaseOrders');
        $recentOrders = $supplier->purchaseOrders()
            ->latest('po_date')
            ->take(5)
            ->get();

        return view('suppliers.show', compact('supplier', 'recentOrders'));
    }

    public function create()
    {
        $code = $this->generateCode();
        return view('suppliers.create', compact('code'));
    }

    public function store(StoreSupplierRequest $request)
    {
        Supplier::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$request->name}\" berhasil ditambahkan.");
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $supplier->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" berhasil diperbarui.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->with('error',
                "Supplier \"{$supplier->name}\" tidak bisa dihapus karena memiliki riwayat purchase order.");
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', "Supplier \"{$supplier->name}\" berhasil dihapus.");
    }

    public function toggleStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        $status = $supplier->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Supplier \"{$supplier->name}\" berhasil {$status}.");
    }

    private function generateCode(): string
    {
        $last = Supplier::withTrashed()->orderByDesc('id')->value('code');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'SUP' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}