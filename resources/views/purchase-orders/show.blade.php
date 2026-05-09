@extends('layouts.app')
@section('title', $purchaseOrder->po_number)
@section('page-title', 'Detail Purchase Order')

@section('content')
    <div class="max-w-4xl space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 font-mono">{{ $purchaseOrder->po_number }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Dibuat oleh {{ $purchaseOrder->user->name }} •
                        {{ $purchaseOrder->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
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
                            'partial' => 'Partial Diterima',
                            'received' => 'Semua Diterima',
                            'cancelled' => 'Dibatalkan',
                        ];
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusConfig[$purchaseOrder->status] }}">
                        {{ $statusLabel[$purchaseOrder->status] }}
                    </span>

                    @can('purchase_orders.edit')
                        {{-- Tombol kirim ke supplier --}}
                        @if ($purchaseOrder->status === 'draft')
                            <form method="POST" action="{{ route('purchase-orders.send', $purchaseOrder) }}"
                                onsubmit="return confirm('Tandai PO ini sebagai sudah dikirim ke supplier?')">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium rounded-lg transition">
                                    Kirim ke Supplier
                                </button>
                            </form>
                        @endif

                        {{-- Tombol buat GR --}}
                        @if (in_array($purchaseOrder->status, ['sent', 'partial']))
                            <a href="{{ route('goods-receipts.create', ['po' => $purchaseOrder->id]) }}"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded-lg transition">
                                + Terima Barang (GR)
                            </a>
                        @endif

                        @if ($purchaseOrder->isEditable())
                            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                                class="px-4 py-2 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs rounded-lg transition">
                                Edit
                            </a>
                        @endif
                    @endcan
                </div>
            </div>

            {{-- Info PO --}}
            <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-4 gap-x-8 gap-y-4">
                <div>
                    <p class="text-xs text-gray-400">Supplier</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">{{ $purchaseOrder->supplier->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $purchaseOrder->supplier->code }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Tanggal PO</p>
                    <p class="text-sm text-gray-900 mt-0.5">{{ $purchaseOrder->po_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Estimasi Tiba</p>
                    <p
                        class="text-sm mt-0.5 {{ $purchaseOrder->expected_date?->isPast() && !in_array($purchaseOrder->status, ['received', 'cancelled']) ? 'text-red-500 font-medium' : 'text-gray-900' }}">
                        {{ $purchaseOrder->expected_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Grand Total</p>
                    <p class="text-base font-bold text-gray-900 mt-0.5">
                        Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}
                    </p>
                </div>
                @if ($purchaseOrder->notes)
                    <div class="col-span-2 sm:col-span-4">
                        <p class="text-xs text-gray-400">Catatan</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $purchaseOrder->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabel item --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Item Produk</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty Order</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Diterima</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Beli</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Diskon</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($purchaseOrder->items as $i => $item)
                        <tr>
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->product->code }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                {{ number_format($item->qty_ordered, 0) }}
                                <span class="text-xs text-gray-400">{{ $item->product->unit->symbol }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <span
                                    class="{{ $item->isFullyReceived() ? 'text-green-600' : ($item->qty_received > 0 ? 'text-yellow-600' : 'text-gray-400') }} font-medium">
                                    {{ number_format($item->qty_received, 0) }}
                                </span>
                                <span class="text-xs text-gray-400">{{ $item->product->unit->symbol }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-gray-500">
                                {{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '—' }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="6" class="px-5 py-3.5 text-right font-semibold text-gray-700">Grand Total</td>
                        <td class="px-5 py-3.5 text-right font-bold text-gray-900 text-base">
                            Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Riwayat GR --}}
        @if ($purchaseOrder->goodsReceipts->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800 text-sm">Riwayat Penerimaan Barang (GR)</h3>
                </div>
                <div class="divide-y divide-gray-50">
                    @foreach ($purchaseOrder->goodsReceipts as $gr)
                        <div class="px-6 py-3.5 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-mono font-semibold text-gray-900">{{ $gr->gr_number }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $gr->receipt_date->format('d M Y') }} • {{ $gr->user->name }}
                                    @if ($gr->invoice_number)
                                        • Invoice: {{ $gr->invoice_number }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('goods-receipts.show', $gr) }}"
                                class="text-xs text-blue-600 hover:underline">
                                Lihat Detail →
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <a href="{{ route('purchase-orders.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar PO
        </a>
    </div>
@endsection
