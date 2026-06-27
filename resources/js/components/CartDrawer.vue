<script setup>
import { ref } from 'vue';
import { useCartStore } from '@/stores/cart';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    businessPhone: { type: String, default: '6281234567890' },
    businessName: { type: String, default: 'Rima Craft' },
    checkoutUrl: { type: String, required: true },
});

const isOpen = ref(false);
const checkoutMethod = ref('form');

const cart = useCartStore();
const toast = useToastStore();

function open()  { isOpen.value = true; }
function close() { isOpen.value = false; }

function handleClear() {
    cart.clear();
    toast.success('Keranjang telah dibersihkan!');
}

function handleRemove(id) {
    cart.remove(id);
}

function handleIncrement(id) {
    const result = cart.increment(id);
    if (result?.error) toast.error(result.error);
}

function handleDecrement(id) {
    cart.decrement(id);
}

function handleCheckoutWA() {
    cart.checkout(props.businessPhone, props.businessName);
}

// Expose open() so parent layout can trigger it
defineExpose({ open });
</script>

<template>
    <!-- Backdrop -->
    <Transition name="fade">
        <div
            v-if="isOpen"
            class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50"
            @click="close"
        />
    </Transition>

    <!-- Drawer -->
    <Transition name="slide-right">
        <div
            v-if="isOpen"
            class="fixed inset-y-0 right-0 w-screen max-w-md h-full bg-white dark:bg-gray-950 shadow-2xl flex flex-col border-l border-gray-200 dark:border-gray-800 z-50"
        >
            <!-- Header -->
            <div class="flex items-center justify-between px-4 py-3.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-black/40">
                <h2 class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Keranjang Belanja
                </h2>
                <div class="flex items-center gap-2">
                    <button
                        v-if="cart.items.length > 0"
                        @click="handleClear"
                        class="text-[10px] font-bold text-red-500 hover:text-red-600 transition flex items-center gap-1 bg-red-500/5 hover:bg-red-500/10 px-2 py-1 rounded-md border border-red-500/20 cursor-pointer"
                    >
                        Kosongkan
                    </button>
                    <button @click="close" class="p-1.5 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 transition cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4 hide-scrollbar bg-white dark:bg-[#050505]">
                <!-- Empty state -->
                <div v-if="cart.items.length === 0" class="h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 space-y-4">
                    <svg class="w-16 h-16 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="font-medium">Keranjang masih kosong</p>
                    <button @click="close" class="px-4 py-2 bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400 rounded-lg font-semibold text-sm cursor-pointer">
                        Mulai Belanja
                    </button>
                </div>

                <!-- Cart items -->
                <div
                    v-for="item in cart.items"
                    :key="item.id"
                    class="flex gap-4 p-3 bg-white dark:bg-black border border-gray-100 dark:border-gray-800/80 rounded-xl shadow-sm"
                >
                    <div class="w-24 h-24 rounded-lg bg-gray-100 dark:bg-gray-900 overflow-hidden flex-shrink-0">
                        <img v-if="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover" />
                        <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>

                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white line-clamp-2">{{ item.name }}</h3>
                                <button @click="handleRemove(item.id)" class="text-gray-400 hover:text-red-500 transition ml-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="text-amber-600 dark:text-amber-400 font-bold text-xs mt-1">
                                Rp {{ item.price.toLocaleString('id-ID') }}
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-2">
                            <div class="flex items-center border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden bg-gray-50 dark:bg-gray-900">
                                <button @click="handleDecrement(item.id)" class="px-2.5 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 transition font-bold cursor-pointer">-</button>
                                <div class="px-2 py-1 text-xs font-semibold text-gray-900 dark:text-white min-w-[1.5rem] text-center">{{ item.qty }}</div>
                                <button @click="handleIncrement(item.id)" class="px-2.5 py-1 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-800 transition font-bold cursor-pointer">+</button>
                            </div>
                            <div class="flex flex-col items-end">
                                <div class="text-xs font-bold text-gray-900 dark:text-white">
                                    Rp {{ (item.price * item.qty).toLocaleString('id-ID') }}
                                </div>
                                <span v-if="item.qty >= item.stock" class="text-[8px] text-red-500 font-bold uppercase tracking-wide mt-0.5">Batas Stok</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div v-if="cart.items.length > 0" class="p-5 border-t border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-black/40">
                <!-- Notes -->
                <div class="mb-4">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5">Catatan Pesanan</label>
                    <textarea
                        v-model="cart.notes"
                        @input="cart.save()"
                        rows="2"
                        placeholder="Contoh: Request rotan warna gelap, kirim sebelum jam 3 sore, dll."
                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-black text-gray-800 dark:text-gray-200 outline-none focus:ring-1 focus:ring-amber-500/50 resize-none transition-colors placeholder-gray-400 dark:placeholder-gray-600"
                    />
                </div>

                <!-- Total -->
                <div class="flex justify-between items-center mb-4">
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">Total Pesanan</span>
                    <span class="text-lg font-black text-gray-900 dark:text-white">
                        Rp {{ cart.totalPrice.toLocaleString('id-ID') }}
                    </span>
                </div>

                <!-- Checkout method -->
                <div class="space-y-3">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pilih Metode Pemesanan</p>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            @click="checkoutMethod = 'form'"
                            :class="checkoutMethod === 'form'
                                ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20'
                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'"
                            class="p-3 rounded-xl border-2 transition-all text-left"
                        >
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4" :class="checkoutMethod === 'form' ? 'text-amber-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs font-bold" :class="checkoutMethod === 'form' ? 'text-amber-700 dark:text-amber-400' : 'text-gray-600 dark:text-gray-400'">Form Order</span>
                            </div>
                            <p class="text-[9px]" :class="checkoutMethod === 'form' ? 'text-amber-600 dark:text-amber-500' : 'text-gray-400'">Isi data & bayar langsung</p>
                        </button>

                        <button
                            @click="checkoutMethod = 'whatsapp'"
                            :class="checkoutMethod === 'whatsapp'
                                ? 'border-[#25D366] bg-green-50 dark:bg-green-900/20'
                                : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900'"
                            class="p-3 rounded-xl border-2 transition-all text-left"
                        >
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-4 h-4" :class="checkoutMethod === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-400'" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                <span class="text-xs font-bold" :class="checkoutMethod === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-600 dark:text-gray-400'">WhatsApp</span>
                            </div>
                            <p class="text-[9px]" :class="checkoutMethod === 'whatsapp' ? 'text-[#25D366]' : 'text-gray-400'">Chat langsung via WA</p>
                        </button>
                    </div>

                    <!-- Form checkout -->
                    <div v-if="checkoutMethod === 'form'">
                        <a
                            :href="checkoutUrl"
                            class="w-full py-3.5 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-amber-600/20 transition duration-300 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Pesan Sekarang (Isi Form)
                        </a>
                        <p class="text-[9px] text-center text-gray-400 dark:text-gray-500 mt-2 font-light">Isi data lengkap & pilih metode pembayaran</p>
                    </div>

                    <!-- WhatsApp checkout -->
                    <div v-if="checkoutMethod === 'whatsapp'">
                        <button
                            @click="handleCheckoutWA"
                            class="w-full py-3.5 px-4 bg-[#25D366] hover:bg-[#20ba5a] text-white rounded-xl font-bold uppercase tracking-widest text-xs shadow-lg shadow-[#25D366]/20 transition duration-300 flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98]"
                        >
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Kirim Pesanan (WhatsApp)
                        </button>
                        <p class="text-[9px] text-center text-gray-400 dark:text-gray-500 mt-2 font-light">Pemesanan akan dilanjutkan via chat WhatsApp secara otomatis</p>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-right-enter-active, .slide-right-leave-active {
    transition: transform 0.3s ease-in-out;
}
.slide-right-enter-from, .slide-right-leave-to { transform: translateX(100%); }
.slide-right-enter-to, .slide-right-leave-from { transform: translateX(0); }

.bounce-enter-active { animation: bounce-in 0.3s; }
.bounce-leave-active { animation: bounce-in 0.2s reverse; }
@keyframes bounce-in {
    0% { transform: scale(0); }
    60% { transform: scale(1.2); }
    100% { transform: scale(1); }
}
</style>
