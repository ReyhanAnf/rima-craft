<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import Message from 'primevue/message';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
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

// Filters
const searchQuery = ref(props.filters.search || '');
const filterRole = ref(props.filters.role || '');

const roleOptions = computed(() => {
    return [
        { name: 'Semua Peran / Role', name_value: '' },
        ...props.roles.map(r => ({ name: r.name, name_value: r.name }))
    ];
});

const filteredRoles = computed(() => {
    const isDev = page.props.auth?.roles?.includes('dev-admin');
    if (isDev) {
        return props.roles;
    }
    return props.roles.filter(r => r.name !== 'dev-admin');
});

let filterTimeout = null;
const applyFilters = () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        router.get(route('users.index'), {
            search: searchQuery.value,
            role: filterRole.value,
        }, { preserveState: true, replace: true });
    }, 400);
};

// Form Dialog State
const isFormOpen = ref(false);
const editingUser = ref(null);

const form = useForm({
    name: '',
    email: '',
    password: '',
    role: '',
    phone: '',
    address: '',
});

const openCreateModal = () => {
    editingUser.value = null;
    form.clearErrors();
    form.reset();
    if (props.roles.length > 0) {
        form.role = props.roles[0].name;
    }
    isFormOpen.value = true;
};

const openEditModal = (user) => {
    editingUser.value = user;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.role = user.roles[0]?.name || '';
    form.phone = user.contact?.phone || '';
    form.address = user.contact?.address || '';
    isFormOpen.value = true;
};

const submitForm = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    } else {
        form.post(route('users.store'), {
            onSuccess: () => {
                isFormOpen.value = false;
                form.reset();
            }
        });
    }
};

const deleteUser = (user) => {
    if (confirm(`Hapus pengguna ${user.name}?`)) {
        router.delete(route('users.destroy', user.id));
    }
};

const verifyReseller = (user) => {
    if (confirm(`Verifikasi akun reseller ${user.name}?`)) {
        router.patch(route('users.verify-reseller', user.id));
    }
};

const rejectReseller = (user) => {
    if (confirm(`Tolak pendaftaran reseller ${user.name}?`)) {
        router.patch(route('users.reject-reseller', user.id));
    }
};

const isReseller = (user) => user.roles?.some(r => r.name === 'reseller');
const resellerStatus = (user) => user.reseller_status ?? null;

const getResellerBadge = (status) => {
    switch (status) {
        case 'verified': return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'pending':  return 'bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400';
        case 'rejected': return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
        default: return '';
    }
};

const getResellerLabel = (status) => {
    switch (status) {
        case 'verified': return '✓ Verified';
        case 'pending':  return '⏳ Pending';
        case 'rejected': return '✗ Ditolak';
        default: return '';
    }
};

const getRoleBadge = (roleName) => {
    switch (roleName) {
        case 'super-admin': return 'bg-red-50 text-red-650 dark:bg-red-500/10 dark:text-red-400';
        case 'owner': return 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400';
        case 'operator': return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400';
        case 'partner': return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400';
        default: return 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Pengguna" />
        <Toast />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Manajemen Pengguna</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola hak akses sistem, profil staff, dan akun partner/reseller.</p>
                </div>
                <div class="flex gap-2">
                    <Link v-if="$page.props.auth.permissions.includes('manage-roles') || $page.props.auth.roles.includes('dev-admin')" :href="route('roles.index')">
                        <Button label="Kelola Peran / Hak Akses" icon="pi pi-shield" severity="secondary" outlined />
                    </Link>
                    <Button
                        label="Tambah Pengguna"
                        icon="pi pi-plus"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                        @click="openCreateModal"
                    />
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-gray-900 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="relative w-full">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    </span>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Cari nama atau email..."
                        class="w-full !pl-9"
                        @input="applyFilters"
                    />
                </div>
                <Dropdown
                    v-model="filterRole"
                    :options="roleOptions"
                    optionLabel="name"
                    optionValue="name_value"
                    class="w-full"
                    @change="applyFilters"
                />
            </div>

            <!-- Users list table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                            <tr>
                                <th scope="col" class="px-6 py-4 font-bold">Nama Pengguna</th>
                                <th scope="col" class="px-6 py-4 font-bold">Email</th>
                                <th scope="col" class="px-6 py-4 font-bold">Peran / Role</th>
                                <th scope="col" class="px-6 py-4 font-bold">Status Reseller</th>
                                <th scope="col" class="px-6 py-4 font-bold">No. Telp</th>
                                <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ user.name }}
                                </td>
                                <td class="px-6 py-4">{{ user.email }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span v-for="role in user.roles" :key="role.id" :class="['text-xs px-2 py-0.5 rounded font-bold uppercase', getRoleBadge(role.name)]">
                                            {{ role.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <template v-if="isReseller(user) && resellerStatus(user)">
                                        <span :class="['text-xs px-2 py-0.5 rounded font-bold', getResellerBadge(resellerStatus(user))]">
                                            {{ getResellerLabel(resellerStatus(user)) }}
                                        </span>
                                    </template>
                                    <span v-else class="text-gray-300 dark:text-gray-700">—</span>
                                </td>
                                <td class="px-6 py-4">{{ user.contact?.phone || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <!-- Reseller verify/reject actions -->
                                    <template v-if="isReseller(user) && resellerStatus(user) === 'pending'">
                                        <Button
                                            icon="pi pi-check"
                                            severity="success"
                                            text rounded
                                            v-tooltip.top="'Verifikasi Reseller'"
                                            @click="verifyReseller(user)"
                                            class="mr-1"
                                        />
                                        <Button
                                            icon="pi pi-times"
                                            severity="danger"
                                            text rounded
                                            v-tooltip.top="'Tolak'"
                                            @click="rejectReseller(user)"
                                            class="mr-1"
                                        />
                                    </template>
                                    <Button icon="pi pi-pencil" severity="secondary" text rounded @click="openEditModal(user)" class="mr-2" />
                                    <Button icon="pi pi-trash" severity="danger" text rounded @click="deleteUser(user)" :disabled="user.id === page.props.auth.user.id" />
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td colspan="6" class="px-6 py-8 text-center text-gray-400">Tidak ada pengguna ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="users.links.length > 3" class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/20 flex justify-between items-center">
                    <span class="text-xs text-gray-500">Menampilkan {{ users.from || 0 }} - {{ users.to || 0 }} dari {{ users.total }} pengguna</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in users.links"
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
            <Dialog v-model:visible="isFormOpen" modal :header="editingUser ? 'Edit Pengguna' : 'Tambah Pengguna Baru'" class="w-full max-w-md">
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                        {{ err }}
                    </Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Nama Lengkap <span class="text-red-500">*</span></label>
                        <InputText v-model="form.name" required placeholder="Nama lengkap..." />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Email <span class="text-red-500">*</span></label>
                        <InputText v-model="form.email" type="email" required placeholder="Email..." />
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Password <span v-if="!editingUser" class="text-red-500">*</span></label>
                        <InputText v-model="form.password" type="password" :required="!editingUser" placeholder="Kata sandi..." />
                        <p v-if="editingUser" class="text-[10px] text-gray-400">Kosongkan password jika tidak ingin diubah.</p>
                    </div>

                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Peran / Role Akses <span class="text-red-500">*</span></label>
                        <Dropdown
                            v-model="form.role"
                            :options="filteredRoles"
                            optionLabel="name"
                            optionValue="name"
                            placeholder="Pilih Peran..."
                            class="w-full"
                            required
                        />
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-3 space-y-4">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Profil Kontak Opsional</span>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">No. Telepon / WA</label>
                            <InputText v-model="form.phone" placeholder="Contoh: 081234..." />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Alamat Rumah</label>
                            <Textarea v-model="form.address" rows="3" placeholder="Masukkan alamat..." />
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button type="submit" :label="editingUser ? 'Simpan Perubahan' : 'Buat Akun'" :loading="form.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
