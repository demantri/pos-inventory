@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('page-title', 'Tambah Supplier')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">Informasi Supplier</h3>
            <p class="text-sm text-gray-500 mt-0.5">Isi data supplier baru</p>
        </div>
        <form method="POST" action="{{ route('suppliers.store') }}" class="px-6 py-6">
            @csrf
            @include('suppliers._form')
            <div class="flex items-center gap-3 mt-6 pt-6 border-t border-gray-100">
                <button type="submit"
                        class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                    Simpan Supplier
                </button>
                <a href="{{ route('suppliers.index') }}"
                   class="px-5 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm rounded-lg transition">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection