@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <a href="{{ route('settings.index') }}" class="text-sm text-primary-600 hover:underline">← Kembali ke Pengaturan</a>
        @can('users.create')
        <a href="{{ route('settings.users.create') }}"
           class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition">
            + Tambah User
        </a>
        @endcan
    </div>

    {{-- Filter --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-200 p-4 mb-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..."
               class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
        <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <option value="">Semua Role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
            Filter
        </button>
        @if(request()->hasAny(['search', 'role']))
        <a href="{{ route('settings.users.index') }}" class="px-4 py-2 text-gray-500 rounded-lg text-sm hover:text-gray-700">
            Reset
        </a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Nama</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Email</th>
                    <th class="text-left px-5 py-3 font-semibold text-gray-600">Role</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Status</th>
                    <th class="text-center px-5 py-3 font-semibold text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-5 py-3.5 font-medium text-gray-900">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-xs text-gray-400">(Anda)</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-gray-600">{{ $user->email }}</td>
                        <td class="px-5 py-3.5">
                            @foreach($user->roles as $role)
                                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-medium rounded-full">
                                    {{ $role->name }}
                                </span>
                            @endforeach
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <form action="{{ route('settings.users.toggle-status', $user) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium transition
                                               {{ $user->is_active
                                                   ? 'bg-green-50 text-green-700 hover:bg-green-100'
                                                   : 'bg-red-50 text-red-700 hover:bg-red-100' }}"
                                        {{ $user->id === auth()->id() ? 'disabled title=Tidak dapat mengubah status sendiri' : '' }}>
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @can('users.edit')
                                <a href="{{ route('settings.users.edit', $user) }}"
                                   class="text-xs text-primary-600 hover:underline font-medium">Edit</a>
                                @endcan
                                @can('users.delete')
                                @if($user->id !== auth()->id())
                                <form action="{{ route('settings.users.destroy', $user) }}" method="POST"
                                      onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-red-500 hover:underline font-medium">Hapus</button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                            Tidak ada user ditemukan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
    <div class="mt-4">{{ $users->links() }}</div>
    @endif
@endsection
