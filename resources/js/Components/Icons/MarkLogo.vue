<script setup>
import mark from '../../../images/mark.png';

defineProps({
    size: {
        type: String,
        default: 'h-14 w-auto'
    }
});
</script>

<template>
    <span class="block">
        <img :src="mark" alt="eWarranty" :class="`${size} object-contain`">
    </span>
</template>