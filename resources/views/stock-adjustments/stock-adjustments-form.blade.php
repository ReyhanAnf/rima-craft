<div class="px-4 sm:px-4 py-2.5 flex items-start justify-between border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 sticky top-0 z-10">
    <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Stok</h2>
    <button type="button" @click="drawerOpen = false" class="rounded p-1 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
    </button>
</div>
<div class="px-4 sm:px-4 py-2.5 pb-20"
     x-data="{
        submitting: false,
        type: 'material',
        materials: {{ $materials->toJson() }},
        products: {{ $products->toJson() }},
        selectedId: '',
        actualStock: 0,
        
        get items() {
            return this.type === 'material' ? this.materials : this.products;
        },
        
        get selectedItem() {
            if (!this.selectedId) return null;
            return this.items.find(i => i.id == this.selectedId) || null;
        },
        
        get difference() {
            if (!this.selectedItem) return 0;
            return parseFloat(this.actualStock || 0) - parseFloat(this.selectedItem.current_stock || 0);
        },
        
        onTypeChange() {
            this.selectedId = '';
            this.actualStock = 0;
        },
        
        onItemChange() {
            if (this.selectedItem) {
                this.actualStock = parseFloat(this.selectedItem.current_stock);
            } else {
                this.actualStock = 0;
            }
        }
     }">
    
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/20 rounded-md p-3 mb-4">
        <p class="text-xs text-blue-700 dark:text-blue-400">Pilih item yang akan dikoreksi, lalu masukkan <strong>Stok Fisik Aktual</strong> yang Anda temukan saat ini. Sistem akan otomatis menghitung selisihnya.</p>
    </div>

    <form hx-post="{{ route('stock-adjustments.store') }}"
          hx-target="#stock-adjustments-list"
          hx-swap="outerHTML"
          class="space-y-4"
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
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Kategori Item <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-2 gap-2">
                <label class="relative flex items-center justify-center p-3 border rounded-md cursor-pointer transition-colors"
                       :class="type === 'material' ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500'">
                    <input type="radio" name="adjustable_type" value="material" x-model="type" @change="onTypeChange" class="sr-only">
                    <span class="text-sm font-bold">Bahan Baku</span>
                </label>
                <label class="relative flex items-center justify-center p-3 border rounded-md cursor-pointer transition-colors"
                       :class="type === 'product' ? 'border-primary-500 bg-primary-50 dark:bg-primary-500/10 text-primary-700 dark:text-primary-400' : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-500'">
                    <input type="radio" name="adjustable_type" value="product" x-model="type" @change="onTypeChange" class="sr-only">
                    <span class="text-sm font-bold">Produk Jadi</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Pilih Item <span class="text-red-500">*</span></label>
            <select name="adjustable_id" x-model="selectedId" @change="onItemChange" required class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                <option value="">-- Pilih --</option>
                <template x-for="item in items" :key="item.id">
                    <option :value="item.id" x-text="item.name + ' (Stok Tercatat: ' + parseFloat(item.current_stock) + (type === 'material' ? ' ' + item.unit : '') + ')'"></option>
                </template>
            </select>
        </div>

        <template x-if="selectedItem">
            <div class="space-y-4">
                <div class="bg-gray-50 dark:bg-gray-800/50 p-3 rounded-md border border-gray-100 dark:border-gray-800 flex justify-between items-center">
                    <div>
                        <div class="text-[10px] text-gray-500 uppercase font-bold mb-0.5">Stok Tercatat di Sistem</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white" x-text="parseFloat(selectedItem.current_stock) + (type === 'material' ? ' ' + selectedItem.unit : ' Pcs')"></div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] text-gray-500 uppercase font-bold mb-0.5">Selisih (+ / -)</div>
                        <div class="text-lg font-bold" 
                             :class="difference > 0 ? 'text-emerald-600 dark:text-emerald-400' : (difference < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400')"
                             x-text="(difference > 0 ? '+' : '') + difference"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Stok Aktual Fisik <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="number" step="any" name="actual_stock" x-model="actualStock" min="0" required class="w-full px-3 py-2.5 text-lg font-bold rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600" :class="difference !== 0 ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-white'">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span class="text-sm font-medium text-gray-400" x-text="type === 'material' ? selectedItem.unit : 'Pcs'"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1">Alasan / Keterangan <span class="text-red-500">*</span></label>
                    <textarea name="reason" rows="2" placeholder="Contoh: Barang rusak terkena air, salah hitung saat stok masuk..." required class="w-full px-3 py-2 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600"></textarea>
                </div>
            </div>
        </template>

        <div class="fixed bottom-0 right-0 w-full max-w-lg bg-white dark:bg-gray-950 p-3 border-t border-gray-200 dark:border-gray-800 z-20" x-show="selectedItem && difference !== 0" style="display: none;">
            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2.5 px-4 rounded-md text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 transition-all hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed" x-bind:disabled="submitting">
                <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span x-show="!submitting">💾 Simpan Penyesuaian</span>
                <span x-show="submitting">⏳ Menyimpan...</span>
            </button>
        </div>
        <div class="fixed bottom-0 right-0 w-full max-w-lg bg-white dark:bg-gray-950 p-3 border-t border-gray-200 dark:border-gray-800 z-20" x-show="!selectedItem || difference === 0">
            <button type="button" disabled class="w-full flex justify-center py-2.5 px-4 rounded-md text-sm font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 cursor-not-allowed">
                <span x-text="!selectedItem ? 'Pilih Item Terlebih Dahulu' : 'Tidak Ada Perubahan Stok'"></span>
            </button>
        </div>
    </form>
</div>

