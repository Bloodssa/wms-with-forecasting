<script setup>
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
    <GuestLayout title="About" :canLogin="canLogin" :canRegister="canRegister">
        
    </GuestLayout>
</template>
