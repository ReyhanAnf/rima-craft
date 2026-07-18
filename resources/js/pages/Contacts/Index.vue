<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

const props = defineProps({
    contacts: Object,
    filters: Object,
});

const page = usePage();

// Filter Panel State
const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

const typeOptions = [
    { label: 'Semua Tipe Kontak', value: '' },
    { label: 'Pelanggan (Customer)', value: 'customer' },
    { label: 'Supplier', value: 'supplier' },
    { label: 'Pengrajin (Crafter)', value: 'crafter' },
];

const contactTypes = [
    { label: 'Pelanggan (Customer)', value: 'customer' },
    { label: 'Supplier', value: 'supplier' },
    { label: 'Pengrajin (Crafter)', value: 'crafter' },
];

let filterTimeout = null;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('contacts.index'), {
            search: searchQuery.value,
            type: typeFilter.value,
        }, { preserveState: true, replace: true });
    }, 400);
};

// Form & Modal State
const isFormOpen = ref(false);
const editingContact = ref(null);

const form = useForm({
    type: 'customer',
    name: '',
    phone: '',
    address: '',
});

const openCreateModal = () => {
    editingContact.value = null;
    form.clearErrors();
    form.reset();
    isFormOpen.value = true;
};

const openEditModal = (contact) => {
    editingContact.value = contact;
    form.clearErrors();
    form.type = contact.type;
    form.name = contact.name;
    form.phone = contact.phone || '';
    form.address = contact.address || '';
    isFormOpen.value = true;
};

const submitForm = () => {
    if (editingContact.value) {
        form.put(route('contacts.update', editingContact.value.id), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('contacts.store'), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteContact = (contact) => {
    if (confirm(`Hapus kontak ${contact.name}?`)) {
        router.delete(route('contacts.destroy', contact.id));
    }
};

const getBadgeClass = (type) => {
    switch (type) {
        case 'customer':
            return 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
        case 'supplier':
            return 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400';
        case 'crafter':
            return 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400';
        default:
            return 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
    }
};

const getTypeText = (type) => {
    switch (type) {
        case 'customer': return 'Pelanggan';
        case 'supplier': return 'Supplier';
        case 'crafter': return 'Pengrajin';
        default: return type;
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Kontak & Relasi" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Buku Kontak</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data pelanggan, supplier bahan baku, dan pengrajin lokal.</p>
                </div>
                <Button
                    label="Tambah Kontak"
                    icon="pi pi-plus"
                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold self-start md:self-auto"
                    @click="openCreateModal"
                />
            </div>

            <!-- Filters Panel -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Cari kontak..."
                        class="w-full !pl-9"
                        @input="applyFilters"
                    />
                </div>

                <Dropdown
                    v-model="typeFilter"
                    :options="typeOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Pilih Tipe Kontak"
                    class="w-full"
                    @change="applyFilters"
                />
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Nama</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tipe</th>
                                <th scope="col" class="px-6 py-4 font-bold">No. Telepon</th>
                                <th scope="col" class="px-6 py-4 font-bold">Alamat</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ contact.name }}
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs px-2.5 py-1 rounded-full font-bold uppercase', getBadgeClass(contact.type)]">
                                        {{ getTypeText(contact.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    {{ contact.phone || '-' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">
                                    {{ contact.address || '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(contact)" class="mr-2" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteContact(contact)" />
                                </td>
                            </tr>
                            <tr v-if="contacts.data.length === 0">
                                <td colspan="5" class="px-6 py-8 text-center text-gray-400">Tidak ada kontak ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile view -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div v-for="contact in contacts.data" :key="contact.id" class="p-4 flex justify-between items-center">
                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ contact.name }}</h4>
                            <div class="flex items-center gap-3 mt-1.5">
                                <span :class="['text-[10px] px-1.5 py-0.5 rounded font-bold uppercase', getBadgeClass(contact.type)]">
                                    {{ getTypeText(contact.type) }}
                                </span>
                                <span class="text-xs text-gray-500">{{ contact.phone || '-' }}</span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(contact)" />
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteContact(contact)" />
                        </div>
                    </div>
                    <div v-if="contacts.data.length === 0" class="p-6 text-center text-gray-400">Tidak ada kontak ditemukan.</div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="contacts.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ contacts.from || 0 }} - {{ contacts.to || 0 }} dari {{ contacts.total }} kontak</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in contacts.links"
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

            <!-- Form Dialog Modal -->
            <Dialog
                v-model:visible="isFormOpen"
                modal
                :header="editingContact ? 'Edit Kontak' : 'Tambah Kontak Baru'"
                class="w-full max-w-md"
            >
                <!-- Form Errors -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <!-- Type selection -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tipe Hubungan <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.type"
                            :options="contactTypes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Hubungan..."
                            class="w-full"
                            required
                        />
                    </div>

                    <!-- Name -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Lengkap <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Nama lengkap..." />
                    </div>

                    <!-- Phone -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nomor Telepon</label>
                        <InputText v-model="form.phone" placeholder="Contoh: 0812345..." />
                    </div>

                    <!-- Address -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Alamat Lengkap</label>
                        <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat..." />
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            :label="editingContact ? 'Simpan Perubahan' : 'Tambah Kontak'"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
