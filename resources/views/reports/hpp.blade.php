@extends('layouts.app')
@section('title', 'Laporan HPP')
@section('page-title', 'Laporan HPP')

@section('content')
    <div class="space-y-5">

        {{-- Filter --}}
        <form method="GET" action="{{ route('reports.hpp') }}"
            class="bg-white rounded-xl border border-gray-200 p-4 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                    class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                    class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white
                       text-sm font-medium rounded-lg transition">
                Tampilkan
            </button>
            {{-- Shortcut periode --}}
            <div class="flex gap-2 ml-auto flex-wrap">
                @php
                    $shortcuts = [
                        'Hari Ini' => [now()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Minggu Ini' => [now()->startOfWeek()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Bulan Ini' => [now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d')],
                        'Bulan Lalu' => [
                            now()->subMonth()->startOfMonth()->format('Y-m-d'),
                            now()->subMonth()->endOfMonth()->format('Y-m-d'),
                        ],
                    ];
                @endphp
                @foreach ($shortcuts as $label => [$f, $t])
                    <a href="{{ route('reports.hpp', ['from' => $f, 'to' => $t]) }}"
                        class="px-3 py-2 text-xs rounded-lg border transition
                      {{ $from === $f && $to === $t
                          ? 'bg-blue-50 border-blue-300 text-blue-700 font-medium'
                          : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Kartu ringkasan --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Penjualan</p>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($totals['revenue'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total HPP</p>
                <p class="text-xl font-bold text-orange-600">
                    Rp {{ number_format($totals['hpp'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Laba Kotor</p>
                <p class="text-xl font-bold {{ $totals['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($totals['profit'], 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Margin Kotor</p>
                <p class="text-3xl font-bold {{ $totals['margin'] >= 20 ? 'text-green-600' : 'text-orange-500' }}">
                    {{ number_format($totals['margin'], 1) }}%
                </p>
            </div>
        </div>

        {{-- Chart laba kotor --}}
        @if ($chartDays->count() > 1)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-800 text-sm mb-4">Tren Penjualan vs HPP vs Laba</h3>
                <canvas id="hpp-chart" height="80"></canvas>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Tabel HPP per produk --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800 text-sm">HPP per Produk</h3>
                    <span class="text-xs text-gray-400">{{ $hppByProduct->count() }} produk</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Terjual</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">HPP</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Laba</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Margin</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($hppByProduct as $i => $row)
                                @php
                                    $margin =
                                        $row->total_revenue > 0
                                            ? round(($row->total_profit / $row->total_revenue) * 100, 1)
                                            : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 text-sm">
                                            {{ $row->product->name }}
                                        </p>
                                        <p class="text-xs text-gray-400 font-mono">
                                            {{ $row->product->code }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-gray-600">
                                        {{ number_format($row->total_qty, 0) }}
                                        <span class="text-xs text-gray-400">
                                            {{ $row->product->unit->symbol }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm font-semibold text-gray-900">
                                        Rp {{ number_format($row->total_revenue, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm text-orange-600">
                                        Rp {{ number_format($row->total_hpp, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right text-sm font-bold
                                       {{ $row->total_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($row->total_profit, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                             {{ $margin >= 20
                                                 ? 'bg-green-100 text-green-700'
                                                 : ($margin >= 10
                                                     ? 'bg-yellow-100 text-yellow-700'
                                                     : 'bg-red-100 text-red-600') }}">
                                            {{ number_format($margin, 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-10 text-center text-sm text-gray-400">
                                        Tidak ada data HPP pada periode ini
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($hppByProduct->count() > 0)
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                                <tr>
                                    <td colspan="3"
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">
                                        Total
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900">
                                        Rp {{ number_format($totals['revenue'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-orange-600">
                                        Rp {{ number_format($totals['hpp'], 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-bold
                                       {{ $totals['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        Rp {{ number_format($totals['profit'], 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-700">
                                        {{ number_format($totals['margin'], 1) }}%
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- HPP per kategori --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">Per Kategori</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse($hppByCategory as $catName => $row)
                        @php
                            $margin = $row['revenue'] > 0 ? round(($row['profit'] / $row['revenue']) * 100, 1) : 0;
                        @endphp
                        <div class="px-5 py-3.5">
                            <div class="flex items-center justify-between mb-1">
                                <p class="text-sm font-medium text-gray-800">{{ $catName ?? 'Tanpa Kategori' }}</p>
                                <span
                                    class="text-xs font-semibold {{ $margin >= 20 ? 'text-green-600' : 'text-orange-500' }}">
                                    {{ number_format($margin, 1) }}%
                                </span>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mb-1.5">
                                <span>Revenue: Rp {{ number_format($row['revenue'], 0, ',', '.') }}</span>
                                <span>Laba: Rp {{ number_format($row['profit'], 0, ',', '.') }}</span>
                            </div>
                            {{-- Progress bar margin --}}
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $margin >= 20 ? 'bg-green-400' : ($margin >= 10 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                    style="width: {{ min(100, $margin * 2) }}%">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">
                            Tidak ada data
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @if ($chartDays->count() > 1)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            const ctx = document.getElementById('hpp-chart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartDays),
                    datasets: [{
                            label: 'Penjualan',
                            data: @json($chartRevenue),
                            backgroundColor: 'rgba(59,130,246,0.7)',
                            borderRadius: 4,
                            order: 2,
                        },
                        {
                            label: 'HPP',
                            data: @json($chartHpp),
                            backgroundColor: 'rgba(249,115,22,0.7)',
                            borderRadius: 4,
                            order: 2,
                        },
                        {
                            label: 'Laba Kotor',
                            data: @json($chartProfit),
                            type: 'line',
                            borderColor: '#16a34a',
                            backgroundColor: 'rgba(22,163,74,0.1)',
                            borderWidth: 2,
                            pointRadius: 3,
                            fill: true,
                            tension: 0.3,
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const val = ctx.raw ?? 0;
                                    return ` ${ctx.dataset.label}: Rp ${parseInt(val).toLocaleString('id-ID')}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: val => 'Rp ' + parseInt(val).toLocaleString('id-ID')
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
