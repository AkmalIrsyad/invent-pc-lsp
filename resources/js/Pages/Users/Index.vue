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
    users: Array,
});

const currentUserId = usePage().props.auth.user.id;

// Modal and Form for create/edit
const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);

const form = useForm({
    name: '',
    email: '',
    role: 'viewer',
    password: '',
    password_confirmation: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    isEditing.value = true;
    editingId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.password = '';
    form.password_confirmation = '';
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
        form.put(route('users.update', editingId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('users.store'), {
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
    deleteForm.delete(route('users.destroy', deletingId.value), {
        preserveScroll: true,
        onSuccess: () => closeDeleteModal(),
    });
};
</script>

<template>
    <Head title="Manajemen Pengguna" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Manajemen Pengguna
                </h2>
                <button
                    @click="openCreateModal"
                    type="button"
                    class="inline-flex items-center rounded-md bg-cyan-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-cyan-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600 transition-colors duration-200"
                >
                    <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                    Tambah Pengguna
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
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Nama Pengguna</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Role</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tanggal Dibuat</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 w-24">
                                    <span class="sr-only">Aksi</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(user, index) in users" :key="user.id" class="hover:bg-gray-50 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">{{ index + 1 }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                    {{ user.name }}
                                    <span v-if="user.id === currentUserId" class="ml-2 inline-flex items-center rounded-full bg-cyan-50 px-2 py-0.5 text-xs font-medium text-cyan-700 ring-1 ring-inset ring-cyan-600/20">Anda</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ user.email }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    <span :class="[user.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-700', 'inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-medium capitalize']">
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ user.created_at }}</td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex justify-end gap-2">
                                        <button @click="openEditModal(user)" class="text-cyan-600 hover:text-cyan-900 transition-colors duration-150" title="Edit">
                                            <PencilSquareIcon class="h-5 w-5" aria-hidden="true" />
                                        </button>
                                        <button v-if="user.id !== currentUserId" @click="openDeleteModal(user.id)" class="text-red-600 hover:text-red-900 transition-colors duration-150" title="Hapus">
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
                <h3 class="text-lg font-medium text-gray-900">{{ isEditing ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h3>
            </div>
            
            <form @submit.prevent="submit" class="p-6 space-y-6">
                <div>
                    <InputLabel for="name" value="Nama Lengkap" />
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
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        v-model="form.email"
                        class="mt-1 block w-full"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div>
                    <InputLabel for="role" value="Role Pengguna" />
                    <select
                        id="role"
                        v-model="form.role"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-cyan-500 focus:ring-cyan-500 sm:text-sm capitalize"
                        required
                    >
                        <option value="admin">Admin</option>
                        <option value="viewer">Viewer (Hanya Lihat)</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <p class="text-sm text-gray-500 mb-4" v-if="isEditing">Kosongkan password jika tidak ingin mengubahnya.</p>
                    <div class="space-y-6">
                        <div>
                            <InputLabel for="password" value="Password" />
                            <TextInput
                                id="password"
                                type="password"
                                v-model="form.password"
                                class="mt-1 block w-full"
                                :required="!isEditing"
                            />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" value="Konfirmasi Password" />
                            <TextInput
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                class="mt-1 block w-full"
                                :required="!isEditing"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-6">
                    <SecondaryButton @click="closeModal" type="button">
                        Batal
                    </SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        class="bg-cyan-600 hover:bg-cyan-500 focus:bg-cyan-500 active:bg-cyan-700 focus:ring-cyan-500"
                    >
                        {{ isEditing ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
                    </PrimaryButton>
                </div>
            </form>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <DeleteConfirmModal
            :show="isDeleteModalOpen"
            title="Hapus Pengguna"
            message="Apakah Anda yakin ingin menghapus pengguna ini? Tindakan ini tidak dapat dibatalkan."
            @close="closeDeleteModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
