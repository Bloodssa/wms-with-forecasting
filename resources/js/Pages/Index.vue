<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import grid from '../../images/grid-01.svg';

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
    <GuestLayout title="Home" :canLogin="canLogin" :canRegister="canRegister">
        <div class="relative h-100 flex justify-between -mt-10">
            <img :src="grid" />
            <img :src="grid" />
        </div>
        <div class="-mt-80 flex items-center justify-center">
            <p>dsadasd</p>
        </div>  
    </GuestLayout>
</template>
