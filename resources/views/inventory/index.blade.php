@extends('layouts.app')
@section('title', 'Stok & Lot FIFO')
@section('page-title', 'Stok & Lot FIFO')

@section('content')
    <div class="space-y-5">

        {{-- Statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-2">Total Produk Aktif</p>
                <p class="text-3xl font-bold text-gray-900">
                    {{ number_format($stats['total_products']) }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-2">Lot Aktif (FIFO)</p>
                <p class="text-3xl font-bold text-blue-600">
                    {{ number_format($stats['total_lots']) }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-2">Produk Stok Menipis</p>
                <p class="text-3xl font-bold {{ $stats['low_stock_count'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ number_format($stats['low_stock_count']) }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-2">Nilai Persediaan</p>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($stats['inventory_value'], 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Filter --}}
        <form method="GET" action="{{ route('inventory.index') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3">
            <div class="flex-1 min-w-48 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari kode atau nama produk..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <select name="category_id"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            <label
                class="flex items-center gap-2 px-4 py-2.5 border border-gray-300 rounded-lg
                      text-sm text-gray-600 cursor-pointer hover:bg-gray-50 select-none">
                <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                    class="w-4 h-4 text-red-600 rounded border-gray-300">
                Stok Menipis Saja
            </label>
            <button type="submit"
                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white
                       text-sm font-medium rounded-lg transition">
                Cari
            </button>
            @if (request()->hasAny(['search', 'category_id', 'low_stock']))
                <a href="{{ route('inventory.index') }}"
                    class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50
                  text-gray-600 text-sm rounded-lg transition">
                    Reset
                </a>
            @endif
        </form>

        {{-- Tabel --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Produk
                        </th>
                        <th
                            class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Kategori
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Lot Aktif
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Stok Tersedia
                        </th>
                        <th
                            class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Stok Min
                        </th>
                        <th
                            class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                            Nilai Stok
                        </th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Kondisi
                        </th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $product)
                        @php
                            $summary = $stockSummary[$product->id] ?? null;
                            $totalStock = $summary ? (float) $summary->total_stock : 0;
                            $lotCount = $summary ? (int) $summary->lot_count : 0;
                            $totalValue = $summary ? (float) $summary->total_value : 0;
                            $isLow = $totalStock <= (float) $product->min_stock;
                            $isEmpty = $totalStock <= 0;
                        @endphp
                        <tr
                            class="hover:bg-gray-50 transition {{ $isEmpty ? 'bg-red-50/40' : ($isLow ? 'bg-orange-50/40' : '') }}">

                            {{-- Produk --}}
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $product->code }}</p>
                            </td>

                            {{-- Kategori --}}
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs
                                     bg-blue-50 text-blue-700">
                                    {{ $product->category->name }}
                                </span>
                            </td>

                            {{-- Lot aktif --}}
                            <td class="px-5 py-3.5 text-center">
                                @if ($lotCount > 0)
                                    <span
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-green-50 text-green-700 text-xs font-semibold">
                                        {{ $lotCount }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Stok tersedia --}}
                            <td class="px-5 py-3.5 text-right">
                                <p
                                    class="font-bold text-lg {{ $isEmpty ? 'text-red-600' : ($isLow ? 'text-orange-500' : 'text-gray-900') }}">
                                    {{ number_format($totalStock, 0) }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $product->unit->symbol }}</p>
                            </td>

                            {{-- Stok minimum --}}
                            <td class="px-5 py-3.5 text-right hidden lg:table-cell text-sm text-gray-500">
                                {{ number_format($product->min_stock, 0) }}
                                <span class="text-xs text-gray-400">{{ $product->unit->symbol }}</span>
                            </td>

                            {{-- Nilai stok --}}
                            <td class="px-5 py-3.5 text-right hidden lg:table-cell">
                                @if ($totalValue > 0)
                                    <p class="text-sm font-semibold text-gray-700">
                                        Rp {{ number_format($totalValue, 0, ',', '.') }}
                                    </p>
                                @else
                                    <span class="text-gray-300 text-sm">—</span>
                                @endif
                            </td>

                            {{-- Kondisi --}}
                            <td class="px-5 py-3.5 text-center">
                                @if ($isEmpty)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                     text-xs font-medium bg-red-100 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                        Habis
                                    </span>
                                @elseif($isLow)
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                     text-xs font-medium bg-orange-100 text-orange-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                        Menipis
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                                     text-xs font-medium bg-green-100 text-green-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        Aman
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-3.5 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('inventory.show', $product) }}" title="Detail Lot FIFO"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50
                                      rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7
                                                 M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4
                                                 M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('inventory.stock-card', $product) }}" title="Kartu Stok"
                                        class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50
                                      rounded-lg transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5
                                                 a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414
                                                 a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
                                <svg class="w-10 h-10 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4
                                         m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                                <p class="text-sm text-gray-400">Tidak ada data produk</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($products->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
