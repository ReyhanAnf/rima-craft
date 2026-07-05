<script setup>
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

defineProps({
    form: Object,
});
</script>

<template>
    <div class="space-y-6">
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
</template>
