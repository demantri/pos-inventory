@extends('layouts.app')
@section('title', 'Purchase Order')
@section('page-title', 'Purchase Order')

@section('content')
    <div class="space-y-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500">
                Total <span class="font-semibold text-gray-800">{{ $purchaseOrders->total() }}</span> PO
            </p>
            @can('purchase_orders.create')
                <a href="{{ route('purchase-orders.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                  text-sm font-medium px-4 py-2.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat PO Baru
                </a>
            @endcan
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('purchase-orders.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari no. PO atau supplier..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                @foreach (['draft' => 'Draft', 'sent' => 'Terkirim', 'partial' => 'Partial', 'received' => 'Diterima', 'cancelled' => 'Dibatalkan'] as $val => $label)
                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>
                        {{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="from" value="{{ request('from') }}"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500">
            <input type="date" name="to" value="{{ request('to') }}"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                      focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit"
                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition">
                Cari
            </button>
            @if (request()->hasAny(['search', 'status', 'from', 'to']))
                <a href="{{ route('purchase-orders.index') }}"
                    class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. PO
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Supplier</th>
                        <th
                            class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Tgl PO</th>
                        <th
                            class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Est. Tiba</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Item
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4">
                                <p class="font-mono text-sm font-semibold text-gray-900">{{ $po->po_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $po->user->name }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $po->supplier->name }}</p>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $po->supplier->code }}</p>
                            </td>
                            <td class="px-5 py-4 text-center text-sm text-gray-600 hidden md:table-cell">
                                {{ $po->po_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-center hidden lg:table-cell">
                                @if ($po->expected_date)
                                    <span
                                        class="{{ $po->expected_date->isPast() && !in_array($po->status, ['received', 'cancelled']) ? 'text-red-500' : 'text-gray-600' }} text-sm">
                                        {{ $po->expected_date->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-blue-50 text-blue-700 text-xs font-semibold">
                                    {{ $po->items_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">
                                Rp {{ number_format($po->grand_total, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @php
                                    $statusConfig = [
                                        'draft' => 'bg-gray-100 text-gray-600',
                                        'sent' => 'bg-blue-100 text-blue-700',
                                        'partial' => 'bg-yellow-100 text-yellow-700',
                                        'received' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-600',
                                    ];
                                    $statusLabel = [
                                        'draft' => 'Draft',
                                        'sent' => 'Terkirim',
                                        'partial' => 'Partial',
                                        'received' => 'Diterima',
                                        'cancelled' => 'Dibatalkan',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                     {{ $statusConfig[$po->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusLabel[$po->status] ?? $po->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('purchase-orders.show', $po) }}"
                                        class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @can('purchase_orders.edit')
                                        @if ($po->isEditable())
                                            <a href="{{ route('purchase-orders.edit', $po) }}"
                                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </a>
                                        @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm text-gray-400">Belum ada Purchase Order</p>
                                @can('purchase_orders.create')
                                    <a href="{{ route('purchase-orders.create') }}"
                                        class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                                        + Buat PO pertama
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($purchaseOrders->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $purchaseOrders->links() }}</div>
            @endif
        </div>
    </div>
@endsection
