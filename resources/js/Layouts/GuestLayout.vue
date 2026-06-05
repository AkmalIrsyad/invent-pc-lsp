<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gray-50">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="flex justify-center">
                <Link href="/">
                    <ApplicationLogo class="h-20 w-auto fill-current text-cyan-600" />
                </Link>
            </div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-gray-900">
                Invent-PC
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Sistem Persediaan Barang Komponen Komputer
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white px-4 py-8 shadow sm:rounded-lg sm:px-10 border border-gray-100">
                <slot />
            </div>
        </div>
    </div>
</template>
