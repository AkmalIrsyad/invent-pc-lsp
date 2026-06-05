<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email" />
                <div class="mt-2">
                    <TextInput
                        id="email"
                        type="email"
                        class="block w-full"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between mt-2">
                    <InputLabel for="password" value="Password" />
                </div>
                <div class="mt-2">
                    <TextInput
                        id="password"
                        type="password"
                        class="block w-full"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <label class="flex items-center">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                </div>

                <div class="text-sm leading-6">
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="font-semibold text-cyan-600 hover:text-cyan-500"
                    >
                        Lupa password?
                    </Link>
                </div>
            </div>

            <div>
                <PrimaryButton
                    class="flex w-full justify-center bg-cyan-600 hover:bg-cyan-500 focus-visible:outline-cyan-600"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Masuk ke Sistem
                </PrimaryButton>
            </div>
            
            <!-- Default credentials helper -->
            <div class="mt-6 rounded-md bg-cyan-50 p-4">
                <div class="flex">
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-cyan-800">Akun Demo:</h3>
                        <div class="mt-2 text-sm text-cyan-700">
                            <ul role="list" class="list-disc space-y-1 pl-5">
                                <li>Admin: <strong>admin@admin.com</strong> / <strong>password</strong></li>
                                <li>Viewer: <strong>viewer@viewer.com</strong> / <strong>password</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
