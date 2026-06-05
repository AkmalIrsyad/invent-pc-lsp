<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import SearchInput from '@/Components/SearchInput.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { PlusIcon, PhotoIcon, InformationCircleIcon, PencilSquareIcon } from '@heroicons/vue/24/outline';
import debounce from 'lodash/debounce';

const props = defineProps({
    products: Object,
    categories: Array,
    filters: Object,
});

const isAdmin = usePage().props.auth.user.role === 'admin';

const search = ref(props.filters.search || '');
const filterCategory = ref(props.filters.category || '');

const debouncedSearch = debounce((value) => {
    router.get(
        route('products.index'),
        { search: value, category: filterCategory.value },
        { preserveState: true, replace: true }
    );
}, 300);

watch(search, (value) => debouncedSearch(value));

const applyFilters = () => {
    router.get(
        route('products.index'),
        { search: search.value, category: filterCategory.value },
        { preserveState: true, replace: true }
    );
};

// Modal and Form for create/edit
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const photoPreview = ref(null);

const form = useForm({
    code: '',
    name: '',
    category_id: '',
    initial_stock: 0,
    description: '',
    photo: null,
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    photoPreview.value = null;
    form.reset();
    form.clearErrors();
    
    // Auto generate code prefix
    form.code = 'BRG-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    
    isModalOpen.value = true;
};

const openEditModal = (product) => {
    isEditing.value = true;
    editingId.value = product.id;
    photoPreview.value = product.photo;
    
    form.code = product.code;
    form.name = product.name;
    form.category_id = product.category_id;
    form.initial_stock = product.initial_stock;
    form.description = product.description || '';
    form.photo = null; // Don't bind existing photo object to form
    
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
        photoPreview.value = null;
    }, 300);
};

const handlePhotoChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    form.photo = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const submit = () => {
    if (isEditing.value) {
        // We use POST with _method=PUT because multipart/form-data doesn't work well with native PUT in PHP
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(route('products.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('products.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};
</script>

<template>
    <Head title="Daftar Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Daftar Barang
                </h2>
                <button
                    v-if="isAdmin"
                    @click="openCreateModal"
                    type="button"
                    class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 transition-colors duration-200"
                >
                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                    Tambah Barang
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Filters -->
            <div class="bg-white p-4 shadow-sm sm:rounded-lg border border-gray-100 flex flex-col sm:flex-row gap-4">
                <div class="w-full sm:w-1/2 md:w-1/3">
                    <SearchInput v-model="search" placeholder="Cari Kode atau Nama Barang..." />
                </div>
                <div class="w-full sm:w-1/3 md:w-1/4">
                    <select
                        v-model="filterCategory"
                        @change="applyFilters"
                        class="block w-full rounded-md border-0 py-2 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-cyan-600 sm:text-sm sm:leading-6"
                    >
                        <option value="">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Grid View for Products -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-for="product in products.data" :key="product.id" class="flex flex-col overflow-hidden rounded-lg bg-white shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200">
                    <div class="flex-shrink-0 relative group">
                        <img v-if="product.photo" :src="product.photo" :alt="product.name" class="h-48 w-full object-cover" />
                        <div v-else class="h-48 w-full bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                            <PhotoIcon class="h-12 w-12" />
                            <span class="text-sm mt-2">No Photo</span>
                        </div>
                        <!-- Overlay actions -->
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-opacity duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 gap-2">
                            <Link :href="route('products.show', product.id)" class="inline-flex items-center rounded-full bg-white p-2 text-gray-900 shadow-sm hover:bg-gray-100" title="Detail">
                                <InformationCircleIcon class="h-5 w-5" />
                            </Link>
                            <button v-if="isAdmin" @click="openEditModal(product)" class="inline-flex items-center rounded-full bg-cyan-600 p-2 text-white shadow-sm hover:bg-cyan-500" title="Edit">
                                <PencilSquareIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col justify-between p-4 border-t border-gray-100">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-cyan-600">
                                {{ product.category }}
                            </p>
                            <div class="mt-1 flex justify-between items-start">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 line-clamp-1" :title="product.name">{{ product.name }}</h3>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ product.code }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3">
                            <span class="text-sm text-gray-500">Sisa Stok:</span>
                            <span :class="[product.current_stock <= 0 ? 'bg-red-100 text-red-700' : (product.current_stock <= 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700'), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold']">
                                {{ product.current_stock }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="products.data.length === 0" class="text-center py-12 bg-white rounded-lg border border-gray-100 border-dashed">
                <CubeIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                <h3 class="mt-2 text-sm font-semibold text-gray-900">Tidak ada barang</h3>
                <p class="mt-1 text-sm text-gray-500">Belum ada barang yang ditambahkan atau tidak sesuai filter.</p>
                <div class="mt-6" v-if="isAdmin">
                    <button @click="openCreateModal" type="button" class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500">
                        <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                        Tambah Barang Baru
                    </button>
                </div>
            </div>

            <Pagination v-if="products.data.length > 0" :data="products" />
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="2xl">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit Barang' : 'Tambah Barang Baru' }}</h3>
            </div>
            
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="code" value="Kode Barang" />
                            <TextInput
                                id="code"
                                type="text"
                                v-model="form.code"
                                class="mt-1 block w-full bg-gray-50"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.code" />
                        </div>

                        <div>
                            <InputLabel for="name" value="Nama Barang" />
                            <TextInput
                                id="name"
                                type="text"
                                v-model="form.name"
                                class="mt-1 block w-full"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="category_id" value="Kategori" />
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                                required
                            >
                                <option value="" disabled>Pilih Kategori...</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <InputError class="mt-2" :message="form.errors.category_id" />
                        </div>

                        <div>
                            <InputLabel for="initial_stock" value="Stok Awal" />
                            <TextInput
                                id="initial_stock"
                                type="number"
                                v-model="form.initial_stock"
                                min="0"
                                class="mt-1 block w-full"
                                required
                            />
                            <p class="mt-1 text-xs text-gray-500">Hanya diisi saat barang pertama kali didaftarkan.</p>
                            <InputError class="mt-2" :message="form.errors.initial_stock" />
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="photo" value="Foto Barang" />
                            <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 relative" :class="{'bg-gray-50': !photoPreview}">
                                <div v-if="photoPreview" class="absolute inset-0 overflow-hidden rounded-lg">
                                    <img :src="photoPreview" alt="Preview" class="h-full w-full object-cover" />
                                    <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center opacity-0 hover:opacity-100 transition-opacity">
                                        <label for="photo" class="cursor-pointer rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                            Ganti Foto
                                        </label>
                                    </div>
                                </div>
                                <div v-else class="text-center">
                                    <PhotoIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                                    <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                        <label for="photo" class="relative cursor-pointer rounded-md bg-white font-semibold text-cyan-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-cyan-600 focus-within:ring-offset-2 hover:text-cyan-500">
                                            <span>Upload a file</span>
                                            <input id="photo" name="photo" type="file" class="sr-only" @change="handlePhotoChange" accept="image/jpeg,image/png,image/jpg,image/webp" />
                                        </label>
                                    </div>
                                    <p class="text-xs leading-5 text-gray-600">PNG, JPG up to 2MB</p>
                                </div>
                                <input v-if="photoPreview" id="photo" name="photo" type="file" class="sr-only" @change="handlePhotoChange" accept="image/jpeg,image/png,image/jpg,image/webp" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.photo" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Keterangan (Opsional)" />
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-4">
                    <SecondaryButton @click="closeModal" type="button">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="bg-cyan-600 hover:bg-cyan-500 focus:bg-cyan-500 active:bg-cyan-700 focus:ring-cyan-500"
                    >
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Barang' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>
    </AuthenticatedLayout>
</template>
