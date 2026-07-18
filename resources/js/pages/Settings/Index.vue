<script setup>
import { ref, watch, computed } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import Message from 'primevue/message';

// Import Tab Components
import TabUmum from './Components/TabUmum.vue';
import TabLanding from './Components/TabLanding.vue';
import TabSEO from './Components/TabSEO.vue';
import TabInfo from './Components/TabInfo.vue';
import TabDev from './Components/TabDev.vue';

const props = defineProps({
    settings: Object,
});

const page = usePage();

const activeTab = ref('umum');

const form = useForm({
    // Umum
    business_name: props.settings.business_name || 'Rima Craft',
    business_phone: props.settings.business_phone || '6281234567890',
    email: props.settings.email || '',
    instagram: props.settings.instagram || '',
    address: props.settings.address || '',
    gmaps_iframe: props.settings.gmaps_iframe || '',
    business_hours: props.settings.business_hours || '',

    // Landing
    hero_badge: props.settings.hero_badge || 'Karya Autentik Nusantara',
    hero_title_1: props.settings.hero_title_1 || 'Seni Anyaman Tradisional',
    hero_title_2: props.settings.hero_title_2 || 'Bercita Rasa Modern',
    hero_description: props.settings.hero_description || '',
    looping_video_url: props.settings.looping_video_url || '',
    video_url: props.settings.video_url || '',
    hero_image: null,
    looping_video: null,
    gallery_1_image: null,
    gallery_2_image: null,

    // SEO
    seo_title: props.settings.seo_title || '',
    seo_description: props.settings.seo_description || '',
    seo_keywords: props.settings.seo_keywords || '',

    // Info Halaman
    page_terms: props.settings.page_terms || '',
    page_privacy: props.settings.page_privacy || '',
    page_shipping: props.settings.page_shipping || '',

    // Dev Admin
    logo: null,
    business_subtitle: props.settings.business_subtitle || '',
    sponsors_json: props.settings.sponsors_json || '[]',
    sponsor_logos: {},
});

const isDevAdmin = computed(() => page.props.auth?.roles?.includes('dev-admin'));

const submitForm = () => {
    form.post(route('settings.update'), {
        preserveState: true,
        onSuccess: () => {
            form.clearErrors();
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Pengaturan Web" />

        <div class="space-y-6">
            <!-- Header section -->
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pengaturan Web</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Atur konten informasi bisnis, landing page, dan konfigurasi sistem.</p>
                </div>

                <!-- Tab chooser: scrollable pills, aligned right on desktop -->
                <div class="overflow-x-auto pb-1 -mb-1 md:flex-shrink-0">
                    <div class="inline-flex min-w-max bg-gray-100 dark:bg-gray-900 p-1 rounded-xl border border-gray-200 dark:border-gray-800">
                        <button @click="activeTab = 'umum'" type="button" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap', activeTab === 'umum' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                            Data Umum
                        </button>
                        <button @click="activeTab = 'landing'" type="button" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap', activeTab === 'landing' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                            Landing Page
                        </button>
                        <button @click="activeTab = 'seo'" type="button" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap', activeTab === 'seo' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                            SEO & Meta
                        </button>
                        <button @click="activeTab = 'info'" type="button" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all whitespace-nowrap', activeTab === 'info' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                            Halaman Info
                        </button>
                        <button @click="activeTab = 'dev'" type="button" :class="['px-4 py-2 text-xs font-bold rounded-lg transition-all flex items-center gap-1 whitespace-nowrap', activeTab === 'dev' ? 'bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 shadow border border-gray-200/50 dark:border-gray-700' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300']">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" :class="isDevAdmin ? 'text-amber-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Sistem & Sponsor
                        </button>
                    </div>
                </div>
            </div>

            <!-- Form Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Tab Components -->
                <TabUmum v-show="activeTab === 'umum'" :form="form" :settings="settings" />
                
                <TabLanding v-show="activeTab === 'landing'" :form="form" :settings="settings" />
                
                <TabSEO v-show="activeTab === 'seo'" :form="form" />
                
                <TabInfo v-show="activeTab === 'info'" :form="form" />
                
                <TabDev v-show="activeTab === 'dev'" :form="form" :settings="settings" :isDevAdmin="isDevAdmin" />

                <!-- Save CTA -->
                <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-800 pt-5">
                    <Button
                        type="submit"
                        label="Simpan Pengaturan"
                        icon="pi pi-save"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
