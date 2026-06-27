<x-layouts.app>
    <div class="max-w-4xl mx-auto pb-24">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('purchases.index') }}" class="p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Catat Pembelian Baru</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Stok bahan baku akan bertambah otomatis setelah transaksi disimpan.</p>
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

        <form action="{{ route('purchases.store') }}" method="POST" x-data="purchaseForm()" @submit="submitting = true" class="space-y-6 relative">
            @csrf
            
            <div class="glass-card rounded-md p-4 md:p-4 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">Informasi Transaksi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Pembelian <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Tanggal pencatatan transaksi pembelian</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status Pembayaran <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_status" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            <option value="paid">✅ Lunas (Paid)</option>
                            <option value="unpaid">❌ Belum Lunas (Unpaid)</option>
                            <option value="partial">⏳ Sebagian (DP/Partial)</option>
                        </select>
                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Status pembayaran awal untuk transaksi ini</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="supplier_type" value="registered" x-model="supplierType" class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pilih dari Buku Kontak</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="supplier_type" value="manual" x-model="supplierType" class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Input Manual</span>
                        </label>
                    </div>

                    <!-- Registered Supplier -->
                    <div x-show="supplierType === 'registered'" x-transition>
                        <select name="supplier_id" x-bind:required="supplierType === 'registered'" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            <option value="">-- Pilih Supplier --</option>
                            @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[10px] text-gray-500 dark:text-gray-400">Pilih dari kontak supplier terdaftar</p>
                    </div>

                    <!-- Manual Supplier -->
                    <div x-show="supplierType === 'manual'" x-transition class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Supplier <span class="text-red-500">*</span></label>
                                <input type="text" name="supplier_name" x-bind:required="supplierType === 'manual'" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">No. Telepon (Opsional)</label>
                                <input type="text" name="supplier_phone" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" name="save_supplier" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Simpan info ini sebagai kontak baru di Buku Kontak</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Item Lines -->
            <div class="glass-card rounded-md p-4 md:p-4">
                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Bahan Baku yang Dibeli</h3>
                    <button type="button" @click="addItem" class="px-3 py-1.5 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 rounded-lg text-xs font-bold hover:bg-primary-100 dark:hover:bg-primary-500/20 transition hover:scale-105">
                        ➕ Tambah Baris
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="flex flex-col md:flex-row gap-3 items-end bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md border border-gray-100 dark:border-gray-800">
                            <div class="w-full md:flex-1">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Bahan Baku <span class="text-red-500">*</span></label>
                                <select x-model="item.material_id" :name="`items[${index}][material_id]`" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                                    <option value="">-- Pilih --</option>
                                    @foreach($materials as $mat)
                                    <option value="{{ $mat->id }}">{{ $mat->name }} ({{ $mat->unit }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-24">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Qty <span class="text-red-500">*</span></label>
                                <input type="number" x-model.number="item.qty" :name="`items[${index}][qty]`" step="0.01" min="0.01" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            </div>
                            <div class="w-full md:w-40">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Harga Satuan (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" x-model.number="item.price" :name="`items[${index}][price]`" min="0" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition hover:border-gray-300 dark:hover:border-gray-600">
                            </div>
                            <div class="w-full md:w-32 flex justify-between items-center pt-2 md:pt-0">
                                <div class="text-sm font-bold text-gray-900 dark:text-white" x-text="'Rp ' + (item.qty * item.price).toLocaleString('id-ID')"></div>
                                <button type="button" @click="removeItem(index)" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-lg transition" title="Hapus Baris" x-show="items.length > 1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 flex justify-end">
                    <div class="text-right">
                        <div class="text-xs text-gray-500 mb-1">Total Pembelian</div>
                        <div class="text-2xl font-bold text-primary-700 dark:text-primary-400" x-text="'Rp ' + totalAmount.toLocaleString('id-ID')"></div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer CTA -->
            <div class="fixed bottom-0 left-0 w-full md:pl-64 z-20">
                <div class="bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 p-4 md:px-8 flex justify-end gap-3">
                    <a href="{{ route('purchases.index') }}" class="px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition">Batal</a>
                    <button type="submit" x-bind:disabled="submitting || items.length === 0" class="px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-md shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-xl hover:scale-105 flex items-center gap-2">
                        <svg x-show="submitting" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span x-show="!submitting">💾 Simpan Transaksi</span>
                        <span x-show="submitting">⏳ Memproses...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('purchaseForm', () => ({
                submitting: false,
                supplierType: 'registered',
                items: [
                    { id: Date.now(), material_id: '', qty: 1, price: 0 }
                ],
                addItem() {
                    this.items.push({ id: Date.now(), material_id: '', qty: 1, price: 0 });
                },
                removeItem(index) {
                    if(this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                get totalAmount() {
                    return this.items.reduce((total, item) => total + (item.qty * item.price), 0);
                }
            }))
        })
    </script>
</x-layouts.app>

