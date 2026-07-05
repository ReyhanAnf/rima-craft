<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Message from 'primevue/message';

const props = defineProps({
    user: Object,
    contact: Object,
});

const form = useForm({
    name: props.user.name,
    phone: props.contact?.phone || '',
    address: props.contact?.address || '',
    company_name: props.contact?.company_name || '',
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const submitForm = () => {
    form.post(route('reseller.profile.update'), {
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
        }
    });
};
</script>

<script>
export default {
    name: 'ResellerProfile'
}
</script>

<template>
    <AdminLayout>
        <Head title="Profil Reseller Saya" />

        <div class="max-w-2xl mx-auto space-y-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Profil Reseller / B2B</h2>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data profil kontak, nama usaha/toko, alamat pengiriman, dan keamanan sandi keagenan Anda.</p>
            </div>

            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm">
                <div class="space-y-6">
                    <!-- Informasi Perusahaan / Profil -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                        <template #title>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Informasi Keagenan</span>
                        </template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Lengkap PIC <span class="text-red-500">*</span></label>
                                    <InputText v-model="form.name" required />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nama Perusahaan / Toko</label>
                                    <InputText v-model="form.company_name" placeholder="Nama toko reseller Anda..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Alamat Email Resmi</label>
                                    <InputText :value="user.email" disabled class="opacity-60" />
                                    <p class="text-[10px] text-gray-400">Email akun tidak dapat diubah secara mandiri.</p>
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Nomor WhatsApp PIC</label>
                                    <InputText v-model="form.phone" placeholder="Contoh: 0812..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Alamat Pengiriman Keagenan</label>
                                    <Textarea v-model="form.address" rows="3" placeholder="Alamat gudang / toko reseller..." />
                                </div>
                            </div>
                        </template>
                    </Card>

                    <!-- Ganti Password -->
                    <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 shadow-sm">
                        <template #title>
                            <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Keamanan & Sandi</span>
                        </template>
                        <template #content>
                            <div class="space-y-4 pt-2">
                                <p class="text-xs text-gray-400">Kosongkan kolom di bawah jika Anda tidak ingin mengubah password.</p>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Password Saat Ini</label>
                                    <InputText v-model="form.current_password" type="password" placeholder="Masukkan password sekarang..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Password Baru</label>
                                    <InputText v-model="form.new_password" type="password" placeholder="Minimal 8 karakter..." />
                                </div>

                                <div class="flex flex-col gap-1.5">
                                    <label class="text-xs font-semibold">Konfirmasi Password Baru</label>
                                    <InputText v-model="form.new_password_confirmation" type="password" placeholder="Ketik ulang password baru..." />
                                </div>
                            </div>
                        </template>
                        <template #footer>
                            <div class="flex justify-end pt-4 border-t border-gray-150 dark:border-gray-800">
                                <Button type="submit" label="Simpan Perubahan" :loading="form.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                            </div>
                        </template>
                    </Card>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
