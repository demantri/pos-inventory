@extends('layouts.app')
@section('title', 'Terima Barang')
@section('page-title', 'Penerimaan Barang (GR)')

@section('content')
    <form method="POST" action="{{ route('goods-receipts.store') }}" id="gr-form">
        @csrf
        <div class="space-y-5 max-w-5xl">

            {{-- Header GR --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Informasi Penerimaan</h3>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

                    {{-- No GR --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. GR</label>
                        <input type="text" value="{{ $grNumber }}" readonly
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm
                              font-mono bg-gray-50 text-gray-500">
                    </div>

                    {{-- Pilih PO --}}
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="purchase_order_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Purchase Order <span class="text-red-500">*</span>
                        </label>
                        <select id="purchase_order_id" name="purchase_order_id" onchange="this.form.submit()"
                            class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('purchase_order_id') ? 'border-red-400' : 'border-gray-300' }}">
                            <option value="">— Pilih PO —</option>
                            @foreach ($availablePOs as $po)
                                <option value="{{ $po->id }}"
                                    {{ old('purchase_order_id', $purchaseOrder?->id) == $po->id ? 'selected' : '' }}>
                                    {{ $po->po_number }} — {{ $po->supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('purchase_order_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Terima --}}
                    <div>
                        <label for="receipt_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Terima <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="receipt_date" name="receipt_date"
                            value="{{ old('receipt_date', now()->format('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('receipt_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- No Invoice --}}
                    <div>
                        <label for="invoice_number" class="block text-sm font-medium text-gray-700 mb-1">
                            No. Invoice Supplier
                        </label>
                        <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}"
                            placeholder="INV/2024/001"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Tanggal Invoice --}}
                    <div>
                        <label for="invoice_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Invoice
                        </label>
                        <input type="date" id="invoice_date" name="invoice_date" value="{{ old('invoice_date') }}"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Catatan --}}
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Catatan kondisi barang, kekurangan, dll..."
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                                 resize-none focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Tabel item dari PO --}}
            @if ($purchaseOrder)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-gray-800">Item dari {{ $purchaseOrder->po_number }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Supplier: {{ $purchaseOrder->supplier->name }} •
                                Isi qty yang benar-benar diterima
                            </p>
                        </div>
                    </div>

                    @error('items')
                        <p class="px-6 pt-3 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Produk
                                    </th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty Order
                                    </th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sudah
                                        Diterima</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Sisa</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-32">Qty
                                        Terima Kali Ini</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-36">
                                        Harga Beli</th>
                                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Subtotal
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($poItems as $i => $item)
                                    <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]"
                                        value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $i }}][product_id]"
                                        value="{{ $item->product_id }}">
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3.5 text-gray-400 text-xs">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3.5">
                                            <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                            <p class="text-xs text-gray-400 font-mono mt-0.5">{{ $item->product->code }}
                                            </p>
                                        </td>
                                        <td class="px-5 py-3.5 text-right text-gray-600">
                                            {{ number_format($item->qty_ordered, 0) }}
                                            <span class="text-xs text-gray-400">{{ $item->product->unit->symbol }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right text-gray-600">
                                            {{ number_format($item->qty_received, 0) }}
                                            <span class="text-xs text-gray-400">{{ $item->product->unit->symbol }}</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-semibold text-orange-600">
                                            {{ number_format($item->remaining_qty, 0) }}
                                            <span
                                                class="text-xs font-normal text-gray-400">{{ $item->product->unit->symbol }}</span>
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <input type="number" name="items[{{ $i }}][qty_received]"
                                                id="qty_{{ $i }}" min="0.01"
                                                max="{{ $item->remaining_qty }}" step="0.01"
                                                value="{{ old("items.{$i}.qty_received", $item->remaining_qty) }}"
                                                oninput="recalcSubtotal({{ $i }})" required
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-right
                                          focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            @error("items.{$i}.qty_received")
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-5 py-3.5">
                                            <div class="relative">
                                                <span
                                                    class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                                                <input type="text" name="items[{{ $i }}][unit_cost]"
                                                    id="cost_{{ $i }}" inputmode="numeric"
                                                    value="{{ old("items.{$i}.unit_cost", number_format($item->unit_cost, 0, ',', '.')) }}"
                                                    oninput="formatCost(this, {{ $i }})" required
                                                    class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-right
                                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            </div>
                                            @error("items.{$i}.unit_cost")
                                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-semibold text-gray-900"
                                            id="subtotal_{{ $i }}">
                                            Rp 0
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                                <tr>
                                    <td colspan="7" class="px-5 py-3.5 text-right font-semibold text-gray-700">
                                        Total Nilai Penerimaan
                                    </td>
                                    <td class="px-5 py-3.5 text-right font-bold text-gray-900 text-base" id="grand-total">
                                        Rp 0
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Info FIFO --}}
                <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-amber-800">Pencatatan FIFO Otomatis</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Setelah disimpan, sistem akan membuat <strong>Stock Lot baru</strong> untuk setiap item
                            dengan tanggal terima dan harga beli hari ini.
                            Lot ini akan digunakan sebagai antrian FIFO saat ada penjualan.
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition">
                        Konfirmasi Penerimaan & Update Stok
                    </button>
                    <a href="{{ route('goods-receipts.index') }}"
                        class="px-5 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm rounded-lg transition">
                        Batal
                    </a>
                </div>
            @else
                {{-- Belum pilih PO --}}
                <div class="bg-white rounded-xl border border-gray-200 px-6 py-12 text-center">
                    <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm text-gray-400">Pilih Purchase Order terlebih dahulu</p>
                    <p class="text-xs text-gray-300 mt-1">Hanya PO berstatus "Terkirim" atau "Partial" yang bisa di-GR</p>
                </div>
            @endif

        </div>
    </form>

    @push('scripts')
        <script>
            function formatCost(input, i) {
                let raw = input.value.replace(/\D/g, '');
                input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
                recalcSubtotal(i);
            }

            function recalcSubtotal(i) {
                const qty = parseFloat(document.getElementById(`qty_${i}`)?.value) || 0;
                const raw = (document.getElementById(`cost_${i}`)?.value ?? '0').replace(/\./g, '').replace(',', '.');
                const cost = parseFloat(raw) || 0;
                const sub = qty * cost;

                const cell = document.getElementById(`subtotal_${i}`);
                if (cell) cell.textContent = 'Rp ' + sub.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });

                recalcTotal();
            }

            function recalcTotal() {
                let total = 0;
                document.querySelectorAll('[id^="subtotal_"]').forEach(cell => {
                    total += parseInt(cell.textContent.replace(/[^0-9]/g, '')) || 0;
                });
                document.getElementById('grand-total').textContent =
                    'Rp ' + total.toLocaleString('id-ID');
            }

            // Hitung subtotal awal saat halaman load
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[id^="qty_"]').forEach(el => {
                    const i = el.id.replace('qty_', '');
                    recalcSubtotal(parseInt(i));
                });
            });
        </script>
    @endpush
@endsection
