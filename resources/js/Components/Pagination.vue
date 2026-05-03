<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-sm text-neutral-500 border rounded-md"
                v-html="link.label"
            />
            
            <Link
                v-else
                :href="link.url"
                class="px-4 py-2 text-sm border border-gray-300 rounded-md transition hover:bg-gray-50"
                :class="{ 'bg-neutral-900 text-white hover:bg-neutral-800': link.active }"
                v-html="link.label"
                preserve-scroll
            />
        </template>
    </div>
</template>