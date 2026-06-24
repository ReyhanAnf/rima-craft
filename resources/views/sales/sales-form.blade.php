<x-layouts.app>
    <div class="max-w-4xl mx-auto pb-24">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('sales.index') }}" class="p-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">Catat Penjualan Baru</h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Stok produk jadi akan otomatis berkurang setelah faktur disimpan.</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 text-red-800 text-sm border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20 shadow-sm">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
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

        <form action="{{ route('sales.store') }}" method="POST" x-data="saleForm()" @submit="submitting = true" class="space-y-6 relative">
            @csrf
            
            <!-- Info Customer & Transaksi -->
            <div class="glass-card rounded-md p-4 md:p-4 space-y-4">
                <h3 class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">Informasi Faktur & Pengiriman</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Tanggal Penjualan
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" required 
                               class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition-colors hover:border-gray-300 dark:hover:border-gray-600">
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Tanggal faktur penjualan dibuat</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status Pembayaran
                            <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_status" required 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition-colors hover:border-gray-300 dark:hover:border-gray-600">
                            <option value="paid">✅ Lunas (Paid)</option>
                            <option value="unpaid">❌ Belum Lunas (Unpaid)</option>
                            <option value="partial">⏳ Sebagian (DP/Partial)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status pembayaran dari pelanggan</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Status Pengiriman
                            <span class="text-red-500">*</span>
                        </label>
                        <select name="shipping_status" required 
                                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none transition-colors hover:border-gray-300 dark:hover:border-gray-600">
                            <option value="pending">📦 Menunggu (Pending)</option>
                            <option value="shipped">🚚 Dikirim (Shipped)</option>
                            <option value="delivered">✅ Diterima (Delivered)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Status pengiriman barang</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <div class="flex items-center gap-4 mb-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="customer_type" value="registered" x-model="customerType" class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Pilih dari Buku Kontak</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="customer_type" value="manual" x-model="customerType" class="text-primary-600 focus:ring-primary-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Input Manual</span>
                        </label>
                    </div>

                    <!-- Registered Customer -->
                    <div x-show="customerType === 'registered'" x-transition>
                        <select name="customer_id" x-bind:required="customerType === 'registered'" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            <option value="">-- Pilih Customer --</option>
                            @foreach($customers as $cus)
                            <option value="{{ $cus->id }}">{{ $cus->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Manual Customer -->
                    <div x-show="customerType === 'manual'" x-transition class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Nama Pelanggan</label>
                                <input type="text" name="customer_name" x-bind:required="customerType === 'manual'" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">No. Telepon (Opsional)</label>
                                <input type="text" name="customer_phone" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer mt-2">
                            <input type="checkbox" name="save_customer" value="1" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="text-xs text-gray-600 dark:text-gray-400">Simpan info ini sebagai kontak baru di Buku Kontak</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Item Lines -->
            <div class="glass-card rounded-md p-4 md:p-4">
                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2 mb-4">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Produk yang Dijual</h3>
                    <button type="button" @click="addItem" class="px-3 py-1.5 bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-400 rounded-lg text-xs font-bold hover:bg-primary-100 dark:hover:bg-primary-500/20 transition">
                        + Tambah Baris
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="item.id">
                        <div class="flex flex-col md:flex-row gap-3 items-end bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-md border border-gray-100 dark:border-gray-800">
                            <div class="w-full md:flex-1">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Pilih Produk</label>
                                <select x-model="item.product_id" :name="`items[${index}][product_id]`" @change="item.price = getProductPrice(item.product_id)" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                                    <option value="">-- Pilih --</option>
                                    @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ $prod->name }} (Sisa: {{ $prod->current_stock }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-24">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Qty</label>
                                <input type="number" x-model.number="item.qty" :name="`items[${index}][qty]`" min="1" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                            </div>
                            <div class="w-full md:w-40">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Harga Jual (Rp)</label>
                                <input type="number" x-model.number="item.price" :name="`items[${index}][price]`" min="0" required class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
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
                
                <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-800 space-y-3">
                    <div class="flex justify-between items-center md:justify-end md:gap-12">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Subtotal</label>
                        <div class="text-sm font-bold text-gray-900 dark:text-white w-32 text-right" x-text="'Rp ' + totalAmount.toLocaleString('id-ID')"></div>
                    </div>
                    <div class="flex justify-between items-center md:justify-end md:gap-12">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Ongkos Kirim (+)</label>
                        <div class="w-32">
                            <input type="number" name="shipping_fee" x-model.number="shippingFee" min="0" class="w-full px-2 py-1 text-right text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-between items-center md:justify-end md:gap-12">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-400">Diskon (-)</label>
                        <div class="w-32">
                            <input type="number" name="discount" x-model.number="discount" min="0" class="w-full px-2 py-1 text-right text-sm rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none">
                        </div>
                    </div>
                    <div class="flex justify-between items-center md:justify-end md:gap-12 pt-3 border-t border-gray-100 dark:border-gray-800">
                        <div class="text-sm font-bold text-gray-900 dark:text-white uppercase">Grand Total</div>
                        <div class="text-2xl font-bold text-primary-700 dark:text-primary-400 w-32 text-right" x-text="'Rp ' + grandTotal.toLocaleString('id-ID')"></div>
                    </div>
                </div>
            </div>

            <!-- Sticky Footer CTA -->
            <div class="fixed bottom-0 left-0 w-full md:pl-64 z-20">
                <div class="bg-white/80 dark:bg-gray-950/80 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 p-4 md:px-8 flex justify-end gap-3">
                    <a href="{{ route('sales.index') }}" class="px-4 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-md transition">Batal</a>
                    <button type="submit" x-bind:disabled="submitting || items.length === 0" 
                            class="px-6 py-2.5 text-sm font-bold rounded-md shadow-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2
                                   {{ $errors->any() ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-primary-600 hover:bg-primary-700 text-white hover:shadow-xl hover:scale-105' }}">
                        <svg x-show="submitting" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!submitting">💾 Simpan Transaksi</span>
                        <span x-show="submitting">⏳ Memproses...</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const productsData = @json($products->map(fn($p) => ['id' => $p->id, 'price' => $p->base_price]));
        
        document.addEventListener('alpine:init', () => {
            Alpine.data('saleForm', () => ({
                submitting: false,
                customerType: 'registered',
                shippingFee: 0,
                discount: 0,
                items: [
                    { id: Date.now(), product_id: '', qty: 1, price: 0 }
                ],
                getProductPrice(id) {
                    const prod = productsData.find(p => p.id == id);
                    return prod ? Number(prod.price) : 0;
                },
                addItem() {
                    this.items.push({ id: Date.now(), product_id: '', qty: 1, price: 0 });
                },
                removeItem(index) {
                    if(this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                get totalAmount() {
                    return this.items.reduce((total, item) => total + (item.qty * item.price), 0);
                },
                get grandTotal() {
                    let gt = this.totalAmount + Number(this.shippingFee) - Number(this.discount);
                    return gt > 0 ? gt : 0;
                }
            }))
        })
    </script>
</x-layouts.app>

