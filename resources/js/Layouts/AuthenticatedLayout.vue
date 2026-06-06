<script setup>
import { ref, computed, watch } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import {
    HomeIcon,
    ArchiveBoxIcon,
    ChartBarIcon,
    Bars3Icon,
    XMarkIcon,
    UserGroupIcon,
    TagIcon,
    CubeIcon,
    FolderIcon,
    CheckCircleIcon,
    XCircleIcon,
} from '@heroicons/vue/24/outline';

const showingSidebar = ref(false);

const navigation = computed(() => {
    const nav = [
        { name: 'Dashboard', href: route('dashboard'), icon: HomeIcon, current: route().current('dashboard') },
        { name: 'Persediaan', href: route('inventory.index'), icon: ArchiveBoxIcon, current: route().current('inventory.*') },
        {
            name: 'Master Data',
            icon: FolderIcon,
            current: route().current('categories.*') || route().current('products.*') || route().current('users.*'),
            children: [
                { name: 'Kategori Barang', href: route('categories.index'), icon: TagIcon, current: route().current('categories.*') },
                { name: 'Daftar Barang', href: route('products.index'), icon: CubeIcon, current: route().current('products.*') },
            ],
        },
        { name: 'Laporan', href: route('reports.index'), icon: ChartBarIcon, current: route().current('reports.*') },
    ];

    if (usePage().props.auth.user.role === 'admin') {
        nav[2].children.push({ name: 'Pengguna', href: route('users.index'), icon: UserGroupIcon, current: route().current('users.*') });
    }

    return nav;
});

const flash = computed(() => usePage().props.flash);
const showFlash = ref(false);

watch(flash, () => {
    if (flash.value.success || flash.value.error) {
        showFlash.value = true;
        setTimeout(() => {
            showFlash.value = false;
        }, 3000);
    }
}, { deep: true });

</script>

<template>
    <div>
        <!-- Flash Message -->
        <Transition
            enter-active-class="transform ease-out duration-300 transition"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="showFlash && (flash.success || flash.error)" class="fixed top-4 right-4 z-50 w-full max-w-sm overflow-hidden rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <CheckCircleIcon v-if="flash.success" class="h-6 w-6 text-green-400" aria-hidden="true" />
                            <XCircleIcon v-else class="h-6 w-6 text-red-400" aria-hidden="true" />
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="text-sm font-medium text-gray-900">{{ flash.success ? 'Berhasil' : 'Gagal' }}</p>
                            <p class="mt-1 text-sm text-gray-500">{{ flash.success || flash.error }}</p>
                        </div>
                        <div class="ml-4 flex flex-shrink-0">
                            <button type="button" @click="showFlash = false" class="inline-flex rounded-md bg-white text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                                <span class="sr-only">Close</span>
                                <XMarkIcon class="h-5 w-5" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <div class="min-h-screen bg-gray-50">
            <!-- Mobile sidebar -->
            <div v-show="showingSidebar" class="relative z-40 md:hidden" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-600 bg-opacity-75"></div>
                <div class="fixed inset-0 z-40 flex">
                    <div class="relative flex w-full max-w-xs flex-1 flex-col bg-gray-900 pb-4 pt-5">
                        <div class="absolute right-0 top-0 -mr-12 pt-2">
                            <button type="button" @click="showingSidebar = false" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white">
                                <span class="sr-only">Close sidebar</span>
                                <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
                            </button>
                        </div>
                        <div class="flex flex-shrink-0 items-center px-4">
                            <ApplicationLogo class="h-8 w-auto text-cyan-500 fill-current" />
                            <span class="ml-3 text-xl font-bold text-white tracking-wider">Invent-PC</span>
                        </div>
                        <div class="mt-5 h-0 flex-1 overflow-y-auto">
                            <nav class="space-y-1 px-2">
                                <template v-for="item in navigation" :key="item.name">
                                    <div v-if="!item.children">
                                        <Link :href="item.href" :class="[item.current ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                            <component :is="item.icon" :class="[item.current ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300', 'mr-4 flex-shrink-0 h-6 w-6']" aria-hidden="true" />
                                            {{ item.name }}
                                        </Link>
                                    </div>
                                    <div v-else class="space-y-1">
                                        <div :class="[item.current ? 'bg-gray-800 text-white' : 'text-gray-300', 'group flex items-center px-2 py-2 text-base font-medium rounded-md']">
                                            <component :is="item.icon" :class="[item.current ? 'text-gray-300' : 'text-gray-400', 'mr-4 flex-shrink-0 h-6 w-6']" aria-hidden="true" />
                                            {{ item.name }}
                                        </div>
                                        <div class="pl-10 space-y-1">
                                            <Link v-for="subItem in item.children" :key="subItem.name" :href="subItem.href" :class="[subItem.current ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white', 'group flex items-center px-2 py-2 text-sm font-medium rounded-md']">
                                                <component :is="subItem.icon" :class="[subItem.current ? 'text-gray-300' : 'text-gray-400 group-hover:text-gray-300', 'mr-3 flex-shrink-0 h-5 w-5']" aria-hidden="true" />
                                                {{ subItem.name }}
                                            </Link>
                                        </div>
                                    </div>
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Static sidebar for desktop -->
            <div class="hidden md:fixed md:inset-y-0 md:flex md:w-64 md:flex-col">
                <div class="flex min-h-0 flex-1 flex-col bg-gray-900">
                    <div class="flex h-16 flex-shrink-0 items-center bg-gray-900 px-4 shadow-sm border-b border-gray-800">
                        <ApplicationLogo class="h-8 w-auto text-cyan-500 fill-current" />
                        <span class="ml-3 text-xl font-bold text-white tracking-wider">Invent-PC</span>
                    </div>
                    <div class="flex flex-1 flex-col overflow-y-auto">
                        <nav class="flex-1 space-y-1 px-2 py-4">
                            <template v-for="item in navigation" :key="item.name">
                                <div v-if="!item.children">
                                    <Link :href="item.href" :class="[item.current ? 'bg-cyan-900 text-white border-l-4 border-cyan-500 shadow-sm' : 'text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent transition-colors duration-150', 'group flex items-center px-2 py-2 text-sm font-medium']">
                                        <component :is="item.icon" :class="[item.current ? 'text-cyan-400' : 'text-gray-400 group-hover:text-gray-300', 'mr-3 flex-shrink-0 h-5 w-5 transition-colors duration-150']" aria-hidden="true" />
                                        {{ item.name }}
                                    </Link>
                                </div>
                                <div v-else class="space-y-1 mt-2">
                                    <div :class="[item.current ? 'text-white' : 'text-gray-400', 'group flex items-center px-3 py-2 text-xs font-semibold uppercase tracking-wider']">
                                        {{ item.name }}
                                    </div>
                                    <div class="space-y-1">
                                        <Link v-for="subItem in item.children" :key="subItem.name" :href="subItem.href" :class="[subItem.current ? 'bg-cyan-900 text-white border-l-4 border-cyan-500 shadow-sm' : 'text-gray-300 hover:bg-gray-800 hover:text-white border-l-4 border-transparent transition-colors duration-150', 'group flex items-center pl-8 pr-2 py-2 text-sm font-medium']">
                                            <component :is="subItem.icon" :class="[subItem.current ? 'text-cyan-400' : 'text-gray-400 group-hover:text-gray-300', 'mr-3 flex-shrink-0 h-5 w-5 transition-colors duration-150']" aria-hidden="true" />
                                            {{ subItem.name }}
                                        </Link>
                                    </div>
                                </div>
                            </template>
                        </nav>
                    </div>
                    <!-- User profile section at bottom -->
                    <div class="flex flex-shrink-0 bg-gray-800 p-4">
                        <div class="group block w-full flex-shrink-0">
                            <div class="flex items-center">
                                <div>
                                    <div class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-cyan-600">
                                        <span class="text-sm font-medium leading-none text-white">{{ $page.props.auth.user.name.charAt(0) }}</span>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-white">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-xs font-medium text-gray-300 group-hover:text-gray-200 capitalize">{{ $page.props.auth.user.role }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main column -->
            <div class="flex flex-1 flex-col md:pl-64">
                <div class="sticky top-0 z-10 flex h-16 flex-shrink-0 bg-white shadow-sm backdrop-blur-sm bg-white/90 border-b border-gray-200">
                    <button type="button" class="border-r border-gray-200 px-4 text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-cyan-500 md:hidden" @click="showingSidebar = true">
                        <span class="sr-only">Open sidebar</span>
                        <Bars3Icon class="h-6 w-6" aria-hidden="true" />
                    </button>
                    <div class="flex flex-1 justify-between px-4">
                        <div class="flex flex-1 items-center">
                            <div v-if="$slots.header" class="w-full flex items-center">
                                <slot name="header" />
                            </div>
                        </div>
                        <div class="ml-4 flex items-center md:ml-6">
                            <!-- Profile dropdown -->
                            <div class="relative ml-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button type="button" class="flex max-w-xs items-center rounded-full bg-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transition-all duration-200 hover:ring-2 hover:ring-cyan-300 hover:ring-offset-1">
                                            <span class="sr-only">Open user menu</span>
                                            <div class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-cyan-100 text-cyan-700">
                                                <span class="text-sm font-medium leading-none">{{ $page.props.auth.user.name.charAt(0) }}</span>
                                            </div>
                                        </button>
                                    </template>

                                    <template #content>
                                        <div class="px-4 py-3 border-b border-gray-100">
                                            <p class="text-sm text-gray-900">{{ $page.props.auth.user.name }}</p>
                                            <p class="text-sm font-medium text-gray-500 truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profil Saya
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Keluar
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>
                    </div>
                </div>

                <main class="flex-1">
                    <div class="py-6">
                        <div class="mx-auto max-w-7xl px-4 sm:px-6 md:px-8">
                            <slot />
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </div>
</template>
