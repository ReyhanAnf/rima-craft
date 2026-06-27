<div class="px-4 sm:px-4 py-5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
        {{ $material->exists ? 'Edit Bahan Baku' : 'Tambah Bahan Baku' }}
    </h2>
    <button type="button" @click="drawerOpen = false" class="rounded-full p-1.5 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300 transition-colors">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
    </button>
</div>

<div class="px-4 sm:px-4 py-6 pb-24">
    <form hx-{{ $material->exists ? 'put' : 'post' }}="{{ $material->exists ? route('materials.update', $material) : route('materials.store') }}"
          hx-target="#materials-list"
          hx-swap="innerHTML"
          class="space-y-6"
          x-data="{ submitting: false }"
          @submit="submitting = true"
          @htmx:after-request="submitting = false">
        @csrf

        @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 shadow-sm">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div>
                    <p class="font-semibold mb-1">Terdapat kesalahan pada input:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Bahan <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" value="{{ $material->name }}" required
                class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="unit" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Satuan <span class="text-red-500">*</span></label>
                <select name="unit" id="unit" required
                    class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                    <option value="kg" {{ $material->unit == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                    <option value="meter" {{ $material->unit == 'meter' ? 'selected' : '' }}>Meter</option>
                    <option value="pcs" {{ $material->unit == 'pcs' ? 'selected' : '' }}>Pcs</option>
                    <option value="roll" {{ $material->unit == 'roll' ? 'selected' : '' }}>Roll</option>
                </select>
            </div>
            <div>
                <label for="current_stock" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Stok Saat Ini <span class="text-red-500">*</span></label>
                <input type="number" name="current_stock" id="current_stock" value="{{ $material->current_stock ?? 0 }}" min="0" required
                    class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="min_stock" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Batas Stok Min <span class="text-red-500">*</span></label>
                <input type="number" name="min_stock" id="min_stock" value="{{ $material->min_stock ?? 0 }}" min="0" required
                    class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
            </div>
            <div>
                <label for="last_buy_price" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Harga Beli Akhir <span class="text-red-500">*</span></label>
                <input type="number" name="last_buy_price" id="last_buy_price" value="{{ intval($material->last_buy_price) ?? 0 }}" min="0" required
                    class="w-full px-4 py-3 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
            </div>
        </div>

        <!-- Sticky Footer inside Drawer -->
        <div class="fixed bottom-0 right-0 w-full max-w-md bg-white dark:bg-gray-950 p-4 border-t border-gray-200 dark:border-gray-800 z-20">
            <button type="submit" 
                class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-md text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 dark:bg-primary-500 dark:hover:bg-primary-600 transition-all hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed"
                x-bind:disabled="submitting">
                <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-show="!submitting">💾 Simpan Bahan Baku</span>
                <span x-show="submitting">⏳ Menyimpan...</span>
            </button>
        </div>
    </form>
</div>

