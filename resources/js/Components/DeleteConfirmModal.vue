<script setup>
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline';

defineProps({
    show: Boolean,
    title: String,
    message: String,
});

defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
                </div>
                <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                    <h3 class="text-base font-semibold leading-6 text-gray-900" id="modal-title">
                        {{ title }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ message }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <DangerButton class="w-full sm:ml-3 sm:w-auto" @click="$emit('confirm')">
                    Hapus
                </DangerButton>
                <SecondaryButton class="mt-3 w-full sm:mt-0 sm:w-auto" @click="$emit('close')">
                    Batal
                </SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
