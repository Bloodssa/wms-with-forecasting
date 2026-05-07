<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage()

const currentPath = computed(() => page.url.split('?')[0])

const isActiveLink = (path) => {
    if (path === '/') {
        return currentPath.value === '/'
    }

    return currentPath.value.startsWith(path)
}
</script>

<template>
    <Link href="/"
        :class="[isActiveLink('/') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-sm', 'font-semibold', 'py-1', 'px-2', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">
        Home</Link>
    <Link href="/about"
        :class="[isActiveLink('/about') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-sm', 'font-semibold', 'py-1', 'px-2', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">
        About</Link>
    <Link href="/products"
        :class="[isActiveLink('/products') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-sm', 'font-semibold', 'py-1', 'px-2', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">
        Products</Link>
</template>