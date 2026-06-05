<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/20/solid';

defineProps({
    data: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <div v-if="data.links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-b-lg shadow-sm">
        <div class="flex flex-1 justify-between sm:hidden">
            <Link :href="data.prev_page_url" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 pointer-events-none': !data.prev_page_url}">
                Sebelumnya
            </Link>
            <Link :href="data.next_page_url" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" :class="{'opacity-50 pointer-events-none': !data.next_page_url}">
                Selanjutnya
            </Link>
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Menampilkan <span class="font-medium">{{ data.from || 0 }}</span> sampai <span class="font-medium">{{ data.to || 0 }}</span> dari <span class="font-medium">{{ data.total }}</span> hasil
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                    <template v-for="(link, key) in data.links" :key="key">
                        <!-- Prev/Next links with icons -->
                        <Link v-if="link.label.includes('Previous')" :href="link.url" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" :class="{'opacity-50 pointer-events-none': !link.url}">
                            <span class="sr-only">Previous</span>
                            <ChevronLeftIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>
                        
                        <Link v-else-if="link.label.includes('Next')" :href="link.url" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0" :class="{'opacity-50 pointer-events-none': !link.url}">
                            <span class="sr-only">Next</span>
                            <ChevronRightIcon class="h-5 w-5" aria-hidden="true" />
                        </Link>

                        <!-- Number links -->
                        <Link v-else :href="link.url" aria-current="page" :class="[link.active ? 'relative z-10 inline-flex items-center bg-cyan-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-cyan-600' : 'relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0', !link.url ? 'opacity-50 pointer-events-none' : '']" v-html="link.label">
                        </Link>
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
