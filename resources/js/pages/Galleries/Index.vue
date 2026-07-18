<script setup>
import { ref, watch } from 'vue';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

const props = defineProps({
    galleries: Array,
});

const page = usePage();

const isUploadOpen = ref(false);

const form = useForm({
    image: null,
    label: '',
    title: '',
});

const handleFileChange = (e) => {
    form.image = e.target.files[0];
};

const submitForm = () => {
    form.post(route('galleries.store'), {
        onSuccess: () => {
            isUploadOpen.value = false;
            form.reset();
        }
    });
};

const deletePhoto = (photo) => {
    if (confirm('Hapus foto ini dari galeri?')) {
        router.delete(route('galleries.destroy', photo.id));
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Galeri Foto" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Galeri Foto</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola foto portofolio dan katalog yang ditampilkan di Landing Page.</p>
                </div>
                <Button
                    label="Unggah Foto"
                    icon="pi pi-plus"
                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold self-start md:self-auto"
                    @click="isUploadOpen = true"
                />
            </div>

            <!-- Grid Gallery -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div v-for="photo in galleries" :key="photo.id" class="group relative bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition">
                    <img :src="`/storage/${photo.image_url}`" class="w-full h-48 object-cover" alt="" />
                    <div class="p-3">
                        <div class="font-bold text-sm text-gray-900 dark:text-white truncate">{{ photo.title || 'Tanpa Judul' }}</div>
                        <div class="text-xs text-gray-400 truncate mt-0.5">{{ photo.label || 'Kategori Umum' }}</div>
                    </div>
                    <!-- Overlay actions -->
                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button icon="pi pi-trash" severity="danger" rounded @click="deletePhoto(photo)" />
                    </div>
                </div>

                <div v-if="galleries.length === 0" class="col-span-full py-12 text-center text-gray-400">
                    Belum ada foto dalam galeri.
                </div>
            </div>

            <!-- Upload Photo Modal -->
            <Dialog v-model:visible="isUploadOpen" modal header="Unggah Foto Baru" class="w-full max-w-sm">
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Pilih File Foto <span class="text-red-500">*</span></label>
                        <input type="file" accept="image/*" @change="handleFileChange" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400" required />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Judul / Nama Karya</label>
                        <InputText v-model="form.title" placeholder="Contoh: Keranjang Rotan Aura" />
                    </div>
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Label / Kategori</label>
                        <InputText v-model="form.label" placeholder="Contoh: Anyaman Bambu, Furnitur..." />
                    </div>
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isUploadOpen = false" />
                        <Button type="submit" label="Unggah Foto" :loading="form.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
