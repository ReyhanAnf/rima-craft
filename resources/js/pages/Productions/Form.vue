<script setup>
import { ref, computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AdminLayout from "@/layouts/AdminLayout.vue";
import Card from "primevue/card";
import Button from "primevue/button";
import InputText from "primevue/inputtext";
import InputNumber from "primevue/inputnumber";
import Dropdown from "primevue/dropdown";
import Message from "primevue/message";
import Toast from "primevue/toast";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    materials: Array,
    products: Array,
    artisans: Array,
});

const toast = useToast();

const form = useForm({
    date: new Date().toISOString().split("T")[0],
    labor_cost: 0,
    overhead_cost: 0,
    additional_cost: 0,
    notes: "",
    artisan_wages: [],
    materials: [{ material_id: null, qty: 1 }],
    products: [{ product_id: null, qty: 1 }],
});

const addMaterial = () => {
    form.materials.push({ material_id: null, qty: 1 });
};

const removeMaterial = (idx) => {
    if (form.materials.length > 1) {
        form.materials.splice(idx, 1);
    }
};

const addProduct = () => {
    form.products.push({ product_id: null, qty: 1 });
};

const removeProduct = (idx) => {
    if (form.products.length > 1) {
        form.products.splice(idx, 1);
    }
};

const addArtisanWage = () => {
    form.artisan_wages.push({
        artisan_id: null,
        amount: 0,
        work_description: "",
        notes: "",
    });
};

const removeArtisanWage = (idx) => {
    form.artisan_wages.splice(idx, 1);
};

const getMaterialUnit = (materialId) => {
    const mat = props.materials.find((m) => m.id === materialId);
    return mat ? mat.unit : "";
};

const getMaterialStock = (materialId) => {
    const mat = props.materials.find((m) => m.id === materialId);
    return mat ? mat.current_stock : 0;
};

// Estimasi HPP Bahan
const totalMaterialCost = computed(() => {
    let cost = form.materials.reduce((sum, item) => {
        const mat = props.materials.find((m) => m.id === item.material_id);
        const price = mat ? Number(mat.last_buy_price) || 0 : 0;
        return sum + price * Number(item.qty || 0);
    }, 0);
    return cost;
});

const totalArtisanWages = computed(() => {
    return form.artisan_wages.reduce((sum, item) => sum + Number(item.amount || 0), 0);
});

const grandTotalCost = computed(() => {
    return totalMaterialCost.value +
        Number(form.labor_cost || 0) +
        totalArtisanWages.value +
        Number(form.overhead_cost || 0) +
        Number(form.additional_cost || 0);
});

const submitForm = () => {
    form.clearErrors();
    form.post(route("productions.store"));
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val || 0);
};
</script>

<template>
    <AdminLayout>
        <Head title="Proses Produksi Baru" />
        <Toast />

        <div class="max-w-5xl mx-auto pb-24 space-y-6">
            <div class="flex items-center gap-4">
                <Link :href="route('productions.index')">
                    <Button
                        icon="pi pi-arrow-left"
                        severity="secondary"
                        rounded
                        outlined
                    />
                </Link>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Proses Produksi Baru
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Bahan baku akan dipotong dan produk jadi akan
                        ditambahkan setelah form ini disimpan.
                    </p>
                </div>
            </div>

            <!-- Validation Errors -->
            <div v-if="Object.keys(form.errors).length > 0" class="mb-4">
                <Message
                    severity="error"
                    v-for="(err, key) in form.errors"
                    :key="key"
                    size="small"
                    class="mb-1"
                >
                    {{ err }}
                </Message>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <!-- Info Umum -->
                <div
                    class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm"
                >
                    <h3 class="text-sm font-bold uppercase tracking-wider text-gray-400 mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">Informasi Umum & Biaya</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                        <div class="flex flex-col gap-1.5 lg:col-span-2">
                            <label class="text-xs font-semibold"
                                >Tanggal Produksi (Selesai)
                                <span class="text-red-500">*</span></label
                            >
                            <input
                                type="date"
                                v-model="form.date"
                                required
                                class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 text-gray-900 dark:text-white w-full"
                            />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold"
                                >Upah Umum Non-Pengrajin (Rp)</label
                            >
                            <InputNumber
                                v-model="form.labor_cost"
                                :min="0"
                                class="w-full"
                            />
                        </div>

                        <div class="flex flex-col gap-1.5">
                            <label class="text-xs font-semibold"
                                >Biaya Overhead / Listrik (Rp)</label
                            >
                            <InputNumber
                                v-model="form.overhead_cost"
                                :min="0"
                                class="w-full"
                            />
                        </div>

                        <div class="flex flex-col gap-1.5 lg:col-span-2">
                            <label class="text-xs font-semibold"
                                >Biaya Lain-lain / Tambahan (Rp)</label
                            >
                            <InputNumber
                                v-model="form.additional_cost"
                                :min="0"
                                class="w-full"
                            />
                        </div>

                        <div class="lg:col-span-2 flex flex-col gap-1.5">
                            <label class="text-xs font-semibold"
                                >Catatan (Opsional)</label
                            >
                            <InputText
                                v-model="form.notes"
                                placeholder="Misal: Batch produksi keranjang rotan sesi pagi"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <!-- Upah Pengrajin -->
                <div class="border border-amber-200 dark:border-amber-500/20 bg-amber-50/40 dark:bg-amber-500/5 p-6 rounded-xl shadow-sm">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-amber-100 dark:border-amber-500/10 pb-4 mb-4">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Tambahkan Upah Pengrajin</h3>
                            <p class="text-xs text-amber-700/70 dark:text-amber-300/70 mt-1">Opsional. Rincian ini dipakai untuk melihat total upah per pengrajin.</p>
                        </div>
                        <Button
                            label="Tambah Pengrajin"
                            icon="pi pi-plus"
                            size="small"
                            outlined
                            class="!border-amber-500 !text-amber-700 dark:!text-amber-300"
                            @click="addArtisanWage"
                        />
                    </div>

                    <div v-if="form.artisan_wages.length" class="space-y-3">
                        <div
                            v-for="(item, idx) in form.artisan_wages"
                            :key="idx"
                            class="bg-white dark:bg-gray-900 border border-amber-100 dark:border-amber-500/10 rounded-xl p-4 space-y-3"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Upah #{{ idx + 1 }}</span>
                                <Button icon="pi pi-trash" severity="danger" text rounded size="small" @click="removeArtisanWage(idx)" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pengrajin</label>
                                    <Dropdown
                                        v-model="item.artisan_id"
                                        :options="artisans"
                                        optionLabel="name"
                                        optionValue="id"
                                        placeholder="Pilih pengrajin..."
                                        filter
                                        class="w-full"
                                    />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Upah (Rp)</label>
                                    <InputNumber v-model="item.amount" :min="0" class="w-full" inputClass="w-full" />
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Pekerjaan</label>
                                    <InputText v-model="item.work_description" placeholder="Misal: Anyam badan tas" class="w-full" />
                                </div>
                            </div>
                            <InputText v-model="item.notes" placeholder="Catatan opsional untuk upah ini" class="w-full" />
                        </div>
                    </div>

                    <div v-else class="rounded-xl border border-dashed border-amber-200 dark:border-amber-500/20 p-5 text-center text-xs text-amber-700/70 dark:text-amber-300/70">
                        Belum ada upah pengrajin. Klik tombol tambah jika produksi ini melibatkan pengrajin tertentu.
                    </div>

                    <div class="mt-4 flex justify-between items-center text-sm font-bold">
                        <span class="text-gray-700 dark:text-gray-300">Total Upah Pengrajin:</span>
                        <span class="text-amber-600 dark:text-amber-300">{{ formatCurrency(totalArtisanWages) }}</span>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Bahan Baku Dipakai -->
                    <div
                        class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm min-w-0"
                    >
                        <div
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-3 mb-4"
                        >
                            <span
                                class="text-sm font-bold uppercase text-red-500 tracking-wider"
                                >1. Bahan Baku Dipakai (-)</span
                            >
                            <Button
                                label="Tambah"
                                icon="pi pi-plus"
                                size="small"
                                severity="danger"
                                outlined
                                @click="addMaterial"
                            />
                        </div>
                        <div class="space-y-4 pt-2">
                            <div
                                v-for="(item, idx) in form.materials"
                                :key="idx"
                                class="bg-gray-50/50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200 dark:border-gray-800 space-y-3 min-w-0"
                            >
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Bahan #{{ idx + 1 }}</span>
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        rounded
                                        size="small"
                                        @click="removeMaterial(idx)"
                                        :disabled="form.materials.length === 1"
                                        class="hover:bg-red-50 dark:hover:bg-red-950/20"
                                    />
                                </div>
                                <div class="grid grid-cols-3 gap-3 min-w-0">
                                    <div class="col-span-2 flex flex-col gap-1.5 min-w-0">
                                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pilih Bahan (Stok)</label>
                                        <Dropdown
                                            v-model="item.material_id"
                                            :options="materials"
                                            optionLabel="name"
                                            optionValue="id"
                                            placeholder="Pilih Bahan..."
                                            filter
                                            class="w-full min-w-0"
                                            required
                                        >
                                            <template #option="slotProps">
                                                <div class="flex justify-between items-center w-full min-w-0">
                                                    <span class="truncate mr-2">{{ slotProps.option.name }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold shrink-0">{{ slotProps.option.current_stock }} {{ slotProps.option.unit }}</span>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>
                                    <div class="col-span-1 flex flex-col gap-1.5 min-w-0">
                                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Qty</label>
                                        <InputNumber v-model="item.qty" :min="0.01" :maxFractionDigits="2" required class="w-full min-w-0" inputClass="w-full min-w-0" />
                                    </div>
                                </div>
                            </div>

                            <div
                                class="border-t border-gray-150 dark:border-gray-800 pt-3 mt-4 space-y-2 text-xs text-gray-500"
                            >
                                <div class="flex justify-between items-center">
                                    <span>Estimasi HPP Bahan:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(totalMaterialCost) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span>Upah Pengrajin:</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">
                                        {{ formatCurrency(totalArtisanWages) }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center border-t border-gray-100 dark:border-gray-800 pt-2 font-bold text-sm">
                                    <span class="text-gray-700 dark:text-gray-300">Estimasi Total Biaya:</span>
                                    <span class="text-amber-500">
                                        {{ formatCurrency(grandTotalCost) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Produk Dihasilkan -->
                    <div
                        class="border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 rounded-xl shadow-sm min-w-0"
                    >
                        <div
                            class="flex justify-between items-center border-b border-gray-100 dark:border-gray-855 pb-3 mb-4"
                        >
                            <span
                                class="text-sm font-bold uppercase text-emerald-500 tracking-wider"
                                >2. Produk Dihasilkan (+)</span
                            >
                            <Button
                                label="Tambah"
                                icon="pi pi-plus"
                                size="small"
                                severity="success"
                                outlined
                                @click="addProduct"
                            />
                        </div>
                        <div class="space-y-4 pt-2">
                            <div
                                v-for="(item, idx) in form.products"
                                :key="idx"
                                class="bg-gray-50/50 dark:bg-gray-800/40 p-4 rounded-xl border border-gray-200 dark:border-gray-800 space-y-3 min-w-0"
                            >
                                <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Produk #{{ idx + 1 }}</span>
                                    <Button
                                        icon="pi pi-trash"
                                        severity="danger"
                                        text
                                        rounded
                                        size="small"
                                        @click="removeProduct(idx)"
                                        :disabled="form.products.length === 1"
                                        class="hover:bg-red-50 dark:hover:bg-red-950/20"
                                    />
                                </div>
                                <div class="grid grid-cols-3 gap-3 min-w-0">
                                    <div class="col-span-2 flex flex-col gap-1.5 min-w-0">
                                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Pilih Produk</label>
                                        <Dropdown
                                            v-model="item.product_id"
                                            :options="products"
                                            optionLabel="name"
                                            optionValue="id"
                                            placeholder="Pilih Produk..."
                                            filter
                                            class="w-full min-w-0"
                                            required
                                        >
                                            <template #option="slotProps">
                                                <div class="flex justify-between items-center w-full min-w-0">
                                                    <span class="truncate mr-2">{{ slotProps.option.name }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500 font-semibold shrink-0">Stok: {{ slotProps.option.current_stock }}</span>
                                                </div>
                                            </template>
                                        </Dropdown>
                                    </div>
                                    <div class="col-span-1 flex flex-col gap-1.5 min-w-0">
                                        <label class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Qty</label>
                                        <InputNumber v-model="item.qty" :min="1" required class="w-full min-w-0" inputClass="w-full min-w-0" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer Buttons -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('productions.index')">
                        <Button label="Batal" severity="secondary" text />
                    </Link>
                    <Button
                        type="submit"
                        label="Catat Produksi"
                        icon="pi pi-save"
                        :loading="form.processing"
                        class="!bg-amber-500 hover:!bg-amber-600 !border-amber-500 hover:!border-amber-600 !text-gray-950 font-bold"
                    />
                </div>
            </form>
        </div>
    </AdminLayout>
</template>
