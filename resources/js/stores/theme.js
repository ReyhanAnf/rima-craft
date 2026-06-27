import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(
        localStorage.getItem('theme') === 'dark' ||
        (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
    );

    function apply() {
        document.documentElement.classList.toggle('dark', isDark.value);
    }

    function toggle() {
        isDark.value = !isDark.value;
    }

    watch(isDark, (val) => {
        localStorage.setItem('theme', val ? 'dark' : 'light');
        apply();
    }, { immediate: true });

    return { isDark, toggle };
});
