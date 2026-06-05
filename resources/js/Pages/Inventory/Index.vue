<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { PlusIcon, FunnelIcon, ArrowPathIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    transactions: Object,
    categories: Array,
    products: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const filterCategory = ref(props.filters.category || '');
const filterType = ref(props.filters.type || '');
const filterDateFrom = ref(props.filters.date_from || '');
const filterDateTo = ref(props.filters.date_to || '');

// Debounced search to avoid too many requests
const debouncedSearch = debounce((value) => {
    router.get(
        route('inventory.index'),
        { 
            search: value, 
            category: filterCategory.value,
            type: filterType.value,
            date_from: filterDateFrom.value,
            date_to: filterDateTo.value,
        },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => debouncedSearch(value));

const applyFilters = () => {
    router.get(
        route('inventory.index'),
        { 
            search: search.value, 
            category: filterCategory.value,
            type: filterType.value,
            date_from: filterDateFrom.value,
            date_to: filterDateTo.value,
        },
        { preserveState: true, replace: true }
    );
};

const resetFilters = () => {
    search.value = '';
    filterCategory.value = '';
    filterType.value = '';
    filterDateFrom.value = '';
    filterDateTo.value = '';
    router.get(route('inventory.index'), {}, { preserveState: true, replace: true });
};

// Modal and Form for new transaction
const isModalOpen = ref(false);
const selectedProductStock = ref(0);

const form = useForm({
    product_id: '',
    type: 'masuk',
    quantity: 1,
    transaction_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    selectedProductStock.value = 0;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const updateStockPreview = () => {
    if (form.product_id) {
        const product = props.products.find(p => p.id === parseInt(form.product_id));
        if (product) {
            selectedProductStock.value = product.current_stock;
        }
    } else {
        selectedProductStock.value = 0;
    }
};

const submit = () => {
    form.post(route('inventory.store'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const isAdmin = usePage().props.auth.user.role === 'admin';
</script>

<template>
    <Head title="Persediaan Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Persediaan Barang
                </h2>
                <button
                    v-if="isAdmin"
                    @click="openModal"
                    type="button"
                    class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 transition-colors duration-200"
                >
                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                    Tambah Transaksi
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-white p-4 shadow-sm sm:rounded-lg border border-gray-100">
                <div class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="w-full sm:w-1/3">
                        <InputLabel for="search" value="Cari Barang" />
                        <div class="mt-1">
                            <SearchInput v-model="search" placeholder="Kode atau Nama Barang..." />
                        </div>
                    </div>
                    
                    <div class="w-full sm:w-1/6">
                        <InputLabel for="category" value="Kategori" />
                        <select
                            id="category"
                            v-model="filterCategory"
                            @change="applyFilters"
                            class="mt-1 block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                        >
                            <option value="">Semua</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div class="w-full sm:w-1/6">
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

                    <div class="w-full sm:w-1/6">
                        <InputLabel for="date_from" value="Dari Tanggal" />
                        <input
                            type="date"
                            id="date_from"
                            v-model="filterDateFrom"
                            @change="applyFilters"
                            class="mt-1 block w-full rounded-md border-0 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                        />
                    </div>

                    <div class="w-full sm:w-1/6">
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kategori</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Masuk</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Keluar</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Stok Akhir</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="transactions.data.length === 0">
                                <td colspan="9" class="py-12 text-center text-sm text-gray-500">
                                    Tidak ada data persediaan yang ditemukan.
                                </td>
                            </tr>
                            <tr v-for="(txn, index) in transactions.data" :key="txn.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                    {{ (transactions.current_page - 1) * transactions.per_page + index + 1 }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ txn.transaction_date_formatted }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ txn.product_code }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <div class="font-medium text-gray-900">{{ txn.product_name }}</div>
                                    <div class="text-xs text-gray-400 mt-1" v-if="txn.notes">{{ txn.notes }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ txn.category }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-center">
                                    <span v-if="txn.type === 'masuk'" class="text-green-600">+{{ txn.quantity }}</span>
                                    <span v-else class="text-gray-300">-</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-center">
                                    <span v-if="txn.type === 'keluar'" class="text-red-600">-{{ txn.quantity }}</span>
                                    <span v-else class="text-gray-300">-</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900 text-center">{{ txn.current_stock }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                    <StatusBadge :status="txn.status" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <Pagination :data="transactions" />
            </div>
        </div>

        <!-- Add Transaction Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                <h3 class="text-lg font-medium text-gray-900">Tambah Transaksi Baru</h3>
            </div>
            
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div>
                    <InputLabel for="product_id" value="Barang" />
                    <select
                        id="product_id"
                        v-model="form.product_id"
                        @change="updateStockPreview"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                        required
                    >
                        <option value="" disabled>Pilih Barang...</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">
                            {{ product.code }} - {{ product.name }} (Stok: {{ product.current_stock }})
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.product_id" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="form_type" value="Jenis Transaksi" />
                        <select
                            id="form_type"
                            v-model="form.type"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                            required
                        >
                            <option value="masuk">Barang Masuk</option>
                            <option value="keluar">Barang Keluar</option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>
                    
                    <div>
                        <InputLabel for="quantity" value="Jumlah" />
                        <div class="relative mt-1 rounded-md shadow-sm">
                            <TextInput
                                id="quantity"
                                type="number"
                                v-model="form.quantity"
                                min="1"
                                class="block w-full"
                                required
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.quantity" />
                        <p v-if="form.product_id && form.type === 'keluar'" class="mt-1 text-xs text-gray-500">
                            Maksimal: {{ selectedProductStock }}
                        </p>
                    </div>
                </div>

                <div>
                    <InputLabel for="transaction_date" value="Tanggal Transaksi" />
                    <TextInput
                        id="transaction_date"
                        type="date"
                        v-model="form.transaction_date"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.transaction_date" />
                </div>

                <div>
                    <InputLabel for="notes" value="Keterangan (Opsional)" />
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                        placeholder="Contoh: Restok dari supplier A, atau Digunakan untuk perakitan PC B..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" type="button">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing || (form.type === 'keluar' && form.quantity > selectedProductStock)"
                        class="bg-cyan-600 hover:bg-cyan-500 focus:bg-cyan-500 active:bg-cyan-700 focus:ring-cyan-500"
                    >
                        Simpan Transaksi
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
