<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    fixed: { type: Boolean, default: true },
});

const page = usePage();
const activeAnnouncement = computed(() => page.props.activeAnnouncement);

const isDismissed = ref(false);

function checkDismissed() {
    if (activeAnnouncement.value) {
        const key = `dismissed_announcement_${activeAnnouncement.value.id}`;
        const storedVersion = sessionStorage.getItem(key);
        const currentVersion = `v${activeAnnouncement.value.version || 1}`;
        if (storedVersion === currentVersion) {
            isDismissed.value = true;
            return;
        }
    }
    isDismissed.value = false;
}

onMounted(() => {
    checkDismissed();
});

function dismiss() {
    isDismissed.value = true;
    if (activeAnnouncement.value) {
        const key = `dismissed_announcement_${activeAnnouncement.value.id}`;
        const currentVersion = `v${activeAnnouncement.value.version || 1}`;
        sessionStorage.setItem(key, currentVersion);
    }
    window.dispatchEvent(new CustomEvent('announcement-dismissed'));
}

const bannerClasses = computed(() => {
    const type = activeAnnouncement.value?.type || 'info';
    switch (type) {
        case 'warning':
            return 'bg-amber-600 text-white dark:bg-amber-700';
        case 'danger':
            return 'bg-red-600 text-white dark:bg-red-700';
        case 'success':
            return 'bg-emerald-600 text-white dark:bg-emerald-700';
        case 'info':
        default:
            return 'bg-gradient-to-r from-amber-500 via-amber-600 to-amber-500 text-gray-950 font-medium';
    }
});
</script>

<template>
    <div
        v-if="activeAnnouncement && !isDismissed"
        :class="[
            fixed ? 'fixed top-0 inset-x-0 z-50 h-[30px]' : 'relative z-50 w-full min-h-[30px] py-1',
            'px-3 text-[11px] sm:text-xs flex items-center justify-between shadow-sm transition-all duration-300',
            bannerClasses
        ]"
    >
        <div class="max-w-7xl mx-auto flex items-center justify-center gap-2 text-center flex-1 min-w-0 px-2">
            <i class="pi pi-megaphone text-xs shrink-0"></i>
            <span class="truncate font-medium">
                <strong v-if="activeAnnouncement.title" class="font-bold mr-1">{{ activeAnnouncement.title }}:</strong>
                {{ activeAnnouncement.content }}
            </span>
            <a
                v-if="activeAnnouncement.url"
                :href="activeAnnouncement.url"
                target="_blank"
                class="underline underline-offset-2 hover:opacity-80 shrink-0 font-bold ml-1 inline-flex items-center gap-1"
            >
                Selengkapnya
                <i class="pi pi-external-link text-[9px]"></i>
            </a>
        </div>
        <button
            type="button"
            @click="dismiss"
            class="p-0.5 rounded hover:bg-black/10 transition shrink-0 ml-2"
            title="Tutup pengumuman"
        >
            <i class="pi pi-times text-[10px]"></i>
        </button>
    </div>
</template>
