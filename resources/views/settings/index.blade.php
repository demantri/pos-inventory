@extends('layouts.app')
@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        @can('settings.store')
        <a href="{{ route('settings.store') }}"
           class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-primary-400 hover:shadow-md transition group">
            <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center mb-4 group-hover:bg-blue-100 transition">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Informasi Toko</h3>
            <p class="text-sm text-gray-500">Nama toko, alamat, telepon, logo, dan info struk</p>
        </a>
        @endcan

        @can('settings.payment_gateway')
        <a href="{{ route('settings.payment-gateway') }}"
           class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-primary-400 hover:shadow-md transition group">
            <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center mb-4 group-hover:bg-green-100 transition">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Payment Gateway</h3>
            <p class="text-sm text-gray-500">Konfigurasi Midtrans: API key, mode sandbox/production</p>
        </a>
        @endcan

        @can('users.view')
        <a href="{{ route('settings.users.index') }}"
           class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-primary-400 hover:shadow-md transition group">
            <div class="w-12 h-12 bg-purple-50 rounded-lg flex items-center justify-center mb-4 group-hover:bg-purple-100 transition">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Manajemen User</h3>
            <p class="text-sm text-gray-500">Tambah, edit, hapus user dan assign role</p>
        </a>
        @endcan

        @can('settings.roles')
        <a href="{{ route('settings.roles.index') }}"
           class="block bg-white rounded-xl border border-gray-200 p-6 hover:border-primary-400 hover:shadow-md transition group">
            <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center mb-4 group-hover:bg-orange-100 transition">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <h3 class="font-semibold text-gray-900 mb-1">Role & Hak Akses</h3>
            <p class="text-sm text-gray-500">Kelola role dan permission untuk setiap role</p>
        </a>
        @endcan
    </div>
@endsection
