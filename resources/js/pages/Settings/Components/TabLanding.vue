<script setup>
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import ImageUpload from './ImageUpload.vue';

const props = defineProps({
    form: Object,
    settings: Object,
});
</script>

<template>
    <div class="space-y-6">
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Hero Section (Bagian Atas)</span></template>
            <template #content>
                <div class="space-y-4 pt-2">
                    <ImageUpload
                        label="Gambar Latar (Hero Background)"
                        hint="Disarankan ukuran minimal 1920×1080px. Format: JPG, PNG, WebP."
                        :current-url="settings.hero_image_url"
                        preview-class="h-20 w-32"
                        @change="(file) => form.hero_image = file"
                    />
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
                <div class="space-y-5 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">File Video Pendek (.mp4 untuk Looping Background)</label>
                        <label class="flex items-center gap-2.5 w-full cursor-pointer rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-amber-400 hover:bg-amber-50/40 px-4 py-3 text-xs font-medium text-gray-600 dark:text-gray-300 transition">
                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            <span>{{ form.looping_video ? form.looping_video.name : 'Pilih file .mp4...' }}</span>
                            <span v-if="settings.looping_video_url && !form.looping_video" class="ml-auto text-emerald-600 dark:text-emerald-400 font-semibold text-[10px]">✓ Tersimpan</span>
                            <input type="file" accept="video/mp4" class="sr-only" @change="(e) => form.looping_video = e.target.files[0]" />
                        </label>
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
