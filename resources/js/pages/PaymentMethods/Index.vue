<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Editor from 'primevue/editor';
import InputNumber from 'primevue/inputnumber';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    paymentMethods: Array,
});

const toast = useToast();
const page = usePage();

watch(() => page.props.flash, (flash) => {
    if (flash?.success) {
        toast.add({ severity: 'success', summary: 'Sukses', detail: flash.success, life: 3000 });
    }
    if (flash?.error) {
        toast.add({ severity: 'error', summary: 'Error', detail: flash.error, life: 4000 });
    }
}, { deep: true, immediate: true });

const isFormOpen = ref(false);
const editingMethod = ref(null);
const logoPreview = ref(null);

const form = useForm({
    _method: 'POST',
    name: '',
    type: 'bank',
    code: '',
    account_number: '',
    account_name: '',
    description: '',
    is_active: true,
    sort_order: 0,
    logo: null,
});

const typeOptions = [
    { label: 'Transfer Bank', value: 'bank' },
    { label: 'E-Wallet', value: 'ewallet' },
    { label: 'QRIS', value: 'qris' },
    { label: 'Bayar di Tempat (COD)', value: 'cod' }
];

const openCreateModal = () => {
    editingMethod.value = null;
    form.clearErrors();
    form.reset();
    logoPreview.value = null;
    isFormOpen.value = true;
};

const openEditModal = (method) => {
    editingMethod.value = method;
    form.clearErrors();
    form.name = method.name;
    form.type = method.type;
    form.code = method.code;
    form.account_number = method.account_number || '';
    form.account_name = method.account_name || '';
    form.description = method.description || '';
    form.is_active = !!method.is_active;
    form.sort_order = method.sort_order || 0;
    form.logo = null;
    logoPreview.value = method.icon ? `/storage/${method.icon}` : null;
    isFormOpen.value = true;
};

const onLogoChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.logo = file;
        logoPreview.value = URL.createObjectURL(file);
    }
};

const submitForm = () => {
    if (editingMethod.value) {
        form._method = 'PUT';
        form.post(route('payment-methods.update', editingMethod.value.id), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form._method = 'POST';
        form.post(route('payment-methods.store'), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteMethod = (method) => {
    if (confirm(`Hapus metode pembayaran ${method.name}?`)) {
        router.delete(route('payment-methods.destroy', method.id));
    }
};



function stripTags(html) {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '');
}
</script>

<template>
    <AdminLayout>
        <Head title="Pengaturan Metode Pembayaran" />
        <Toast />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Metode Pembayaran</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola metode pembayaran yang tersedia saat checkout pelanggan.</p>
                </div>
                <Button
                    label="Tambah Metode"
                    icon="pi pi-plus"
                    class="w-full sm:w-auto justify-center !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    @click="openCreateModal"
                />
            </div>

            <!-- List Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Metode Pembayaran</th>
                                <th scope="col" class="px-6 py-4 font-bold">Kode</th>
                                <th scope="col" class="px-6 py-4 font-bold">Tipe</th>
                                <th scope="col" class="px-6 py-4 font-bold">Detail Akun</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status</th>
                                <th scope="col" class="px-6 py-4 font-bold">Urutan</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img
                                            :src="method.icon ? `/storage/${method.icon}` : 'https://placehold.co/100'"
                                            class="w-12 h-12 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 p-1"
                                            alt=""
                                        />
                                        <div>
                                            <div class="font-bold text-gray-900 dark:text-white">{{ method.name }}</div>
                                            <div class="text-xs text-gray-400 max-w-xs truncate">{{ stripTags(method.description) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-xs">
                                    {{ method.code }}
                                </td>
                                <td class="px-6 py-4 capitalize">
                                    {{ method.type }}
                                </td>
                                <td class="px-6 py-4 text-xs">
                                    <div v-if="method.account_number" class="space-y-0.5">
                                        <div class="font-semibold text-gray-800 dark:text-gray-200">{{ method.account_number }}</div>
                                        <div class="text-gray-500">a/n {{ method.account_name }}</div>
                                    </div>
                                    <span v-else class="text-gray-400 italic">Tidak ada</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['text-xs px-2 py-1 rounded-full font-bold', method.is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400']">
                                        {{ method.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs font-semibold">
                                    {{ method.sort_order }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(method)" class="mr-2" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteMethod(method)" />
                                </td>
                            </tr>
                            <tr v-if="paymentMethods.length === 0">
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">Belum ada metode pembayaran yang terdaftar.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden divide-y divide-gray-150 dark:divide-gray-800">
                    <div
                        v-for="method in paymentMethods"
                        :key="method.id"
                        class="p-4 space-y-3"
                    >
                        <div class="flex items-start gap-3">
                            <img
                                :src="method.icon ? `/storage/${method.icon}` : 'https://placehold.co/100'"
                                class="w-14 h-14 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 p-1 shrink-0"
                                alt=""
                            />
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ method.name }}</h4>
                                        <p class="text-xs text-gray-400 truncate mt-0.5">{{ stripTags(method.description) }}</p>
                                    </div>
                                    <span :class="['shrink-0 text-[10px] px-2 py-0.5 rounded-full font-bold', method.is_active ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400']">
                                        {{ method.is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 font-mono uppercase">
                                        {{ method.code }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 font-bold uppercase">
                                        {{ method.type }}
                                    </span>
                                    <span class="text-[10px] px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">
                                        Urutan {{ method.sort_order }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-end justify-between gap-3">
                            <div class="min-w-0 text-xs">
                                <template v-if="method.account_number">
                                    <p class="font-semibold text-gray-800 dark:text-gray-200 truncate">{{ method.account_number }}</p>
                                    <p class="text-gray-500 truncate">a/n {{ method.account_name }}</p>
                                </template>
                                <span v-else class="text-gray-400 italic">Tidak ada detail akun</span>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(method)" />
                                <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteMethod(method)" />
                            </div>
                        </div>
                    </div>
                    <div v-if="paymentMethods.length === 0" class="p-6 text-center text-gray-400">Belum ada metode pembayaran yang terdaftar.</div>
                </div>
            </div>

            <!-- Create/Edit Right Side Drawer -->
            <div v-if="isFormOpen" class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
                <div class="absolute inset-0 overflow-hidden">
                    <!-- Backdrop overlay -->
                    <div class="absolute inset-0 bg-gray-500/30 dark:bg-black/60 backdrop-blur-sm transition-opacity" @click="isFormOpen = false"></div>
                    
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div class="pointer-events-auto w-screen max-w-md transform bg-white dark:bg-gray-900 shadow-2xl transition-all duration-300 ease-in-out border-l border-gray-200 dark:border-gray-800 flex flex-col h-full">
                            <!-- Drawer Header -->
                            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-gray-50 dark:bg-gray-900/50">
                                <h3 class="text-sm font-black text-gray-950 dark:text-white uppercase tracking-wider">
                                    {{ editingMethod ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran' }}
                                </h3>
                                <Button icon="pi pi-times" severity="secondary" text rounded @click="isFormOpen = false" class="!p-1" />
                            </div>

                            <!-- Drawer Body -->
                            <div class="flex-1 overflow-y-auto p-6 space-y-4">
                                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                                        {{ err }}
                                    </Message>
                                </div>

                                <form id="paymentForm" @submit.prevent="submitForm" class="space-y-4">
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Nama Metode <span class="text-red-500">*</span></label>
                                        <InputText v-model="form.name" required placeholder="Contoh: Transfer Bank BCA" class="w-full" />
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Kode Unik <span class="text-red-500">*</span></label>
                                            <InputText v-model="form.code" required placeholder="Contoh: bca" :disabled="!!editingMethod" class="w-full" />
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Tipe <span class="text-red-500">*</span></label>
                                            <select v-model="form.type" required class="w-full p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md text-sm outline-none focus:border-amber-500">
                                                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4" v-if="form.type !== 'cod'">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Nomor Rekening / No. HP</label>
                                            <InputText v-model="form.account_number" placeholder="Contoh: 123456789" class="w-full" />
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Nama Pemilik Akun</label>
                                            <InputText v-model="form.account_name" placeholder="Contoh: PT Rima Craft" class="w-full" />
                                        </div>
                                    </div>

                                    <!-- Rich-Text Wording Instruction Area -->
                                    <div class="flex flex-col gap-1.5">
                                        <label class="text-xs font-semibold">Instruksi Pembayaran / Deskripsi</label>
                                        <Editor 
                                            v-model="form.description" 
                                            editorStyle="height: 180px" 
                                            placeholder="Masukkan instruksi transfer atau deskripsi pembayaran..." 
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

                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Nomor Urut Tampil</label>
                                            <input 
                                                type="number" 
                                                v-model.number="form.sort_order" 
                                                required 
                                                min="0" 
                                                class="w-full p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md text-sm outline-none focus:border-amber-500" 
                                            />
                                        </div>
                                        <div class="flex flex-col gap-1.5">
                                            <label class="text-xs font-semibold">Status Aktif</label>
                                            <div class="flex items-center gap-2 mt-2">
                                                <input type="checkbox" id="is_active" v-model="form.is_active" class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500" />
                                                <label for="is_active" class="text-sm font-semibold cursor-pointer select-none">Metode ini Aktif</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-1.5 pt-2">
                                        <label class="text-xs font-semibold">Logo / QR Code QRIS</label>
                                        <div class="flex gap-4 items-center">
                                            <img
                                                v-if="logoPreview"
                                                :src="logoPreview"
                                                class="w-16 h-16 object-contain rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 p-1"
                                                alt=""
                                            />
                                            <input
                                                type="file"
                                                accept="image/*"
                                                @change="onLogoChange"
                                                class="text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-500/10 file:text-amber-700 dark:file:text-amber-400 cursor-pointer"
                                            />
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Drawer Footer -->
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-800 flex justify-end gap-2 bg-gray-50 dark:bg-gray-900/50">
                                <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                                <Button
                                    type="submit"
                                    form="paymentForm"
                                    label="Simpan"
                                    :loading="form.processing"
                                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
