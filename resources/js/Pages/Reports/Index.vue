<script setup>
import { ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import StatCard from '@/Components/StatCard.vue';
import InputLabel from '@/Components/InputLabel.vue';
import {
    ArrowDownTrayIcon,
    ArrowUpTrayIcon,
    DocumentTextIcon,
    ArrowPathIcon,
    DocumentArrowDownIcon,
    TableCellsIcon,
    FunnelIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object,
    transactions: Object,
    filters: Object,
});

const filterType     = ref(props.filters.type      || '');
const filterDateFrom = ref(props.filters.date_from || '');
const filterDateTo   = ref(props.filters.date_to   || '');

const applyFilters = () => {
    router.get(
        route('reports.index'),
        {
            type:      filterType.value,
            date_from: filterDateFrom.value,
            date_to:   filterDateTo.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    filterType.value     = '';
    filterDateFrom.value = '';
    filterDateTo.value   = '';
    router.get(route('reports.index'), {}, { preserveState: true, replace: true });
};

/* ── Export ────────────────────────────────────────────────── */
const exportLoading = ref('');

const buildExportUrl = (format) => {
    const params = new URLSearchParams();
    if (filterType.value)     params.append('type',      filterType.value);
    if (filterDateFrom.value) params.append('date_from', filterDateFrom.value);
    if (filterDateTo.value)   params.append('date_to',   filterDateTo.value);
    const qs = params.toString();
    return route('reports.export', { format }) + (qs ? '?' + qs : '');
};

const doExport = (format) => {
    exportLoading.value = format;
    const url = buildExportUrl(format);
    // Trigger browser download
    const a = document.createElement('a');
    a.href = url;
    a.download = '';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    setTimeout(() => { exportLoading.value = ''; }, 2000);
};
</script>

<template>
    <Head title="Laporan Transaksi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between w-full">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Laporan Transaksi
                </h2>

                <!-- Export Buttons -->
                <div class="flex items-center gap-2">
                    <!-- PDF -->
                    <button
                        @click="doExport('pdf')"
                        :disabled="exportLoading === 'pdf'"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600 transition-colors duration-200 disabled:opacity-60"
                    >
                        <DocumentArrowDownIcon class="h-4 w-4" aria-hidden="true" />
                        {{ exportLoading === 'pdf' ? '...' : 'PDF' }}
                    </button>

                    <!-- CSV -->
                    <button
                        @click="doExport('csv')"
                        :disabled="exportLoading === 'csv'"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 transition-colors duration-200 disabled:opacity-60"
                    >
                        <DocumentTextIcon class="h-4 w-4" aria-hidden="true" />
                        {{ exportLoading === 'csv' ? '...' : 'CSV' }}
                    </button>

                    <!-- XLSX -->
                    <button
                        @click="doExport('xlsx')"
                        :disabled="exportLoading === 'xlsx'"
                        type="button"
                        class="inline-flex items-center gap-1.5 rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 transition-colors duration-200 disabled:opacity-60"
                    >
                        <TableCellsIcon class="h-4 w-4" aria-hidden="true" />
                        {{ exportLoading === 'xlsx' ? '...' : 'Excel' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Grid -->
            <dl class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <StatCard
                    title="Total Transaksi"
                    :value="stats.totalTransactions"
                    :icon="DocumentTextIcon"
                    iconBgColor="bg-cyan-500"
                />
                <StatCard
                    title="Total Barang Masuk"
                    :value="stats.totalMasuk"
                    :icon="ArrowDownTrayIcon"
                    iconBgColor="bg-emerald-500"
                />
                <StatCard
                    title="Total Barang Keluar"
                    :value="stats.totalKeluar"
                    :icon="ArrowUpTrayIcon"
                    iconBgColor="bg-violet-500"
                />
            </dl>

            <!-- Filters -->
            <div class="bg-white p-4 shadow-sm sm:rounded-lg border border-gray-100">
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="w-full sm:w-1/4">
                        <InputLabel for="type" value="Tipe Transaksi" />
                        <select
                            id="type"
                            v-model="filterType"
                            @change="applyFilters"
                            class="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">Semua</option>
                            <option value="masuk">Masuk</option>
                            <option value="keluar">Keluar</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/4">
                        <InputLabel for="date_from" value="Dari Tanggal" />
                        <input
                            type="date"
                            id="date_from"
                            v-model="filterDateFrom"
                            @change="applyFilters"
                            class="mt-1 block w-full rounded-md border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                        />
                    </div>

                    <div class="w-full sm:w-1/4">
                        <InputLabel for="date_to" value="Sampai Tanggal" />
                        <input
                            type="date"
                            id="date_to"
                            v-model="filterDateTo"
                            @change="applyFilters"
                            class="mt-1 block w-full rounded-md border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                        />
                    </div>

                    <div class="w-full sm:w-auto">
                        <button
                            @click="resetFilters"
                            type="button"
                            class="inline-flex w-full justify-center items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 mt-1"
                        >
                            <ArrowPathIcon class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" aria-hidden="true" />
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-16">No</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kode Barang</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-1/4">Nama Barang</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Masuk</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Keluar</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Keterangan</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pengguna</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="8" class="py-12 text-center text-sm text-gray-500">
                                    Tidak ada data laporan yang ditemukan pada periode ini.
                                </td>
                            </tr>
                            <tr
                                v-for="(txn, index) in transactions.data"
                                :key="txn.id"
                                class="hover:bg-gray-50 transition-colors duration-150"
                            >
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                    {{ (transactions.current_page - 1) * transactions.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ txn.transaction_date }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ txn.product_code }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <div class="font-medium text-gray-900">{{ txn.product_name }}</div>
                                    <div class="text-xs text-gray-500 mt-1">{{ txn.category }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-center">
                                    <span v-if="txn.type === 'masuk'" class="text-green-600">+{{ txn.quantity }}</span>
                                    <span v-else class="text-gray-300">-</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-center">
                                    <span v-if="txn.type === 'keluar'" class="text-red-600">-{{ txn.quantity }}</span>
                                    <span v-else class="text-gray-300">-</span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500 max-w-xs truncate">{{ txn.notes || '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ txn.user_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :data="transactions" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
