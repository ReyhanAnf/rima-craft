<x-layouts.app>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit User</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbarui informasi user</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-700 rounded-lg border border-red-200 dark:bg-red-900/10 dark:text-red-400 dark:border-red-800">
            {{ session('error') }}
        </div>
        @endif

        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 p-6">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-3 py-2 rounded-lg border @error('name') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror bg-white dark:bg-gray-900">
                        @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 rounded-lg border @error('email') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror bg-white dark:bg-gray-900">
                        @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role <span class="text-red-500">*</span></label>
                        <select name="role" required class="w-full px-3 py-2 rounded-lg border @error('role') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror bg-white dark:bg-gray-900">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('role', $user->roles->pluck('name')->first()) === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                            @endforeach
                        </select>
                        @error('role')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-800 pt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Kosongkan password jika tidak ingin mengubahnya</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                                <input type="password" name="password" class="w-full px-3 py-2 rounded-lg border @error('password') border-red-500 @else border-gray-300 dark:border-gray-600 @enderror bg-white dark:bg-gray-900">
                                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->contact?->phone) }}" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat</label>
                        <textarea name="address" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">{{ old('address', $user->contact?->address) }}</textarea>
                    </div>
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">Perbarui</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
