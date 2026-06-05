<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StatCard from '@/Components/StatCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { CubeIcon, ArchiveBoxIcon, ArrowDownTrayIcon, ArrowUpTrayIcon, ExclamationCircleIcon, CheckBadgeIcon } from '@heroicons/vue/24/outline';
import { Bar, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement);

const props = defineProps({
    stats: Object,
    chartData: Array,
    recentTransactions: Array,
    lowStock: Array,
    highStock: Array,
});

const chartDataConfig = {
    labels: props.chartData.map(item => item.month),
    datasets: [
        {
            label: 'Barang Masuk',
            backgroundColor: '#06b6d4', // cyan-500
            data: props.chartData.map(item => item.masuk),
            borderRadius: 4,
        },
        {
            label: 'Barang Keluar',
            backgroundColor: '#8b5cf6', // violet-500
            data: props.chartData.map(item => item.keluar),
            borderRadius: 4,
        }
    ]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        title: {
            display: false,
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 10
            }
        }
    }
};

const donutDataConfig = {
    labels: ['Barang Masuk', 'Barang Keluar'],
    datasets: [
        {
            backgroundColor: ['#10b981', '#8b5cf6'], // emerald-500, violet-500
            data: [props.stats.barangMasuk, props.stats.barangKeluar],
            borderWidth: 0,
            hoverOffset: 4
        }
    ]
};

const donutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
        },
        title: {
            display: false,
        }
    },
    cutout: '70%',
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            Dashboard
        </template>

        <!-- Stats Grid -->
        <dl class="mt-2 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                title="Total Barang"
                :value="stats.totalProducts"
                :icon="CubeIcon"
                iconBgColor="bg-cyan-500"
            />
            <StatCard
                title="Total Stok Keseluruhan"
                :value="stats.totalStock"
                :icon="ArchiveBoxIcon"
                iconBgColor="bg-blue-500"
            />
            <StatCard
                title="Barang Masuk (Bulan Ini)"
                :value="stats.barangMasuk"
                :icon="ArrowDownTrayIcon"
                iconBgColor="bg-emerald-500"
            />
            <StatCard
                title="Barang Keluar (Bulan Ini)"
                :value="stats.barangKeluar"
                :icon="ArrowUpTrayIcon"
                iconBgColor="bg-violet-500"
            />
        </dl>

        <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-3">
            <!-- Chart Section -->
            <div class="lg:col-span-2">
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 p-6 h-full min-h-[400px]">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Grafik Transaksi (6 Bulan Terakhir)</h3>
                    <div class="relative h-[300px] w-full">
                        <Bar :data="chartDataConfig" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Donut Chart -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Rasio Transaksi (Bulan Ini)</h3>
                    <div class="relative h-[250px] w-full flex justify-center items-center">
                        <Doughnut v-if="stats.barangMasuk > 0 || stats.barangKeluar > 0" :data="donutDataConfig" :options="donutOptions" />
                        <div v-else class="text-sm text-gray-500 flex items-center justify-center h-full">Belum ada transaksi bulan ini</div>
                    </div>
                </div>

                <!-- Low Stock -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-200 bg-red-50/50 px-4 py-4 sm:px-6 flex items-center justify-between">
                        <h3 class="text-base font-semibold leading-6 text-red-900 flex items-center">
                            <ExclamationCircleIcon class="h-5 w-5 mr-2 text-red-500" />
                            Stok Hampir Habis
                        </h3>
                    </div>
                    <ul role="list" class="divide-y divide-gray-100">
                        <li v-if="lowStock.length === 0" class="px-4 py-4 sm:px-6 text-sm text-gray-500 text-center">
                            Semua stok aman.
                        </li>
                        <li v-for="product in lowStock" :key="product.id" class="flex items-center justify-between gap-x-6 py-4 px-4 sm:px-6 hover:bg-gray-50">
                            <div class="min-w-0">
                                <div class="flex items-start gap-x-3">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ product.name }}</p>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                    <p class="truncate">{{ product.code }}</p>
                                    <svg viewBox="0 0 2 2" class="h-0.5 w-0.5 fill-current"><circle cx="1" cy="1" r="1" /></svg>
                                    <p class="whitespace-nowrap">{{ product.category }}</p>
                                </div>
                            </div>
                            <div class="flex flex-none items-center gap-x-4">
                                <span :class="[product.current_stock <= 0 ? 'bg-red-100 text-red-700 ring-red-600/20' : 'bg-yellow-100 text-yellow-700 ring-yellow-600/20', 'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset']">
                                    Sisa: {{ product.current_stock }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- High Stock -->
                <div class="rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden">
                    <div class="border-b border-gray-200 bg-green-50/50 px-4 py-4 sm:px-6 flex items-center justify-between">
                        <h3 class="text-base font-semibold leading-6 text-green-900 flex items-center">
                            <CheckBadgeIcon class="h-5 w-5 mr-2 text-green-500" />
                            Stok Terbanyak
                        </h3>
                    </div>
                    <ul role="list" class="divide-y divide-gray-100">
                        <li v-if="highStock.length === 0" class="px-4 py-4 sm:px-6 text-sm text-gray-500 text-center">
                            Belum ada data barang.
                        </li>
                        <li v-for="product in highStock" :key="product.id" class="flex items-center justify-between gap-x-6 py-4 px-4 sm:px-6 hover:bg-gray-50">
                            <div class="min-w-0">
                                <div class="flex items-start gap-x-3">
                                    <p class="text-sm font-semibold leading-6 text-gray-900">{{ product.name }}</p>
                                </div>
                                <div class="mt-1 flex items-center gap-x-2 text-xs leading-5 text-gray-500">
                                    <p class="truncate">{{ product.code }}</p>
                                </div>
                            </div>
                            <div class="flex flex-none items-center gap-x-4">
                                <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                    Sisa: {{ product.current_stock }}
                                </span>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="mt-8 rounded-xl bg-white shadow-sm border border-gray-100 overflow-hidden">
            <div class="border-b border-gray-200 px-4 py-4 sm:px-6 flex items-center justify-between">
                <h3 class="text-base font-semibold leading-6 text-gray-900">Transaksi Terbaru</h3>
                <Link :href="route('inventory.index')" class="text-sm font-medium text-cyan-600 hover:text-cyan-500">
                    Lihat semua <span aria-hidden="true">&rarr;</span>
                </Link>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tanggal</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Barang</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tipe</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pengguna</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-if="recentTransactions.length === 0">
                            <td colspan="5" class="py-8 text-center text-sm text-gray-500">Belum ada transaksi.</td>
                        </tr>
                        <tr v-for="txn in recentTransactions" :key="txn.id" class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ txn.transaction_date }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ txn.product_name }}</div>
                                <div class="text-gray-500">{{ txn.product_code }}</div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <StatusBadge :status="txn.type" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                <span :class="txn.type === 'masuk' ? 'text-green-600' : 'text-red-600'">
                                    {{ txn.type === 'masuk' ? '+' : '-' }}{{ txn.quantity }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ txn.user_name }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
