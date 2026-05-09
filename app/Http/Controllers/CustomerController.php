<?php

namespace App\Http\Controllers;

use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:customers.view')->only(['index', 'show']);
        $this->middleware('can:customers.create')->only(['create', 'store']);
        $this->middleware('can:customers.edit')->only(['edit', 'update', 'toggleStatus']);
        $this->middleware('can:customers.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Customer::withCount('sales');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $customers = $query->orderByDesc('total_transaction')->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function show(Customer $customer)
    {
        $customer->loadCount('sales');
        $recentSales = $customer->sales()
            ->with('user')
            ->latest('sale_date')
            ->take(5)
            ->get();

        return view('customers.show', compact('customer', 'recentSales'));
    }

    public function create()
    {
        $code = $this->generateCode();
        return view('customers.create', compact('code'));
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$request->name}\" berhasil ditambahkan.");
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil diperbarui.");
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->exists()) {
            return back()->with('error',
                "Customer \"{$customer->name}\" tidak bisa dihapus karena memiliki riwayat transaksi.");
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$customer->name}\" berhasil dihapus.");
    }

    public function toggleStatus(Customer $customer)
    {
        $customer->update(['is_active' => !$customer->is_active]);
        $status = $customer->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Customer \"{$customer->name}\" berhasil {$status}.");
    }

    private function generateCode(): string
    {
        $last = Customer::withTrashed()->orderByDesc('id')->value('code');
        $next = $last ? (int) substr($last, 3) + 1 : 1;
        return 'CST' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}