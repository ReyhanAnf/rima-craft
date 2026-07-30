<script setup>
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import ToggleSwitch from 'primevue/toggleswitch';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    form: Object,
    settings: Object,
});



const provinces = ref([]);
const cities = ref([]);

const modals = ref({
    shipping: false,
    payment: false,
    qrisly: false
});

const openIntegration = (id) => {
    modals.value[id] = true;
};

const showKeys = ref({
    shipping: false,
    payment: false,
    qrisly: false
});

const toggleKey = (id) => {
    showKeys.value[id] = !showKeys.value[id];
};

const getMaskedKey = (key) => {
    if (!key) return '-';
    return '•'.repeat(Math.min(key.length, 16));
};

const integrations = [
    { id: 'shipping', name: 'Shipping Cost', desc: 'Pengaturan RajaOngkir untuk perhitungan ongkir otomatis.', key: () => props.form.rajaongkir_api_key, activeKey: 'rajaongkir_enabled', date: '29/07/2026' },
    { id: 'payment', name: 'Payment API', desc: 'Pengaturan Gateway Pembayaran (Contoh: Midtrans).', key: () => '', activeKey: 'payment_enabled', date: '29/07/2026' },
    { id: 'qrisly', name: 'QRISLY API', desc: 'Integrasi sistem pembayaran QRISLY.', key: () => '', activeKey: 'qrisly_enabled', date: '29/07/2026' },
];

const loadProvinces = async () => {
    try {
        const response = await axios.get(route('api.shipping.provinces'), { 
            params: { 
                api_key: props.form.rajaongkir_api_key
            } 
        });
        provinces.value = response.data.map(p => ({
            label: p.province || p.name,
            value: p.province_id || p.id
        }));
    } catch (e) {
        console.error('Gagal memuat provinsi RajaOngkir');
    }
};

const loadCities = async (provinceId) => {
    if (!provinceId) return;
    try {
        const response = await axios.get(route('api.shipping.cities'), { 
            params: { 
                province: provinceId, 
                api_key: props.form.rajaongkir_api_key
            } 
        });
        cities.value = response.data.map(c => ({
            label: c.type && c.city_name ? `${c.type} ${c.city_name}` : c.name,
            value: c.city_id || c.id
        }));
    } catch (e) {
        console.error('Gagal memuat kota RajaOngkir');
    }
};

const onProvinceChange = () => {
    cities.value = [];
    props.form.store_origin_city_id = null;
    loadCities(props.form.store_origin_province_id);
};

onMounted(async () => {
    if (props.form.rajaongkir_api_key) {
        await loadProvinces();
        if (props.form.store_origin_province_id) {
            await loadCities(props.form.store_origin_province_id);
        }
    }
});

const checkRajaOngkir = async () => {
    if (!props.form.rajaongkir_api_key) {
        alert('Silakan masukkan API Key RajaOngkir terlebih dahulu.');
        return;
    }
    await loadProvinces();
    if (provinces.value.length > 0) {
        alert('Berhasil terhubung ke RajaOngkir! Silakan pilih Provinsi dan Kota Asal Pengiriman.');
    } else {
        alert('Gagal terhubung! Pastikan API Key benar.');
    }
};
</script>

<template>
    <div class="space-y-6 bg-white dark:bg-gray-900 p-5 rounded-xl border border-gray-150 dark:border-gray-800">
        <div class="mb-4">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Manage API Keys for every services that you use</h3>
            <p class="text-sm text-gray-500">
                Pilih layanan di bawah ini untuk mengatur API Key dan konfigurasi lainnya.
            </p>
        </div>

        <!-- Integration List Table -->
        <div class="border border-gray-100 dark:border-gray-800 rounded-xl overflow-hidden bg-white dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                <div class="w-16 text-sm font-semibold text-gray-600 dark:text-gray-400"></div>
                <div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">API Name</div>
                <div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">API Key</div>
                <div class="w-1/6 text-sm font-semibold text-gray-600 dark:text-gray-400 text-center">Status</div>
                <div class="w-1/4 text-sm font-semibold text-gray-600 dark:text-gray-400">Added</div>
            </div>

            <!-- List Items -->
            <div 
                v-for="(item, index) in integrations" 
                :key="item.id"
                class="flex items-center p-4 border-b border-gray-100 dark:border-gray-800 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
            >
                <div class="w-16 text-sm text-gray-500 dark:text-gray-400 pl-2 flex items-center">
                    {{ index + 1 }}
                </div>
                <div class="w-1/4 flex items-center gap-3 cursor-pointer group" @click="openIntegration(item.id)">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ item.name }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-200 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
                <div class="w-1/4 flex items-center gap-2">
                    <template v-if="item.key()">
                        <span class="text-sm text-gray-600 dark:text-gray-400 font-mono tracking-widest" v-if="!showKeys[item.id]">{{ getMaskedKey(item.key()) }}</span>
                        <span class="text-sm text-gray-800 dark:text-gray-200 font-mono" v-else>{{ item.key() }}</span>
                        <button @click.stop="toggleKey(item.id)" class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 hover:text-amber-600 transition ml-2">
                            <svg v-if="!showKeys[item.id]" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            {{ showKeys[item.id] ? '' : '' }}
                        </button>
                    </template>
                    <template v-else>
                        <span class="text-xs text-gray-400 italic">Not set</span>
                    </template>
                </div>
                <div class="w-1/6 flex justify-center">
                    <ToggleSwitch v-model="form[item.activeKey]" />
                </div>
                <div class="w-1/4 text-sm text-gray-500 dark:text-gray-400 pl-4">
                    {{ item.date }}
                </div>
            </div>
        </div>

        <!-- Shipping Cost Modal (RajaOngkir) -->
        <Dialog 
            v-model:visible="modals.shipping" 
            modal 
            header="Pengaturan Shipping Cost (RajaOngkir)" 
            :style="{ width: '50rem' }" 
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <div class="text-sm text-gray-500 dark:text-gray-400 mb-6 border-b border-gray-100 dark:border-gray-800 pb-4">
                Hubungkan website Anda dengan API RajaOngkir untuk perhitungan ongkos kirim otomatis secara *real-time*.
                Anda bisa mendapatkan API Key gratis di <a href="https://rajaongkir.com" target="_blank" class="text-amber-600 font-bold hover:underline">rajaongkir.com</a>.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold">RajaOngkir API Key</label>
                    <div class="flex gap-2">
                        <InputText 
                            v-model="form.rajaongkir_api_key" 
                            placeholder="Ketik/Paste API Key di sini" 
                            class="w-full"
                        />
                        <button 
                            type="button"
                            @click="checkRajaOngkir"
                            class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-sm font-bold rounded-lg border border-gray-300 dark:border-gray-600 transition"
                        >
                            Cek
                        </button>
                    </div>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold">Provinsi Asal (Toko)</label>
                    <Select 
                        v-model="form.store_origin_province_id" 
                        :options="provinces" 
                        optionLabel="label" 
                        optionValue="value" 
                        placeholder="Pilih Provinsi"
                        class="w-full"
                        @change="onProvinceChange"
                    />
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="text-sm font-semibold">Kota Asal (Toko)</label>
                    <Select 
                        v-model="form.store_origin_city_id" 
                        :options="cities" 
                        optionLabel="label" 
                        optionValue="value" 
                        placeholder="Pilih Kota Asal"
                        class="w-full"
                    />
                    <p class="text-xs text-gray-500 mt-1">Semua ongkos kirim akan dihitung dengan titik awal dari kota ini.</p>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-800">
                <Button label="Selesai" icon="pi pi-check" @click="modals.shipping = false" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
            </div>
        </Dialog>

        <!-- Payment API Modal Placeholder -->
        <Dialog 
            v-model:visible="modals.payment" 
            modal 
            header="Pengaturan Payment API" 
            :style="{ width: '40rem' }" 
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <div class="p-4 text-center">
                <i class="pi pi-cog text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Konfigurasi Payment API akan segera tersedia.</p>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <Button label="Tutup" severity="secondary" @click="modals.payment = false" />
            </div>
        </Dialog>

        <!-- QRISLY API Modal Placeholder -->
        <Dialog 
            v-model:visible="modals.qrisly" 
            modal 
            header="Pengaturan QRISLY API" 
            :style="{ width: '40rem' }" 
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        >
            <div class="p-4 text-center">
                <i class="pi pi-qrcode text-4xl text-gray-300 mb-3"></i>
                <p class="text-gray-500">Konfigurasi QRISLY API akan segera tersedia.</p>
            </div>
            <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                <Button label="Tutup" severity="secondary" @click="modals.qrisly = false" />
            </div>
        </Dialog>

    </div>
</template>
