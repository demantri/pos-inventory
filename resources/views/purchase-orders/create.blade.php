@extends('layouts.app')
@section('title', 'Buat Purchase Order')
@section('page-title', 'Buat Purchase Order')

@section('content')
    <form method="POST" action="{{ route('purchase-orders.store') }}" id="po-form">
        @csrf
        <div class="space-y-5 max-w-5xl">

            {{-- Header PO --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Informasi PO</h3>
                </div>
                <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- No PO (readonly) --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. PO</label>
                        <input type="text" value="{{ $poNumber }}" readonly
                            class="w-full px-3.5 py-2.5 border border-gray-200 rounded-lg text-sm
                              font-mono bg-gray-50 text-gray-500">
                    </div>

                    {{-- Supplier --}}
                    <div class="lg:col-span-1">
                        <label for="supplier_id" class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier <span class="text-red-500">*</span>
                        </label>
                        <select id="supplier_id" name="supplier_id"
                            class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                               focus:outline-none focus:ring-2 focus:ring-blue-500
                               {{ $errors->has('supplier_id') ? 'border-red-400' : 'border-gray-300' }}">
                            <option value="">— Pilih Supplier —</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal PO --}}
                    <div>
                        <label for="po_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal PO <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="po_date" name="po_date"
                            value="{{ old('po_date', now()->format('Y-m-d')) }}"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('po_date')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Estimasi Tiba --}}
                    <div>
                        <label for="expected_date" class="block text-sm font-medium text-gray-700 mb-1">
                            Estimasi Tiba
                        </label>
                        <input type="date" id="expected_date" name="expected_date" value="{{ old('expected_date') }}"
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Catatan --}}
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea id="notes" name="notes" rows="2" placeholder="Catatan tambahan untuk PO ini..."
                            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm resize-none
                                 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Tabel item --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Item Produk</h3>
                    <button type="button" onclick="addRow()"
                        class="inline-flex items-center gap-1.5 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item
                    </button>
                </div>

                @error('items')
                    <p class="px-6 pt-3 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase w-8">#</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase min-w-52">
                                    Produk</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-28">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-36">Harga
                                    Beli</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-24">Diskon %
                                </th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase w-36">Subtotal
                                </th>
                                <th class="px-4 py-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody id="items-body">
                            {{-- Rows diisi via JS atau old input --}}
                        </tbody>
                        <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                            <tr>
                                <td colspan="5" class="px-4 py-3 text-right text-sm font-semibold text-gray-700">
                                    Grand Total
                                </td>
                                <td class="px-4 py-3 text-right text-base font-bold text-gray-900" id="grand-total">
                                    Rp 0
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Simpan Purchase Order
                </button>
                <a href="{{ route('purchase-orders.index') }}"
                    class="px-5 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm rounded-lg transition">
                    Batal
                </a>
            </div>
        </div>
    </form>

    @push('scripts')
        <script>
            const products = @json($productsJson);

            let rowIndex = 0;

            function addRow(data = {}) {
                const tbody = document.getElementById('items-body');
                const i = rowIndex++;
                const row = document.createElement('tr');
                row.className = 'border-b border-gray-100 item-row';
                row.dataset.index = i;
                row.innerHTML = `
        <td class="px-4 py-3 text-gray-400 text-xs">${tbody.children.length + 1}</td>
        <td class="px-4 py-3">
            <select name="items[${i}][product_id]" onchange="onProductChange(this, ${i})" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">— Pilih Produk —</option>
                ${products.map(p => `
                            <option value="${p.id}" data-symbol="${p.symbol}"
                                    ${data.product_id == p.id ? 'selected' : ''}>
                                ${p.code} — ${p.name}
                            </option>`).join('')}
            </select>
        </td>
        <td class="px-4 py-3">
            <div class="flex items-center justify-end gap-1.5">
                <input type="number" name="items[${i}][qty_ordered]" min="0.01" step="0.01"
                       value="${data.qty_ordered ?? ''}" placeholder="0"
                       onchange="recalcRow(${i})" oninput="recalcRow(${i})" required
                       class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-right
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
                <span class="text-xs text-gray-400 unit-label-${i} w-8">
                    ${data.symbol ?? ''}
                </span>
            </div>
        </td>
        <td class="px-4 py-3">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                <input type="text" name="items[${i}][unit_cost]" inputmode="numeric"
                       value="${data.unit_cost ?? ''}" placeholder="0"
                       oninput="formatAndRecalc(this, ${i})" required
                       class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-lg text-sm text-right
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </td>
        <td class="px-4 py-3">
            <input type="number" name="items[${i}][discount_percent]" min="0" max="100" step="0.01"
                   value="${data.discount_percent ?? 0}" placeholder="0"
                   oninput="recalcRow(${i})"
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm text-right
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </td>
        <td class="px-4 py-3 text-right font-semibold text-gray-900 subtotal-${i}">Rp 0</td>
        <td class="px-4 py-3 text-center">
            <button type="button" onclick="removeRow(this)"
                    class="p-1 text-gray-300 hover:text-red-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
                tbody.appendChild(row);
                renumberRows();

                // Jika ada data (old input / existing), trigger recalc
                if (data.qty_ordered || data.unit_cost) {
                    recalcRow(i);
                }
            }

            function onProductChange(select, i) {
                const opt = select.options[select.selectedIndex];
                const symbol = opt?.dataset?.symbol ?? '';
                const label = document.querySelector(`.unit-label-${i}`);
                if (label) label.textContent = symbol;
            }

            function formatAndRecalc(input, i) {
                let raw = input.value.replace(/\D/g, '');
                input.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
                recalcRow(i);
            }

            function recalcRow(i) {
                const qtyEl = document.querySelector(`[name="items[${i}][qty_ordered]"]`);
                const costEl = document.querySelector(`[name="items[${i}][unit_cost]"]`);
                const discEl = document.querySelector(`[name="items[${i}][discount_percent]"]`);
                const cell = document.querySelector(`.subtotal-${i}`);

                if (!qtyEl || !costEl || !cell) return;

                const qty = parseFloat(qtyEl.value) || 0;
                const cost = parseFloat((costEl.value || '0').replace(/\./g, '').replace(',', '.')) || 0;
                const disc = parseFloat(discEl?.value || 0) || 0;
                const subtotal = qty * cost * (1 - disc / 100);

                cell.textContent = 'Rp ' + subtotal.toLocaleString('id-ID', {
                    maximumFractionDigits: 0
                });
                recalcTotal();
            }

            function recalcTotal() {
                let total = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const i = row.dataset.index;
                    const cell = document.querySelector(`.subtotal-${i}`);
                    if (!cell) return;
                    const val = cell.textContent.replace(/[^0-9]/g, '');
                    total += parseInt(val) || 0;
                });
                document.getElementById('grand-total').textContent =
                    'Rp ' + total.toLocaleString('id-ID');
            }

            function removeRow(btn) {
                btn.closest('tr').remove();
                renumberRows();
                recalcTotal();
            }

            function renumberRows() {
                document.querySelectorAll('#items-body tr').forEach((row, i) => {
                    const first = row.querySelector('td:first-child');
                    if (first) first.textContent = i + 1;
                });
            }

            document.addEventListener('DOMContentLoaded', () => {
                @if (old('items'))
                    @foreach (old('items') as $item)
                        addRow({
                            product_id: '{{ $item['product_id'] ?? '' }}',
                            qty_ordered: '{{ $item['qty_ordered'] ?? '' }}',
                            unit_cost: '{{ $item['unit_cost'] ?? '' }}',
                            discount_percent: '{{ $item['discount_percent'] ?? 0 }}',
                        });
                    @endforeach
                @else
                    addRow();
                @endif
            });
        </script>
    @endpush
@endsection
