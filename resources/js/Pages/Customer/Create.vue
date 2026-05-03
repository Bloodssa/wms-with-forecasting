<script setup>
import Inquire from '@/Components/Inquiry/Inquire.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftToLine } from 'lucide-vue-next';
const props = defineProps({
    warranty: {
        type: Object,
        default: () => ({})
    }
});
</script>

<template>

    <Head title="Home" />
    <CustomerLayout>
        <div class="flex items-center gap-3">
            <Link :href="route('inquiries')"
                class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
            <ArrowLeftToLine class="hover:text-neutral-900 transition duration-200" />
            </Link>
            <div>
                <h1 class="text-xl font-bold text-neutral-900">
                    Make an Inquiry for {{ props.warranty.product.name }}
                </h1>
            </div>
        </div>
        <Inquire :warranty="props.warranty" />
    </CustomerLayout>
</template>