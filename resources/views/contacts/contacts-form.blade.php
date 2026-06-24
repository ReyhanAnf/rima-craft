<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $contact->exists ? 'Edit Kontak' : 'Tambah Kontak' }}</h2>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-6 pb-24">
    <form hx-{{ $contact->exists ? 'put' : 'post' }}="{{ $contact->exists ? route('contacts.update', $contact) : route('contacts.store') }}" hx-target="#contacts-list" hx-swap="innerHTML" class="space-y-6" x-data="{ submitting: false }" @submit="submitting = true" @htmx:after-request="submitting = false">
        @csrf
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Tipe Kontak</label>
            <select name="type" required class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
                <option value="supplier" {{ $contact->type == 'supplier' ? 'selected' : '' }}>Supplier</option>
                <option value="customer" {{ $contact->type == 'customer' ? 'selected' : '' }}>Customer</option>
                <option value="crafter" {{ $contact->type == 'crafter' ? 'selected' : '' }}>Crafter</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Kontak</label>
            <input type="text" name="name" value="{{ $contact->name }}" required class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nomor Telepon</label>
            <input type="text" name="phone" value="{{ $contact->phone }}" class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Alamat</label>
            <textarea name="address" rows="3" class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition">{{ $contact->address }}</textarea>
        </div>
        <div class="fixed bottom-0 right-0 w-full max-w-md bg-white dark:bg-gray-950 p-4 border-t border-gray-200 dark:border-gray-800 z-20">
            <button type="submit" class="w-full flex justify-center py-3.5 px-4 rounded-md text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition" x-bind:disabled="submitting">
                <span x-show="!submitting">Simpan Kontak</span>
                <span x-show="submitting">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>

