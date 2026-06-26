<x-layouts.app>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('roles.index') }}" class="p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Role: {{ ucfirst($role->name) }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Atur izin akses untuk role ini</p>
            </div>
        </div>

        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 p-6">
            <form action="{{ route('roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Role <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full px-3 py-2 rounded-lg border @error('name') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror bg-white dark:bg-gray-900">
                    @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="border-t border-gray-200 dark:border-gray-800 pt-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Izin Akses (Permissions)</h3>
                    
                    <div class="space-y-6">
                        @foreach($groupedPermissions as $module => $permissions)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 capitalize">{{ $module }}</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($permissions as $permission)
                                <label class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/50 p-2 rounded transition-colors">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                           {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}
                                           class="w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ str_replace('-', ' ', $permission->name) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                    <a href="{{ route('roles.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Batal</a>
                    <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
