<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';

const props = defineProps({
    role: Object,
    groupedPermissions: Object,
});

const page = usePage();
const isDevAdmin = computed(() => page.props.auth?.roles?.includes('dev-admin'));

const form = useForm({
    name: props.role.name,
    permissions: props.role.permissions.map(p => p.id),
});

const submitForm = () => {
    form.put(route('roles.update', props.role.id));
};

const toggleAllInModule = (permissionsList, event) => {
    const ids = permissionsList.map(p => p.id);
    const checked = event.target.checked;
    if (checked) {
        // Add only those not present
        ids.forEach(id => {
            if (!form.permissions.includes(id)) {
                form.permissions.push(id);
            }
        });
    } else {
        // Remove ids
        form.permissions = form.permissions.filter(id => !ids.includes(id));
    }
};

const isAllInModuleChecked = (permissionsList) => {
    return permissionsList.every(p => form.permissions.includes(p.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit Hak Akses: ${role.name}`" />

        <div class="space-y-6 max-w-4xl mx-auto pb-24">
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

            <div v-else class="space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-4">
                    <Link :href="route('roles.index')">
                        <Button icon="pi pi-arrow-left" severity="secondary" rounded outlined />
                    </Link>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white uppercase">Edit Hak Akses: {{ role.name }}</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Aktifkan atau matikan hak akses menu secara spesifik untuk peran ini.</p>
                    </div>
                </div>

            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Grouped Permissions list -->
                <div class="space-y-6">
                    <Card
                        v-for="(permissions, moduleName) in groupedPermissions"
                        :key="moduleName"
                        class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900"
                    >
                        <template #title>
                            <div class="flex justify-between items-center border-b border-gray-150 dark:border-gray-800 pb-2 mb-3">
                                <span class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">{{ moduleName }}</span>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        :checked="isAllInModuleChecked(permissions)"
                                        @change="toggleAllInModule(permissions, $event)"
                                        class="rounded border-gray-300 text-amber-500 focus:ring-amber-500 w-4 h-4"
                                    />
                                    <span class="text-xs text-gray-400 font-semibold uppercase">Pilih Semua</span>
                                </label>
                            </div>
                        </template>
                        <template #content>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 pt-1">
                                <div v-for="permission in permissions" :key="permission.id" class="flex items-start gap-2.5">
                                    <Checkbox
                                        :id="`perm-${permission.id}`"
                                        v-model="form.permissions"
                                        :value="permission.id"
                                    />
                                    <label :for="`perm-${permission.id}`" class="text-xs font-medium text-gray-700 dark:text-gray-300 cursor-pointer select-none leading-none pt-0.5">
                                        <div class="font-bold text-gray-800 dark:text-white capitalize">{{ permission.name.split('-')[0] }}</div>
                                        <div class="text-[10px] text-gray-400 mt-0.5">{{ permission.name }}</div>
                                    </label>
                                </div>
                            </div>
                        </template>
                    </Card>
                </div>

                <!-- Action Footer CTA -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-150 dark:border-gray-800">
                    <Link :href="route('roles.index')">
                        <Button label="Batal" severity="secondary" text />
                    </Link>
                    <Button
                        type="submit"
                        label="Simpan Kewenangan"
                        icon="pi pi-save"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </form>
        </div>
        </div>
    </AdminLayout>
</template>
