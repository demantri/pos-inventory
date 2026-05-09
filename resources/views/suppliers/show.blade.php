@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title', 'Detail Supplier')

@section('content')
<div class="max-w-3xl space-y-5">

    {{-- Info card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-900">{{ $supplier->name }}</h3>
                <span class="font-mono text-xs text-gray-500">{{ $supplier->code }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                             {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $supplier->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                    {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
                @can('suppliers.edit')
                <a href="{{ route('suppliers.edit', $supplier) }}"
                   class="px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs rounded-lg transition">
                    Edit
                </a>
                @endcan
            </div>
        </div>
        <div class="px-6 py-5 grid grid-cols-2 gap-x-8 gap-y-4">
            @foreach([
                'Contact Person' => $supplier->contact_person,
                'Telepon'        => $supplier->phone,
                'Email'          => $supplier->email,
                'Kota'           => $supplier->city,
                'NPWP'           => $supplier->npwp,
            ] as $label => $value)
            <div>
                <p class="text-xs text-gray-400 mb-0.5">{{ $label }}</p>
                <p class="text-sm text-gray-800">{{ $value ?: '—' }}</p>
            </div>
            @endforeach
            <div class="col-span-2">
                <p class="text-xs text-gray-400 mb-0.5">Alamat</p>
                <p class="text-sm text-gray-800">{{ $supplier->address ?: '—' }}</p>
            </div>
            @if($supplier->notes)
            <div class="col-span-2">
                <p class="text-xs text-gray-400 mb-0.5">Catatan</p>
                <p class="text-sm text-gray-800">{{ $supplier->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Riwayat PO --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 text-sm">Riwayat Purchase Order</h3>
            <span class="text-xs text-gray-400">{{ $supplier->purchase_orders_count }} total PO</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($recentOrders as $po)
            <div class="px-6 py-3.5 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ $po->po_number }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $po->po_date->format('d M Y') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-900">
                        Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                    </p>
                    <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-0.5
                        {{ match($po->status) {
                            'received'  => 'bg-green-100 text-green-700',
                            'partial'   => 'bg-yellow-100 text-yellow-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        } }}">
                        {{ ucfirst($po->status) }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-sm text-gray-400">Belum ada purchase order</div>
            @endforelse
        </div>
    </div>

    <a href="{{ route('suppliers.index') }}"
       class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Kembali ke daftar supplier
    </a>
</div>
@endsection