<script setup>
import { onMounted, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Message from 'primevue/message';

const props = defineProps({
    user: Object,
    contact: Object,
    provinces: Array,
});

const cities = ref([]);
const loadingCities = ref(false);

const form = useForm({
    name: props.user.name,
    phone: props.contact?.phone || '',
    address: props.contact?.address || '',
    province_id: props.contact?.province_id || '',
    city_id: props.contact?.city_id || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const loadCities = async (provinceId) => {
    if (!provinceId) {
        cities.value = [];
        return;
    }
    loadingCities.value = true;
    try {
        const res = await fetch(`/api/regions/${provinceId}/cities`);
        cities.value = await res.json();
    } catch (e) {
        console.error(e);
    } finally {
        loadingCities.value = false;
    }
};

const onProvinceChange = () => {
    form.city_id = '';
    loadCities(form.province_id);
};

onMounted(() => {
    if (form.province_id) {
        loadCities(form.province_id);
    }
});

const submitForm = () => {
    form.post(route('customer.profile.update'), {
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Profil Akun Saya" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Profil Pelanggan</h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data nama, no. telepon WhatsApp, alamat pengiriman, dan keamanan sandi Anda.</p>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm">
                <div class="space-y-6">
                    <!-- Data Profil -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                        <template #title>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Informasi Pribadi</span>
                        </template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Lengkap <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.name" required />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Alamat Email</label>
                                    <InputText :value="user.email" disabled class="opacity-60" />
                                    <p class="text-[10px] text-gray-400">Email tidak dapat diubah secara mandiri.</p>
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nomor WhatsApp</label>
                                    <InputText
                                        v-model="form.phone"
                                        type="tel"
                                        inputmode="numeric"
                                        @keydown="(e) => { if (!/[\d+\b]/.test(e.key) && !['Backspace','Delete','ArrowLeft','ArrowRight','Tab'].includes(e.key)) e.preventDefault() }"
                                        @input="form.phone = form.phone.replace(/[^0-9+]/g, '')"
                                        placeholder="Contoh: 0812..."
                                    />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Provinsi</label>
                                        <select
                                            v-model="form.province_id"
                                            @change="onProvinceChange"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-800 dark:border-gray-700 text-sm py-2 px-3"
                                        >
                                            <option value="">Pilih Provinsi</option>
                                            <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                                                {{ prov.name }}
                                            </option>
                                        </select>
                                    </div>

                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Kota / Kabupaten</label>
                                        <select
                                            v-model="form.city_id"
                                            :disabled="!form.province_id || loadingCities"
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500 dark:bg-gray-800 dark:border-gray-700 text-sm py-2 px-3 disabled:opacity-50"
                                        >
                                            <option value="">Pilih Kota / Kabupaten</option>
                                            <option v-for="city in cities" :key="city.id" :value="city.id">
                                                {{ city.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Alamat Pengiriman Lengkap (Jalan, RT/RW, No. Rumah)</label>
                                    <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat lengkap..." />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Ganti Password -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                        <template #title>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Keamanan & Sandi</span>
                        </template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <p class="text-xs text-gray-400">Kosongkan kolom di bawah jika Anda tidak ingin mengubah password.</p>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Password Saat Ini</label>
                                    <InputText v-model="form.current_password" type="password" placeholder="Masukkan password sekarang..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Password Baru</label>
                                    <InputText v-model="form.new_password" type="password" placeholder="Minimal 8 karakter..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Konfirmasi Password Baru</label>
                                    <InputText v-model="form.new_password_confirmation" type="password" placeholder="Ketik ulang password baru..." />
                                </div>
                            </div>
                        </template>
                        <template #footer>
                            <div class="flex justify-end pt-4 border-t border-gray-150 dark:border-gray-800">
                                <Button type="submit" label="Simpan Perubahan" :loading="form.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                            </div>
                        </template>
                    </Card>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
