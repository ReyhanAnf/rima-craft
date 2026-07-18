<script setup>
import { watch, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

const props = defineProps({
    roles: Array,
});

const page = usePage();

const isDevAdmin = computed(() => page.props.auth?.roles?.includes('dev-admin'));

</script>

<template>
    <AdminLayout>
        <Head title="Manajemen Peran / Hak Akses" />

        <div class="space-y-6 max-w-4xl mx-auto">
            <!-- Access Restriction Warning -->
            <div v-if="!isDevAdmin" class="py-16 text-center space-y-4 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl shadow-sm p-8">
                <div class="w-16 h-16 bg-red-50 dark:bg-red-950/20 rounded-full flex items-center justify-center mx-auto text-red-500">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Akses Dibatasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mx-auto">Halaman manajemen peran dan hak akses (roles & permissions) hanya dapat dikelola oleh **Developer Admin**.</p>
                <Link :href="route('users.index')">
                    <Button label="Kembali ke Manajemen Pengguna" class="mt-4 !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                </Link>
            </div>

            <!-- Roles Table -->
            <div v-else class="space-y-6">
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
        </div>
    </AdminLayout>
</template>
