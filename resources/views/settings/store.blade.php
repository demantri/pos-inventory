@extends('layouts.app')
@section('title', 'Informasi Toko')
@section('page-title', 'Informasi Toko')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-4">
            <a href="{{ route('settings.index') }}" class="text-sm text-primary-600 hover:underline">← Kembali ke Pengaturan</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form action="{{ route('settings.store.update') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Logo --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Logo Toko</label>
                    @if ($settings['store.logo'])
                        <img src="{{ asset('storage/' . $settings['store.logo']) }}" alt="Logo"
                             class="w-24 h-24 object-contain border border-gray-200 rounded-lg mb-3">
                    @endif
                    <input type="file" name="store_logo" accept="image/png,image/jpg,image/jpeg"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG max 2MB. Digunakan pada struk/receipt.</p>
                    @error('store_logo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko <span class="text-red-500">*</span></label>
                    <input type="text" name="store_name" value="{{ old('store_name', $settings['store.name']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('store_name') border-red-400 @enderror"
                           placeholder="Nama toko Anda">
                    @error('store_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="store_address" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Alamat lengkap toko">{{ old('store_address', $settings['store.address']) }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon</label>
                        <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store.phone']) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="08xx-xxxx-xxxx">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="store_email" value="{{ old('store_email', $settings['store.email']) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="toko@email.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                    <input type="text" name="store_tax_number" value="{{ old('store_tax_number', $settings['store.tax_number']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="xx.xxx.xxx.x-xxx.xxx">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Footer Struk</label>
                    <input type="text" name="store_footer_note" value="{{ old('store_footer_note', $settings['store.footer_note']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="Terima kasih atas kunjungan Anda">
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
