<div class="space-y-6">

    {{-- Baris 1: Kode + Barcode + Nama --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Kode Produk <span class="text-red-500">*</span>
            </label>
            <input type="text" id="code" name="code" value="{{ old('code', $product->code ?? ($code ?? '')) }}"
                placeholder="PRD00001"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('code') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('code')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="barcode" class="block text-sm font-medium text-gray-700 mb-1">
                Barcode
            </label>
            <input type="text" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode ?? '') }}"
                placeholder="Scan atau ketik..."
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('barcode') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('barcode')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Produk <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}"
                placeholder="Nama produk lengkap"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Baris 2: Kategori + Satuan --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">
                Kategori <span class="text-red-500">*</span>
            </label>
            <select id="category_id" name="category_id"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           {{ $errors->has('category_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <option value="">— Pilih Kategori —</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id', $product->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="unit_id" class="block text-sm font-medium text-gray-700 mb-1">
                Satuan <span class="text-red-500">*</span>
            </label>
            <select id="unit_id" name="unit_id"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           {{ $errors->has('unit_id') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
                <option value="">— Pilih Satuan —</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }} ({{ $unit->symbol }})
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Baris 3: Harga Jual + Stok Minimum --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-1">
                Harga Jual <span class="text-red-500">*</span>
            </label>
            <div class="relative">
                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-500">Rp</span>
                <input type="text" id="sale_price" name="sale_price"
                    value="{{ old('sale_price', isset($product) ? number_format($product->sale_price, 0, ',', '.') : '') }}"
                    placeholder="0" inputmode="numeric"
                    class="w-full pl-10 pr-4 py-2.5 border rounded-lg text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500
                              {{ $errors->has('sale_price') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            </div>
            @error('sale_price')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">
                Stok Minimum
                <span class="text-gray-400 font-normal">(alert jika di bawah ini)</span>
            </label>
            <input type="number" id="min_stock" name="min_stock" min="0" step="1"
                value="{{ old('min_stock', $product->min_stock ?? 0) }}"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
            @error('min_stock')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Deskripsi --}}
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
            Deskripsi <span class="text-gray-400 font-normal">(opsional)</span>
        </label>
        <textarea id="description" name="description" rows="3" placeholder="Deskripsi singkat produk..."
            class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm resize-none
                         focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    {{-- Gambar --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
            Foto Produk <span class="text-gray-400 font-normal">(opsional, maks 2MB)</span>
        </label>
        <div class="flex items-start gap-4">
            {{-- Preview gambar existing --}}
            @if (isset($product) && $product->image)
                <div class="flex-shrink-0">
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" id="img-preview"
                        class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                </div>
            @else
                <div id="img-preview-wrap"
                    class="w-20 h-20 rounded-lg border-2 border-dashed border-gray-300
                        flex items-center justify-center bg-gray-50 flex-shrink-0">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
            <div class="flex-1">
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)"
                    class="w-full text-sm text-gray-500
                              file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                              file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700
                              hover:file:bg-blue-100 cursor-pointer">
                <p class="text-xs text-gray-400 mt-1">PNG, JPG, JPEG, WEBP</p>
            </div>
        </div>
        @error('image')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Status --}}
    <div class="flex items-center gap-3">
        <button type="button" id="toggle-active" onclick="toggleActive()"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                       {{ old('is_active', $product->is_active ?? true) ? 'bg-blue-600' : 'bg-gray-200' }}">
            <span id="toggle-knob"
                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                         {{ old('is_active', $product->is_active ?? true) ? 'translate-x-6' : 'translate-x-1' }}">
            </span>
        </button>
        <input type="hidden" name="is_active" id="is_active"
            value="{{ old('is_active', $product->is_active ?? true) ? '1' : '0' }}">
        <span class="text-sm text-gray-700" id="toggle-label">
            {{ old('is_active', $product->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>
</div>

@push('scripts')
    <script>
        function toggleActive() {
            const input = document.getElementById('is_active');
            const btn = document.getElementById('toggle-active');
            const knob = document.getElementById('toggle-knob');
            const label = document.getElementById('toggle-label');
            const isOn = input.value === '1';
            input.value = isOn ? '0' : '1';
            btn.classList.toggle('bg-blue-600', !isOn);
            btn.classList.toggle('bg-gray-200', isOn);
            knob.classList.toggle('translate-x-6', !isOn);
            knob.classList.toggle('translate-x-1', isOn);
            label.textContent = isOn ? 'Nonaktif' : 'Aktif';
        }

        function previewImage(input) {
            if (!input.files || !input.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                let preview = document.getElementById('img-preview');
                const wrap = document.getElementById('img-preview-wrap');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'img-preview';
                    preview.className = 'w-20 h-20 object-cover rounded-lg border border-gray-200';
                    if (wrap) wrap.replaceWith(preview);
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(input.files[0]);
        }

        // Format ribuan otomatis pada harga jual
        document.getElementById('sale_price')?.addEventListener('input', function() {
            let raw = this.value.replace(/\D/g, '');
            this.value = raw ? parseInt(raw).toLocaleString('id-ID') : '';
        });
    </script>
@endpush
