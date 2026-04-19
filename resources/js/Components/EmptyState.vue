<script setup>
import { Boxes, Plus } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps({
    haveButton: {
        type: Boolean,
        default: false
    },
    href: {
        type: String,
        default: '#'
    },
    message: {
        type: String,
        default: 'No records found. Metrics will populate here once system activity is registered.'
    },
    border: {
        type: Boolean,
        default: true
    }
});
</script>

<template>
    <div :class="border ? 'border-t border-gray-300' : ''" class="flex min-h-100 w-full flex-col items-center justify-center bg-neutral-50/50 px-6 py-12 text-center transition-all hover:border-neutral-300">
        <div class="relative mb-6">
            <div class="absolute -inset-1 rounded-full bg-neutral-100 blur-sm"></div>
            <div class="relative flex h-16 w-16 items-center justify-center rounded-full bg-white ring-1 ring-neutral-200">
                <Boxes class="h-10 w-10 text-neutral-900" stroke-width="1.5" />
            </div>  
        </div>

        <h2 class="text-lg font-bold text-neutral-900">No records found</h2>
        <p class="mt-2 max-w-xs text-sm text-neutral-500">
            {{ message }}
        </p>

        <div v-if="haveButton" class="mt-8 flex flex-col sm:flex-row items-center gap-3">
            <Link :href="href"
                class="flex items-center gap-2 rounded-md bg-neutral-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-neutral-800 active:scale-95">
                <Plus class="h-4 w-4" stroke-width="3" />
                Add Record
            </Link>
        </div>
    </div>
</template>