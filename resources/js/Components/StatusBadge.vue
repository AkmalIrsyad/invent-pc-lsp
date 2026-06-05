<script setup>
defineProps({
    status: {
        type: String,
        required: true,
    },
});

const getStatusClasses = (status) => {
    switch (status.toLowerCase()) {
        case 'tersedia':
        case 'masuk':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'hampir habis':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'habis':
        case 'keluar':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};
</script>

<template>
    <span :class="[getStatusClasses(status), 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium border']">
        <svg v-if="status.toLowerCase() === 'tersedia' || status.toLowerCase() === 'masuk'" class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
        </svg>
        <svg v-else-if="status.toLowerCase() === 'hampir habis'" class="-ml-0.5 mr-1.5 h-2 w-2 text-yellow-400" fill="currentColor" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
        </svg>
        <svg v-else-if="status.toLowerCase() === 'habis' || status.toLowerCase() === 'keluar'" class="-ml-0.5 mr-1.5 h-2 w-2 text-red-400" fill="currentColor" viewBox="0 0 8 8">
            <circle cx="4" cy="4" r="3" />
        </svg>
        <span class="capitalize">{{ status }}</span>
    </span>
</template>
