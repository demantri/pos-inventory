@extends('layouts.app')
@section('title', 'Lot FIFO — ' . $product->name)
@section('page-title', 'Detail Stok & Lot FIFO')

@section('content')
    <div class="max-w-5xl space-y-5">

        {{-- Info produk + ringkasan --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $product->code }}</p>
                </div>
                <a href="{{ route('inventory.stock-card', $product) }}"
                    class="inline-flex items-center gap-2 text-sm text-purple-600 hover:text-purple-700 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Lihat Kartu Stok
                </a>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 border-b border-gray-100">
                <div class="px-6 py-4 text-center">
                    <p
                        class="text-2xl font-bold {{ $totalStock <= $product->min_stock ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($totalStock, 0) }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Stok Tersedia ({{ $product->unit->symbol }})</p>
                </div>
                <div class="px-6 py-4 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $activeLots->count() }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Lot Aktif</p>
                </div>
                <div class="px-6 py-4 text-center">
                    <p class="text-xl font-bold text-gray-900">
                        Rp {{ number_format($totalValue, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Nilai Persediaan</p>
                </div>
                <div class="px-6 py-4 text-center">
                    <p class="text-xl font-bold text-gray-900">
                        Rp {{ number_format($avgCost, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Rata-rata HPP/unit</p>
                </div>
            </div>
        </div>

        {{-- Antrian Lot FIFO --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <h3 class="font-semibold text-gray-800 text-sm">Antrian Lot FIFO (Aktif)</h3>
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">
                    Lot #1 digunakan pertama saat penjualan
                </span>
            </div>

            @if ($activeLots->isEmpty())
                <div class="px-6 py-10 text-center text-sm text-gray-400">
                    Tidak ada stok tersedia untuk produk ini
                </div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase w-16">Urutan</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Lot</th>
                            <th
                                class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                                Sumber (GR)</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tgl Masuk</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Beli</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty Awal</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sisa Qty</th>
                            <th
                                class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                                Nilai Sisa</th>
                            <th
                                class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                                Terpakai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($activeLots as $i => $lot)
                            @php
                                $usedPct =
                                    $lot->qty_initial > 0
                                        ? (($lot->qty_initial - $lot->qty_remaining) / $lot->qty_initial) * 100
                                        : 0;
                            @endphp
                            <tr class="{{ $i === 0 ? 'bg-amber-50' : 'hover:bg-gray-50' }} transition">
                                <td class="px-5 py-3.5 text-center">
                                    @if ($i === 0)
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-amber-400 text-white text-xs font-bold">
                                            NEXT
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-gray-100 text-gray-500 text-xs font-semibold">
                                            {{ $i + 1 }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <span
                                        class="font-mono text-xs font-semibold
                                     {{ $i === 0 ? 'text-amber-700' : 'text-gray-700' }}">
                                        {{ $lot->lot_number }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 hidden md:table-cell">
                                    @if ($lot->goodsReceipt)
                                        <div>
                                            <p class="text-xs font-mono text-gray-600">{{ $lot->goodsReceipt->gr_number }}
                                            </p>
                                            @if ($lot->goodsReceipt->purchaseOrder)
                                                <p class="text-xs text-gray-400 mt-0.5">
                                                    {{ $lot->goodsReceipt->purchaseOrder->po_number }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center text-sm text-gray-600">
                                    {{ $lot->received_date->format('d M Y') }}
                                    <p class="text-xs text-gray-400">{{ $lot->received_date->diffForHumans() }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <p class="font-semibold text-gray-900">
                                        Rp {{ number_format($lot->unit_cost, 0, ',', '.') }}
                                    </p>
                                </td>
                                <td class="px-5 py-3.5 text-right text-gray-500">
                                    {{ number_format($lot->qty_initial, 0) }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <p class="font-bold text-gray-900">{{ number_format($lot->qty_remaining, 0) }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->unit->symbol }}</p>
                                </td>
                                <td class="px-5 py-3.5 text-right hidden lg:table-cell text-sm font-semibold text-gray-700">
                                    Rp {{ number_format($lot->qty_remaining * $lot->unit_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3.5 hidden lg:table-cell">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full
                                            {{ $usedPct >= 90 ? 'bg-red-400' : ($usedPct >= 50 ? 'bg-yellow-400' : 'bg-green-400') }}"
                                                style="width: {{ $usedPct }}%">
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400 w-8">{{ number_format($usedPct, 0) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                        <tr>
                            <td colspan="6" class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase">
                                Total
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900">
                                {{ number_format($totalStock, 0) }} {{ $product->unit->symbol }}
                            </td>
                            <td class="px-5 py-3 text-right font-bold text-gray-900 hidden lg:table-cell">
                                Rp {{ number_format($totalValue, 0, ',', '.') }}
                            </td>
                            <td class="hidden lg:table-cell"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        </div>

        {{-- Histori lot habis --}}
        @if ($exhaustedLots->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm text-gray-500">
                        Lot Sudah Habis (10 Terakhir)
                    </h3>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-400 uppercase">No. Lot</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-400 uppercase">Tgl Masuk</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Harga Beli</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-400 uppercase">Qty Awal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($exhaustedLots as $lot)
                            <tr class="opacity-60">
                                <td class="px-5 py-3 font-mono text-xs text-gray-500">{{ $lot->lot_number }}</td>
                                <td class="px-5 py-3 text-center text-xs text-gray-500">
                                    {{ $lot->received_date->format('d M Y') }}
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-500">
                                    Rp {{ number_format($lot->unit_cost, 0, ',', '.') }}
                                </td>
                                <td class="px-5 py-3 text-right text-xs text-gray-500">
                                    {{ number_format($lot->qty_initial, 0) }} {{ $product->unit->symbol }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Mutasi stok terbaru --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Mutasi Stok Terbaru</h3>
                <a href="{{ route('inventory.stock-card', $product) }}" class="text-xs text-blue-600 hover:underline">
                    Lihat semua →
                </a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($movements as $mov)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                                 {{ $mov->isIncoming() ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="w-4 h-4 {{ $mov->isIncoming() ? 'text-green-600' : 'text-red-600' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="{{ $mov->isIncoming() ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $mov->movement_type)) }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $mov->movement_date->format('d M Y H:i') }} •
                                    {{ $mov->user->name }} •
                                    Lot: {{ $mov->stockLot?->lot_number ?? '—' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold {{ $mov->isIncoming() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mov->isIncoming() ? '+' : '-' }}{{ number_format($mov->qty, 0) }}
                                {{ $product->unit->symbol }}
                            </p>
                            <p class="text-xs text-gray-400">Sisa: {{ number_format($mov->running_stock, 0) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-400">Belum ada mutasi stok</div>
                @endforelse
            </div>
            @if ($movements->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $movements->links() }}</div>
            @endif
        </div>

        <a href="{{ route('inventory.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar stok
        </a>
    </div>
@endsection
