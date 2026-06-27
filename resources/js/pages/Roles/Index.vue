<script setup>
import { watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    roles: Array,
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
</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Peran / Hak Akses" />
        <Toast />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Header section -->
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('users.index')">
                        <Button icon="pi pi-arrow-left" severity="secondary" rounded outlined />
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Peran & Hak Akses (Roles)</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Kelola tingkat kewenangan dan otorisasi menu untuk setiap peran sistem.</p>
                    </div>
                </div>
            </div>

            <!-- Roles Table -->
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-800">
                        <tr>
                            <th scope="col" class="px-6 py-4 font-bold">Nama Peran / Role</th>
                            <th scope="col" class="px-6 py-4 font-bold text-center">Jumlah Pengguna</th>
                            <th scope="col" class="px-6 py-4 font-bold text-center">Kewenangan Aktif</th>
                            <th scope="col" class="px-6 py-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        <tr v-for="role in roles" :key="role.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition">
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white uppercase tracking-wider">
                                {{ role.name }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-bold px-2 py-0.5 rounded text-xs">
                                    {{ role.users_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-amber-50 dark:bg-amber-550/10 text-amber-600 dark:text-amber-400 font-bold px-2 py-0.5 rounded text-xs">
                                    {{ role.permissions_count }} Hak
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('roles.edit', role.id)">
                                    <Button label="Edit Hak Akses" icon="pi pi-shield" size="small" text rounded class="!text-amber-550" />
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
