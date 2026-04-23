<script setup>
import Badge from '@/Components/Badge.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeftToLine } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    inquiry: {
        type: Object,
        default: () => ({})
    }
});

// days remaining
const daysRemaining = computed(() => {
    if (!props.inquiry.warranty?.expiry_date) return null;
    const expiry = new Date(props.inquiry.warranty.expiry_date);
    const today = new Date();
    const diffTime = expiry - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
});

const formatDate = (dateString, format = 'long') => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (format === 'short') {
        return date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
    }
    return date.toLocaleString('en-US', {
        month: 'short', day: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: true
    });
};
</script>

<template>

    <Head title="Inquiry" />
    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <Link :href="route('inquiries')" class="p-2 hover:bg-gray-100 rounded-full transition text-neutral-500">
                    <ArrowLeftToLine class="hover:text-neutral-900 transition duration-200" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-neutral-900">
                        Inquiry for {{ props.inquiry.warranty.product.name }}
                    </h1>
                    <p class="text-xs capitalize text-neutral-500">
                        {{ props.inquiry.status }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <Link :href="route('warranty.show', props.inquiry.warranty.id)"
                    class="px-4 py-2 border border-gray-300 bg-neutral-900 text-white text-sm font-semibold rounded-md">
                    View Chats
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white border border-gray-300 rounded-md">
                    <div class="px-5 py-4 border-b border-gray-300">
                        <h2 class="text-neutral-900 font-semibold text-base">Inquiry Details</h2>
                    </div>
                    <div class="px-5 py-4 space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-neutral-500">Status</span>
                            <Badge :type="props.inquiry.status" size="sm">
                                {{ props.inquiry.status }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-neutral-500">Created</span>
                            <span class="text-sm font-semibold text-neutral-900">
                                {{ formatDate(props.inquiry.created_at) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-neutral-500">Last Updated</span>
                            <span class="text-sm font-semibold text-neutral-900">
                                {{ formatDate(props.inquiry.updated_at) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-300">
                        <h2 class="text-neutral-900 font-semibold text-base">Issue Message</h2>
                    </div>
                    <div class="p-5">
                        <p class="text-neutral-900">{{ props.inquiry.message }}</p>
                        <div class="mt-6 pt-4 border-t border-gray-300">
                            <div v-if="props.inquiry.attachments?.length" class="flex flex-wrap gap-2">
                                <a v-for="(attachment, index) in props.inquiry.attachments" :key="index"
                                    :href="`/storage/${attachment}`" target="_blank">
                                    <img :src="`/storage/${attachment}`"
                                        class="h-20 w-20 object-cover rounded-md border border-gray-300 hover:opacity-80 transition">
                                </a>
                            </div>
                            <p v-else class="text-sm text-neutral-500">No attachments</p>
                        </div>
                    </div>
                </div>

                <div v-if="props.inquiry.warranty?.resolved_message" class="bg-white border border-gray-300 rounded-md">
                    <div class="px-5 py-4 border-b border-gray-300">
                        <h2 class="text-neutral-900 font-semibold text-base">Resolution</h2>
                    </div>
                    <div class="p-5">
                        <p class="text-neutral-900">{{ props.inquiry.warranty.resolved_message }}</p>
                    </div>
                </div>
                <div v-else class="bg-white border border-gray-300 rounded-md p-5 text-sm text-neutral-500">
                    <p v-if="props.inquiry.status === 'pending'">Your inquiry has been received and is waiting for
                        review.</p>
                    <p v-else-if="props.inquiry.status === 'in_progress'">Your inquiry is currently being reviewed.</p>
                    <p v-else>Awaiting for updates.</p>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-gray-300 rounded-md">
                    <div class="px-5 py-4 border-b border-gray-300">
                        <h1 class="text-neutral-900 font-semibold text-base">Warranty Details</h1>
                    </div>

                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="shrink-0">
                            <img class="h-16 w-16 object-contain border border-gray-300 rounded bg-white"
                                :src="`/storage/${props.inquiry.warranty.product.product_image_url}`"
                                :alt="props.inquiry.warranty.product.name" />
                        </div>
                        <div class="min-w-0">
                            <span class="text-sm text-neutral-500">Product Name</span>
                            <p class="text-md font-bold text-neutral-900">{{ props.inquiry.warranty.product.name }}</p>
                            <p class="text-sm text-neutral-500">{{ props.inquiry.warranty.product.category.name }}</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-300 px-5 py-4 space-y-4">
                        <div>
                            <span class="text-sm text-neutral-500">Serial Number</span>
                            <p class="text-md font-semibold text-neutral-900">{{ props.inquiry.warranty.serial_number }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-neutral-500">Status</span>
                                <div class="mt-1">
                                    <Badge :type="props.inquiry.warranty.status" size="sm">
                                        {{ props.inquiry.warranty.status }}
                                    </Badge>
                                </div>
                            </div>
                            <div>
                                <span class="text-sm text-neutral-500">Date Purchased</span>
                                <p class="text-md font-semibold text-neutral-900">
                                    {{ formatDate(props.inquiry.warranty.purchase_date, 'short') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-300 px-5 py-4">
                        <span class="text-sm text-neutral-500">Warranty Expiration</span>
                        <div class="flex justify-between items-start mt-1">
                            <p class="text-md font-bold text-neutral-900">
                                {{ formatDate(props.inquiry.warranty.expiry_date, 'short') }}
                            </p>
                            <div v-if="props.inquiry.warranty.expiry_date" class="flex items-center space-x-1">
                                <p :class="daysRemaining < 0 ? 'text-red-800' : 'text-neutral-500'"
                                    class="text-md font-medium">
                                    {{ daysRemaining < 0 ? 'Expired' : `${daysRemaining} days remaining` }} </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-300 py-4 px-5">
                        <span class="text-sm text-neutral-500">Service Center</span>
                        <p class="text-md font-semibold text-neutral-900">{{
                            props.inquiry.warranty.product.service_center_name }}</p>
                        <p class="text-sm text-neutral-500 mt-1 leading-snug">{{
                            props.inquiry.warranty.product.service_center_address }}</p>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template> 