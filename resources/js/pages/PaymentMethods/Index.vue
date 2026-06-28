<script setup>
import { ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
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
                    class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    @click="openCreateModal"
                />
            </div>

            <!-- List Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
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
                                            <div class="text-xs text-gray-400 max-w-xs truncate">{{ method.description }}</div>
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
            </div>

            <!-- Create/Edit Dialog -->
            <Dialog
                v-model:visible="isFormOpen"
                modal
                :header="editingMethod ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran'"
                class="w-full max-w-lg"
                :contentStyle="{ maxHeight: '65vh', overflowY: 'auto' }"
            >
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form id="paymentForm" @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Metode <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Contoh: Transfer Bank BCA" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Kode Unik <span class="text-red-500">*</span></label>
                            <InputText v-model="form.code" required placeholder="Contoh: bca" :disabled="!!editingMethod" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Tipe <span class="text-red-500">*</span></label>
                            <select v-model="form.type" required class="w-full p-2 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md text-sm">
                                <option v-for="opt in typeOptions" :key="opt.value" :value="opt.value">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4" v-if="form.type !== 'cod'">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nomor Rekening / No. HP</label>
                            <InputText v-model="form.account_number" placeholder="Contoh: 123456789" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nama Pemilik Akun</label>
                            <InputText v-model="form.account_name" placeholder="Contoh: PT Rima Craft" />
                        </div>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Instruksi Pembayaran / Deskripsi</label>
                        <Textarea v-model="form.description" rows="3" placeholder="Masukkan instruksi transfer atau deskripsi pembayaran..." />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Nomor Urut Tampil</label>
                            <InputNumber v-model="form.sort_order" required :min="0" showButtons />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Status Aktif</label>
                            <div class="flex items-center gap-2 mt-2">
                                <input type="checkbox" id="is_active" v-model="form.is_active" class="w-4 h-4 rounded text-amber-500 focus:ring-amber-500" />
                                <label for="is_active" class="text-sm font-semibold cursor-pointer">Metode ini Aktif</label>
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

                <template #footer>
                    <div class="flex justify-end gap-2 border-t border-gray-150 dark:border-gray-800 pt-3">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button
                            type="submit"
                            form="paymentForm"
                            label="Simpan"
                            :loading="form.processing"
                            class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        />
                    </div>
                </template>
            </Dialog>
        </div>
    </AdminLayout>
</template>
