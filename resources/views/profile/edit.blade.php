<x-layouts.app>
    <div class="mb-6">
        <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Profil Pengguna</h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola informasi pribadi dan keamanan akun Anda.</p>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 rounded-md bg-red-50 text-red-800 text-sm border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20">
        <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="glass-card rounded-md border border-gray-200 dark:border-gray-800 overflow-hidden max-w-2xl">
        <form action="{{ route('profile.update') }}" method="POST" class="p-6">
            @csrf
            
            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider">Informasi Umum</h3>
            
            <div class="space-y-4 mb-8">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Alamat Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 uppercase tracking-wider pt-6 border-t border-gray-100 dark:border-gray-800">Ubah Password</h3>
            <p class="text-xs text-gray-500 mb-4">Kosongkan kolom di bawah ini jika Anda tidak ingin mengubah password.</p>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Password Baru</label>
                    <input type="password" name="password" class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-2.5 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-950 outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-gray-900 hover:bg-black dark:bg-white dark:hover:bg-gray-200 dark:text-gray-900 text-white text-sm font-bold rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
