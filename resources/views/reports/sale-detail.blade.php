@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('page-title', 'Detail Transaksi')

@section('content')
    <div class="max-w-4xl space-y-5">

        {{-- Header transaksi --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 font-mono">{{ $sale->sale_number }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $sale->sale_date->format('d M Y, H:i') }} •
                        Kasir: {{ $sale->user->name }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        Completed
                    </span>
                    <a href="{{ route('pos.receipt', $sale) }}" target="_blank"
                        class="px-4 py-2 border border-gray-300 hover:bg-gray-50
                          text-gray-700 text-xs rounded-lg transition">
                        Cetak Struk
                    </a>
                </div>
            </div>

            {{-- Info transaksi --}}
            <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-4">
                <div>
                    <p class="text-xs text-gray-400">Customer</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $sale->customer?->name ?? 'Umum' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Metode Pembayaran</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5 uppercase">
                        {{ $sale->payment_method }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Dibayar</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Kembalian</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        Rp {{ number_format($sale->change_amount, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Item + detail FIFO --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Item Terjual & Detail FIFO</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Jual</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">HPP</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Laba</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($sale->items as $i => $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $item->product->code }}</p>
                                {{-- Detail lot FIFO --}}
                                @if ($item->saleItemLots->isNotEmpty())
                                    <div class="mt-1.5 space-y-0.5">
                                        @foreach ($item->saleItemLots as $lot)
                                            <p class="text-xs text-gray-400">
                                                <span class="font-mono bg-gray-100 px-1.5 py-0.5 rounded text-gray-500">
                                                    {{ $lot->stockLot->lot_number }}
                                                </span>
                                                {{ number_format($lot->qty_taken, 0) }} {{ $item->product->unit->symbol }}
                                                × Rp {{ number_format($lot->unit_cost, 0, ',', '.') }}
                                                = Rp {{ number_format($lot->total_cost, 0, ',', '.') }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                {{ number_format($item->qty, 0) }}
                                <span class="text-xs text-gray-400">{{ $item->product->unit->symbol }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-gray-700">
                                Rp {{ number_format($item->sale_price, 0, ',', '.') }}
                                @if ($item->discount_percent > 0)
                                    <p class="text-xs text-red-400">-{{ $item->discount_percent }}%</p>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-orange-600">
                                Rp {{ number_format($item->hpp_amount, 0, ',', '.') }}
                                <p class="text-xs text-gray-400">
                                    @ Rp {{ number_format($item->hpp_per_unit, 0, ',', '.') }}/unit
                                </p>
                            </td>
                            <td
                                class="px-5 py-3.5 text-right font-semibold
                               {{ $item->gross_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($item->gross_profit, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-5 py-3 text-right text-xs font-semibold text-gray-600 uppercase">
                            Subtotal
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-gray-900">
                            Rp {{ number_format($sale->subtotal, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-orange-600">
                            Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-green-600">
                            Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}
                        </td>
                    </tr>
                    @if ($sale->discount_amount > 0)
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right text-xs text-gray-500">Diskon</td>
                            <td class="px-5 py-2 text-right text-sm text-red-500">
                                - Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                    @if ($sale->tax_amount > 0)
                        <tr>
                            <td colspan="4" class="px-5 py-2 text-right text-xs text-gray-500">
                                Pajak ({{ $sale->tax_percent }}%)
                            </td>
                            <td class="px-5 py-2 text-right text-sm text-gray-700">
                                Rp {{ number_format($sale->tax_amount, 0, ',', '.') }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    @endif
                    <tr class="border-t border-gray-200">
                        <td colspan="4" class="px-5 py-3 text-right font-bold text-gray-800 uppercase text-sm">
                            Grand Total
                        </td>
                        <td class="px-5 py-3 text-right font-bold text-gray-900 text-base">
                            Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Margin summary --}}
        <div class="grid grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-500 mb-1">Total Revenue</p>
                <p class="text-lg font-bold text-gray-900">
                    Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-orange-100 bg-orange-50 p-5 text-center">
                <p class="text-xs text-orange-500 mb-1">Total HPP</p>
                <p class="text-lg font-bold text-orange-600">
                    Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-green-100 bg-green-50 p-5 text-center">
                <p class="text-xs text-green-600 mb-1">Laba Kotor</p>
                <p class="text-lg font-bold text-green-700">
                    Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}
                </p>
                <p class="text-xs text-green-500 mt-0.5">
                    Margin:
                    @if ($sale->total_amount > 0)
                        {{ number_format(($sale->gross_profit / $sale->total_amount) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </p>
            </div>
        </div>

        <a href="{{ route('reports.sales') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke laporan penjualan
        </a>
    </div>
@endsection
