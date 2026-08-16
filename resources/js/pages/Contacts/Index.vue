<script setup>
import { ref, computed, watch } from 'vue';
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
    existingContacts: {
        type: Array,
        default: () => [],
    },
});

const confirm = useConfirm();
const page = usePage();

// Filter Panel State
const searchQuery = ref(props.filters?.search || '');
const typeFilter = ref(props.filters?.type || '');

const typeOptions = [
    { label: 'Semua Tipe Kontak', value: '' },
    { label: 'Pelanggan (Customer)', value: 'customer' },
    { label: 'Reseller', value: 'reseller' },
    { label: 'Supplier Bahan', value: 'supplier' },
    { label: 'Pengrajin (Crafter)', value: 'crafter' },
];

const contactTypes = [
    { label: 'Pelanggan (Customer)', value: 'customer' },
    { label: 'Reseller', value: 'reseller' },
    { label: 'Supplier Bahan', value: 'supplier' },
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
    email: '',
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
    form.type = contact.type || 'customer';
    form.name = contact.name || '';
    form.phone = contact.phone || '';
    form.email = contact.email || '';
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

// --- SMART PHONE & DUPLICATE UTILITIES ---
const cleanPhone = (val) => {
    if (!val) return '';
    let c = String(val).replace(/[\s\-\(\)\.]/g, '');
    if (c.startsWith('+62')) c = '0' + c.slice(3);
    else if (c.startsWith('62') && c.length > 9) c = '0' + c.slice(2);
    return c;
};

const checkDuplicate = (contact) => {
    const existing = props.existingContacts || [];
    const normPhone = cleanPhone(contact.phone);
    const normName = (contact.name || '').trim().toLowerCase();

    // Match by phone first
    if (normPhone) {
        const foundByPhone = existing.find(e => e.clean_phone && (e.clean_phone === normPhone || cleanPhone(e.phone) === normPhone));
        if (foundByPhone) {
            return {
                isDuplicate: true,
                reason: `No. telepon sudah terdaftar atas nama "${foundByPhone.name}" (${getTypeText(foundByPhone.type)})`,
            };
        }
    }

    // Match by exact name
    if (normName) {
        const foundByName = existing.find(e => e.name && e.name === normName);
        if (foundByName) {
            return {
                isDuplicate: true,
                reason: `Nama "${contact.name}" sudah ada di buku kontak (${getTypeText(foundByName.type)})`,
            };
        }
    }

    return { isDuplicate: false, reason: '' };
};

// --- IMPORT KONTAK FEATURE STATE ---
const isImportDrawerOpen = ref(false);
const importSourceTab = ref('android'); // 'android' or 'file'
const defaultImportType = ref('customer');
const duplicateStrategy = ref('skip'); // 'skip', 'update', 'create_all'
const previewSearch = ref('');
const previewFilterTab = ref('all'); // 'all', 'new', 'duplicate'
const showHelpAndroid = ref(false);

const fileInputRef = ref(null);
const selectedFile = ref(null);
const isDragover = ref(false);
const isParsingFile = ref(false);
const phoneErrorMsg = ref('');

// Parsed Contacts for Review
const parsedContacts = ref([]);
const isImporting = ref(false);

const importForm = useForm({
    default_type: 'customer',
    duplicate_strategy: 'skip',
    contacts: [],
});

const isContactPickerSupported = computed(() => {
    return typeof window !== 'undefined' && 'contacts' in navigator && 'select' in navigator.contacts;
});

// Update types for all contacts when default changes
watch(defaultImportType, (newType) => {
    if (parsedContacts.value.length > 0) {
        parsedContacts.value.forEach(c => {
            if (!c.customTypeSet) {
                c.type = newType;
            }
        });
    }
});

// Open Drawer and trigger Picker or select tab
const openImportDrawer = (preferredTab = 'android') => {
    phoneErrorMsg.value = '';
    selectedFile.value = null;
    parsedContacts.value = [];
    previewSearch.value = '';
    previewFilterTab.value = 'all';
    importSourceTab.value = preferredTab;
    isImportDrawerOpen.value = true;
};

// 1-Click Auto Import direct from Android
const triggerAndroidAutoImport = async () => {
    phoneErrorMsg.value = '';
    if (!isContactPickerSupported.value) {
        openImportDrawer('android');
        return;
    }

    try {
        const propsToSelect = ['name', 'tel', 'address', 'email'];
        const contacts = await navigator.contacts.select(propsToSelect, { multiple: true });

        if (!contacts || contacts.length === 0) {
            return;
        }

        const formatted = contacts.map((c, idx) => {
            const rawPhone = c.tel?.[0] || '';
            const phone = cleanPhone(rawPhone);
            const name = (c.name?.[0] || (phone ? `Kontak (${phone})` : `Kontak #${idx + 1}`)).trim();
            const email = (c.email?.[0] || '').trim();
            
            let address = '';
            if (c.address?.[0]) {
                if (typeof c.address[0] === 'object') {
                    const addrObj = c.address[0];
                    const parts = [
                        Array.isArray(addrObj.addressLine) ? addrObj.addressLine.join(', ') : addrObj.addressLine,
                        addrObj.city,
                        addrObj.region,
                        addrObj.country
                    ].filter(Boolean);
                    address = parts.join(', ');
                } else {
                    address = String(c.address[0]);
                }
            }

            const item = {
                id: idx,
                name: name,
                phone: phone,
                email: email,
                address: address,
                type: defaultImportType.value,
                customTypeSet: false,
                isDuplicate: false,
                duplicateReason: '',
                selected: true,
            };

            const dup = checkDuplicate(item);
            item.isDuplicate = dup.isDuplicate;
            item.duplicateReason = dup.reason;
            item.selected = duplicateStrategy.value === 'skip' ? !dup.isDuplicate : true;

            return item;
        });

        parsedContacts.value = formatted;
        importSourceTab.value = 'android';
        isImportDrawerOpen.value = true;
    } catch (err) {
        console.error('Error picking phone contacts:', err);
        if (err.name !== 'AbortError') {
            phoneErrorMsg.value = 'Gagal mengakses kontak perangkat. Pastikan izin kontak telah diberikan di browser Android Anda.';
            openImportDrawer('android');
        }
    }
};

// --- PARSERS FOR VCF & CSV ---
const parseVCardText = (text) => {
    const contacts = [];
    const lines = text.split(/\r\n|\r|\n/);
    let current = null;

    for (let i = 0; i < lines.length; i++) {
        let line = lines[i].trim();
        if (!line) continue;

        if (line.toUpperCase() === 'BEGIN:VCARD') {
            current = {
                id: contacts.length,
                name: '',
                phone: '',
                email: '',
                address: '',
                type: defaultImportType.value,
                customTypeSet: false,
                isDuplicate: false,
                duplicateReason: '',
                selected: true,
            };
            continue;
        }

        if (line.toUpperCase() === 'END:VCARD') {
            if (current && (current.name || current.phone)) {
                if (!current.name) current.name = current.phone ? `Kontak (${current.phone})` : `Kontak #${contacts.length + 1}`;
                const dup = checkDuplicate(current);
                current.isDuplicate = dup.isDuplicate;
                current.duplicateReason = dup.reason;
                current.selected = duplicateStrategy.value === 'skip' ? !dup.isDuplicate : true;
                contacts.push(current);
            }
            current = null;
            continue;
        }

        if (!current) continue;

        // FN (Full Name)
        if (/^FN(?:;[^:]*)?:(.*)$/i.test(line)) {
            current.name = line.replace(/^FN(?:;[^:]*)?:/i, '').trim();
        }
        // N fallback
        else if (!current.name && /^N(?:;[^:]*)?:(.*)$/i.test(line)) {
            const val = line.replace(/^N(?:;[^:]*)?:/i, '');
            const parts = val.split(';').map(s => s.trim()).filter(Boolean).reverse();
            current.name = parts.join(' ');
        }
        // TEL
        else if (/^TEL(?:;[^:]*)?:(.*)$/i.test(line)) {
            if (!current.phone) {
                const rawTel = line.replace(/^TEL(?:;[^:]*)?:/i, '').trim();
                current.phone = cleanPhone(rawTel);
            }
        }
        // EMAIL
        else if (/^EMAIL(?:;[^:]*)?:(.*)$/i.test(line)) {
            if (!current.email) {
                current.email = line.replace(/^EMAIL(?:;[^:]*)?:/i, '').trim();
            }
        }
        // ADR
        else if (/^ADR(?:;[^:]*)?:(.*)$/i.test(line)) {
            if (!current.address) {
                const rawAdr = line.replace(/^ADR(?:;[^:]*)?:/i, '');
                const adrParts = rawAdr.split(';').map(s => s.trim()).filter(Boolean);
                current.address = adrParts.join(', ');
            }
        }
    }
    return contacts;
};

const parseCsvText = (text) => {
    const lines = text.split(/\r\n|\r|\n/).filter(l => l.trim().length > 0);
    if (lines.length === 0) return [];

    const parseRow = (line) => {
        const result = [];
        let current = '';
        let inQuotes = false;
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"' || char === "'") {
                inQuotes = !inQuotes;
            } else if (char === ',' && !inQuotes) {
                result.push(current.trim().replace(/^["']|["']$/g, ''));
                current = '';
            } else {
                current += char;
            }
        }
        result.push(current.trim().replace(/^["']|["']$/g, ''));
        return result;
    };

    const header = parseRow(lines[0]);
    const headerMap = {};
    header.forEach((h, idx) => {
        const clean = h.toLowerCase().replace(/[^a-z0-9_]/g, '');
        headerMap[clean] = idx;
    });

    const contacts = [];
    for (let i = 1; i < lines.length; i++) {
        const row = parseRow(lines[i]);
        if (row.length === 0 || !row.some(Boolean)) continue;

        let name = '';
        if (headerMap.nama !== undefined) name = row[headerMap.nama];
        else if (headerMap.name !== undefined) name = row[headerMap.name];
        else if (headerMap.fullname !== undefined) name = row[headerMap.fullname];
        else if (headerMap.givenname !== undefined) {
            const f = row[headerMap.givenname] || '';
            const l = row[headerMap.familyname] || '';
            name = `${f} ${l}`.trim();
        } else {
            name = row[0] || '';
        }

        if (!name) continue;

        let type = defaultImportType.value;
        if (headerMap.tipe !== undefined && ['customer', 'reseller', 'supplier', 'crafter'].includes((row[headerMap.tipe] || '').toLowerCase())) {
            type = row[headerMap.tipe].toLowerCase();
        } else if (headerMap.type !== undefined && ['customer', 'reseller', 'supplier', 'crafter'].includes((row[headerMap.type] || '').toLowerCase())) {
            type = row[headerMap.type].toLowerCase();
        }

        let phone = '';
        if (headerMap.telepon !== undefined) phone = row[headerMap.telepon];
        else if (headerMap.phone !== undefined) phone = row[headerMap.phone];
        else if (headerMap.phone1value !== undefined) phone = row[headerMap.phone1value];
        else if (headerMap.phonenumber !== undefined) phone = row[headerMap.phonenumber];
        else if (headerMap.mobile !== undefined) phone = row[headerMap.mobile];
        else phone = row[2] || '';

        let email = '';
        if (headerMap.email !== undefined) email = row[headerMap.email];
        else if (headerMap.email1value !== undefined) email = row[headerMap.email1value];
        else if (headerMap.emailaddress !== undefined) email = row[headerMap.emailaddress];
        else email = row[3] || '';

        let address = '';
        if (headerMap.alamat !== undefined) address = row[headerMap.alamat];
        else if (headerMap.address !== undefined) address = row[headerMap.address];
        else if (headerMap.address1formatted !== undefined) address = row[headerMap.address1formatted];
        else address = row[4] || '';

        const item = {
            id: contacts.length,
            name: name.trim(),
            phone: cleanPhone(phone),
            email: (email || '').trim(),
            address: (address || '').trim(),
            type: type,
            customTypeSet: false,
            isDuplicate: false,
            duplicateReason: '',
            selected: true,
        };

        const dup = checkDuplicate(item);
        item.isDuplicate = dup.isDuplicate;
        item.duplicateReason = dup.reason;
        item.selected = duplicateStrategy.value === 'skip' ? !dup.isDuplicate : true;

        contacts.push(item);
    }

    return contacts;
};

// Process dropped or selected file
const processSelectedFile = async (file) => {
    if (!file) return;
    selectedFile.value = file;
    isParsingFile.value = true;
    phoneErrorMsg.value = '';

    try {
        const text = await file.text();
        const ext = file.name.split('.').pop()?.toLowerCase();

        let extracted = [];
        if (ext === 'vcf' || ext === 'vcard' || file.type.includes('vcard')) {
            extracted = parseVCardText(text);
        } else {
            extracted = parseCsvText(text);
        }

        if (extracted.length === 0) {
            phoneErrorMsg.value = 'Tidak ada kontak valid yang dapat diekstrak dari file ini.';
        } else {
            parsedContacts.value = extracted;
        }
    } catch (err) {
        console.error('Error parsing file:', err);
        phoneErrorMsg.value = 'Gagal membaca isi file. Pastikan format file sesuai (.vcf atau .csv).';
    } finally {
        isParsingFile.value = false;
    }
};

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const onFileChange = (e) => {
    const file = e.target.files?.[0];
    if (file) processSelectedFile(file);
};

const onFileDrop = (e) => {
    isDragover.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) processSelectedFile(file);
};

const clearParsedContacts = () => {
    parsedContacts.value = [];
    selectedFile.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

// Filtering & Selection for Preview List
const filteredParsedContacts = computed(() => {
    let list = parsedContacts.value;

    if (previewFilterTab.value === 'new') {
        list = list.filter(c => !c.isDuplicate);
    } else if (previewFilterTab.value === 'duplicate') {
        list = list.filter(c => c.isDuplicate);
    }

    if (previewSearch.value.trim()) {
        const q = previewSearch.value.toLowerCase();
        list = list.filter(c =>
            c.name.toLowerCase().includes(q) ||
            c.phone.includes(q) ||
            c.email.toLowerCase().includes(q) ||
            c.address.toLowerCase().includes(q)
        );
    }

    return list;
});

const statsSummary = computed(() => {
    const total = parsedContacts.value.length;
    const duplicates = parsedContacts.value.filter(c => c.isDuplicate).length;
    const newItems = total - duplicates;
    const selected = parsedContacts.value.filter(c => c.selected).length;
    return { total, duplicates, newItems, selected };
});

const toggleSelectAll = (selectState) => {
    parsedContacts.value.forEach(c => {
        c.selected = selectState;
    });
};

const selectOnlyNonDuplicates = () => {
    parsedContacts.value.forEach(c => {
        c.selected = !c.isDuplicate;
    });
};

// Submit Import Payload
const submitImport = () => {
    const selectedList = parsedContacts.value.filter(c => c.selected);

    if (selectedList.length === 0) {
        alert('Silakan pilih minimal 1 kontak untuk diimpor.');
        return;
    }

    importForm.default_type = defaultImportType.value;
    importForm.duplicate_strategy = duplicateStrategy.value;
    importForm.contacts = selectedList.map(c => ({
        name: c.name,
        phone: c.phone,
        email: c.email,
        address: c.address,
        type: c.type || defaultImportType.value,
    }));

    isImporting.value = true;
    importForm.post(route('contacts.import'), {
        onSuccess: () => {
            isImportDrawerOpen.value = false;
            clearParsedContacts();
        },
        onFinish: () => {
            isImporting.value = false;
        }
    });
};

const downloadSampleCsv = () => {
    window.location.href = route('contacts.sample-csv');
};

const getBadgeClass = (type) => {
    switch (type) {
        case 'customer':
            return 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20';
        case 'reseller':
            return 'bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-500/20';
        case 'supplier':
            return 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20';
        case 'crafter':
            return 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20';
        default:
            return 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400';
    }
};

const getTypeText = (type) => {
    switch (type) {
        case 'customer': return 'Pelanggan';
        case 'reseller': return 'Reseller';
        case 'supplier': return 'Supplier';
        case 'crafter': return 'Pengrajin';
        default: return type;
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Buku Kontak - Rima Craft" />
        <ConfirmDialog :style="{ width: '90vw', maxWidth: '380px' }" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent p-4 sm:p-6 rounded-2xl border border-amber-500/20">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-xl bg-amber-500 text-gray-950 shadow-sm">
                            <i class="pi pi-address-book text-base"></i>
                        </span>
                        <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">Buku Kontak</h2>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Kelola relasi bisnis: pelanggan setia, reseller toko, supplier bahan baku, dan pengrajin lokal.
                    </p>
                </div>
                
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                    <!-- Quick Android Auto Import Button -->
                    <Button
                        label="Auto Import Kontak Android"
                        icon="pi pi-android"
                        class="!bg-gradient-to-r !from-emerald-600 !to-teal-600 hover:!from-emerald-500 hover:!to-teal-500 !border-0 !text-white font-bold !text-xs !shadow-sm flex-1 sm:flex-none"
                        @click="triggerAndroidAutoImport"
                    />
                    
                    <Button
                        label="Import File"
                        icon="pi pi-upload"
                        severity="secondary"
                        outlined
                        class="!text-xs font-bold shrink-0"
                        @click="openImportDrawer('file')"
                    />

                    <Button
                        label="Tambah Kontak"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold !text-xs shrink-0"
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
                        placeholder="Cari nama, no. telepon, alamat..."
                        class="w-full !pl-9 !text-xs"
                        @input="applyFilters"
                    />
                </div>

                <Dropdown
                    v-model="typeFilter"
                    :options="typeOptions"
                    optionLabel="label"
                    optionValue="value"
                    placeholder="Semua Tipe Kontak"
                    class="w-full !text-xs"
                    @change="applyFilters"
                />
            </div>

            <!-- Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Nama Kontak</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tipe Hubungan</th>
                                <th scope="col" class="px-6 py-4 font-bold">No. Telepon / WhatsApp</th>
                                <th scope="col" class="px-6 py-4 font-bold">Email</th>
                                <th scope="col" class="px-6 py-4 font-bold">Alamat</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="contact in contacts.data" :key="contact.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ contact.name ? contact.name.charAt(0).toUpperCase() : '?' }}
                                        </div>
                                        <span class="font-bold text-gray-900 dark:text-white">{{ contact.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-[11px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider', getBadgeClass(contact.type)]">
                                        {{ getTypeText(contact.type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="contact.phone" class="flex items-center gap-2">
                                        <span class="font-mono text-xs text-gray-800 dark:text-gray-200">{{ contact.phone }}</span>
                                        <a
                                            :href="`https://wa.me/${contact.phone.replace(/^0/, '62').replace(/[^\d]/g, '')}`"
                                            target="_blank"
                                            class="text-emerald-500 hover:text-emerald-600 text-xs inline-flex items-center"
                                            title="Kirim Pesan WhatsApp"
                                        >
                                            <i class="pi pi-whatsapp"></i>
                                        </a>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs">-</span>
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    {{ contact.email || '-' }}
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500 max-w-xs truncate">
                                    {{ contact.address || '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(contact)" class="mr-1 !w-8 !h-8" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteContact(contact)" class="!w-8 !h-8" />
                                </td>
                            </tr>
                            <tr v-if="contacts.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <i class="pi pi-users text-4xl text-gray-300 dark:text-gray-600"></i>
                                        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300">Belum ada kontak terdaftar</p>
                                        <p class="text-xs text-gray-400">Gunakan tombol Auto Import Kontak Android untuk memindahkan kontak dari smartphone Anda dalam hitungan detik.</p>
                                        <div class="flex justify-center gap-2 pt-2">
                                            <Button
                                                label="Auto Import Android"
                                                icon="pi pi-android"
                                                size="small"
                                                class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !text-white font-bold !text-xs"
                                                @click="triggerAndroidAutoImport"
                                            />
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile view -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div v-for="contact in contacts.data" :key="contact.id" class="p-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-800/40">
                        <div class="flex-1 min-w-0 pr-3">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ contact.name }}</h4>
                                <span :class="['text-[9px] px-1.5 py-0.5 rounded font-bold uppercase', getBadgeClass(contact.type)]">
                                    {{ getTypeText(contact.type) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-3 mt-1.5 text-xs text-gray-500">
                                <span v-if="contact.phone" class="font-mono">{{ contact.phone }}</span>
                                <span v-if="contact.address" class="truncate max-w-[150px]">{{ contact.address }}</span>
                            </div>
                        </div>
                        <div class="flex gap-1 shrink-0">
                            <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(contact)" />
                            <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteContact(contact)" />
                        </div>
                    </div>
                    <div v-if="contacts.data.length === 0" class="p-8 text-center text-gray-400">
                        Tidak ada kontak ditemukan.
                    </div>
                </div>

                <!-- Pagination Footer -->
                <div v-if="contacts.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex flex-col sm:flex-row gap-3 justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ contacts.from || 0 }} - {{ contacts.to || 0 }} dari {{ contacts.total }} kontak</span>
                    <div class="flex gap-1 flex-wrap">
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

            <!-- Form Modal / Drawer (Create / Edit) -->
            <Drawer
                v-model:visible="isFormOpen"
                position="right"
                :header="editingContact ? 'Edit Kontak' : 'Tambah Kontak Baru'"
                class="!w-full sm:!w-[440px]"
            >
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tipe Hubungan <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.type"
                            :options="contactTypes"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Pilih Hubungan..."
                            class="w-full !text-xs"
                            required
                        />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Nama Lengkap <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Nama lengkap..." class="!text-xs" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Nomor Telepon / WhatsApp</label>
                        <InputText v-model="form.phone" placeholder="Contoh: 081234567890" class="!text-xs" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Email</label>
                        <InputText v-model="form.email" type="email" placeholder="contoh@domain.com" class="!text-xs" />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Alamat Lengkap</label>
                        <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat jalan, kota, provinsi..." class="!text-xs" />
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" class="!text-xs" />
                        <Button
                            type="submit"
                            :label="editingContact ? 'Simpan Perubahan' : 'Tambah Kontak'"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold !text-xs"
                        />
                    </div>
                </form>
            </Drawer>

            <!-- Comprehensive Import Drawer (Android Contact Picker + VCF + CSV + Interactive Review) -->
            <Drawer
                v-model:visible="isImportDrawerOpen"
                position="right"
                :header="parsedContacts.length > 0 ? 'Review & Konfirmasi Import Kontak' : 'Auto Import Kontak'"
                class="!w-full sm:!w-[580px]"
            >
                <div class="space-y-4 pt-1">
                    <!-- STATE A: Source Selection (When no contacts are parsed yet) -->
                    <div v-if="parsedContacts.length === 0" class="space-y-4">
                        <!-- Source Switcher Tabs -->
                        <div class="flex p-1 bg-gray-100 dark:bg-gray-800 rounded-xl">
                            <button
                                type="button"
                                @click="importSourceTab = 'android'"
                                :class="[
                                    'flex-1 py-2 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2',
                                    importSourceTab === 'android'
                                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                ]"
                            >
                                <i class="pi pi-android text-emerald-500"></i>
                                Kontak HP Android
                            </button>
                            <button
                                type="button"
                                @click="importSourceTab = 'file'"
                                :class="[
                                    'flex-1 py-2 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2',
                                    importSourceTab === 'file'
                                        ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm'
                                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'
                                ]"
                            >
                                <i class="pi pi-file text-amber-500"></i>
                                File Kontak (.vcf / .csv)
                            </button>
                        </div>

                        <!-- TAB 1: Android Contact Picker -->
                        <div v-if="importSourceTab === 'android'" class="space-y-4">
                            <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 via-emerald-500/5 to-transparent border border-emerald-500/20 text-center space-y-3">
                                <div class="w-12 h-12 mx-auto rounded-full bg-emerald-500 text-white flex items-center justify-center shadow-md">
                                    <i class="pi pi-mobile text-xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Sinkronisasi Kontak Langsung dari Android</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                                        Pilih kontak langsung dari aplikasi kontak smartphone Android Anda. Nama, nomor telepon, dan email akan diekstrak secara otomatis.
                                    </p>
                                </div>

                                <div class="pt-2">
                                    <Button
                                        label="Buka Kontak Android Sekarang"
                                        icon="pi pi-android"
                                        class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !text-white font-bold !text-xs !py-2.5 !px-5 !shadow"
                                        @click="triggerAndroidAutoImport"
                                    />
                                </div>

                                <div v-if="!isContactPickerSupported" class="pt-2 text-left bg-amber-50 dark:bg-amber-950/30 p-3 rounded-xl border border-amber-200 dark:border-amber-800 text-[11px] text-amber-800 dark:text-amber-300">
                                    <div class="flex items-start gap-2">
                                        <i class="pi pi-info-circle text-amber-600 mt-0.5 shrink-0"></i>
                                        <div>
                                            <p class="font-bold">Browser tidak mendukung Contact Picker API</p>
                                            <p class="mt-0.5 text-amber-700 dark:text-amber-400">
                                                Fitur tombol kontak langsung ini didukung di <strong>Google Chrome Mobile (Android)</strong> dan <strong>Samsung Internet</strong>. Jika Anda membuka dari Laptop/PC atau browser lain, Anda dapat menggunakan tab <strong>File Kontak (.vcf / .csv)</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Android Help Accordion -->
                            <div class="border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden">
                                <button
                                    type="button"
                                    class="w-full p-3 flex justify-between items-center text-xs font-bold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/40 hover:bg-gray-100 transition"
                                    @click="showHelpAndroid = !showHelpAndroid"
                                >
                                    <span class="flex items-center gap-2">
                                        <i class="pi pi-question-circle text-amber-500"></i>
                                        Cara Ekspor Kontak dari HP Android ke File .VCF
                                    </span>
                                    <i :class="['pi text-[10px] transition-transform', showHelpAndroid ? 'pi-chevron-up' : 'pi-chevron-down']"></i>
                                </button>
                                
                                <div v-if="showHelpAndroid" class="p-3 text-[11px] text-gray-600 dark:text-gray-400 space-y-2 bg-white dark:bg-gray-900">
                                    <ol class="list-decimal list-inside space-y-1.5 leading-relaxed">
                                        <li>Buka aplikasi <strong>Kontak (Google Contacts)</strong> di HP Android Anda.</li>
                                        <li>Pilih tab <strong>Kelola / Perbaiki (Fix & manage)</strong> di bagian bawah.</li>
                                        <li>Pilih <strong>Ekspor ke file (Export to file)</strong> lalu simpan file <code class="bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded text-amber-600">.vcf</code>.</li>
                                        <li>Pindahkan / upload file tersebut ke tab <strong>File Kontak</strong> di atas untuk langsung mengimpor seluruh kontak Anda sekaligus!</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <!-- TAB 2: File Upload (.VCF / .CSV) -->
                        <div v-if="importSourceTab === 'file'" class="space-y-4">
                            <div class="flex justify-between items-center bg-amber-50/50 dark:bg-amber-500/10 p-3 rounded-xl border border-amber-200 dark:border-amber-500/20">
                                <div class="space-y-0.5">
                                    <p class="text-xs font-bold text-amber-800 dark:text-amber-300">Mendukung File .VCF (vCard) & .CSV</p>
                                    <p class="text-[10px] text-amber-700 dark:text-amber-400">File vCard export kontak Android atau CSV Google Contacts.</p>
                                </div>
                                <Button
                                    label="Download CSV"
                                    icon="pi pi-download"
                                    severity="warn"
                                    size="small"
                                    text
                                    class="!text-xs shrink-0 font-bold"
                                    @click="downloadSampleCsv"
                                />
                            </div>

                            <input
                                ref="fileInputRef"
                                type="file"
                                accept=".vcf,.vcard,.csv,text/vcard,text/csv,text/plain"
                                class="hidden"
                                @change="onFileChange"
                            />

                            <div
                                @click="triggerFileInput"
                                @dragover.prevent="isDragover = true"
                                @dragleave.prevent="isDragover = false"
                                @drop.prevent="onFileDrop"
                                :class="[
                                    'border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center min-h-[150px]',
                                    isDragover
                                        ? 'border-amber-500 bg-amber-50/30 dark:bg-amber-500/10'
                                        : 'border-gray-200 dark:border-gray-700 hover:border-amber-400 dark:hover:border-amber-500 bg-gray-50/50 dark:bg-gray-800/40 hover:bg-amber-50/20 dark:hover:bg-amber-500/5'
                                ]"
                            >
                                <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mb-2">
                                    <i v-if="!isParsingFile" class="pi pi-upload text-lg"></i>
                                    <i v-else class="pi pi-spin pi-spinner text-lg"></i>
                                </div>
                                <p class="text-xs font-bold text-gray-700 dark:text-gray-200">
                                    Klik atau seret file <span class="text-emerald-600 dark:text-emerald-400 font-bold">.VCF</span> atau <span class="text-amber-600 dark:text-amber-400 font-bold">.CSV</span> ke sini
                                </p>
                                <p class="text-[10px] text-gray-400 mt-1">Ekstrak otomatis kontak, nama, telepon, dan alamat</p>
                            </div>
                        </div>

                        <Message v-if="phoneErrorMsg" severity="error" size="small">
                            {{ phoneErrorMsg }}
                        </Message>
                    </div>

                    <!-- STATE B: Interactive Review & Configuration (When contacts are loaded) -->
                    <div v-else class="space-y-4">
                        <!-- Top Action Bar -->
                        <div class="flex justify-between items-center pb-2 border-b border-gray-100 dark:border-gray-800">
                            <Button
                                label="Pilih Ulang / Ganti Sumber"
                                icon="pi pi-arrow-left"
                                severity="secondary"
                                text
                                size="small"
                                class="!text-xs font-bold"
                                @click="clearParsedContacts"
                            />
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ statsSummary.total }} Kontak Terdeteksi
                            </span>
                        </div>

                        <!-- Configuration: Target Type & Duplicate Strategy -->
                        <div class="bg-gray-50 dark:bg-gray-800/50 p-3.5 rounded-xl border border-gray-200 dark:border-gray-800 space-y-3">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-bold text-gray-700 dark:text-gray-300">Tipe Relasi Default</label>
                                    <Dropdown
                                        v-model="defaultImportType"
                                        :options="contactTypes"
                                        optionLabel="label"
                                        optionValue="value"
                                        class="w-full !text-xs"
                                    />
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="text-[11px] font-bold text-gray-700 dark:text-gray-300">Penanganan Duplikat</label>
                                    <Dropdown
                                        v-model="duplicateStrategy"
                                        :options="[
                                            { label: 'Lewati Duplikat (Rekomendasi)', value: 'skip' },
                                            { label: 'Perbarui Data Lama', value: 'update' },
                                            { label: 'Tetap Tambahkan Semua', value: 'create_all' }
                                        ]"
                                        optionLabel="label"
                                        optionValue="value"
                                        class="w-full !text-xs"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Summary Cards -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                <p class="text-[10px] text-gray-500 font-bold uppercase">Terpilih</p>
                                <p class="text-base font-extrabold text-gray-900 dark:text-white">{{ statsSummary.selected }}</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                                <p class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold uppercase">Kontak Baru</p>
                                <p class="text-base font-extrabold text-emerald-700 dark:text-emerald-300">{{ statsSummary.newItems }}</p>
                            </div>
                            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800">
                                <p class="text-[10px] text-amber-600 dark:text-amber-400 font-bold uppercase">Duplikat</p>
                                <p class="text-base font-extrabold text-amber-700 dark:text-amber-300">{{ statsSummary.duplicates }}</p>
                            </div>
                        </div>

                        <!-- Search & Filter Tab Controls -->
                        <div class="space-y-2">
                            <div class="flex gap-2">
                                <InputText
                                    v-model="previewSearch"
                                    placeholder="Cari kontak dalam daftar..."
                                    class="w-full !text-xs !py-1.5"
                                />
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <div class="flex gap-1">
                                    <button
                                        type="button"
                                        @click="previewFilterTab = 'all'"
                                        :class="['px-2.5 py-1 rounded-lg font-bold text-[11px] transition', previewFilterTab === 'all' ? 'bg-gray-900 dark:bg-white text-white dark:text-gray-900' : 'bg-gray-100 dark:bg-gray-800 text-gray-500']"
                                    >
                                        Semua ({{ statsSummary.total }})
                                    </button>
                                    <button
                                        type="button"
                                        @click="previewFilterTab = 'new'"
                                        :class="['px-2.5 py-1 rounded-lg font-bold text-[11px] transition', previewFilterTab === 'new' ? 'bg-emerald-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500']"
                                    >
                                        Baru ({{ statsSummary.newItems }})
                                    </button>
                                    <button
                                        type="button"
                                        @click="previewFilterTab = 'duplicate'"
                                        :class="['px-2.5 py-1 rounded-lg font-bold text-[11px] transition', previewFilterTab === 'duplicate' ? 'bg-amber-600 text-white' : 'bg-gray-100 dark:bg-gray-800 text-gray-500']"
                                    >
                                        Duplikat ({{ statsSummary.duplicates }})
                                    </button>
                                </div>

                                <div class="flex gap-1">
                                    <Button
                                        label="Pilih Semua"
                                        size="small"
                                        text
                                        class="!text-[10px] !py-0.5 !px-1.5"
                                        @click="toggleSelectAll(true)"
                                    />
                                    <Button
                                        v-if="statsSummary.duplicates > 0"
                                        label="Hanya Baru"
                                        size="small"
                                        text
                                        severity="help"
                                        class="!text-[10px] !py-0.5 !px-1.5"
                                        @click="selectOnlyNonDuplicates"
                                    />
                                    <Button
                                        label="Batal"
                                        size="small"
                                        text
                                        severity="secondary"
                                        class="!text-[10px] !py-0.5 !px-1.5"
                                        @click="toggleSelectAll(false)"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Scrollable Contact Items -->
                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 border border-gray-200 dark:border-gray-800 rounded-xl bg-white dark:bg-gray-900">
                            <div
                                v-for="item in filteredParsedContacts"
                                :key="item.id"
                                class="p-3 flex items-start gap-3 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition"
                            >
                                <input
                                    type="checkbox"
                                    :id="`preview_check_${item.id}`"
                                    v-model="item.selected"
                                    class="w-4 h-4 mt-1 text-amber-600 rounded border-gray-300 focus:ring-amber-500"
                                />

                                <label :for="`preview_check_${item.id}`" class="flex-1 min-w-0 cursor-pointer">
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs font-bold text-gray-900 dark:text-white truncate">{{ item.name }}</p>
                                        <span
                                            v-if="item.isDuplicate"
                                            class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 shrink-0"
                                            :title="item.duplicateReason"
                                        >
                                            ⚠️ Duplikat
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 mt-0.5 text-[11px] text-gray-500">
                                        <span v-if="item.phone" class="font-mono text-gray-700 dark:text-gray-300">
                                            <i class="pi pi-phone text-[9px] mr-1 text-gray-400"></i>{{ item.phone }}
                                        </span>
                                        <span v-if="item.email" class="truncate max-w-[150px]">
                                            <i class="pi pi-envelope text-[9px] mr-1 text-gray-400"></i>{{ item.email }}
                                        </span>
                                        <span v-if="item.address" class="truncate max-w-[150px]">
                                            <i class="pi pi-map-marker text-[9px] mr-1 text-gray-400"></i>{{ item.address }}
                                        </span>
                                    </div>

                                    <p v-if="item.isDuplicate" class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 italic">
                                        {{ item.duplicateReason }}
                                    </p>
                                </label>

                                <div class="shrink-0">
                                    <select
                                        v-model="item.type"
                                        @change="item.customTypeSet = true"
                                        class="text-[10px] font-semibold bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1 text-gray-700 dark:text-gray-300"
                                    >
                                        <option value="customer">Pelanggan</option>
                                        <option value="reseller">Reseller</option>
                                        <option value="supplier">Supplier</option>
                                        <option value="crafter">Pengrajin</option>
                                    </select>
                                </div>
                            </div>

                            <div v-if="filteredParsedContacts.length === 0" class="p-6 text-center text-xs text-gray-400">
                                Tidak ada kontak yang sesuai dengan filter pencarian.
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex justify-between items-center pt-3 border-t border-gray-150 dark:border-gray-800">
                            <Button
                                label="Batal"
                                severity="secondary"
                                text
                                size="small"
                                class="!text-xs"
                                @click="isImportDrawerOpen = false"
                            />
                            
                            <Button
                                type="button"
                                :label="`Simpan & Import ${statsSummary.selected} Kontak`"
                                icon="pi pi-check"
                                :loading="isImporting"
                                :disabled="statsSummary.selected === 0"
                                class="!bg-emerald-600 hover:!bg-emerald-700 !border-0 !text-white font-bold !text-xs !py-2 !px-4"
                                @click="submitImport"
                            />
                        </div>
                    </div>
                </div>
            </Drawer>
        </div>
    </AdminLayout>
</template>
