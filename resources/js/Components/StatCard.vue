<script setup>
import { ArrowUpIcon, ArrowDownIcon } from '@heroicons/vue/20/solid';

defineProps({
    title: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    icon: {
        type: [Object, Function],
        required: true,
    },
    iconBgColor: {
        type: String,
        default: 'bg-cyan-500',
    },
    trend: {
        type: String,
        default: null, // 'up' or 'down'
    },
    trendValue: {
        type: String,
        default: null,
    },
});
</script>

<template>
    <div class="relative overflow-hidden rounded-xl bg-white px-4 pt-5 pb-12 shadow-sm border border-gray-100 sm:px-6 sm:pt-6 hover:shadow-md transition-shadow duration-200">
        <dt>
            <div :class="[iconBgColor, 'absolute rounded-md p-3 shadow-sm']">
                <component :is="icon" class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <p class="ml-16 truncate text-sm font-medium text-gray-500">{{ title }}</p>
        </dt>
        <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
            <p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
            <p v-if="trend" :class="[trend === 'up' ? 'text-green-600' : 'text-red-600', 'ml-2 flex items-baseline text-sm font-semibold']">
                <ArrowUpIcon v-if="trend === 'up'" class="h-5 w-5 flex-shrink-0 self-center text-green-500" aria-hidden="true" />
                <ArrowDownIcon v-else class="h-5 w-5 flex-shrink-0 self-center text-red-500" aria-hidden="true" />
                <span class="sr-only"> {{ trend === 'up' ? 'Naik' : 'Turun' }} sebesar </span>
                {{ trendValue }}
            </p>
            <div class="absolute inset-x-0 bottom-0 bg-gray-50 px-4 py-4 sm:px-6">
                <div class="text-sm">
                    <slot name="footer" />
                </div>
            </div>
        </dd>
    </div>
</template>
