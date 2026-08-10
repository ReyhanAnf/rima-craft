<script setup>
import { ref } from 'vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';

const props = defineProps({
    form: Object,
    settings: Object,
});

const faviconInput = ref(null);
const faviconPreview = ref(props.settings?.favicon_url ? `/storage/${props.settings.favicon_url}` : null);

const ogImageInput = ref(null);
const ogImagePreview = ref(props.settings?.seo_og_image_url ? `/storage/${props.settings.seo_og_image_url}` : null);

const triggerFaviconInput = () => {
    faviconInput.value?.click();
};

const onFaviconChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        props.form.favicon = file;
        faviconPreview.value = URL.createObjectURL(file);
    }
};

const removeFavicon = () => {
    props.form.favicon = null;
    faviconPreview.value = null;
    if (faviconInput.value) faviconInput.value.value = '';
};

const triggerOgImageInput = () => {
    ogImageInput.value?.click();
};

const onOgImageChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        props.form.seo_og_image = file;
        ogImagePreview.value = URL.createObjectURL(file);
    }
};

const removeOgImage = () => {
    props.form.seo_og_image = null;
    ogImagePreview.value = null;
    if (ogImageInput.value) ogImageInput.value.value = '';
};
</script>

<template>
    <div class="space-y-6">
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-search text-amber-500"></i>
                    <span class="text-sm font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">Optimasi Mesin Pencari (SEO) & Branding</span>
                </div>
            </template>

            <template #content>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-6">Informasi ini dibaca oleh Google saat mengindeks website dan oleh media sosial saat link dibagikan.</p>

                <div class="space-y-6">
                    <!-- Favicon Upload -->
                    <div class="space-y-2 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                            <span>Favicon Website (Ikon Tab & Google Index)</span>
                            <span class="text-[10px] text-gray-400 font-normal">Disarankan PNG/ICO 512x512px (Rasio 1:1)</span>
                        </label>

                        <input
                            ref="faviconInput"
                            type="file"
                            accept="image/png,image/x-icon,image/vnd.microsoft.icon,image/svg+xml,image/jpeg,image/webp"
                            class="hidden"
                            @change="onFaviconChange"
                        />

                        <div class="flex flex-wrap items-center gap-4 p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <div class="w-16 h-16 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 shadow-sm flex items-center justify-center shrink-0">
                                <img v-if="faviconPreview" :src="faviconPreview" class="w-full h-full object-contain" alt="Favicon preview" />
                                <i v-else class="pi pi-globe text-2xl text-gray-400"></i>
                            </div>

                            <div class="space-y-1.5 flex-1 min-w-0">
                                <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Ikon Tab & Favicon Google</p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Favicon akan tampil di samping nama toko Anda pada tab browser dan di hasil pencarian mesin cari Google.</p>
                                <div class="flex gap-2 pt-1">
                                    <Button
                                        type="button"
                                        label="Unggah Favicon"
                                        icon="pi pi-upload"
                                        severity="secondary"
                                        size="small"
                                        class="!text-xs font-bold"
                                        @click="triggerFaviconInput"
                                    />
                                    <Button
                                        v-if="faviconPreview"
                                        type="button"
                                        label="Hapus"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        size="small"
                                        class="!text-xs"
                                        @click="removeFavicon"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Share / OpenGraph Image Upload -->
                    <div class="space-y-2 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <label class="text-xs font-bold text-gray-800 dark:text-gray-200 flex items-center justify-between">
                            <span>Gambar Pratinjau Link Embed (OG Image / Social Share)</span>
                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-bold">Rekomendasi 1200 x 630px</span>
                        </label>

                        <input
                            ref="ogImageInput"
                            type="file"
                            accept="image/*"
                            class="hidden"
                            @change="onOgImageChange"
                        />

                        <div class="space-y-3 p-4 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30">
                            <div v-if="ogImagePreview" class="relative group max-w-md aspect-[1200/630] rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-md bg-gray-900">
                                <img :src="ogImagePreview" class="w-full h-full object-cover" alt="OG Image Preview" />
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    <Button
                                        type="button"
                                        label="Ganti Gambar Embed"
                                        icon="pi pi-pencil"
                                        severity="warn"
                                        size="small"
                                        class="!text-xs font-bold"
                                        @click="triggerOgImageInput"
                                    />
                                    <Button
                                        type="button"
                                        icon="pi pi-trash"
                                        severity="danger"
                                        size="small"
                                        class="!text-xs"
                                        @click="removeOgImage"
                                    />
                                </div>
                            </div>

                            <div v-else class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-xl hover:border-amber-500 transition cursor-pointer" @click="triggerOgImageInput">
                                <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2">
                                    <i class="pi pi-share-alt text-lg"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Klik untuk mengunggah Gambar Preview Link (WhatsApp / Sosmed)</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Format: JPG, PNG, WEBP (Resolusi ideal 1200 x 630 piksel)</p>
                            </div>

                            <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed">
                                Gambar ini akan otomatis muncul sebagai kartu pratinjau saat tautan/link website Anda dikirim melalui <strong>WhatsApp, Telegram, Facebook, Twitter (X), LinkedIn, Discord</strong>, dsb.
                            </p>
                        </div>
                    </div>

                    <!-- SEO Title -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold flex justify-between">
                            <span>SEO Title (Judul Halaman Utama)</span>
                            <span class="text-[10px] text-gray-400">{{ (form.seo_title || '').length }}/60 Karakter</span>
                        </label>
                        <InputText v-model="form.seo_title" placeholder="Contoh: Rima Craft - Kerajinan Tangan Autentik Nusantara" />
                        <p class="text-[10px] text-gray-500">Muncul pada tab peramban dan judul pencarian utama Google. Usahakan maksimal 60 karakter.</p>
                    </div>

                    <!-- SEO Meta Description -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold flex justify-between">
                            <span>SEO Meta Description (Deskripsi Ringkas)</span>
                            <span class="text-[10px] text-gray-400">{{ (form.seo_description || '').length }}/160 Karakter</span>
                        </label>
                        <Textarea v-model="form.seo_description" rows="3" placeholder="Contoh: Belanja produk kerajinan tangan khas Indonesia dari pengrajin lokal berkualitas tinggi..." />
                        <p class="text-[10px] text-gray-500">Ringkasan singkat website Anda. Tampil di bawah judul pada pencarian Google. Usahakan maksimal 160 karakter.</p>
                    </div>

                    <!-- SEO Meta Keywords -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">SEO Meta Keywords</label>
                        <InputText v-model="form.seo_keywords" placeholder="kerajinan, rima craft, anyaman, rotan, kayu jati, jepara" />
                        <p class="text-[10px] text-gray-500">Kata kunci utama bisnis Anda, pisahkan setiap kata dengan tanda koma.</p>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
