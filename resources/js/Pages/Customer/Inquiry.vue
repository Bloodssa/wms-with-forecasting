<script setup>
import Badge from '@/Components/Badge.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, router, usePoll } from '@inertiajs/vue3';
import { ArrowLeftToLine } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import Discussion from '@/Components/Discussion.vue';
import BaseModal from '@/Components/Modals/BaseModal.vue';
import useCountdown from '@/composables/useCountDown';

const props = defineProps({
    inquiry: {
        type: Object,
        default: () => ({})
    },
    messages: {
        type: Array,
        default: () => []
    },
    activeTab: {
        type: String,
        default: 'messages'
    }
});

const activeTab = computed(() => props.activeTab);
const showCancelModal = ref(false);
const cancelMessage = ref('');
const loading = ref(false);

const submitCancel = () => {
    if (!cancelMessage.value.trim()) return;

    loading.value = true;

    router.patch(route('inquiry-cancel', props.inquiry.id), {
        message: cancelMessage.value
    }, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
            showCancelModal.value = false;
            cancelMessage.value = '';
        }
    });
};

const switchTab = (tabName) => {
    router.get(
        route('inquiry.show', props.inquiry.id),
        { tab: tabName },
        {
            preserveScroll: true,
            replace: true
        }
    );
};

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

const isDone = computed(() => {
    return ['resolved', 'replaced', 'closed'].includes(props.inquiry.status);
});

const lastResponse = computed(() => {
    if (!props.messages?.length) return null;

    return [...props.messages].sort(
        (a, b) => new Date(a.created_at) - new Date(b.created_at)
    ).at(-1);
});


const { timeLeft, isExpired } = useCountdown();
// usePoll(5000, {
//     only: ['messages', 'inquiry', 'flash'],
//     preserveScroll: true,
// });
</script>

<template>

    <Head title="Inquiry" />
    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <Link :href="route('inquiries')"
                    class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
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
            <div class="flex mb-6 gap-2">
                <div
                    class="inline-flex w-full md:w-auto items-center p-1 space-x-2 bg-white border border-gray-300 rounded-md">
                    <button @click="switchTab('messages')"
                        :class="props.activeTab === 'messages' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                        class="flex-1 font-semibold md:flex-none text-center px-4 py-2 text-sm rounded-md transition focus:outline-none select-none">
                        Inquiry Messages
                    </button>
                    <button @click="switchTab('details')"
                        :class="props.activeTab === 'details' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                        class="flex-1 font-semibold md:flex-none text-center px-4 py-2 text-sm rounded-md transition focus:outline-none select-none">
                        Inquiry Details
                    </button>
                </div>
            </div>
        </div>
        <div v-if="activeTab === 'messages'">
            <Discussion :inquiry-id="props.inquiry.id" :messages="props.messages" @cancel="showCancelModal = true"
                :warranty="props.inquiry.warranty" :inquiry="props.inquiry" />
        </div>

        <div v-if="activeTab === 'details'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
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
                <div v-if="isDone && lastResponse" class="bg-white border border-gray-300 rounded-md p-5">
                    <h2 class="font-semibold text-base text-neutral-900 mb-3">
                        Resolution
                    </h2>
                    <p class="text-neutral-900">
                        {{ lastResponse.message }}
                    </p>
                    <p class="text-xs text-neutral-500 mt-2">
                        Resolved at {{ formatDate(lastResponse.created_at) }}
                    </p>
                </div>
                <div v-else class="bg-white border border-gray-300 rounded-md p-5 text-sm text-neutral-500">
                    <p v-if="props.inquiry.status === 'pending'">
                        Your inquiry has been received and is waiting for review.
                    </p>
                    <p v-else-if="props.inquiry.status === 'in_progress'">
                        Your inquiry is currently being reviewed.
                    </p>
                    <p v-else>
                        Awaiting for updates.
                    </p>
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
                                {{ timeLeft(props.inquiry.warranty.expiry_date) }} left</p>
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
    <BaseModal :show="showCancelModal" @close="showCancelModal = false" title="Cancel Inquiry"
        subtitle="Please provide a reason for cancelling">
        <div class="space-y-4">

            <textarea v-model="cancelMessage" class="w-full  input-border border border-nuetral-900 rounded-md p-3 text-sm" rows="4"
                placeholder="Enter your reason why you cancel the inquiry"></textarea>

            <div class="flex justify-end gap-2">
                <button @click="showCancelModal = false"
                    class="px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-md">
                    Close
                </button>
                <button @click="submitCancel" :disabled="loading || !cancelMessage"
                    class="px-4 py-2 bg-red-700 text-white rounded-md disabled:opacity-50">
                    Confirm Cancel
                </button>
            </div>

        </div>
    </BaseModal>
</template>