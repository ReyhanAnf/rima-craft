<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import Message from 'primevue/message';
import Textarea from 'primevue/textarea';

const props = defineProps({
    type: String,
    businessName: String,
});

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    company_name: '',
    business_type: '',
    password: '',
    password_confirmation: '',
    agree_terms: false,
});

const submitForm = () => {
    form.post(route('register.submit', props.type));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8">
        <Head :title="type === 'partner' ? 'Daftar Partner' : 'Daftar Akun Baru'" />

        <!-- Header -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
            <Link href="/" class="inline-block">
                <span class="text-3xl font-serif font-bold text-gray-900 dark:text-white">{{ businessName }}</span>
            </Link>
            <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ type === 'partner' ? 'Daftar sebagai Partner' : 'Buat Akun Baru' }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ type === 'partner' ? 'Dapatkan harga reseller dan keuntungan khusus keagenan' : 'Belanja kerajinan tradisional berkualitas Nusantara' }}
            </p>
        </div>

        <!-- Form Card -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg">
            <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900 !py-2 !px-1">
                <template #content>
                    <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                        <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small">
                            {{ err }}
                        </Message>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <!-- Name -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold">Nama Lengkap <span class="text-red-500">*</span></label>
                            <InputText v-model="form.name" required />
                        </div>

                        <!-- Email -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold">Alamat Email <span class="text-red-500">*</span></label>
                            <InputText v-model="form.email" type="email" required />
                        </div>

                        <!-- Phone -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold">Nomor WhatsApp / Telepon</label>
                            <InputText v-model="form.phone" placeholder="0812..." />
                        </div>

                        <!-- Address -->
                        <div class="flex flex-col gap-1">
                            <label class="text-xs font-semibold">Alamat Lengkap</label>
                            <Textarea v-model="form.address" rows="3" placeholder="Alamat pengiriman barang..." />
                        </div>

                        <!-- Partner Info -->
                        <div v-if="type === 'partner'" class="border-t border-gray-100 dark:border-gray-850 pt-4 space-y-4">
                            <h3 class="text-sm font-bold uppercase text-gray-400">Informasi Bisnis</h3>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold">Nama Perusahaan / Toko <span class="text-red-500">*</span></label>
                                <InputText v-model="form.company_name" required />
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-semibold">Jenis Usaha</label>
                                <InputText v-model="form.business_type" placeholder="Contoh: Retailer, Reseller, Distributor" />
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="border-t border-gray-100 dark:border-gray-850 pt-4 space-y-4">
                            <h3 class="text-sm font-bold uppercase text-gray-400">Keamanan Akun</h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold">Password <span class="text-red-500">*</span></label>
                                    <Password v-model="form.password" toggleMask :feedback="false" required class="w-full" inputClass="w-full" />
                                    <span class="text-[10px] text-gray-400">Minimal 8 karakter</span>
                                </div>

                                <div class="flex flex-col gap-1">
                                    <label class="text-xs font-semibold">Konfirmasi Password <span class="text-red-500">*</span></label>
                                    <Password v-model="form.password_confirmation" toggleMask :feedback="false" required class="w-full" inputClass="w-full" />
                                </div>
                            </div>
                        </div>

                        <!-- Agree terms -->
                        <div class="flex items-start gap-2 pt-2">
                            <Checkbox id="agree_terms" v-model="form.agree_terms" binary required />
                            <label for="agree_terms" class="text-xs text-gray-650 dark:text-gray-400 cursor-pointer">
                                Saya setuju dengan <a href="#" class="text-amber-500 hover:underline">Syarat & Ketentuan</a> serta <a href="#" class="text-amber-500 hover:underline">Kebijakan Privasi</a> {{ businessName }}
                            </label>
                        </div>

                        <Button type="submit" label="Daftar Sekarang" class="w-full !bg-amber-600 hover:!bg-amber-700 !border-amber-600 hover:!border-amber-700 !text-white font-bold" :loading="form.processing" />
                    </form>

                    <!-- Login redirection -->
                    <div class="mt-6 border-t border-gray-100 dark:border-gray-850 pt-6 text-center">
                        <span class="text-xs text-gray-400">Sudah memiliki akun? </span>
                        <Link :href="route('login')" class="text-xs font-bold text-amber-500 hover:underline">Masuk disini</Link>
                    </div>
                </template>
            </Card>

            <!-- Benefits box for partner -->
            <div v-if="type === 'partner'" class="mt-6 bg-gradient-to-r from-emerald-500/5 to-teal-500/5 border border-emerald-500/10 rounded-xl p-5 text-xs text-emerald-800 dark:text-emerald-300 space-y-3">
                <h4 class="font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Keuntungan Partner</h4>
                <ul class="space-y-1.5">
                    <li class="flex items-center gap-2"><i class="pi pi-check text-emerald-500"></i> Harga khusus reseller (lebih murah 10-20%)</li>
                    <li class="flex items-center gap-2"><i class="pi pi-check text-emerald-500"></i> Akses dashboard reseller dengan analitik khusus</li>
                    <li class="flex items-center gap-2"><i class="pi pi-check text-emerald-500"></i> Riwayat pembayaran & laporan tagihan piutang</li>
                </ul>
            </div>
        </div>
    </div>
</template>
