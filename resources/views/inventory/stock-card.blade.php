@extends('layouts.app')
@section('title', 'Kartu Stok — ' . $product->name)
@section('page-title', 'Kartu Stok')

@section('content')
    <div class="max-w-5xl space-y-5">

        {{-- Header + filter periode --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $product->code }}</p>
                </div>
                <form method="GET" action="{{ route('inventory.stock-card', $product) }}" class="flex items-center gap-2">
                    <input type="date" name="from" value="{{ $from }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <span class="text-gray-400 text-sm">—</span>
                    <input type="date" name="to" value="{{ $to }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit"
                        class="px-4 py-2 bg-gray-800 hover:bg-gray-900 text-white text-sm rounded-lg transition">
                        Tampilkan
                    </button>
                </form>
            </div>
        </div>

        {{-- Tabel kartu stok --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Keterangan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                            No. Lot</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-green-600">Masuk
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase text-red-500">Keluar
                        </th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Saldo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Harga/unit</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">
                            Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($movements as $mov)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                {{ $mov->movement_date->format('d M Y') }}
                                <p class="text-xs text-gray-400">{{ $mov->movement_date->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded text-xs font-medium
                                     {{ $mov->isIncoming() ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $mov->movement_type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="font-mono text-xs text-gray-500">
                                    {{ $mov->stockLot?->lot_number ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($mov->isIncoming())
                                    <span class="font-semibold text-green-600">
                                        +{{ number_format($mov->qty, 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if (!$mov->isIncoming())
                                    <span class="font-semibold text-red-500">
                                        -{{ number_format($mov->qty, 0) }}
                                    </span>
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-gray-900">
                                {{ number_format($mov->running_stock, 0) }}
                                <span class="text-xs font-normal text-gray-400">{{ $product->unit->symbol }}</span>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500 hidden lg:table-cell">
                                Rp {{ number_format($mov->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs hidden lg:table-cell">
                                {{ $mov->user->name }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-sm text-gray-400">
                                Tidak ada mutasi stok pada periode ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('inventory.show', $product) }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke detail lot
        </a>
    </div>
@endsection
