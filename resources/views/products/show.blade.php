@extends('layouts.app')
@section('title', $product->name)
@section('page-title', 'Detail Produk')

@section('content')
    <div class="max-w-3xl space-y-5">

        {{-- Info produk --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $product->name }}</h3>
                    <p class="text-xs font-mono text-gray-400 mt-0.5">{{ $product->code }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                             {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @can('products.edit')
                        <a href="{{ route('products.edit', $product) }}"
                            class="px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs rounded-lg transition">
                            Edit
                        </a>
                    @endcan
                </div>
            </div>

            <div class="flex gap-6 p-6">
                {{-- Gambar --}}
                <div class="flex-shrink-0">
                    @if ($product->image)
                        <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                            class="w-28 h-28 object-cover rounded-xl border border-gray-100">
                    @else
                        <div class="w-28 h-28 rounded-xl bg-gray-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Detail --}}
                <div class="flex-1 grid grid-cols-2 gap-x-8 gap-y-3">
                    <div>
                        <p class="text-xs text-gray-400">Kategori</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $product->category->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Satuan</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ $product->unit->name }} ({{ $product->unit->symbol }})
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Harga Jual</p>
                        <p class="text-sm font-semibold text-gray-900 mt-0.5">
                            Rp {{ number_format($product->sale_price, 0, ',', '.') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Stok Saat Ini</p>
                        <p
                            class="text-sm font-semibold mt-0.5 {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($totalStock, 0) }} {{ $product->unit->symbol }}
                            @if ($product->isLowStock())
                                <span class="text-xs font-normal text-red-500 ml-1">(menipis)</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Stok Minimum</p>
                        <p class="text-sm text-gray-800 mt-0.5">{{ number_format($product->min_stock, 0) }}
                            {{ $product->unit->symbol }}</p>
                    </div>
                    @if ($product->barcode)
                        <div>
                            <p class="text-xs text-gray-400">Barcode</p>
                            <p class="text-sm font-mono text-gray-800 mt-0.5">{{ $product->barcode }}</p>
                        </div>
                    @endif
                    @if ($product->description)
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400">Deskripsi</p>
                            <p class="text-sm text-gray-800 mt-0.5">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Lot FIFO aktif --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800 text-sm">Lot Stok Aktif (FIFO)</h3>
                <span class="text-xs text-gray-400">{{ $activeLots->count() }} lot tersedia</span>
            </div>
            @if ($activeLots->isEmpty())
                <div class="px-6 py-8 text-center text-sm text-gray-400">Tidak ada stok tersedia</div>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500">No. Lot</th>
                            <th class="px-5 py-2.5 text-left text-xs font-semibold text-gray-500">Tgl Masuk</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500">Harga Beli</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500">Sisa Qty</th>
                            <th class="px-5 py-2.5 text-right text-xs font-semibold text-gray-500">Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($activeLots as $i => $lot)
                            <tr class="{{ $i === 0 ? 'bg-amber-50' : '' }}">
                                <td class="px-5 py-3">
                                    <span class="font-mono text-xs">{{ $lot->lot_number }}</span>
                                    @if ($i === 0)
                                        <span class="ml-1.5 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">
                                            FIFO berikutnya
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-gray-600">{{ $lot->received_date->format('d M Y') }}</td>
                                <td class="px-5 py-3 text-right">Rp {{ number_format($lot->unit_cost, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-semibold">
                                    {{ number_format($lot->qty_remaining, 0) }}
                                </td>
                                <td class="px-5 py-3 text-right text-gray-600">
                                    Rp {{ number_format($lot->current_value, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Riwayat pergerakan stok --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Riwayat Mutasi Stok</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentMovements as $mov)
                    <div class="px-6 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span
                                class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0
                                 {{ $mov->isIncoming() ? 'bg-green-100' : 'bg-red-100' }}">
                                <svg class="w-3.5 h-3.5 {{ $mov->isIncoming() ? 'text-green-600' : 'text-red-600' }}"
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
                                    {{ $mov->movement_date->format('d M Y H:i') }} • {{ $mov->user->name }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold {{ $mov->isIncoming() ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mov->isIncoming() ? '+' : '-' }}{{ number_format($mov->qty, 0) }}
                            </p>
                            <p class="text-xs text-gray-400">Sisa: {{ number_format($mov->running_stock, 0) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-400">Belum ada mutasi stok</div>
                @endforelse
            </div>
        </div>

        <a href="{{ route('products.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar produk
        </a>
    </div>
@endsection
