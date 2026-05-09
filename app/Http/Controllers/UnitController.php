<?php

namespace App\Http\Controllers;

use App\Http\Requests\Unit\StoreUnitRequest;
use App\Http\Requests\Unit\UpdateUnitRequest;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:units.view')->only(['index']);
        $this->middleware('can:units.create')->only(['create', 'store']);
        $this->middleware('can:units.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:units.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Unit::withCount('products');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $units = $query->latest()->paginate(15)->withQueryString();

        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(StoreUnitRequest $request)
    {
        Unit::create([
            'name'      => $request->name,
            'symbol'    => strtolower($request->symbol),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('units.index')
            ->with('success', "Satuan \"{$request->name}\" berhasil ditambahkan.");
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(UpdateUnitRequest $request, Unit $unit)
    {
        $unit->update([
            'name'      => $request->name,
            'symbol'    => strtolower($request->symbol),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('units.index')
            ->with('success', "Satuan \"{$unit->name}\" berhasil diperbarui.");
    }

    public function destroy(Unit $unit)
    {
        if ($unit->products()->whereNull('deleted_at')->exists()) {
            return back()->with('error',
                "Satuan \"{$unit->name}\" tidak bisa dihapus karena masih digunakan produk.");
        }

        $unit->delete();

        return redirect()->route('units.index')
            ->with('success', "Satuan \"{$unit->name}\" berhasil dihapus.");
    }

    public function toggleStatus(Unit $unit)
    {
        $unit->update(['is_active' => !$unit->is_active]);
        $status = $unit->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Satuan \"{$unit->name}\" berhasil {$status}.");
    }
}