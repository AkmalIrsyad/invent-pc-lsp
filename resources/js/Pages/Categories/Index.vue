<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DeleteConfirmModal from '@/Components/DeleteConfirmModal.vue';
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    categories: Array,
});

const isAdmin = usePage().props.auth.user.role === 'admin';

// Modal and Form for create/edit
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (category) => {
    isEditing.value = true;
    editingId.value = category.id;
    form.name = category.name;
    form.description = category.description || '';
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        form.reset();
        form.clearErrors();
    }, 300);
};

const submit = () => {
    if (isEditing.value) {
        form.put(route('categories.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('categories.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

// Delete Modal
const isDeleteModalOpen = ref(false);
const deletingId = ref(null);
const deleteForm = useForm({});

const openDeleteModal = (id) => {
    deletingId.value = id;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
    deletingId.value = null;
};

const confirmDelete = () => {
    deleteForm.delete(route('categories.destroy', deletingId.value), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Kategori Barang" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Kategori Barang
                </h2>
                <button
                    v-if="isAdmin"
                    @click="openCreateModal"
                    type="button"
                    class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 transition-colors duration-200"
                >
                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                    Tambah Kategori
                </button>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Table -->
            <div class="bg-white shadow-sm sm:rounded-lg border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-16">No</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-1/4">Nama Kategori</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Keterangan</th>
                                <th scope="col" class="px-3 py-3.5 text-center text-sm font-semibold text-gray-900 w-32">Jml Barang</th>
                                <th v-if="isAdmin" scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-24">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-if="categories.length === 0">
                                <td :colspan="isAdmin ? 5 : 4" class="py-12 text-center text-sm text-gray-500">
                                    Belum ada kategori yang ditambahkan.
                                </td>
                            </tr>
                            <tr v-for="(category, index) in categories" :key="category.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ index + 1 }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">{{ category.name }}</td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <p class="truncate max-w-md">{{ category.description || '-' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-center">
                                    <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20">
                                        {{ category.products_count }}
                                    </span>
                                </td>
                                <td v-if="isAdmin" class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(category)" class="text-cyan-600 hover:text-cyan-900 transition-colors duration-150" title="Edit">
                                            <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button @click="openDeleteModal(category.id)" class="text-red-600 hover:text-red-900 transition-colors duration-150" title="Hapus">
                                            <TrashIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="md">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-lg">
                <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h3>
            </div>
            
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div>
                    <InputLabel for="name" value="Nama Kategori" />
                    <TextInput
                        id="name"
                        type="text"
                        v-model="form.name"
                        class="mt-1 block w-full"
                        required
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="description" value="Keterangan (Opsional)" />
                    <textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm"
                        placeholder="Contoh: Komponen penyimpanan data..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal" type="button">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="bg-cyan-600 hover:bg-cyan-500 focus:bg-cyan-500 active:bg-cyan-700 focus:ring-cyan-500"
                    >
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmModal
            :show="isDeleteModalOpen"
            title="Hapus Kategori"
            message="Apakah Anda yakin ingin menghapus kategori ini? Kategori tidak dapat dihapus jika masih ada barang yang terkait dengan kategori ini."
            @close="closeDeleteModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
