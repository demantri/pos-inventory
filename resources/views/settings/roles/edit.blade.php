@extends('layouts.app')
@section('title', 'Edit Hak Akses - ' . $role->name)
@section('page-title', 'Edit Hak Akses: ' . ucfirst($role->name))

@section('content')
    <div class="max-w-3xl">
        <div class="mb-4">
            <a href="{{ route('settings.roles.index') }}" class="text-sm text-primary-600 hover:underline">← Kembali ke Daftar Role</a>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <form action="{{ route('settings.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-5">
                    @foreach ($allPermissions as $group => $permissions)
                        <div class="border border-gray-100 rounded-lg overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2.5 flex items-center justify-between border-b border-gray-100">
                                <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">{{ $group }}</span>
                                <button type="button"
                                        onclick="toggleGroup('{{ $group }}')"
                                        class="text-xs text-primary-600 hover:underline">
                                    Pilih Semua
                                </button>
                            </div>
                            <div class="p-4 grid grid-cols-2 md:grid-cols-3 gap-3" id="group-{{ $group }}">
                                @foreach ($permissions as $permission)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                                               {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary-600 rounded border-gray-300">
                                        <span class="text-sm text-gray-700">
                                            {{ explode('.', $permission->name)[1] ?? $permission->name }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                        Simpan Hak Akses
                    </button>
                    <a href="{{ route('settings.roles.index') }}"
                       class="px-6 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleGroup(group) {
            const container = document.getElementById('group-' + group);
            const checkboxes = container.querySelectorAll('input[type=checkbox]');
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
        }
    </script>
    @endpush
@endsection
