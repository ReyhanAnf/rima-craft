<script setup>
import { ref, computed } from 'vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';

const props = defineProps({
    form: Object,
    settings: Object,
    isDevAdmin: Boolean,
});

// ── Parse sponsors from JSON string in form ──────────────────────────────────

const sponsors = ref([]);

try {
    const parsed = JSON.parse(props.form.sponsors_json || '[]');
    sponsors.value = Array.isArray(parsed) ? parsed : [];
} catch {
    // Fall back: try migrating old sponsor_1..4 format
    const legacy = [];
    for (let i = 1; i <= 4; i++) {
        const name = props.settings[`sponsor_${i}_name`];
        if (name) {
            legacy.push({
                name,
                description: '',
                link: '',
                logo_url: props.settings[`sponsor_${i}_logo_url`] || '',
                _logoFile: null,
                _logoPreview: props.settings[`sponsor_${i}_logo_url`]
                    ? `/storage/${props.settings[`sponsor_${i}_logo_url`]}`
                    : null,
            });
        }
    }
    sponsors.value = legacy;
}

// Ensure each sponsor has all fields
sponsors.value = sponsors.value.map(s => ({
    name: s.name || '',
    description: s.description || '',
    link: s.link || '',
    logo_url: s.logo_url || '',
    _logoFile: null,
    _logoPreview: s.logo_url
        ? (s.logo_url.startsWith('http') || s.logo_url.startsWith('/') ? s.logo_url : `/storage/${s.logo_url}`)
        : null,
}));

// ── Sync sponsors back to form JSON on every change ──────────────────────────

function syncForm() {
    props.form.sponsors_json = JSON.stringify(
        sponsors.value.map(({ name, description, link, logo_url }) => ({ name, description, link, logo_url }))
    );
}

function addSponsor() {
    sponsors.value.push({ name: '', description: '', link: '', logo_url: '', _logoFile: null, _logoPreview: null });
    syncForm();
}

function removeSponsor(idx) {
    sponsors.value.splice(idx, 1);
    // Rebuild logo file map
    rebuildLogoFiles();
    syncForm();
}

function moveSponsor(from, to) {
    const arr = sponsors.value;
    const item = arr.splice(from, 1)[0];
    arr.splice(to, 0, item);
    rebuildLogoFiles();
    syncForm();
}

// ── Logo file handling ────────────────────────────────────────────────────────

function onLogoChange(idx, e) {
    const file = e.target.files?.[0];
    if (!file) return;
    sponsors.value[idx]._logoFile = file;
    sponsors.value[idx]._logoPreview = URL.createObjectURL(file);
    rebuildLogoFiles();
}

function clearLogo(idx) {
    sponsors.value[idx]._logoFile = null;
    sponsors.value[idx]._logoPreview = null;
    sponsors.value[idx].logo_url = '';
    rebuildLogoFiles();
    syncForm();
}

function rebuildLogoFiles() {
    // Inertia useForm doesn't support nested arrays of files, so we pass
    // sponsor_logos as a plain object keyed by index
    const map = {};
    sponsors.value.forEach((s, i) => {
        if (s._logoFile) map[i] = s._logoFile;
    });
    props.form.sponsor_logos = map;
}
</script>

<template>
    <div class="space-y-6">
        <!-- Access warning -->
        <div v-if="!isDevAdmin" class="p-4 rounded-xl bg-amber-50 dark:bg-amber-950/20 text-amber-800 dark:text-amber-400 text-xs border border-amber-200 dark:border-amber-900/40 flex items-center gap-2.5">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <span class="font-bold">Akses Terkunci!</span> Pengaturan ini hanya dapat diubah oleh Developer Admin.
            </div>
        </div>

        <!-- Subtitle -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title><span class="text-sm font-bold uppercase tracking-wider text-gray-400">Subtitle Brand / Project</span></template>
            <template #content>
                <div class="space-y-4 pt-2">
                    <div class="flex flex-col gap-1.5">
                        <label class="text-xs font-semibold">Subtitle Project / Sistem</label>
                        <InputText v-model="form.business_subtitle" :disabled="!isDevAdmin" placeholder="Contoh: Sistem Informasi Management Keuangan Daring" />
                        <p class="text-[10px] text-gray-500">Tampil di bawah nama brand pada navigasi bar website.</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Sponsors — dynamic list -->
        <Card class="!border !border-gray-200 dark:!border-gray-800 !bg-white dark:!bg-gray-900">
            <template #title>
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold uppercase tracking-wider text-gray-400">Sponsor Pembangunan Sistem</span>
                    <button
                        v-if="isDevAdmin"
                        type="button"
                        @click="addSponsor"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-gray-950 text-xs font-bold transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Sponsor
                    </button>
                </div>
            </template>
            <template #content>
                <p class="text-xs text-gray-400 mb-5">Sponsor yang mendukung pembangunan sistem — ditampilkan di sidebar / footer website.</p>

                <!-- Empty state -->
                <div v-if="sponsors.length === 0" class="py-10 text-center text-gray-400 text-sm border border-dashed border-gray-200 dark:border-gray-700 rounded-xl">
                    Belum ada sponsor. Klik <strong>Tambah Sponsor</strong> untuk menambahkan.
                </div>

                <!-- Sponsor list -->
                <div class="space-y-4">
                    <div
                        v-for="(sponsor, idx) in sponsors"
                        :key="idx"
                        class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30 space-y-4"
                    >
                        <!-- Header: sponsor # + actions -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Sponsor {{ idx + 1 }}</span>
                            <div v-if="isDevAdmin" class="flex items-center gap-1">
                                <button type="button" @click="moveSponsor(idx, idx - 1)" :disabled="idx === 0"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 disabled:opacity-30 transition" title="Pindah ke atas">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                </button>
                                <button type="button" @click="moveSponsor(idx, idx + 1)" :disabled="idx === sponsors.length - 1"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 disabled:opacity-30 transition" title="Pindah ke bawah">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <button type="button" @click="removeSponsor(idx)"
                                    class="p-1.5 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/20 transition ml-1" title="Hapus sponsor">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name (short/abbreviation) -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[11px] font-semibold text-gray-500">
                                    Nama Singkat
                                    <span class="ml-1 text-[10px] text-gray-400 font-normal">(tampil di UI, hover = deskripsi)</span>
                                </label>
                                <InputText
                                    v-model="sponsor.name"
                                    :disabled="!isDevAdmin"
                                    placeholder="Contoh: UNTAG, BPPT..."
                                    @input="syncForm"
                                />
                            </div>

                            <!-- Link (optional) -->
                            <div class="flex flex-col gap-1.5">
                                <label class="text-[11px] font-semibold text-gray-500">Link Website <span class="text-gray-400 font-normal">(opsional)</span></label>
                                <InputText
                                    v-model="sponsor.link"
                                    :disabled="!isDevAdmin"
                                    placeholder="https://..."
                                    @input="syncForm"
                                />
                            </div>

                            <!-- Description (full name shown on hover) -->
                            <div class="md:col-span-2 flex flex-col gap-1.5">
                                <label class="text-[11px] font-semibold text-gray-500">
                                    Nama Lengkap / Deskripsi
                                    <span class="ml-1 text-[10px] text-gray-400 font-normal">(muncul saat hover di sidebar)</span>
                                </label>
                                <InputText
                                    v-model="sponsor.description"
                                    :disabled="!isDevAdmin"
                                    placeholder="Contoh: Universitas 17 Agustus 1945 Surabaya"
                                    @input="syncForm"
                                />
                            </div>

                            <!-- Logo upload -->
                            <div class="md:col-span-2 flex flex-col gap-2">
                                <label class="text-[11px] font-semibold text-gray-500">Logo Sponsor</label>
                                <div class="flex items-center gap-4">
                                    <!-- Preview -->
                                    <div class="w-20 h-12 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img v-if="sponsor._logoPreview" :src="sponsor._logoPreview" class="h-full w-full object-contain p-1" />
                                        <svg v-else class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div v-if="isDevAdmin" class="flex flex-col gap-2">
                                        <label :for="`sponsor-logo-${idx}`" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                            Pilih Logo
                                        </label>
                                        <input :id="`sponsor-logo-${idx}`" type="file" accept="image/*" class="hidden" @change="(e) => onLogoChange(idx, e)" />
                                        <button v-if="sponsor._logoPreview" type="button" @click="clearLogo(idx)" class="text-[10px] text-red-500 hover:text-red-700 font-semibold text-left">
                                            Hapus logo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add button at bottom if already has items -->
                <div v-if="isDevAdmin && sponsors.length > 0" class="mt-4 flex justify-center">
                    <button
                        type="button"
                        @click="addSponsor"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 text-xs font-bold text-gray-500 hover:text-amber-600 hover:border-amber-400 transition"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Sponsor Lain
                    </button>
                </div>
            </template>
        </Card>
    </div>
</template>
