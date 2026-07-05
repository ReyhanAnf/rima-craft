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
</template>
