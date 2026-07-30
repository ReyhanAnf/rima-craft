<script setup>
import { watch } from 'vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

const props = defineProps({
    form: Object,
    settings: Object,
});

const handleFileChange = (e, field) => {
    props.form[field] = e.target.files[0];
};

watch(() => props.form.gmaps_iframe, (newVal) => {
    if (newVal && newVal.includes('<iframe') && newVal.includes('src="')) {
        const match = newVal.match(/src="([^"]+)"/);
        if (match && match[1]) {
            props.form.gmaps_iframe = match[1];
        }
    }
});
</script>

<template>
    <div class="space-y-6">
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Informasi Bisnis</span></template>
            <template #content>
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Logo Website Resmi</label>
                        <div class="flex items-center gap-3">
                            <input type="file" accept="image/*" @change="handleFileChange($event, 'logo')" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400" />
                            <img v-if="settings.logo_url" :src="`/storage/${settings.logo_url}`" class="h-10 max-w-[100px] object-contain rounded border border-gray-200 dark:border-gray-800 p-1 bg-gray-50" alt="Logo" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Bisnis</label>
                            <InputText v-model="form.business_name" required />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nomor WhatsApp Utama</label>
                            <InputText v-model="form.business_phone" required placeholder="Contoh: 6281234567890" />
                        </div>
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
                        <label class="text-xs font-semibold">Google Maps Embed (URL / Kode Iframe)</label>
                        <InputText v-model="form.gmaps_iframe" placeholder="Paste URL atau seluruh kode <iframe...> di sini" />
                        <p class="text-[10px] text-gray-500">
                            <b>Panduan:</b> Buka <a href="https://maps.google.com" target="_blank" class="text-emerald-600 hover:underline">Google Maps</a> > Cari Lokasi > Klik <b>Bagikan</b> > Pilih <b>Sematkan Peta</b> > Klik <b>Salin HTML</b>, lalu langsung paste ke kolom di atas. Sistem otomatis memprosesnya.
                        </p>
                        <div v-if="form.gmaps_iframe" class="mt-2 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-800 h-48 relative bg-gray-50 dark:bg-gray-800/50 shadow-inner">
                            <iframe 
                                :src="form.gmaps_iframe" 
                                width="100%" 
                                height="100%" 
                                style="border:0;" 
                                allowfullscreen="" 
                                loading="lazy" 
                                referrerpolicy="no-referrer-when-downgrade"
                                class="absolute inset-0 w-full h-full"
                            ></iframe>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Jam Operasional</label>
                        <Textarea v-model="form.business_hours" rows="3" placeholder="Contoh:&#10;Senin - Jumat: 08.00 - 17.00 WIB&#10;Sabtu: 08.00 - 14.00 WIB&#10;Minggu: Tutup" />
                        <p class="text-[10px] text-gray-500">
                            Anda dapat menekan <b>Enter</b> untuk memisahkan jam operasional per baris agar lebih rapi saat ditampilkan ke pelanggan.
                        </p>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
