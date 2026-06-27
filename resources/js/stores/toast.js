import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useToastStore = defineStore('toast', () => {
    const toasts = ref([]);
    let counter = 0;

    function show(message, type = 'success', duration = 4000) {
        const id = ++counter;
        toasts.value.push({ id, message, type });
        setTimeout(() => dismiss(id), duration);
    }

    function dismiss(id) {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }

    function success(message) { show(message, 'success'); }
    function error(message)   { show(message, 'error'); }
    function warning(message) { show(message, 'warning'); }

    return { toasts, show, dismiss, success, error, warning };
});
