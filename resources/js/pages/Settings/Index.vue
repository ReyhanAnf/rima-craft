<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Message from 'primevue/message';

const props = defineProps({
    settings: Object,
});

const toast = useToast();
const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Sukses', detail: flash.success, life: 3000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Error', detail: flash.error, life: 4000 });
    }
}, { deep: true, immediate: true });

const activeTab = ref('umum');

const form = useForm({
    // Umum
    business_name: props.settings.business_name || 'Rima Craft',
    business_phone: props.settings.business_phone || '6281234567890',
    email: props.settings.email || '',
    instagram: props.settings.instagram || '',
    address: props.settings.address || '',
    gmaps_iframe: props.settings.gmaps_iframe || '',
    business_hours: props.settings.business_hours || '',

    // Landing
    hero_badge: props.settings.hero_badge || 'Karya Autentik Nusantara',
    hero_title_1: props.settings.hero_title_1 || 'Seni Anyaman Tradisional',
    hero_title_2: props.settings.hero_title_2 || 'Bercita Rasa Modern',
    hero_description: props.settings.hero_description || '',
    looping_video_url: props.settings.looping_video_url || '',
    video_url: props.settings.video_url || '',
    hero_image: null,
    looping_video: null,
    gallery_1_image: null,
    gallery_2_image: null,

    // SEO
    seo_title: props.settings.seo_title || '',
    seo_description: props.settings.seo_description || '',
    seo_keywords: props.settings.seo_keywords || '',

    // Info Halaman
    page_terms: props.settings.page_terms || '',
    page_privacy: props.settings.page_privacy || '',
    page_shipping: props.settings.page_shipping || '',
});

const submitForm = () => {
    // Post as multipart because of potential files upload
    form.post(route('settings.update'), {
        preserveState: true,
        onSuccess: () => {
            form.clearErrors();
        }
    });
};

const handleFileChange = (e, field) => {
    form[field] = e.target.files[0];
};
</script>

<template>
    <AdminLayout>
        <Head title="Pengaturan Web" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Web</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Atur konten informasi bisnis dan Landing Page.</p>
                </div>
                
                <div class="inline-flex bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-800">
                    <button
                        @click="activeTab = 'umum'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all',
                            activeTab === 'umum'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        Data Umum
                    </button>
                    <button
                        @click="activeTab = 'landing'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all',
                            activeTab === 'landing'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        Landing Page
                    </button>
                    <button
                        @click="activeTab = 'seo'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all',
                            activeTab === 'seo'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        SEO & Meta
                    </button>
                    <button
                        @click="activeTab = 'info'"
                        :class="[
                            'px-4 py-2 text-xs font-bold rounded-lg transition-all',
                            activeTab === 'info'
                                ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700'
                                : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'
                        ]"
                    >
                        Halaman Info
                    </button>
                </div>
            </div>

            <!-- Form Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- TAB 1: UMUM -->
                <div v-show="activeTab === 'umum'" class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Informasi Bisnis</span></template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Bisnis</label>
                                    <InputText v-model="form.business_name" required />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nomor WhatsApp Utama</label>
                                    <InputText v-model="form.business_phone" required placeholder="Contoh: 6281234567890" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Informasi Kontak & Lokasi</span></template>
                        <template #content>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 mb-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Email</label>
                                    <InputText v-model="form.email" type="email" placeholder="Contoh: info@rimacraft.com" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Instagram Handle</label>
                                    <InputText v-model="form.instagram" placeholder="Contoh: rimacraft_id" />
                                </div>
                            </div>
                            <div class="space-y-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Alamat Lengkap Workshop</label>
                                    <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat lengkap workshop..." />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Google Maps Embed URL (SRC)</label>
                                    <InputText v-model="form.gmaps_iframe" placeholder="Contoh: https://www.google.com/maps/embed?pb=..." />
                                    <p class="text-[10px] text-gray-500">Buka Google Maps > Bagikan > Sematkan Peta > Copy isi dari <code>src="..."</code></p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Jam Operasional</label>
                                    <InputText v-model="form.business_hours" placeholder="Contoh: Senin–Sabtu, 08.00–17.00 WIB" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- TAB 2: LANDING PAGE -->
                <div v-show="activeTab === 'landing'" class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Hero Section (Bagian Atas)</span></template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Gambar Latar (Hero Background)</label>
                                    <div class="flex items-center gap-3">
                                        <input type="file" accept="image/*" @change="handleFileChange($event, 'hero_image')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400" />
                                        <img v-if="settings.hero_image_url" :src="`/storage/${settings.hero_image_url}`" class="w-10 h-10 object-cover rounded-md shadow-sm border border-gray-200 dark:border-gray-800" alt="" />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Badge (Teks Kecil di atas Judul)</label>
                                    <InputText v-model="form.hero_badge" />
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Judul Utama (Baris 1)</label>
                                        <InputText v-model="form.hero_title_1" />
                                    </div>
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Judul Utama (Baris 2 - Warna Emas)</label>
                                        <InputText v-model="form.hero_title_2" />
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Deskripsi Singkat</label>
                                    <Textarea v-model="form.hero_description" rows="3" />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Media Pendukung</span></template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">File Video Pendek (.mp4 untuk Looping Background)</label>
                                    <div class="flex items-center gap-3">
                                        <input type="file" accept="video/mp4" @change="handleFileChange($event, 'looping_video')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400" />
                                        <span v-if="settings.looping_video_url" class="text-xs text-emerald-600 font-bold whitespace-nowrap">✔ File Video Tersimpan</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">URL Video Pendek Eksternal (.mp4 opsional)</label>
                                    <InputText v-model="form.looping_video_url" placeholder="Contoh: https://domain.com/video.mp4" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">URL YouTube Video (Di Balik Layar)</label>
                                    <InputText v-model="form.video_url" placeholder="Contoh: https://www.youtube.com/embed/XXXXX" />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- TAB 3: SEO -->
                <div v-show="activeTab === 'seo'" class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Optimasi Mesin Pencari (SEO)</span></template>
                        <template #content>
                            <p class="text-xs text-gray-400 mb-4">Informasi ini akan dibaca oleh mesin pencari Google saat mengindeks website Anda.</p>
                            <div class="space-y-4">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">SEO Title (Judul Halaman Utama)</label>
                                    <InputText v-model="form.seo_title" />
                                    <p class="text-[10px] text-gray-500">Muncul di tab browser dan hasil pencarian Google. Usahakan max 60 karakter.</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">SEO Meta Description</label>
                                    <Textarea v-model="form.seo_description" rows="3" />
                                    <p class="text-[10px] text-gray-500">Ringkasan website Anda. Muncul di bawah judul pada pencarian Google. Usahakan max 160 karakter.</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">SEO Meta Keywords</label>
                                    <InputText v-model="form.seo_keywords" />
                                    <p class="text-[10px] text-gray-500">Kata kunci yang berhubungan dengan bisnis Anda, pisahkan dengan koma.</p>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- TAB 4: INFO PAGES -->
                <div v-show="activeTab === 'info'" class="space-y-6">
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
                        <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Konten Halaman Informasi</span></template>
                        <template #content>
                            <p class="text-xs text-gray-400 mb-4">Kelola isi dari masing-masing halaman info bisnis.</p>
                            <div class="space-y-6">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Syarat & Ketentuan (Terms and Conditions)</label>
                                    <Textarea v-model="form.page_terms" rows="6" placeholder="Tulis syarat dan ketentuan layanan..." />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Kebijakan Privasi (Privacy Policy)</label>
                                    <Textarea v-model="form.page_privacy" rows="6" placeholder="Tulis kebijakan penanganan data pribadi..." />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Pengiriman & Retur (Shipping & Returns)</label>
                                    <Textarea v-model="form.page_shipping" rows="6" placeholder="Tulis aturan pengiriman dan pengembalian barang..." />
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Save CTA -->
                <div class="flex justify-end gap-3">
                    <Button
                        type="submit"
                        label="Simpan Pengaturan"
                        icon="pi pi-save"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
