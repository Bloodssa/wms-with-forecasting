<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({
    title: {
        type: String,
        required: true
    },
    count: {
        type: Number,
        default: 0
    },
    href: {
        type: String,
        default: '#'
    },
    icon: {
        type: [Object, Function],
        required: true
    }
});
</script>

<template>
    <div class="border border-gray-300 border-l-neutral-900 border-l-4 rounded-md p-5 bg-white">
        <div class="flex justify-between items-start">
            <div class="w-10 h-10 rounded-md bg-neutral-100 flex items-center justify-center text-neutral-900">
                <component :is="icon" class="h-5 w-5 transition-colors group-hover:text-zinc-900" />
            </div>
        </div>
        <div class="mt-4">
            <p class="text-sm font-medium text-neutral-500">{{ title }}</p>
            <div class="flex justify-between items-end mt-2">
                <h2 class="text-2xl font-bold text-neutral-900">{{ count }}</h2>
                <Link :href="href"
                    class="text-xs font-semibold text-neutral-500 hover:underline hover:text-neutral-900">View All
                </Link>
            </div>
        </div>
    </div>
</template>