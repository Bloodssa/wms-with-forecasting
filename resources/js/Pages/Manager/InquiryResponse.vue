<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { ref, computed } from 'vue';
import { Head, Link, useForm, usePoll, usePage } from '@inertiajs/vue3';
import { ArrowLeftFromLine } from 'lucide-vue-next';
import Progress from '@/Components/Inquiry/Progress.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Messages from '@/Components/Inquiry/Messages.vue';
import Reply from '@/Components/Inquiry/Reply.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';
import BaseModal from '@/Components/Modals/BaseModal.vue';

const props = defineProps({
    inquiry: {
        type: Object,
        default: () => ({})
    },
    messages: {
        type: Array,
        default: []
    }
});

const currentStatus = computed(() =>
    props.inquiry.status?.value ?? props.inquiry.status
);

const form = useForm({
    status: props.inquiry.status?.value || props.inquiry.status
});

// for the update of the status and log message
const showModal = ref(false);
const selectedStatus = ref(null);
const resolutionMessage = ref('');
const originalStatus = ref(form.status);

const closeModal = () => {
    showModal.value = false;
    resolutionMessage.value = '';
    form.status = originalStatus.value;
};

const requiresMessage = ['resolved', 'replaced', 'closed'];

const isLocked = computed(() =>
    requiresMessage.includes(currentStatus.value)
);

// before submiting show the modal
const submitStatus = () => {
    const current = currentStatus.value;
    const next = form.status;

    if (!isAllowedTransition(current, next)) {
        form.errors.status = "Invalid status transition.";
        return;
    }

    if (requiresMessage.includes(next)) {
        selectedStatus.value = next;
        originalStatus.value = next;
        showModal.value = true;
        return;
    }

    sendStatusUpdate();
};

const sendStatusUpdate = () => {
    form.transform(data => ({
        ...data,
        resolved_message: resolutionMessage.value
    })).patch(route('inquiry-status', props.inquiry.id), {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            resolutionMessage.value = '';
        }
    });
};

// days remaining before expiry use created from inquiry and expiry since expiry date is immutable
const daysRemainingAtSubmission = dayjs(props.inquiry.warranty.expiry_date).diff(dayjs(props.inquiry.created_at), 'day');

// check if its covered during submission
const wasCovered = daysRemainingAtSubmission >= 0;

// status for options in updae
const statusFlow = [
    'open',
    'pending',
    'in-progress',
    'resolved',
    'replaced',
    'closed'
];

const isAllowedTransition = (current, next) => {
    const currentIndex = statusFlow.indexOf(current);
    const nextIndex = statusFlow.indexOf(next);

    if (currentIndex === -1 || nextIndex === -1) return false;

    return nextIndex > currentIndex;
};

const isDone = computed(() => Boolean(props.inquiry.is_done));

const page = usePage();
const can = computed(() => page.props.can ?? {});
// poll every five seconds
usePoll(5000, {
    only: ['messages', 'inquiry', 'flash'],
    preserveScroll: true,
});
</script>

<template>

    <Head title="Inquiry Details" />
    <ManagerLayout>
        <div class="space-y-6 mt-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-neutral-900 text-2xl font-bold">Inquiry Details</h1>
                </div>
                <Link :href="route('warranty-inquiries')"
                    class="text-sm font-semibold text-neutral-600 hover:text-neutral-900 flex items-center gap-1">
                    <ArrowLeftFromLine />
                    Back to Inquiries
                </Link>
            </div>
            <div v-if="isDone" class="border border-gray-300 rounded-md bg-green-50 text-green-800 p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-sm">
                            Inquiry {{ currentStatus === 'replaced' ? 'Completed via Replacement' : 'Completed' }}
                        </p>
                        <p class="text-xs opacity-80">
                            This inquiry has been marked as {{ currentStatus }} and is no longer active.
                        </p>
                    </div>
                    <Badge :type="currentStatus">
                        {{ currentStatus }}
                    </Badge>
                </div>
                <p v-if="props.inquiry.resolved_message" class="mt-3 text-sm">
                    {{ props.inquiry.resolved_message }}
                </p>
            </div>
            <Progress :inquiryStatus="props.inquiry.status" />
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div
                    class="lg:col-span-2 flex flex-col bg-white border border-gray-300 rounded-md h-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-300">
                        <div class="flex items-center space-x-2">
                            <Avatar :name="props.inquiry.user.name" class="h-8 w-8" />
                            <div>
                                <p class="font-semibold">{{ props.inquiry.user.name }}</p>
                                <p class="font-normal text-xs text-neutral-500">{{ props.inquiry.user.email }}</p>
                            </div>
                        </div>
                    </div>
                    <Messages :inquiryId="props.inquiry.id" :messages="props.messages" />
                    <Reply v-if="!isDone && can.viewInquiryOnly" :inquiry-id="props.inquiry.id" />
                </div>
                <div class="space-y-4">
                    <div class="bg-white border border-gray-300 rounded-md">
                        <div class="px-5 py-4 border-b border-gray-300">
                            <h1 class="text-neutral-900 font-semibold text-base">
                                Update Progress
                            </h1>
                        </div>
                        <div :class="[
                            'px-5 py-3 border-b border-gray-300 text-sm font-medium',
                            wasCovered ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'
                        ]">
                            <template v-if="wasCovered">
                                Inquired <span class="font-bold">{{ daysRemainingAtSubmission }} days</span> before
                                warranty expiry.
                                <span class="block text-xs font-normal opacity-80">(Valid Coverage)</span>
                            </template>
                            <template v-else>
                                Inquired <span class="font-bold">{{ Math.abs(daysRemainingAtSubmission) }} days</span>
                                after warranty expiry.
                                <span class="block text-xs font-normal opacity-80">(Expired Coverage)</span>
                            </template>
                        </div>
                        <form @submit.prevent="submitStatus" class="space-y-4 p-5">
                            <div>
                                <InputLabel value="Current Status" for="status" />
                                <select v-model="form.status"
                                    class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900 transition">
                                    <option v-for="status in statusFlow" :key="status" :value="status"
                                        :disabled="isLocked || (!isAllowedTransition(currentStatus, status) && !requiresMessage.includes(status))">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1).replace('-', ' ') }}
                                    </option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.status }}
                                </div>
                            </div>
                            <BaseModal :show="showModal" title="Provide Resolution Details"
                                subtitle="This action requires technician explanation" size="md" @close="closeModal">
                                <div class="space-y-4">

                                    <textarea v-model="resolutionMessage" class="input-border border-gray-300 focus:neutral-900 w-full border rounded-md p-3 text-sm"
                                        rows="5" placeholder="Explain what was done..."></textarea>

                                    <div class="flex justify-end gap-2">
                                        <button @click="closeModal" class="px-4 py-2 text-sm rounded hover:bg-gray-100">
                                            Cancel
                                        </button>

                                        <button @click="sendStatusUpdate"
                                            class="px-4 py-2 bg-neutral-900 text-white rounded text-sm">
                                            Confirm
                                        </button>
                                    </div>

                                </div>
                            </BaseModal>
                            <button v-if="can.viewInquiryOnly" type="submit" :disabled="form.processing"
                                class="w-full bg-neutral-900 text-white py-2.5 rounded-md text-sm font-semibold hover:bg-neutral-800 transition disabled:opacity-50 flex justify-center items-center">
                                <span v-if="form.processing">Updating...</span>
                                <span v-else>Apply Status Change</span>
                            </button>
                        </form>
                    </div>
                    <div class="bg-white border border-gray-300 rounded-md space-y-3">
                        <div class="px-5 py-4 border-b border-gray-300">
                            <h1 class="text-neutral-900 font-semibold text-base">
                                Warranty Details
                            </h1>
                        </div>

                        <div class="flex items-center gap-4 px-5">
                            <div class="shrink-0">
                                <img class="h-16 w-16 object-contain border border-gray-300 rounded bg-white"
                                    :src="`/storage/${props.inquiry.warranty.product.product_image_url}`"
                                    alt="Product" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-sm text-neutral-500">Product Name</span>
                                <p class="text-md font-bold text-neutral-900">
                                    {{ props.inquiry.warranty.product.name }}
                                </p>
                                <p class="text-sm text-neutral-500">{{ props.inquiry.warranty.product.category.name }}
                                </p>
                            </div>
                        </div>
                        <div class="border-t border-gray-300 px-5">
                            <div>
                                <span class="text-sm text-neutral-500">Serial Number</span>
                                <p class="text-md font-semibold text-neutral-900">
                                    {{ props.inquiry.warranty.serial_number }}
                                </p>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-neutral-500">Status</span>
                                    <div class="mt-1">
                                        <Badge :type="props.inquiry.warranty.status">
                                            {{ props.inquiry.warranty.status }}
                                        </Badge>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-sm text-neutral-500">Date Purchased</span>
                                    <p class="text-md font-semibold text-neutral-900">
                                        {{ props.inquiry.warranty.purchase_date ?
                                            dayjs(props.inquiry.warranty.purchase_date).format('MMM DD, YYYY') : 'N/A' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-300 px-5 py-4">
                            <span class="text-sm text-neutral-500">Warranty Expiration</span>
                            <div class="flex justify-between items-start mt-1">
                                <div>
                                    <p class="text-md font-bold text-neutral-900">
                                        {{ props.inquiry.warranty.expiry_date
                                            ? dayjs(props.inquiry.warranty.expiry_date).format('MMM DD, YYYY')
                                            : 'N/A'
                                        }}
                                    </p>
                                </div>

                                <div v-if="props.inquiry.warranty.expiry_date" class="flex items-center space-x-2">
                                    <!-- <CircleBadge :type="inquiry.warranty.status" size="md" /> -->
                                    <p :class="[
                                        'text-md font-medium',
                                        dayjs().isAfter(dayjs(props.inquiry.warranty.expiry_date)) ? 'text-red-800' : 'text-neutral-500'
                                    ]">
                                        <template v-if="dayjs().isAfter(dayjs(props.inquiry.warranty.expiry_date))">
                                            Expired
                                        </template>
                                        <template v-else>
                                            {{ dayjs(props.inquiry.warranty.expiry_date).diff(dayjs(), 'day') }} days
                                            remaining
                                        </template>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="border-t border-gray-300 py-3 px-5">
                            <span class="text-sm text-neutral-500">Service Center</span>
                            <p class="text-md font-semibold text-neutral-900">
                                {{ props.inquiry.warranty.product.service_center_name }}
                            </p>
                            <p class="text-sm text-neutral-500 mt-1 leading-snug">
                                {{ props.inquiry.warranty.product.service_center_address }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ManagerLayout>
</template>