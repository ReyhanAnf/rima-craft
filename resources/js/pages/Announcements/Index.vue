<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Drawer from 'primevue/drawer';
import Message from 'primevue/message';
import ToggleSwitch from 'primevue/toggleswitch';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    announcements: Object,
});

const confirm = useConfirm();
const isFormOpen = ref(false);
const editingAnnouncement = ref(null);

const form = useForm({
    title: '',
    content: '',
    type: 'info',
    is_active: true,
    url: '',
});

const typeOptions = [
    { label: 'Informasi (Kuning Amber)', value: 'info' },
    { label: 'Peringatan (Oranye Amber)', value: 'warning' },
    { label: 'Berhasil / Promo (Hijau)', value: 'success' },
    { label: 'Penting / Darurat (Merah)', value: 'danger' },
];

function openCreateDrawer() {
    editingAnnouncement.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'info';
    form.is_active = true;
    isFormOpen.value = true;
}

function openEditDrawer(announcement) {
    editingAnnouncement.value = announcement;
    form.clearErrors();
    form.title = announcement.title || '';
    form.content = announcement.content || '';
    form.type = announcement.type || 'info';
    form.is_active = Boolean(announcement.is_active);
    form.url = announcement.url || '';
    isFormOpen.value = true;
}

function submitForm() {
    if (editingAnnouncement.value) {
        form.put(route('announcements.update', editingAnnouncement.value.id), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('announcements.store'), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            },
        });
    }
}

function toggleActive(announcement) {
    router.patch(route('announcements.toggle', announcement.id), {}, {
        preserveScroll: true,
    });
}

function rebroadcast(announcement) {
    confirm.require({
        message: 'Munculkan ulang pengumuman ini untuk semua pengguna?',
        header: 'Munculkan Ulang',
        icon: 'pi pi-bell text-amber-500 text-xl',
        rejectProps: {
            label: 'Batal',
            severity: 'secondary',
            text: true,
        },
        acceptProps: {
            label: 'Munculkan',
            class: '!bg-amber-500 hover:!bg-amber-600 !border-amber-500 !text-gray-950 font-bold',
        },
        accept: () => {
            router.post(route('announcements.rebroadcast', announcement.id), {}, {
                preserveScroll: true,
            });
        },
    });
}

function deleteAnnouncement(announcement) {
    confirm.require({
        message: `Hapus pengumuman "${announcement.title || 'ini'}"?`,
        header: 'Hapus Pengumuman',
        icon: 'pi pi-exclamation-triangle text-red-500 text-xl',
        rejectProps: {
            label: 'Batal',
            severity: 'secondary',
            text: true,
        },
        acceptProps: {
            label: 'Hapus',
            severity: 'danger',
            class: 'font-bold',
        },
        accept: () => {
            router.delete(route('announcements.destroy', announcement.id), {
                preserveScroll: true,
            });
        },
    });
}

function typeBadgeClass(type) {
    switch (type) {
        case 'warning':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
        case 'danger':
            return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
        case 'success':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
        case 'info':
        default:
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300';
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Pengumuman" />
        <ConfirmDialog :style="{ width: '90vw', maxWidth: '380px' }" />

        <div class="space-y-6">
            <!-- Top Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Pengumuman Sistem</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Kelola pesan pengumuman ramping (*slim banner*) yang nempel di bagian paling atas seluruh halaman website.
                    </p>
                </div>
                <Button
                    label="Buat Pengumuman"
                    icon="pi pi-plus"
                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold shrink-0"
                    @click="openCreateDrawer"
                />
            </div>

            <!-- Announcements Table (Desktop) & Cards (Mobile) -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table View -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase font-bold tracking-wider border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th class="p-4">Pengumuman</th>
                                <th class="p-4">Tipe</th>
                                <th class="p-4">Status (Toggle)</th>
                                <th class="p-4">Dibuat Oleh</th>
                                <th class="p-4">Tanggal</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="item in announcements.data" :key="item.id" class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition">
                                <td class="p-4 min-w-[240px]">
                                    <div v-if="item.title" class="font-bold text-gray-900 dark:text-white text-sm mb-0.5">{{ item.title }}</div>
                                    <p class="text-gray-600 dark:text-gray-300 line-clamp-2">{{ item.content }}</p>
                                    <a v-if="item.url" :href="item.url" target="_blank" class="text-[10px] text-amber-600 hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                                        Link: {{ item.url }} <i class="pi pi-external-link text-[8px]"></i>
                                    </a>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <span :class="['px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider', typeBadgeClass(item.type)]">
                                        {{ item.type }}
                                    </span>
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <ToggleSwitch
                                            :modelValue="Boolean(item.is_active)"
                                            @update:modelValue="toggleActive(item)"
                                        />
                                        <span :class="['font-bold text-xs', item.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400']">
                                            {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="p-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                    {{ item.created_by }}
                                </td>
                                <td class="p-4 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ item.created_at }}
                                </td>
                                <td class="p-4 whitespace-nowrap text-right space-x-1">
                                    <Button
                                        v-if="item.is_active"
                                        icon="pi pi-bell"
                                        label="Munculkan Ulang"
                                        severity="warn"
                                        size="small"
                                        outlined
                                        class="!text-xs font-semibold"
                                        @click="rebroadcast(item)"
                                        title="Paksa pengumuman ini muncul kembali untuk seluruh pengunjung/pengguna yang pernah menutupnya"
                                    />
                                    <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditDrawer(item)" title="Edit" />
                                    <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteAnnouncement(item)" title="Hapus" />
                                </td>
                            </tr>
                            <tr v-if="announcements.data.length === 0">
                                <td colspan="6" class="p-8 text-center text-gray-400">
                                    Belum ada pengumuman sistem yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card View -->
                <div class="block md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="item in announcements.data" :key="item.id" class="p-4 space-y-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span :class="['px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider', typeBadgeClass(item.type)]">
                                    {{ item.type }}
                                </span>
                                <h3 v-if="item.title" class="font-bold text-gray-900 dark:text-white text-sm">{{ item.title }}</h3>
                            </div>
                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed">{{ item.content }}</p>
                            <a v-if="item.url" :href="item.url" target="_blank" class="text-[10px] text-amber-600 hover:underline inline-flex items-center gap-1 font-semibold block mt-1">
                                Link: {{ item.url }} <i class="pi pi-external-link text-[8px]"></i>
                            </a>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <ToggleSwitch
                                    :modelValue="Boolean(item.is_active)"
                                    @update:modelValue="toggleActive(item)"
                                />
                                <span :class="['font-bold text-xs', item.is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400']">
                                    {{ item.is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </div>
                            <div class="flex items-center gap-1">
                                <Button
                                    v-if="item.is_active"
                                    icon="pi pi-bell"
                                    label="Munculkan Ulang"
                                    severity="warn"
                                    size="small"
                                    outlined
                                    class="!text-xs font-semibold"
                                    @click="rebroadcast(item)"
                                />
                                <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditDrawer(item)" />
                                <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteAnnouncement(item)" />
                            </div>
                        </div>

                        <div class="text-[10px] text-gray-400 flex justify-between pt-1">
                            <span>Oleh: {{ item.created_by }}</span>
                            <span>{{ item.created_at }}</span>
                        </div>
                    </div>
                    <div v-if="announcements.data.length === 0" class="p-6 text-center text-gray-400 text-xs">
                        Belum ada pengumuman sistem yang dibuat.
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="announcements.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ announcements.from || 0 }} - {{ announcements.to || 0 }} dari {{ announcements.total }} pengumuman</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in announcements.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="[
                                'px-3 py-1.5 rounded-lg text-xs font-semibold border transition',
                                link.active
                                    ? 'bg-amber-500 text-gray-950 border-amber-500 font-bold'
                                    : 'bg-white dark:bg-gray-900 border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800',
                                !link.url ? 'opacity-40 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>

            <!-- Announcement Drawer -->
            <Drawer
                v-model:visible="isFormOpen"
                position="right"
                :header="editingAnnouncement ? 'Edit Pengumuman' : 'Buat Pengumuman Baru'"
                class="!w-full sm:!w-[460px]"
            >
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Judul Singkat (Opsional)</label>
                        <InputText v-model="form.title" placeholder="Contoh: Promo Ramadhan / Maintenance..." />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Isi Teks Pengumuman <span class="text-red-500">*</span></label>
                        <Textarea
                            v-model="form.content"
                            rows="4"
                            required
                            placeholder="Tulis pesan pengumuman yang akan muncul nempel di paling atas website..."
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tipe & Warna Banner <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.type"
                            :options="typeOptions"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tautan / URL (Opsional)</label>
                        <InputText v-model="form.url" placeholder="https://rimacraft.com/katalog atau /promo" />
                        <span class="text-[10px] text-gray-500">Jika diisi, pengunjung dapat mengklik tombol 'Selengkapnya' pada banner.</span>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <ToggleSwitch v-model="form.is_active" id="is_active_check" />
                        <label for="is_active_check" class="text-xs font-semibold cursor-pointer select-none">
                            Langsung Aktifkan Banner Pengumuman Ini
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 border-t border-gray-150 dark:border-gray-800 pt-4">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            :label="editingAnnouncement ? 'Simpan Perubahan' : 'Terbitkan Pengumuman'"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </form>
            </Drawer>
        </div>
    </AdminLayout>
</template>
