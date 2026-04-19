<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: String,
    active: Boolean,
});

const classes = computed(() => {
    return props.active
        ? 'group flex items-center gap-3 rounded-md py-2.5 px-3 font-semibold text-neutral-900 bg-gray-100 transition-all'
        : 'group flex items-center gap-3 rounded-md py-2.5 px-3 font-semibold text-neutral-500 hover:bg-gray-100 hover:text-neutral-900 transition-all';
});
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>