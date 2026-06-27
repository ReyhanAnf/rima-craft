<x-layouts.app>
    <div class="max-w-5xl mx-auto pb-24">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('productions.index') }}" class="p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Proses Produksi Baru</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Bahan baku akan dipotong dan produk jadi akan ditambahkan setelah form ini disimpan.</p>
            </div>
        </div>

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

        <form action="{{ route('productions.store') }}" method="POST" x-data="productionForm()" @submit="submitting = true" class="space-y-6 relative">
            @csrf
            
            <!-- Info Produksi -->
            <div class="glass-card rounded-md p-4 md:p-4 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">Informasi Umum</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Produksi (Selesai) <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Tanggal penyelesaian proses produksi</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Upah Tukang / Makloon (Rp) - Opsional</label>
                        <input type="number" name="additional_cost" x-model.number="additionalCost" min="0" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Biaya tenaga kerja/makloon borongan pengrajin</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan (Opsional)</label>
                        <input type="text" name="notes" placeholder="Misal: Batch produksi keranjang rotan sesi pagi" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <!-- Zona Bahan Baku (INPUT) -->
                <div class="glass-card rounded-md p-4 md:p-4 border-t-4 border-red-500">
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">1. Bahan Baku Dipakai (-)</h3>
                        <button type="button" @click="addMaterial" class="px-3 py-1.5 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 rounded-lg text-xs font-bold hover:bg-red-100 transition hover:scale-105">
                            ➕ Tambah Bahan
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in materials" :key="item.id">
                            <div class="flex items-end gap-2 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md border border-gray-100 dark:border-gray-800">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Pilih Bahan Baku <span class="text-red-500">*</span></label>
                                    <select x-model="item.material_id" :name="`materials[${index}][material_id]`" @change="item.cost = getMaterialCost(item.material_id)" required class="w-full px-2 py-1.5 text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none transition hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Pilih --</option>
                                        @foreach($materials as $mat)
                                        <option value="{{ $mat->id }}">{{ $mat->name }} (Sisa: {{ $mat->current_stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" x-model.number="item.qty" :name="`materials[${index}][qty]`" min="0.01" required class="w-full px-2 py-1.5 text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none transition hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-primary-500">
                                </div>
                                <button type="button" @click="removeMaterial(index)" class="p-1.5 text-red-500 hover:bg-red-50 rounded transition mb-0.5" x-show="materials.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-800 flex justify-between items-center">
                        <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Estimasi HPP Bahan:</span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="'Rp ' + totalMaterialCost.toLocaleString('id-ID')"></span>
                    </div>
                </div>

                <!-- Zona Produk Jadi (OUTPUT) -->
                <div class="glass-card rounded-md p-4 md:p-4 border-t-4 border-emerald-500">
                    <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">2. Produk Dihasilkan (+)</h3>
                        <button type="button" @click="addProduct" class="px-3 py-1.5 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 rounded-lg text-xs font-bold hover:bg-emerald-100 transition hover:scale-105">
                            ➕ Tambah Produk
                        </button>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(item, index) in products" :key="item.id">
                            <div class="flex items-end gap-2 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md border border-gray-100 dark:border-gray-800">
                                <div class="flex-1">
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Pilih Produk Jadi <span class="text-red-500">*</span></label>
                                    <select x-model="item.product_id" :name="`products[${index}][product_id]`" required class="w-full px-2 py-1.5 text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none transition hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-primary-500">
                                        <option value="">-- Pilih --</option>
                                        @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-[10px] font-semibold text-gray-500 mb-1">Qty <span class="text-red-500">*</span></label>
                                    <input type="number" x-model.number="item.qty" :name="`products[${index}][qty]`" min="1" required class="w-full px-2 py-1.5 text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white outline-none transition hover:border-gray-300 dark:hover:border-gray-600 focus:ring-2 focus:ring-primary-500">
                                </div>
                                <button type="button" @click="removeProduct(index)" class="p-1.5 text-red-500 hover:bg-red-50 rounded transition mb-0.5" x-show="products.length > 1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Total Bar -->
            <div class="glass-card rounded-md p-4 md:p-4 mt-6 border border-primary-200 dark:border-primary-500/30 flex justify-between items-center">
                <div>
                    <div class="text-xs text-gray-500">Estimasi Grand Total HPP Produksi</div>
                    <div class="text-lg md:text-2xl font-bold text-primary-700 dark:text-primary-400" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></div>
                </div>
            </div>

            <!-- Sticky Footer CTA -->
            <div class="fixed bottom-0 left-0 w-full md:pl-64 z-20">
                <div class="bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 p-4 md:px-8 flex justify-end gap-3">
                    <a href="{{ route('productions.index') }}" class="px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition">Batal</a>
                    <button type="submit" x-bind:disabled="submitting || materials.length === 0 || products.length === 0" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-md shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl hover:scale-105 flex items-center gap-2">
                        <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="!submitting">📦 Selesaikan Produksi</span>
                        <span x-show="submitting">⏳ Memproses...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const materialsData = @json($materials->map(fn($m) => ['id' => $m->id, 'cost' => $m->last_buy_price ?? 0]));
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('productionForm', () => ({
                submitting: false,
                additionalCost: 0,
                materials: [
                    { id: Date.now(), material_id: '', qty: 1, cost: 0 }
                ],
                products: [
                    { id: Date.now(), product_id: '', qty: 1 }
                ],
                getMaterialCost(id) {
                    const mat = materialsData.find(m => m.id == id);
                    return mat ? Number(mat.cost) : 0;
                },
                addMaterial() {
                    this.materials.push({ id: Date.now(), material_id: '', qty: 1, cost: 0 });
                },
                removeMaterial(index) {
                    if(this.materials.length > 1) {
                        this.materials.splice(index, 1);
                    }
                },
                addProduct() {
                    this.products.push({ id: Date.now(), product_id: '', qty: 1 });
                },
                removeProduct(index) {
                    if(this.products.length > 1) {
                        this.products.splice(index, 1);
                    }
                },
                get totalMaterialCost() {
                    return this.materials.reduce((total, item) => total + (item.qty * item.cost), 0);
                },
                get grandTotal() {
                    return this.totalMaterialCost + Number(this.additionalCost);
                }
            }))
        })
    </script>
</x-layouts.app>

