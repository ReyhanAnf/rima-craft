<script setup>
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';

const props = defineProps({
    form: Object,
    settings: Object,
    isDevAdmin: Boolean,
});

const handleFileChange = (e, field) => {
    props.form[field] = e.target.files[0];
};
</script>

<template>
    <div class="space-y-6">
        <div v-if="!isDevAdmin" class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 text-xs border border-amber-200 dark:border-amber-900/40 flex items-center gap-2.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold">Akses Terkunci!</span> Halaman pengaturan Logo, Subtitle, dan Sponsor SIMKURING hanya dapat diubah oleh **Developer Admin**. Akun Anda (Super Admin) hanya diizinkan untuk melihat.
            </div>
        </div>

        <!-- Subtitle -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Subtitle Brand / Project</span></template>
            <template #content>
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Subtitle Project / Sistem</label>
                        <InputText v-model="form.business_subtitle" :disabled="!isDevAdmin" placeholder="Contoh: Sistem Informasi Management Keuangan Daring (SIMKURING)" />
                        <p class="text-[10px] text-gray-500">Akan tampil dengan ukuran kecil di bawah nama brand pada navigasi bar atas website.</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Sponsors -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Sponsor Pembangunan Sistem</span></template>
            <template #content>
                <p class="text-xs text-gray-400 mb-4">Sponsor yang mendukung dan mendanai pembangunan sistem (Akan dirender di area kaki/footer web).</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                    <!-- Sponsor 1 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 1 (Contoh: UBSI)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_1_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Logo Sponsor</label>
                            <div class="flex items-center gap-3">
                                <input :disabled="!isDevAdmin" type="file" accept="image/*" @change="handleFileChange($event, 'sponsor_1_logo')" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 disabled:opacity-50" />
                                <img v-if="settings.sponsor_1_logo_url" :src="`/storage/${settings.sponsor_1_logo_url}`" class="h-8 max-w-[80px] object-contain rounded border border-gray-200 dark:border-gray-800 bg-white" alt="" />
                            </div>
                        </div>
                    </div>

                    <!-- Sponsor 2 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 2 (Contoh: Mentri Pendidikan)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_2_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Logo Sponsor</label>
                            <div class="flex items-center gap-3">
                                <input :disabled="!isDevAdmin" type="file" accept="image/*" @change="handleFileChange($event, 'sponsor_2_logo')" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 disabled:opacity-50" />
                                <img v-if="settings.sponsor_2_logo_url" :src="`/storage/${settings.sponsor_2_logo_url}`" class="h-8 max-w-[80px] object-contain rounded border border-gray-200 dark:border-gray-800 bg-white" alt="" />
                            </div>
                        </div>
                    </div>

                    <!-- Sponsor 3 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 3 (Opsional)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_3_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Logo Sponsor</label>
                            <div class="flex items-center gap-3">
                                <input :disabled="!isDevAdmin" type="file" accept="image/*" @change="handleFileChange($event, 'sponsor_3_logo')" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 disabled:opacity-50" />
                                <img v-if="settings.sponsor_3_logo_url" :src="`/storage/${settings.sponsor_3_logo_url}`" class="h-8 max-w-[80px] object-contain rounded border border-gray-200 dark:border-gray-800 bg-white" alt="" />
                            </div>
                        </div>
                    </div>

                    <!-- Sponsor 4 -->
                    <div class="p-4 border border-gray-200 dark:border-gray-800 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wide">Sponsor 4 (Opsional)</h4>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Nama Instansi/Sponsor</label>
                            <InputText v-model="form.sponsor_4_name" :disabled="!isDevAdmin" placeholder="Nama sponsor..." />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500">Logo Sponsor</label>
                            <div class="flex items-center gap-3">
                                <input :disabled="!isDevAdmin" type="file" accept="image/*" @change="handleFileChange($event, 'sponsor_4_logo')" class="block w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 disabled:opacity-50" />
                                <img v-if="settings.sponsor_4_logo_url" :src="`/storage/${settings.sponsor_4_logo_url}`" class="h-8 max-w-[80px] object-contain rounded border border-gray-200 dark:border-gray-800 bg-white" alt="" />
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
