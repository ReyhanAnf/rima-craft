<script setup>
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';

const props = defineProps({
    jobs: Object,
    artisans: Array,
});

const isFormOpen = ref(false);
const editingJob = ref(null);

const statusOptions = [
    { label: 'Dibuka', value: 'open' },
    { label: 'Dikerjakan', value: 'in_progress' },
    { label: 'Selesai', value: 'done' },
    { label: 'Dibatalkan', value: 'cancelled' },
];

const assignmentStatusOptions = [
    { label: 'Tertarik', value: 'interested' },
    { label: 'Ditugaskan', value: 'assigned' },
    { label: 'Selesai', value: 'done' },
    { label: 'Dibatalkan', value: 'cancelled' },
];

const form = useForm({
    title: '',
    description: '',
    estimated_wage: 0,
    work_date: '',
    status: 'open',
    assignments: [],
});

const formatCurrency = (val) => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
}).format(val || 0);

const formatDate = (dateStr) => {
    if (!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const openCreateModal = () => {
    editingJob.value = null;
    form.clearErrors();
    form.reset();
    form.status = 'open';
    form.assignments = [];
    isFormOpen.value = true;
};

const openEditModal = (job) => {
    editingJob.value = job;
    form.clearErrors();
    form.title = job.title;
    form.description = job.description || '';
    form.estimated_wage = Number(job.estimated_wage || 0);
    form.work_date = job.work_date ? String(job.work_date).slice(0, 10) : '';
    form.status = job.status || 'open';
    form.assignments = (job.assignments || []).map((assignment) => ({
        artisan_id: assignment.artisan_id,
        assigned_wage: Number(assignment.assigned_wage || 0),
        status: assignment.status || 'assigned',
        notes: assignment.notes || '',
    }));
    isFormOpen.value = true;
};

const addAssignment = () => {
    form.assignments.push({
        artisan_id: null,
        assigned_wage: 0,
        status: 'assigned',
        notes: '',
    });
};

const removeAssignment = (idx) => {
    form.assignments.splice(idx, 1);
};

const submitForm = () => {
    if (editingJob.value) {
        form.put(route('artisan-jobs.update', editingJob.value.id), {
            onSuccess: () => isFormOpen.value = false,
        });
    } else {
        form.post(route('artisan-jobs.store'), {
            onSuccess: () => isFormOpen.value = false,
        });
    }
};

const deleteJob = (job) => {
    if (confirm(`Hapus pekerjaan "${job.title}"?`)) {
        router.delete(route('artisan-jobs.destroy', job.id));
    }
};

const statusBadge = (status) => {
    switch (status) {
        case 'open': return 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400';
        case 'in_progress': return 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400';
        case 'done': return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
        default: return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400';
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Pekerjaan Pengrajin" />

        <div class="space-y-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pekerjaan Pengrajin</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pengumuman kerja produksi yang longgar, tidak tersinkron langsung dengan produk.</p>
                </div>
                <Button
                    label="Buat Pekerjaan"
                    icon="pi pi-plus"
                    class="w-full sm:w-auto justify-center !bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    @click="openCreateModal"
                />
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
                <div class="divide-y divide-gray-100 dark:divide-gray-800">
                    <div v-for="job in jobs.data" :key="job.id" class="p-4 md:p-5">
                        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ job.title }}</h3>
                                    <span :class="['text-[10px] px-2 py-0.5 rounded-full font-bold uppercase', statusBadge(job.status)]">{{ job.status }}</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">{{ job.description || 'Tidak ada deskripsi.' }}</p>
                                <div class="flex flex-wrap gap-3 mt-3 text-xs">
                                    <span class="font-bold text-amber-600 dark:text-amber-400">{{ formatCurrency(job.estimated_wage) }}</span>
                                    <span class="text-gray-500">Tanggal: {{ formatDate(job.work_date) }}</span>
                                    <span class="text-gray-500">{{ job.assignments?.length || 0 }} pengrajin mengikuti/ditugaskan</span>
                                </div>
                            </div>
                            <div class="flex shrink-0 gap-1">
                                <Button icon="pi pi-pencil" severity="secondary" text size="small" @click="openEditModal(job)" />
                                <Button icon="pi pi-trash" severity="danger" text size="small" @click="deleteJob(job)" />
                            </div>
                        </div>
                    </div>
                    <div v-if="jobs.data.length === 0" class="p-8 text-center text-gray-400">Belum ada pekerjaan pengrajin.</div>
                </div>
            </div>

            <Dialog v-model:visible="isFormOpen" modal :header="editingJob ? 'Edit Pekerjaan' : 'Buat Pekerjaan Pengrajin'" class="w-full max-w-2xl">
                <div v-if="Object.keys(form.errors).length" class="mb-4">
                    <Message severity="error" v-for="(err, key) in form.errors" :key="key" size="small" class="mb-1">{{ err }}</Message>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-1.5 md:col-span-2">
                            <label class="text-xs font-semibold">Nama Pekerjaan <span class="text-red-500">*</span></label>
                            <InputText v-model="form.title" required placeholder="Misal: Produksi 20 tas anyam ukuran M" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Estimasi Upah (Rp)</label>
                            <InputNumber v-model="form.estimated_wage" :min="0" class="w-full" inputClass="w-full" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Tanggal Rencana</label>
                            <input v-model="form.work_date" type="date" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white w-full" />
                        </div>
                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold">Status</label>
                            <Dropdown v-model="form.status" :options="statusOptions" optionLabel="label" optionValue="value" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1.5 md:col-span-2">
                            <label class="text-xs font-semibold">Detail Pekerjaan</label>
                            <Textarea v-model="form.description" rows="3" placeholder="Tulis detail kerja, target, bahan, atau instruksi singkat..." />
                        </div>
                    </div>

                    <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400">Assign Manual Pengrajin</h4>
                            <Button label="Tambah" icon="pi pi-plus" size="small" severity="secondary" outlined @click="addAssignment" />
                        </div>
                        <div class="space-y-3">
                            <div v-for="(assignment, idx) in form.assignments" :key="idx" class="grid grid-cols-1 md:grid-cols-12 gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-800">
                                <Dropdown v-model="assignment.artisan_id" :options="artisans" optionLabel="name" optionValue="id" placeholder="Pengrajin" filter class="md:col-span-4" />
                                <InputNumber v-model="assignment.assigned_wage" :min="0" class="md:col-span-3" inputClass="w-full" />
                                <Dropdown v-model="assignment.status" :options="assignmentStatusOptions" optionLabel="label" optionValue="value" class="md:col-span-3" />
                                <Button icon="pi pi-trash" severity="danger" text rounded class="md:col-span-1" @click="removeAssignment(idx)" />
                            </div>
                            <p v-if="form.assignments.length === 0" class="text-xs text-gray-400">Pengrajin bisa mengikuti pekerjaan dari portalnya, atau admin bisa assign manual di sini.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-4 border-t border-gray-150 dark:border-gray-800">
                        <Button label="Batal" severity="secondary" text @click="isFormOpen = false" />
                        <Button type="submit" label="Simpan" :loading="form.processing" class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold" />
                    </div>
                </form>
            </Dialog>
        </div>
    </AdminLayout>
</template>
