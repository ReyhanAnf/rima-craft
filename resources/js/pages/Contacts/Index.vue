<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Drawer from 'primevue/drawer';
import Message from 'primevue/message';
import ConfirmDialog from 'primevue/confirmdialog';
import { useConfirm } from 'primevue/useconfirm';

const props = defineProps({
    contacts: Object,
    filters: Object,
});

const confirm = useConfirm();
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

// Form & Modal State (Create / Edit)
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
    confirm.require({
        message: `Hapus kontak "${contact.name}" dari buku kontak?`,
        header: 'Hapus Kontak',
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
            router.delete(route('contacts.destroy', contact.id));
        },
    });
};

// --- IMPORT KONTAK FEATURE STATE ---
const isImportDrawerOpen = ref(false);
const importMode = ref('csv'); // 'csv' or 'phone'
const defaultImportType = ref('customer');
const csvFileInput = ref(null);
const selectedCsvFile = ref(null);
const isCsvDragover = ref(false);
const pickedPhoneContacts = ref([]);
const phoneContactsSelection = ref([]);
const phoneErrorMsg = ref('');

const importForm = useForm({
    file: null,
    default_type: 'customer',
    contacts: [],
});

const isContactPickerSupported = computed(() => {
    return typeof window !== 'undefined' && 'contacts' in navigator && 'select' in navigator.contacts;
});

const openImportDrawer = () => {
    importForm.reset();
    importForm.clearErrors();
    selectedCsvFile.value = null;
    pickedPhoneContacts.value = [];
    phoneContactsSelection.value = [];
    phoneErrorMsg.value = '';
    importMode.value = 'csv';
    isImportDrawerOpen.value = true;
};

const triggerCsvFileInput = () => {
    csvFileInput.value?.click();
};

const onCsvFileChange = (e) => {
    const file = e.target.files?.[0];
    if (file) {
        selectedCsvFile.value = file;
        importForm.file = file;
    }
};

const onCsvDrop = (e) => {
    isCsvDragover.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) {
        selectedCsvFile.value = file;
        importForm.file = file;
    }
};

const removeSelectedCsvFile = () => {
    selectedCsvFile.value = null;
    importForm.file = null;
    if (csvFileInput.value) csvFileInput.value.value = '';
};

const downloadSampleCsv = () => {
    window.location.href = route('contacts.sample-csv');
};

// Native Web Contact Picker API (Chrome Android / Mobile / Opera)
const pickFromMobileContacts = async () => {
    phoneErrorMsg.value = '';
    try {
        const propsToSelect = ['name', 'tel', 'address', 'email'];
        const contacts = await navigator.contacts.select(propsToSelect, { multiple: true });
        
        if (!contacts || contacts.length === 0) {
            return;
        }

        const formatted = contacts.map((c, idx) => {
            const rawPhone = c.tel?.[0] || '';
            const cleanPhone = rawPhone.replace(/[^\d+]/g, '');
            return {
                id: idx,
                name: c.name?.[0] || 'Kontak Tanpa Nama',
                phone: cleanPhone,
                email: c.email?.[0] || '',
                address: c.address?.[0]?.addressLine || c.address?.[0]?.city || '',
                type: defaultImportType.value,
                selected: true,
            };
        });

        pickedPhoneContacts.value = formatted;
        phoneContactsSelection.value = formatted.map(item => item.id);
    } catch (err) {
        console.error('Error picking phone contacts:', err);
        phoneErrorMsg.value = 'Gagal mengakses kontak perangkat. Pastikan izin kontak telah diberikan di browser Anda.';
    }
};

const toggleSelectAllPhoneContacts = () => {
    if (phoneContactsSelection.value.length === pickedPhoneContacts.value.length) {
        phoneContactsSelection.value = [];
    } else {
        phoneContactsSelection.value = pickedPhoneContacts.value.map(item => item.id);
    }
};

const submitImport = () => {
    importForm.default_type = defaultImportType.value;

    if (importMode.value === 'csv') {
        if (!importForm.file) {
            alert('Silakan pilih file CSV terlebih dahulu.');
            return;
        }
        importForm.post(route('contacts.import'), {
            onSuccess: () => {
                isImportDrawerOpen.value = false;
                importForm.reset();
            },
        });
    } else if (importMode.value === 'phone') {
        const selectedList = pickedPhoneContacts.value.filter(item => phoneContactsSelection.value.includes(item.id));
        if (selectedList.length === 0) {
            alert('Silakan centang minimal 1 kontak dari perangkat untuk diimpor.');
            return;
        }
        importForm.contacts = selectedList.map(item => ({
            name: item.name,
            phone: item.phone,
            email: item.email,
            address: item.address,
            type: defaultImportType.value,
        }));

        importForm.post(route('contacts.import'), {
            onSuccess: () => {
                isImportDrawerOpen.value = false;
                importForm.reset();
            },
        });
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
        <ConfirmDialog :style="{ width: '90vw', maxWidth: '380px' }" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Buku Kontak</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola data pelanggan, supplier bahan baku, dan pengrajin lokal.</p>
                </div>
                <div class="flex items-center gap-2 self-start md:self-auto">
                    <Button
                        label="Import Kontak"
                        icon="pi pi-upload"
                        severity="secondary"
                        outlined
                        class="!text-xs font-bold"
                        @click="openImportDrawer"
                    />
                    <Button
                        label="Tambah Kontak"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        @click="openCreateModal"
                    />
                </div>
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

            <!-- Form Drawer (Create / Edit) -->
            <Drawer
                v-model:visible="isFormOpen"
                position="right"
                :header="editingContact ? 'Edit Kontak' : 'Tambah Kontak Baru'"
                class="!w-full sm:!w-[420px]"
            >
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
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

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Lengkap <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Nama lengkap..." />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nomor Telepon</label>
                        <InputText v-model="form.phone" placeholder="Contoh: 0812345..." />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Alamat Lengkap</label>
                        <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat..." />
                    </div>

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
            </Drawer>

            <!-- Import Kontak Drawer -->
            <Drawer
                v-model:visible="isImportDrawerOpen"
                position="right"
                header="Import Kontak Banyak"
                class="!w-full sm:!w-[520px]"
            >
                <div class="space-y-5 pt-2">
                    <!-- Default Type Selector -->
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Tipe Kontak Hasil Import <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="defaultImportType"
                            :options="contactTypes"
                            optionLabel="label"
                            optionValue="value"
                            class="w-full"
                        />
                        <span class="text-[10px] text-gray-400">Seluruh kontak yang diimpor akan disimpan sebagai tipe ini jika tidak ditentukan di CSV.</span>
                    </div>

                    <!-- Mode Selector Tab -->
                    <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                        <button
                            type="button"
                            @click="importMode = 'csv'"
                            :class="[
                                'flex-1 py-2 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2',
                                importMode === 'csv'
                                    ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <i class="pi pi-file-excel"></i>
                            File CSV / Excel
                        </button>
                        <button
                            type="button"
                            @click="importMode = 'phone'"
                            :class="[
                                'flex-1 py-2 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2',
                                importMode === 'phone'
                                    ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                            ]"
                        >
                            <i class="pi pi-mobile"></i>
                            Kontak HP / Perangkat
                        </button>
                    </div>

                    <!-- Mode A: CSV File Import -->
                    <div v-if="importMode === 'csv'" class="space-y-4">
                        <div class="flex justify-between items-center bg-amber-50/50 dark:bg-amber-500/10 p-3 rounded-xl border border-amber-200 dark:border-amber-500/20">
                            <div class="space-y-0.5">
                                <p class="text-xs font-bold text-amber-800 dark:text-amber-300">Format File CSV</p>
                                <p class="text-[10px] text-amber-700 dark:text-amber-400">Gunakan format header: <code>nama, tipe, telepon, email, alamat</code></p>
                            </div>
                            <Button
                                label="Download Template"
                                icon="pi pi-download"
                                severity="warn"
                                size="small"
                                text
                                class="!text-xs shrink-0 font-bold"
                                @click="downloadSampleCsv"
                            />
                        </div>

                        <input
                            ref="csvFileInput"
                            type="file"
                            accept=".csv,text/csv,text/plain"
                            class="hidden"
                            @change="onCsvFileChange"
                        />

                        <div v-if="!selectedCsvFile">
                            <div
                                @click="triggerCsvFileInput"
                                @dragover.prevent="isCsvDragover = true"
                                @dragleave.prevent="isCsvDragover = false"
                                @drop.prevent="onCsvDrop"
                                :class="[
                                    'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center min-h-[140px]',
                                    isCsvDragover
                                        ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-500/10'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 bg-gray-50/50 dark:bg-gray-800/40 hover:bg-amber-50/20 dark:hover:bg-amber-500/5'
                                ]"
                            >
                                <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2">
                                    <i class="pi pi-upload text-lg"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    Klik atau seret file <span class="text-amber-600 dark:text-amber-400">.CSV</span> ke sini
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">Ukuran maksimal file: 5MB</p>
                            </div>
                        </div>

                        <div v-else class="p-4 rounded-xl border border-emerald-300 dark:border-emerald-700/60 bg-emerald-50/40 dark:bg-emerald-950/20 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                                    <i class="pi pi-file-excel text-lg"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ selectedCsvFile.name }}</p>
                                    <p class="text-[10px] text-gray-500">{{ (selectedCsvFile.size / 1024).toFixed(1) }} KB</p>
                                </div>
                            </div>
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="removeSelectedCsvFile" />
                        </div>
                    </div>

                    <!-- Mode B: Phone Device Contacts -->
                    <div v-else-if="importMode === 'phone'" class="space-y-4">
                        <div v-if="!isContactPickerSupported" class="p-4 rounded-xl border border-amber-300 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/20 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                            <p class="font-bold inline-flex items-center gap-1">
                                <i class="pi pi-exclamation-triangle"></i>
                                Web Contact Picker API tidak didukung di peramban ini.
                            </p>
                            <p class="text-[11px] leading-relaxed text-amber-700 dark:text-amber-400">
                                Fitur ambil kontak HP langsung membutuhkan peramban <strong>Google Chrome Mobile (Android)</strong> atau peramban smartphone yang mendukung izin kontak native. Anda dapat beralih ke tab <strong>File CSV</strong>.
                            </p>
                        </div>

                        <div v-else class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold text-gray-800 dark:text-gray-200">Ambil dari Daftar Kontak HP</p>
                                    <p class="text-[10px] text-gray-400">Pilih kontak dari daftar kontak smartphone Anda secara langsung.</p>
                                </div>
                                <Button
                                    label="Buka Kontak HP"
                                    icon="pi pi-mobile"
                                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 !text-gray-950 font-bold !text-xs shrink-0"
                                    @click="pickFromMobileContacts"
                                />
                            </div>

                            <Message v-if="phoneErrorMsg" severity="error" size="small">
                                {{ phoneErrorMsg }}
                            </Message>

                            <!-- Picked List -->
                            <div v-if="pickedPhoneContacts.length > 0" class="space-y-2 pt-2">
                                <div class="flex justify-between items-center pb-1 border-b border-gray-100 dark:border-gray-800">
                                    <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                                        Terpilih: {{ phoneContactsSelection.length }} dari {{ pickedPhoneContacts.length }} kontak
                                    </span>
                                    <Button
                                        :label="phoneContactsSelection.length === pickedPhoneContacts.length ? 'Batal Semua' : 'Pilih Semua'"
                                        size="small"
                                        text
                                        class="!text-[11px]"
                                        @click="toggleSelectAllPhoneContacts"
                                    />
                                </div>

                                <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 border border-gray-200 dark:border-gray-800 rounded-xl">
                                    <div
                                        v-for="item in pickedPhoneContacts"
                                        :key="item.id"
                                        class="p-2.5 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-800/40"
                                    >
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <input
                                                type="checkbox"
                                                :id="`contact_check_${item.id}`"
                                                :value="item.id"
                                                v-model="phoneContactsSelection"
                                                class="w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-500"
                                            />
                                            <label :for="`contact_check_${item.id}`" class="min-w-0 cursor-pointer">
                                                <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ item.name }}</p>
                                                <p class="text-[10px] text-gray-400 truncate">{{ item.phone || item.email || 'Tanpa telepon' }}</p>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isImportDrawerOpen = false" />
                        <Button
                            type="button"
                            label="Mulai Import Kontak"
                            icon="pi pi-check"
                            :loading="importForm.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold !text-xs"
                            @click="submitImport"
                        />
                    </div>
                </div>
            </Drawer>
        </div>
    </AdminLayout>
</template>
