<script setup>
import { Head } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';

const props = defineProps({
    product: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>
    <Head title="Review Product" />
    <CustomerLayout>
        {{ props.product.name }}
    </CustomerLayout>
</template>