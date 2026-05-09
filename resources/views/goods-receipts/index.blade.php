@extends('layouts.app')
@section('title', 'Penerimaan Barang')
@section('page-title', 'Penerimaan Barang (GR)')

@section('content')
    <div class="space-y-4">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p class="text-sm text-gray-500">
                Total <span class="font-semibold text-gray-800">{{ $goodsReceipts->total() }}</span> GR
            </p>
            @can('goods_receipts.create')
                <a href="{{ route('goods-receipts.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                  text-sm font-medium px-4 py-2.5 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Terima Barang
                </a>
            @endcan
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('goods-receipts.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari no. GR, invoice, atau supplier..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Status</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
                <a href="{{ route('goods-receipts.index') }}"
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">No. GR
                        </th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Supplier</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            No. PO</th>
                        <th
                            class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Tgl Terima</th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            No. Invoice</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Item
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($goodsReceipts as $gr)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-4">
                                <p class="font-mono text-sm font-semibold text-gray-900">{{ $gr->gr_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ $gr->user->name }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $gr->purchaseOrder->supplier->name }}</p>
                            </td>
                            <td class="px-5 py-4 hidden md:table-cell">
                                <span class="font-mono text-xs text-gray-600">
                                    {{ $gr->purchaseOrder->po_number }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center text-sm text-gray-600 hidden lg:table-cell">
                                {{ $gr->receipt_date->format('d M Y') }}
                            </td>
                            <td class="px-5 py-4 hidden lg:table-cell">
                                <span class="text-sm text-gray-600">
                                    {{ $gr->invoice_number ?: '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-green-50 text-green-700 text-xs font-semibold">
                                    {{ $gr->items_count }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span
                                    class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium
                                     {{ $gr->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                    {{ ucfirst($gr->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('goods-receipts.show', $gr) }}"
                                    class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <p class="text-sm text-gray-400">Belum ada penerimaan barang</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($goodsReceipts->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $goodsReceipts->links() }}</div>
            @endif
        </div>
    </div>
@endsection
