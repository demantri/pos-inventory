@extends('layouts.app')
@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')
    <div class="space-y-5">

        {{-- Filter --}}
        <form method="GET" action="{{ route('reports.sales') }}"
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
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Metode Bayar</label>
                <select name="payment_method"
                    class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua</option>
                    @foreach (['cash' => 'Tunai', 'qris' => 'QRIS', 'transfer' => 'Transfer', 'debit_card' => 'Kartu Debit', 'credit_card' => 'Kartu Kredit'] as $val => $label)
                        <option value="{{ $val }}" {{ request('payment_method') === $val ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 relative min-w-48">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari no. transaksi atau customer..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white
                       text-sm font-medium rounded-lg transition">
                Tampilkan
            </button>
            {{-- Shortcut periode --}}
            <div class="flex gap-2 flex-wrap">
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
                    <a href="{{ route('reports.sales', ['from' => $f, 'to' => $t]) }}"
                        class="px-3 py-2 text-xs rounded-lg border transition
                      {{ $from === $f && $to === $t
                          ? 'bg-blue-50 border-blue-300 text-blue-700 font-medium'
                          : 'border-gray-200 text-gray-500 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Transaksi</p>
                <p class="text-3xl font-bold text-gray-900">
                    {{ number_format($stats->total_transactions ?? 0) }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total Penjualan</p>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($stats->total_revenue ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Total HPP</p>
                <p class="text-xl font-bold text-orange-600">
                    Rp {{ number_format($stats->total_hpp ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Laba Kotor</p>
                <p class="text-xl font-bold text-green-600">
                    Rp {{ number_format($stats->total_profit ?? 0, 0, ',', '.') }}
                </p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Rata-rata Transaksi</p>
                <p class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($stats->avg_transaction ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>

        {{-- Chart + Metode Bayar --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Chart tren --}}
            @if ($chartDays->count() > 1)
                <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="font-semibold text-gray-800 text-sm mb-4">Tren Penjualan Harian</h3>
                    <canvas id="sales-chart" height="100"></canvas>
                </div>
            @endif

            {{-- Per metode bayar --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">Per Metode Pembayaran</h3>
                </div>
                @php
                    $paymentLabels = [
                        'cash' => 'Tunai',
                        'qris' => 'QRIS',
                        'transfer' => 'Transfer',
                        'debit_card' => 'Kartu Debit',
                        'credit_card' => 'Kartu Kredit',
                        'mixed' => 'Mixed',
                    ];
                    $totalRevenue = $byPayment->sum('revenue');
                @endphp
                <div class="divide-y divide-gray-50">
                    @forelse($byPayment->sortByDesc('revenue') as $row)
                        @php $pct = $totalRevenue > 0 ? round($row->revenue / $totalRevenue * 100, 1) : 0; @endphp
                        <div class="px-5 py-3.5">
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $paymentLabels[$row->payment_method] ?? $row->payment_method }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $row->total_trx }} trx</span>
                            </div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-semibold text-gray-700">
                                    Rp {{ number_format($row->revenue, 0, ',', '.') }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $pct }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-400 rounded-full" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada data</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Tabel transaksi --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Daftar Transaksi</h3>
                <span class="text-xs text-gray-400">{{ $sales->total() }} transaksi</span>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">No. Transaksi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                            Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Customer</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Kasir</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Bayar</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            HPP</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Laba</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3.5">
                                <p class="font-mono text-sm font-semibold text-gray-900">
                                    {{ $sale->sale_number }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell text-sm text-gray-600">
                                <p>{{ $sale->sale_date->format('d M Y') }}</p>
                                <p class="text-xs text-gray-400">{{ $sale->sale_date->format('H:i') }}</p>
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell text-sm text-gray-600">
                                {{ $sale->customer?->name ?? 'Umum' }}
                            </td>
                            <td class="px-5 py-3.5 hidden lg:table-cell text-sm text-gray-600">
                                {{ $sale->user->name }}
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                @php
                                    $pmColors = [
                                        'cash' => 'bg-green-100 text-green-700',
                                        'qris' => 'bg-purple-100 text-purple-700',
                                        'transfer' => 'bg-blue-100 text-blue-700',
                                        'debit_card' => 'bg-yellow-100 text-yellow-700',
                                        'credit_card' => 'bg-orange-100 text-orange-700',
                                    ];
                                    $pmLabels = [
                                        'cash' => 'Tunai',
                                        'qris' => 'QRIS',
                                        'transfer' => 'Transfer',
                                        'debit_card' => 'Debit',
                                        'credit_card' => 'Kredit',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium
                                     {{ $pmColors[$sale->payment_method] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $pmLabels[$sale->payment_method] ?? $sale->payment_method }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right font-bold text-gray-900">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-sm text-orange-600 hidden lg:table-cell">
                                Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}
                            </td>
                            <td
                                class="px-5 py-3.5 text-right text-sm font-semibold hidden lg:table-cell
                               {{ $sale->gross_profit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('reports.sale-detail', $sale) }}"
                                    class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50
                                  rounded-lg transition inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943
                                             9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-sm text-gray-400">
                                Tidak ada transaksi pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if ($sales->hasPages())
                <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $sales->links() }}</div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    @if ($chartDays->count() > 1)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            new Chart(document.getElementById('sales-chart').getContext('2d'), {
                type: 'bar',
                data: {
                    labels: @json($chartDays),
                    datasets: [{
                            label: 'Revenue',
                            data: @json($chartRevenue),
                            backgroundColor: 'rgba(59,130,246,0.7)',
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Jumlah Transaksi',
                            data: @json($chartTrx),
                            type: 'line',
                            borderColor: '#f59e0b',
                            backgroundColor: 'rgba(245,158,11,0.1)',
                            borderWidth: 2,
                            pointRadius: 3,
                            fill: false,
                            tension: 0.3,
                            yAxisID: 'y2',
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
                                    if (ctx.dataset.yAxisID === 'y2') {
                                        return ` ${ctx.dataset.label}: ${ctx.raw} trx`;
                                    }
                                    return ` ${ctx.dataset.label}: Rp ${parseInt(ctx.raw).toLocaleString('id-ID')}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            position: 'left',
                            ticks: {
                                callback: val => 'Rp ' + parseInt(val).toLocaleString('id-ID')
                            }
                        },
                        y2: {
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            },
                            ticks: {
                                callback: val => val + ' trx'
                            }
                        }
                    }
                }
            });
        </script>
    @endif
@endpush
