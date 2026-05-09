@extends('layouts.app')
@section('title', 'Satuan')
@section('page-title', 'Satuan Produk')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <p class="text-sm text-gray-500">
            Total <span class="font-semibold text-gray-800">{{ $units->total() }}</span> satuan
        </p>
        @can('units.create')
        <a href="{{ route('units.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white
                  text-sm font-medium px-4 py-2.5 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Satuan
        </a>
        @endcan
    </div>

    <form method="GET" action="{{ route('units.index') }}"
          class="bg-white rounded-xl border border-gray-200 p-4 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Cari nama atau simbol..."
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
        <a href="{{ route('units.index') }}"
           class="px-4 py-2.5 border border-gray-300 hover:bg-gray-50 text-gray-600 text-sm rounded-lg transition">
            Reset
        </a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-8">#</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Satuan</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Simbol</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Produk</th>
                    <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($units as $unit)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-5 py-4 text-gray-400 text-xs">{{ $units->firstItem() + $loop->index }}</td>
                    <td class="px-5 py-4 font-medium text-gray-900">{{ $unit->name }}</td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100
                                     text-gray-700 text-xs font-mono font-semibold">
                            {{ $unit->symbol }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full
                                     bg-blue-50 text-blue-700 text-xs font-semibold">
                            {{ $unit->products_count }}
                        </span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        @can('units.edit')
                        <form method="POST" action="{{ route('units.toggle-status', $unit) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition
                                           {{ $unit->is_active ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $unit->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                {{ $unit->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                        @endcan
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @can('units.edit')
                            <a href="{{ route('units.edit', $unit) }}"
                               class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @endcan
                            @can('units.delete')
                            <form method="POST" action="{{ route('units.destroy', $unit) }}"
                                  onsubmit="return confirm('Hapus satuan \"{{ $unit->name }}\"?')">
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
                    <td colspan="6" class="px-5 py-12 text-center">
                        <p class="text-sm text-gray-400">Tidak ada data satuan</p>
                        @can('units.create')
                        <a href="{{ route('units.create') }}" class="inline-block mt-3 text-sm text-blue-600 hover:underline">
                            + Tambah satuan pertama
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($units->hasPages())
        <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">{{ $units->links() }}</div>
        @endif
    </div>
</div>
@endsection