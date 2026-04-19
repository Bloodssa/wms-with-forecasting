<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    links: {
        type: Array,
        required: true
    },
    meta: {
        type: Object,
        default: null
    }
})
</script>

<template>
    <div v-if="meta?.last_page > 1" class="px-3 py-2 w-full border-t border-gray-300 flex flex-wrap gap-1">
        <template v-for="(link, index) in links" :key="index">
            <span
                v-if="!link.url"
                class="px-3 py-1 text-sm border rounded text-gray-400 bg-gray-100 cursor-not-allowed"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                preserve-scroll
                class="px-3 py-1 text-sm border rounded transition"
                :class="link.active
                    ? 'bg-blue-500 text-white border-blue-500'
                    : 'hover:bg-gray-100 text-gray-700 border-gray-300'"
                v-html="link.label"
            />
        </template>
    </div>
</template>