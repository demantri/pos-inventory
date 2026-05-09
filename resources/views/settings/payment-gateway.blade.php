@extends('layouts.app')
@section('title', 'Payment Gateway')
@section('page-title', 'Pengaturan Payment Gateway')

@section('content')
    <div class="max-w-2xl">
        <div class="mb-4">
            <a href="{{ route('settings.index') }}" class="text-sm text-primary-600 hover:underline">← Kembali ke Pengaturan</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-gray-100">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2e/Midtrans_logo_new.png/220px-Midtrans_logo_new.png"
                     alt="Midtrans" class="h-8 object-contain" onerror="this.style.display='none'">
                <div>
                    <h3 class="font-semibold text-gray-900">Midtrans</h3>
                    <p class="text-xs text-gray-500">Untuk pembayaran QRIS, transfer bank, kartu kredit/debit, e-wallet</p>
                </div>
            </div>

            <form action="{{ route('settings.payment-gateway.update') }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Enable Midtrans --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Aktifkan Midtrans</p>
                        <p class="text-xs text-gray-500 mt-0.5">Tampilkan opsi pembayaran via Midtrans di kasir</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="midtrans_enabled" value="0">
                        <input type="checkbox" name="midtrans_enabled" value="1" class="sr-only peer"
                               {{ $settings['midtrans.enabled'] === '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                {{-- Mode --}}
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Mode Production</p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Nonaktif = Sandbox (testing). Aktifkan hanya jika sudah siap go-live.
                        </p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="midtrans_is_production" value="0">
                        <input type="checkbox" name="midtrans_is_production" value="1" class="sr-only peer"
                               {{ $settings['midtrans.is_production'] === '1' ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Server Key</label>
                    <input type="text" name="midtrans_server_key"
                           value="{{ old('midtrans_server_key', $settings['midtrans.server_key']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="SB-Mid-server-xxxx... (Sandbox) atau Mid-server-xxxx... (Production)">
                    <p class="text-xs text-gray-400 mt-1">Digunakan di backend untuk membuat transaksi. Jangan bagikan ke siapapun.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Key</label>
                    <input type="text" name="midtrans_client_key"
                           value="{{ old('midtrans_client_key', $settings['midtrans.client_key']) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500"
                           placeholder="SB-Mid-client-xxxx... (Sandbox) atau Mid-client-xxxx... (Production)">
                    <p class="text-xs text-gray-400 mt-1">Digunakan di frontend untuk membuka popup Snap.</p>
                </div>

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                        Simpan Pengaturan
                    </button>
                    <a href="https://dashboard.sandbox.midtrans.com" target="_blank"
                       class="text-sm text-primary-600 hover:underline">
                        Buka Dashboard Midtrans →
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-100">
            <p class="text-sm font-medium text-blue-900 mb-2">Cara mendapatkan API Key Midtrans:</p>
            <ol class="text-xs text-blue-800 space-y-1 list-decimal list-inside">
                <li>Daftar di <strong>dashboard.midtrans.com</strong></li>
                <li>Masuk ke menu <strong>Settings → Access Keys</strong></li>
                <li>Salin <strong>Server Key</strong> dan <strong>Client Key</strong></li>
                <li>Untuk testing, gunakan key dari tab <strong>Sandbox</strong></li>
            </ol>
        </div>
    </div>
@endsection
