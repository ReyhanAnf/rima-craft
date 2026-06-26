<x-layouts.app>
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Manajemen Role</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola role dan izin akses</p>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200 dark:bg-emerald-900/10 dark:text-emerald-400 dark:border-emerald-800">
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 dark:bg-red-900/10 dark:text-red-400 dark:border-red-800">
            {{ session('error') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($roles as $role)
            <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white capitalize">{{ $role->name }}</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $role->users_count }} users</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-primary-100 text-primary-700 dark:bg-primary-900/20 dark:text-primary-400">
                            {{ $role->permissions_count }} permissions
                        </span>
                    </div>
                    
                    <div class="flex gap-2 mt-4">
                        <a href="{{ route('roles.edit', $role) }}" class="flex-1 px-3 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors text-center">
                            Edit Permissions
                        </a>
                        @if(!in_array($role->name, ['super-admin', 'owner', 'operator', 'customer', 'partner']))
                        <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Hapus role ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-2 border border-red-300 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
