@extends('layouts.app')
@section('title', $customer->name)
@section('page-title', 'Detail Customer')

@section('content')
    <div class="max-w-2xl space-y-5">

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">{{ $customer->name }}</h3>
                    <span class="font-mono text-xs text-gray-500">{{ $customer->code }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                             {{ $customer->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        <span
                            class="w-1.5 h-1.5 rounded-full {{ $customer->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $customer->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                    @can('customers.edit')
                        <a href="{{ route('customers.edit', $customer) }}"
                            class="px-3 py-1.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-xs rounded-lg transition">
                            Edit
                        </a>
                    @endcan
                </div>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 divide-x divide-gray-100 border-b border-gray-100">
                <div class="px-6 py-4 text-center">
                    <p class="text-2xl font-bold text-gray-900">{{ $customer->sales_count }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Total Kunjungan</p>
                </div>
                <div class="px-6 py-4 text-center">
                    <p class="text-xl font-bold text-gray-900">
                        Rp {{ number_format($customer->total_transaction, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Total Belanja</p>
                </div>
                <div class="px-6 py-4 text-center">
                    <p class="text-xl font-bold text-gray-900">
                        @if ($customer->sales_count > 0)
                            Rp {{ number_format($customer->total_transaction / $customer->sales_count, 0, ',', '.') }}
                        @else
                            —
                        @endif
                    </p>
                    <p class="text-xs text-gray-400 mt-0.5">Rata-rata Belanja</p>
                </div>
            </div>

            <div class="px-6 py-5 grid grid-cols-2 gap-x-8 gap-y-4">
                @foreach (['Telepon' => $customer->phone, 'Email' => $customer->email] as $label => $value)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ $label }}</p>
                        <p class="text-sm text-gray-800">{{ $value ?: '—' }}</p>
                    </div>
                @endforeach
                <div class="col-span-2">
                    <p class="text-xs text-gray-400 mb-0.5">Alamat</p>
                    <p class="text-sm text-gray-800">{{ $customer->address ?: '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Riwayat transaksi --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-800 text-sm">Riwayat Transaksi Terakhir</h3>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentSales as $sale)
                    <div class="px-6 py-3.5 flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $sale->sale_number }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $sale->sale_date->format('d M Y, H:i') }} • {{ $sale->user->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-semibold text-gray-900">
                                Rp {{ number_format($sale->total_amount, 0, ',', '.') }}
                            </p>
                            <span class="text-xs text-gray-400 uppercase">{{ $sale->payment_method }}</span>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-gray-400">Belum ada transaksi</div>
                @endforelse
            </div>
        </div>

        <a href="{{ route('customers.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Kembali ke daftar customer
        </a>
    </div>
@endsection
