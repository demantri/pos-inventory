@extends('layouts.app')
@section('title', 'Role & Hak Akses')
@section('page-title', 'Manajemen Role & Hak Akses')

@section('content')
    <div class="mb-4">
        <a href="{{ route('settings.index') }}" class="text-sm text-primary-600 hover:underline">← Kembali ke Pengaturan</a>
    </div>

    <div class="grid grid-cols-1 gap-4">
        @foreach ($roles as $role)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 text-base capitalize">{{ $role->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $role->permissions->count() }} hak akses
                        </p>
                    </div>
                    <a href="{{ route('settings.roles.edit', $role) }}"
                       class="px-3 py-1.5 text-xs font-medium text-primary-600 border border-primary-200 rounded-lg hover:bg-primary-50 transition">
                        Edit Hak Akses
                    </a>
                </div>

                <div class="flex flex-wrap gap-2">
                    @php
                        $permsByGroup = $role->permissions->groupBy(fn($p) => explode('.', $p->name)[0]);
                    @endphp
                    @foreach($permsByGroup as $group => $perms)
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $group }}:</span>
                            @foreach($perms as $perm)
                                <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded">
                                    {{ explode('.', $perm->name)[1] ?? $perm->name }}
                                </span>
                            @endforeach
                        </div>
                    @endforeach

                    @if($role->permissions->isEmpty())
                        <span class="text-xs text-gray-400 italic">Tidak ada hak akses</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endsection
