<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/Components/StatusBadge.vue';
import { ArrowLeftIcon, PhotoIcon, CubeIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    product: Object,
    transactions: Array,
});
</script>

<template>
    <Head :title="`Detail Barang - ${product.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center">
                <Link :href="route('products.index')" class="mr-4 text-gray-400 hover:text-gray-500 transition-colors">
                    <ArrowLeftIcon class="h-6 w-6" aria-hidden="true" />
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Detail Barang
                </h2>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Product Details Card -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Photo -->
                        <div class="col-span-1">
                            <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-lg bg-gray-100 border border-gray-200">
                                <img v-if="product.photo" :src="product.photo" :alt="product.name" class="h-full w-full object-cover object-center" />
                                <div v-else class="flex h-full w-full flex-col items-center justify-center text-gray-400 p-12">
                                    <PhotoIcon class="h-16 w-16 mb-2" />
                                    <span class="text-sm">Tidak ada foto</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="col-span-1 md:col-span-2 flex flex-col">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-medium text-cyan-600 mb-1">{{ product.category }}</p>
                                    <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">{{ product.name }}</h1>
                                    <p class="text-sm text-gray-500 mt-1">Kode: <span class="font-mono text-gray-700">{{ product.code }}</span></p>
                                </div>
                                <span :class="[product.current_stock <= 0 ? 'bg-red-100 text-red-700' : (product.current_stock <= 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'), 'inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold']">
                                    Sisa Stok: {{ product.current_stock }}
                                </span>
                            </div>

                            <div class="mt-8 flex-1">
                                <h3 class="text-sm font-medium text-gray-900 border-b border-gray-200 pb-2 mb-4">Informasi Tambahan</h3>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Stok Awal</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.initial_stock }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">Tanggal Ditambahkan</dt>
                                        <dd class="mt-1 text-sm text-gray-900">{{ product.created_at }}</dd>
                                    </div>
                                    <div class="sm:col-span-2">
                                        <dt class="text-sm font-medium text-gray-500">Keterangan</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ product.description || 'Tidak ada keterangan.' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction History -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden mt-6">
                <div class="border-b border-gray-200 px-4 py-4 sm:px-6">
                    <h3 class="text-lg font-medium leading-6 text-gray-900 flex items-center">
                        <CubeIcon class="h-5 w-5 mr-2 text-gray-500" />
                        Riwayat Transaksi Barang Ini
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-16">No</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tanggal</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jenis Transaksi</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Jumlah</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-1/3">Keterangan</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Oleh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="transactions.length === 0">
                                <td colspan="6" class="py-12 text-center text-sm text-gray-500">
                                    Belum ada riwayat transaksi untuk barang ini.
                                </td>
                            </tr>
                            <tr v-for="(txn, index) in transactions" :key="txn.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ index + 1 }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ txn.transaction_date }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <StatusBadge :status="txn.type" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium">
                                    <span :class="txn.type === 'masuk' ? 'text-green-600' : 'text-red-600'">
                                        {{ txn.type === 'masuk' ? '+' : '-' }}{{ txn.quantity }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">{{ txn.notes || '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ txn.user_name }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
