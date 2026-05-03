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
        <section class="relative min-h-[90vh] md:min-h-[70vh] flex flex-col items-center justify-center bg-white border-b border-gray-300 overflow-hidden">

        </section>
    </GuestLayout>
</template>
