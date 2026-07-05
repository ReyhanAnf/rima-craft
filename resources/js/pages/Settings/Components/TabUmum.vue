<script setup>
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
</template>
