<div class="space-y-6">

    {{-- Baris 1: Kode + Nama --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Kode Supplier <span class="text-red-500">*</span>
            </label>
            <input type="text" id="code" name="code" value="{{ old('code', $supplier->code ?? ($code ?? '')) }}"
                placeholder="SUP0001"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm font-mono
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('code') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('code')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <div class="sm:col-span-2">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                Nama Supplier <span class="text-red-500">*</span>
            </label>
            <input type="text" id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}"
                placeholder="PT. Nama Supplier"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Baris 2: PIC + Phone + Email --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-1">
                Contact Person
            </label>
            <input type="text" id="contact_person" name="contact_person"
                value="{{ old('contact_person', $supplier->contact_person ?? '') }}" placeholder="Nama PIC"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                No. Telepon
            </label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
                placeholder="08xx-xxxx-xxxx"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                Email
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
                placeholder="supplier@email.com"
                class="w-full px-3.5 py-2.5 border rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500
                          {{ $errors->has('email') ? 'border-red-400 bg-red-50' : 'border-gray-300' }}">
            @error('email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Baris 3: Alamat + Kota --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="sm:col-span-2">
            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
            <textarea id="address" name="address" rows="2" placeholder="Alamat lengkap..."
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address', $supplier->address ?? '') }}</textarea>
        </div>
        <div>
            <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Kota</label>
            <input type="text" id="city" name="city" value="{{ old('city', $supplier->city ?? '') }}"
                placeholder="Jakarta"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    {{-- Baris 4: NPWP + Catatan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label for="npwp" class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
            <input type="text" id="npwp" name="npwp" value="{{ old('npwp', $supplier->npwp ?? '') }}"
                placeholder="00.000.000.0-000.000"
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm font-mono
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div class="sm:col-span-2">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
            <textarea id="notes" name="notes" rows="2" placeholder="Catatan tambahan tentang supplier..."
                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-lg text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes', $supplier->notes ?? '') }}</textarea>
        </div>
    </div>

    {{-- Status --}}
    <div class="flex items-center gap-3 pt-2">
        <button type="button" id="toggle-active" onclick="toggleActive()"
            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2
                       {{ old('is_active', $supplier->is_active ?? true) ? 'bg-blue-600' : 'bg-gray-200' }}">
            <span id="toggle-knob"
                class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform
                         {{ old('is_active', $supplier->is_active ?? true) ? 'translate-x-6' : 'translate-x-1' }}">
            </span>
        </button>
        <input type="hidden" name="is_active" id="is_active"
            value="{{ old('is_active', $supplier->is_active ?? true) ? '1' : '0' }}">
        <span class="text-sm text-gray-700" id="toggle-label">
            {{ old('is_active', $supplier->is_active ?? true) ? 'Aktif' : 'Nonaktif' }}
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
    </script>
@endpush
