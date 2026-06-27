import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useAdminStore = defineStore('admin', () => {
    const isSidebarOpen = ref(true);
    const activeRange = ref('this_month');

    function toggleSidebar() {
        isSidebarOpen.value = !isSidebarOpen.value;
    }

    function setRange(range) {
        activeRange.value = range;
    }

    return {
        isSidebarOpen,
        activeRange,
        toggleSidebar,
        setRange
    };
});
