@extends('layouts.app')
@section('title', $goodsReceipt->gr_number)
@section('page-title', 'Detail Penerimaan Barang')

@section('content')
    <div class="max-w-4xl space-y-5">

        {{-- Header --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-lg text-gray-900 font-mono">{{ $goodsReceipt->gr_number }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Diterima oleh {{ $goodsReceipt->user->name }} •
                        {{ $goodsReceipt->created_at->format('d M Y H:i') }}
                    </p>
                </div>
                <span
                    class="px-3 py-1 rounded-full text-xs font-semibold
                         {{ $goodsReceipt->status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                    {{ ucfirst($goodsReceipt->status) }}
                </span>
            </div>

            <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-3 gap-x-8 gap-y-4">
                <div>
                    <p class="text-xs text-gray-400">Purchase Order</p>
                    <a href="{{ route('purchase-orders.show', $goodsReceipt->purchaseOrder) }}"
                        class="text-sm font-mono font-semibold text-blue-600 hover:underline mt-0.5 block">
                        {{ $goodsReceipt->purchaseOrder->po_number }}
                    </a>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Supplier</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $goodsReceipt->purchaseOrder->supplier->name }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Tanggal Terima</p>
                    <p class="text-sm text-gray-900 mt-0.5">{{ $goodsReceipt->receipt_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">No. Invoice</p>
                    <p class="text-sm text-gray-900 mt-0.5">{{ $goodsReceipt->invoice_number ?: '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-400">Tanggal Invoice</p>
                    <p class="text-sm text-gray-900 mt-0.5">
                        {{ $goodsReceipt->invoice_date?->format('d M Y') ?? '—' }}
                    </p>
                </div>
                @if ($goodsReceipt->notes)
                    <div class="col-span-2 sm:col-span-3">
                        <p class="text-xs text-gray-400">Catatan</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $goodsReceipt->notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tabel item + info lot --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Item yang Diterima & Lot FIFO</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty Diterima</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Harga Beli</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">
                            No. Lot FIFO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $grandTotal = 0; @endphp
                    @foreach ($goodsReceipt->items as $i => $item)
                        @php $grandTotal += $item->subtotal; @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-5 py-3.5">
                                <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->product->code }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900">
                                {{ number_format($item->qty_received, 0) }}
                                <span class="text-xs font-normal text-gray-400">{{ $item->product->unit->symbol }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-right text-gray-600">
                                Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 text-right font-semibold text-gray-900">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                            <td class="px-5 py-3.5 hidden md:table-cell">
                                @if ($item->stockLot)
                                    <div>
                                        <span class="font-mono text-xs text-green-700 bg-green-50 px-2 py-1 rounded">
                                            {{ $item->stockLot->lot_number }}
                                        </span>
                                        <p class="text-xs text-gray-400 mt-1">
                                            Sisa: {{ number_format($item->stockLot->qty_remaining, 0) }}
                                            {{ $item->product->unit->symbol }}
                                        </p>
                                    </div>
                                @else
                                    <span class="text-gray-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                    <tr>
                        <td colspan="4" class="px-5 py-3.5 text-right font-semibold text-gray-700">
                            Total Nilai Penerimaan
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-gray-900 text-base">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                        <td class="hidden md:table-cell"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <a href="{{ route('goods-receipts.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar GR
        </a>
    </div>
@endsection
