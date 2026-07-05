<script setup>
import Card from 'primevue/card';
import Editor from 'primevue/editor';

defineProps({
    form: Object,
});
</script>

<template>
    <div class="space-y-6">
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Konten Halaman Informasi</span></template>
            <template #content>
                <p class="text-xs text-gray-400 mb-4">Kelola isi dari masing-masing halaman info bisnis.</p>
                <div class="space-y-6">
                    <!-- Terms and Conditions -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Syarat & Ketentuan (Terms and Conditions)</label>
                        <Editor 
                            v-model="form.page_terms" 
                            editorStyle="height: 200px" 
                            placeholder="Tulis syarat dan ketentuan layanan..." 
                            class="w-full"
                        >
                            <template #toolbar>
                                <span class="ql-formats">
                                    <button class="ql-bold" title="Tebal"></button>
                                    <button class="ql-italic" title="Miring"></button>
                                    <button class="ql-underline" title="Garis Bawah"></button>
                                    <button class="ql-strike" title="Coret"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered" title="Nomor"></button>
                                    <button class="ql-list" value="bullet" title="Bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <select class="ql-align"></select>
                                    <button class="ql-link" title="Link"></button>
                                    <button class="ql-clean" title="Bersihkan Format"></button>
                                </span>
                            </template>
                        </Editor>
                    </div>

                    <!-- Privacy Policy -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Kebijakan Privasi (Privacy Policy)</label>
                        <Editor 
                            v-model="form.page_privacy" 
                            editorStyle="height: 200px" 
                            placeholder="Tulis kebijakan penanganan data pribadi..." 
                            class="w-full"
                        >
                            <template #toolbar>
                                <span class="ql-formats">
                                    <button class="ql-bold" title="Tebal"></button>
                                    <button class="ql-italic" title="Miring"></button>
                                    <button class="ql-underline" title="Garis Bawah"></button>
                                    <button class="ql-strike" title="Coret"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered" title="Nomor"></button>
                                    <button class="ql-list" value="bullet" title="Bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <select class="ql-align"></select>
                                    <button class="ql-link" title="Link"></button>
                                    <button class="ql-clean" title="Bersihkan Format"></button>
                                </span>
                            </template>
                        </Editor>
                    </div>

                    <!-- Shipping and Returns -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Pengiriman & Retur (Shipping & Returns)</label>
                        <Editor 
                            v-model="form.page_shipping" 
                            editorStyle="height: 200px" 
                            placeholder="Tulis aturan pengiriman dan pengembalian barang..." 
                            class="w-full"
                        >
                            <template #toolbar>
                                <span class="ql-formats">
                                    <button class="ql-bold" title="Tebal"></button>
                                    <button class="ql-italic" title="Miring"></button>
                                    <button class="ql-underline" title="Garis Bawah"></button>
                                    <button class="ql-strike" title="Coret"></button>
                                </span>
                                <span class="ql-formats">
                                    <button class="ql-list" value="ordered" title="Nomor"></button>
                                    <button class="ql-list" value="bullet" title="Bullet"></button>
                                </span>
                                <span class="ql-formats">
                                    <select class="ql-align"></select>
                                    <button class="ql-link" title="Link"></button>
                                    <button class="ql-clean" title="Bersihkan Format"></button>
                                </span>
                            </template>
                        </Editor>
                    </div>
                </div>
            </template>
        </Card>
    </div>
</template>
