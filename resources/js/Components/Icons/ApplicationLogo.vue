<script setup>
import { Link } from '@inertiajs/vue3';
import logo from '../../../images/logo.png';

defineProps({
    size: {
        type: String,
        default: 'h7 w-36'
    }
});

</script>

<template>
    <Link href="/" class="flex items-center">
        <img :src="logo" alt="eWarranty" :class="`${size} block object-left`">
    </Link>
</template>
