@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- ── Kartu ringkasan hari ini ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Total Transaksi --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-500">Transaksi Hari Ini</p>
                <span class="w-9 h-9 bg-blue-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ $summary['total_transactions'] }}</p>
            <p class="text-xs text-gray-400 mt-1">transaksi selesai</p>
        </div>

        {{-- Total Penjualan --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-500">Penjualan Hari Ini</p>
                <span class="w-9 h-9 bg-green-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">total pendapatan</p>
        </div>

        {{-- Total HPP --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-500">HPP Hari Ini</p>
                <span class="w-9 h-9 bg-orange-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">harga pokok penjualan</p>
        </div>

        {{-- Laba Kotor --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-sm text-gray-500">Laba Kotor Hari Ini</p>
                <span class="w-9 h-9 bg-purple-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold {{ $summary['gross_profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">margin {{ $summary['avg_margin_percent'] }}%</p>
        </div>
    </div>

    {{-- ── Baris kedua ── --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Transaksi terakhir --}}
        <div class="xl:col-span-2 bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Transaksi Terakhir</h3>
                <a href="#" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentSales as $sale)
                <div class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $sale->sale_number }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ $sale->user->name }} •
                            {{ $sale->sale_date->format('H:i') }} •
                            {{ $sale->customer->name ?? 'Umum' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-gray-900">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </p>
                        <span class="inline-block text-xs px-2 py-0.5 rounded-full mt-0.5
                            {{ $sale->payment_method === 'cash' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ strtoupper($sale->payment_method) }}
                        </span>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-sm text-gray-400">
                    Belum ada transaksi hari ini
                </div>
                @endforelse
            </div>
        </div>

        {{-- Stok menipis --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Stok Menipis</h3>
                @if($lowStockProducts->count() > 0)
                <span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full font-medium">
                    {{ $lowStockProducts->count() }} produk
                </span>
                @endif
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockProducts as $product)
                <div class="px-5 py-3.5 hover:bg-gray-50 transition">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</p>
                    <div class="flex items-center justify-between mt-1">
                        <span class="text-xs text-gray-400">{{ $product->code }}</span>
                        <span class="text-xs font-semibold text-red-600">
                            {{ number_format($product->current_stock, 0) }} {{ $product->unit->symbol }}
                        </span>
                    </div>
                    {{-- Progress bar stok --}}
                    @php
                        $pct = $product->min_stock > 0
                            ? min(100, ($product->current_stock / $product->min_stock) * 100)
                            : 0;
                    @endphp
                    <div class="mt-1.5 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $pct < 50 ? 'bg-red-400' : 'bg-yellow-400' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-8 text-center">
                    <svg class="w-8 h-8 text-green-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-400">Semua stok aman</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
