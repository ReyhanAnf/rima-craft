<script setup>
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import ImageUpload from './ImageUpload.vue';

const props = defineProps({
    form: Object,
    settings: Object,
    isDevAdmin: Boolean,
});
</script>

<template>
    <div class="space-y-6">
        <div v-if="!isDevAdmin" class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 text-xs border border-amber-200 dark:border-amber-900/40 flex items-center gap-2.5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold">Akses Terkunci!</span> Pengaturan Logo, Subtitle, dan Sponsor hanya dapat diubah oleh Developer Admin.
            </div>
        </div>

        <!-- Subtitle -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Subtitle Brand / Project</span></template>
            <template #content>
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Subtitle Project / Sistem</label>
                        <InputText v-model="form.business_subtitle" :disabled="!isDevAdmin" placeholder="Contoh: Sistem Informasi Management Keuangan Daring" />
                        <p class="text-[10px] text-gray-500">Tampil di bawah nama brand pada navigasi bar website.</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Sponsors -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Sponsor Pembangunan Sistem</span></template>
            <template #content>
                <p class="text-xs text-gray-400 mb-5">Sponsor yang mendukung pembangunan sistem — ditampilkan di footer website.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">

                    <!-- Sponsor 1 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 1</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_1_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <ImageUpload
                            label="Logo Sponsor"
                            :current-url="settings.sponsor_1_logo_url"
                            preview-class="h-12 w-20"
                            :disabled="!isDevAdmin"
                            @change="(file) => form.sponsor_1_logo = file"
                        />
                    </div>

                    <!-- Sponsor 2 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 2</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_2_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <ImageUpload
                            label="Logo Sponsor"
                            :current-url="settings.sponsor_2_logo_url"
                            preview-class="h-12 w-20"
                            :disabled="!isDevAdmin"
                            @change="(file) => form.sponsor_2_logo = file"
                        />
                    </div>

                    <!-- Sponsor 3 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 3 (Opsional)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_3_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <ImageUpload
                            label="Logo Sponsor"
                            :current-url="settings.sponsor_3_logo_url"
                            preview-class="h-12 w-20"
                            :disabled="!isDevAdmin"
                            @change="(file) => form.sponsor_3_logo = file"
                        />
                    </div>

                    <!-- Sponsor 4 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 4 (Opsional)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_4_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <ImageUpload
                            label="Logo Sponsor"
                            :current-url="settings.sponsor_4_logo_url"
                            preview-class="h-12 w-20"
                            :disabled="!isDevAdmin"
                            @change="(file) => form.sponsor_4_logo = file"
                        />
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
