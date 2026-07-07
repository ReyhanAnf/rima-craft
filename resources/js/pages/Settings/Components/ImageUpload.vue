<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    label:       { type: String,  default: 'Gambar' },
    hint:        { type: String,  default: null },
    currentUrl:  { type: String,  default: null },  // URL gambar yang sudah tersimpan
    accept:      { type: String,  default: 'image/*' },
    disabled:    { type: Boolean, default: false },
    previewClass:{ type: String,  default: 'h-24 w-24' }, // Ukuran preview
});

const emit = defineEmits(['change']);

const preview   = ref(null); // data URL untuk preview baru
const fileName  = ref(null);
const inputRef  = ref(null);

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    fileName.value = file.name;
    emit('change', file);

    const reader = new FileReader();
    reader.onload = (ev) => { preview.value = ev.target.result; };
    reader.readAsDataURL(file);
};

const clearFile = () => {
    preview.value  = null;
    fileName.value = null;
    if (inputRef.value) inputRef.value.value = '';
    emit('change', null);
};

// Sumber gambar yang ditampilkan: preview baru > gambar tersimpan
const displaySrc = () => preview.value || (props.currentUrl ? `/storage/${props.currentUrl}` : null);
</script>

<template>
    <div class="flex flex-col gap-1.5">
        <label v-if="label" class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ label }}</label>

        <div class="flex items-start gap-3">
            <!-- Preview box -->
            <div
                v-if="displaySrc()"
                :class="['relative flex-shrink-0 rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900', previewClass]"
            >
                <img :src="displaySrc()" class="w-full h-full object-cover" alt="Preview" />
                <!-- Clear button on hover -->
                <button
                    v-if="!disabled && preview"
                    type="button"
                    @click="clearFile"
                    class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 hover:opacity-100 transition-opacity rounded-xl"
                    title="Hapus pilihan"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Empty placeholder -->
            <div
                v-else
                :class="['flex-shrink-0 rounded-xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 flex items-center justify-center text-gray-400', previewClass]"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>

            <!-- Upload area -->
            <div class="flex-1 min-w-0">
                <label
                    :class="[
                        'flex items-center gap-2.5 w-full cursor-pointer rounded-xl border px-4 py-3 text-sm transition',
                        disabled
                            ? 'cursor-not-allowed border-gray-200 dark:border-gray-800 bg-gray-100 dark:bg-gray-900 text-gray-400'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 hover:border-amber-400 hover:bg-amber-50/40 dark:hover:border-amber-500/60 dark:hover:bg-amber-500/5 text-gray-600 dark:text-gray-300'
                    ]"
                >
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span class="truncate text-xs font-medium">
                        {{ fileName ?? (displaySrc() ? 'Ganti gambar...' : 'Pilih gambar...') }}
                    </span>
                    <input
                        ref="inputRef"
                        type="file"
                        :accept="accept"
                        :disabled="disabled"
                        class="sr-only"
                        @change="onFileChange"
                    />
                </label>
                <p v-if="hint" class="mt-1.5 text-[10px] text-gray-400">{{ hint }}</p>
                <p v-if="fileName && !disabled" class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-medium">
                    ✓ File dipilih: {{ fileName }}
                </p>
            </div>
        </div>
    </div>
</template>
