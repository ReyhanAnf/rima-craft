<x-layouts.app>
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil Partner</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola informasi bisnis Anda</p>
        </div>

        @if(session('success'))
        <div class="mb-4 p-4 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
        @endif

        <div class="glass-card rounded-lg border border-gray-200 dark:border-gray-800 p-6">
            <form action="{{ route('partner.profile.update') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Kontak</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required 
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nama Perusahaan/Toko</label>
                        <input type="text" name="company_name" value="{{ auth()->user()->contact->company_name ?? '' }}" 
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telepon</label>
                        <input type="text" name="phone" value="{{ auth()->user()->contact->phone ?? '' }}" 
                               class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Alamat Bisnis</label>
                        <textarea name="address" rows="3" 
                                  class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">{{ auth()->user()->contact->address ?? '' }}</textarea>
                    </div>
                    <button type="submit" class="px-6 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
