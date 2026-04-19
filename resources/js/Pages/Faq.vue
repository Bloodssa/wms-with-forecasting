<script setup>
import { Head } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    }
});
</script>

<template>
    <GuestLayout title="FAQ" :canLogin="canLogin" :canRegister="canRegister">
    
    </GuestLayout>
</template>
