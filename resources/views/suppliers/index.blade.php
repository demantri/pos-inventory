@extends('layouts.app')
@section('title', 'Supplier')
@section('page-title', 'Data Supplier')

@section('content')
<div class="space-y-4">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-gray-500">
            Total <span class="font-semibold text-gray-800">{{ $suppliers->total() }}</span> supplier
        </p>
        @can('suppliers.create')
        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                  text-sm font-medium px-4 py-2.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Supplier
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('suppliers.index') }}"
          class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari kode, nama, telepon, kota..."
                   class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm
                          focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <select name="status"
                class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600
                       focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Aktif</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
        </select>
        <button type="submit"
                class="px-5 py-2.5 bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium rounded-lg transition">
            Cari
        </button>
        @if(request()->hasAny(['search','status']))
        <a href="{{ route('suppliers.index') }}"
           class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm rounded-lg transition">
            Reset
        </a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Supplier</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">Kontak</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Kota</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">PO</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4">
                        <span class="font-mono text-xs font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                            {{ $supplier->code }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-900">{{ $supplier->name }}</p>
                        @if($supplier->contact_person)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $supplier->contact_person }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <div class="space-y-0.5">
                            @if($supplier->phone)
                            <p class="text-xs text-gray-600">{{ $supplier->phone }}</p>
                            @endif
                            @if($supplier->email)
                            <p class="text-xs text-gray-400">{{ $supplier->email }}</p>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell text-sm text-gray-600">
                        {{ $supplier->city ?: '—' }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-blue-50 text-blue-700 text-xs font-semibold">
                            {{ $supplier->purchase_orders_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @can('suppliers.edit')
                        <form method="POST" action="{{ route('suppliers.toggle-status', $supplier) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition
                                           {{ $supplier->is_active
                                              ? 'bg-green-100 text-green-700 hover:bg-green-200'
                                              : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $supplier->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium
                                     {{ $supplier->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                        @endcan
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="p-1.5 text-gray-400 hover:text-purple-600 hover:bg-purple-50 rounded-lg transition"
                               title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            @can('suppliers.edit')
                            <a href="{{ route('suppliers.edit', $supplier) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                               title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endcan
                            @can('suppliers.delete')
                            <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                                  onsubmit="return confirm('Hapus supplier \"{{ $supplier->name }}\"?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-12 text-center">
                        <p class="text-sm text-gray-400">Tidak ada data supplier</p>
                        @can('suppliers.create')
                        <a href="{{ route('suppliers.create') }}" class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                            + Tambah supplier pertama
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($suppliers->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
@endsection