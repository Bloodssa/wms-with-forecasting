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
import useCountdown from '@/composables/useCountDown';

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
    status: props.inquiry.status?.value || props.inquiry.status,
    resolved_message: '',
});

const serviceRecordForm = useForm({
    service_type: '',
    parts_cost: '',
    labor_cost: '',
    total_cost: '',
    notes: '',
});

// for the update of the status and log message
const showModal = ref(false);
const selectedStatus = ref(null);
const originalStatus = ref(currentStatus.value);

const statusApplied = ref(false);

const closeModal = () => {
    showModal.value = false;
    selectedStatus.value = null;
    statusApplied.value = false;
    serviceRecordForm.reset();
    form.status = originalStatus.value;
};

const requiresMessage = ['resolved', 'replaced', 'closed'];

const requiresConfirmation = ['resolved', 'replaced', 'closed'];
const requiresServiceRecord = ['resolved', 'replaced'];

const isLocked = computed(() =>
    requiresMessage.includes(currentStatus.value)
);

// before submiting show the modal
const submitStatus = () => {
    const current = currentStatus.value;
    const next = form.status;

    if (!isAllowedTransition(current, next)) {
        form.errors.status = 'Invalid status transition.';
        return;
    }

    if (requiresConfirmation.includes(next)) {
        selectedStatus.value = next;
        originalStatus.value = current;

        if (next === 'resolved') {
            serviceRecordForm.service_type = 'repair';
        }

        if (next === 'replaced') {
            serviceRecordForm.service_type = 'replacement';
        }

        showModal.value = true;
        return;
    }

    // pending / in-progress: ONLY update status
    sendStatusUpdate();
};

const finishModal = () => {
    showModal.value = false;
    selectedStatus.value = null;
    statusApplied.value = false;
    form.resolved_message = '';
    serviceRecordForm.reset();
};

const saveServiceRecord = (onDone) => {
    serviceRecordForm
        .transform(data => ({
            ...data,
            // Empty strings fail `numeric` even under `nullable` — send real null
            // so the backend can fall back to parts_cost + labor_cost.
            parts_cost: data.parts_cost === '' ? null : data.parts_cost,
            labor_cost: data.labor_cost === '' ? null : data.labor_cost,
            total_cost: data.total_cost === '' ? null : data.total_cost,
            notes: form.resolved_message,
        }))
        .post(
            route('inquiry.service-record.store', props.inquiry.id),
            {
                preserveScroll: true,
                onSuccess: onDone,
            }
        );
};

const sendStatusUpdate = () => {
    const status = selectedStatus.value || form.status;

    // resolved/replaced: ONLY create service record
    if (requiresServiceRecord.includes(status)) {
        saveServiceRecord(finishModal);
        return;
    }

    // closed: ONLY update status
    form.patch(
        route('inquiry-status', props.inquiry.id),
        {
            preserveScroll: true,
            onSuccess: finishModal,
        }
    );
};

// days remaining before expiry use created from inquiry and expiry since expiry date is immutable
const timeLeftAtSubmission = computed(() => {
    const expiry = dayjs(props.inquiry.warranty.expiry_date);
    const now = dayjs();

    const diffDays = expiry.diff(now, 'day');
    const diffHours = expiry.diff(now, 'hour');

    if (diffHours <= 0) return 'Expired';

    if (diffDays >= 1) {
        return `${diffDays} day${diffDays > 1 ? 's' : ''} left`;
    }

    return `${diffHours} hour${diffHours > 1 ? 's' : ''} left`;
});

// check if its covered during submission
const wasCovered = computed(() => {
    if (
        !props.inquiry.warranty ||
        !props.inquiry.warranty.expiry_date ||
        !props.inquiry.created_at
    ) {
        return false;
    }

    const expiry = dayjs(
        props.inquiry.warranty.expiry_date
    );

    const submitted = dayjs(
        props.inquiry.created_at
    );

    return (
        expiry.isAfter(submitted) ||
        expiry.isSame(submitted, 'day')
    );
});

const { now, timeLeft } = useCountdown();

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

const isDone = computed(() =>
    ['resolved', 'replaced', 'closed'].includes(currentStatus.value)
);

const page = usePage();
const can = computed(() => page.props.can ?? {});
// poll every five seconds
// usePoll(5000, {
//     only: ['messages', 'inquiry', 'flash'],
//     preserveScroll: true,
// });
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
                            <Avatar :name="props.inquiry.warranty.user.name" class="h-8 w-8" />
                            <div>
                                <p class="font-semibold">{{ props.inquiry.warranty.user.name }}</p>
                                <p class="font-normal text-xs text-neutral-500">{{ props.inquiry.warranty.user.email }}
                                </p>
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
                        <div class="px-5 py-3 border-b border-gray-300 text-sm font-medium bg-gray-50 text-gray-800">
                            Warranty had <span class="font-bold">{{ timeLeftAtSubmission }}</span> left at the time of
                            inquiry submission.
                        </div>
                        <form @submit.prevent="submitStatus" class="space-y-4 p-5">
                            <div>
                                <InputLabel value="Current Status" for="status" />

                                <select v-model="form.status" class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900 transition">
                                    <option v-for="status in statusFlow" :key="status" :value="status" :disabled="isLocked ||
                                        (
                                            !isAllowedTransition(currentStatus, status) &&
                                            !requiresConfirmation.includes(status)
                                        )
                                        ">
                                        {{ status.charAt(0).toUpperCase() + status.slice(1).replace('-', ' ') }}
                                    </option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.status }}
                                </div>
                            </div>
                            <BaseModal :show="showModal" :title="selectedStatus === 'resolved'
                                ? 'Provide Repair Details'
                                : selectedStatus === 'replaced'
                                    ? 'Provide Replacement Details'
                                    : 'Provide Closure Details'
                                " :subtitle="selectedStatus === 'closed'
                                    ? 'This action requires a reason for closing the inquiry'
                                    : 'This action requires technician service details'
                                    " size="md" @close="closeModal">
                                <div class="space-y-4">
                                    <template v-if="requiresServiceRecord.includes(selectedStatus)">

                                        <div>
                                            <InputLabel value="Service Type" for="service_type" />

                                            <input id="service_type" :value="selectedStatus === 'resolved'
                                                ? 'Repair'
                                                : 'Replacement'
                                                " type="text" readonly
                                                class="w-full border-gray-300 rounded-md text-sm bg-gray-50 text-gray-700" />

                                            <p class="text-xs text-neutral-500 mt-1">
                                                Service type is determined by the selected status.
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-2 gap-4">

                                            <div>
                                                <InputLabel value="Parts Cost" for="parts_cost" />

                                                <input id="parts_cost" v-model="serviceRecordForm.parts_cost" type="number" min="0"
                                                    step="0.01" placeholder="0.00"
                                                    class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900" />

                                                <div v-if="serviceRecordForm.errors.parts_cost" class="text-red-500 text-xs mt-1">
                                                    {{ serviceRecordForm.errors.parts_cost }}
                                                </div>
                                            </div>

                                            <div>
                                                <InputLabel value="Labor Cost" for="labor_cost" />

                                                <input id="labor_cost" v-model="serviceRecordForm.labor_cost" type="number" min="0"
                                                    step="0.01" placeholder="0.00"
                                                    class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900" />

                                                <div v-if="serviceRecordForm.errors.labor_cost" class="text-red-500 text-xs mt-1">
                                                    {{ serviceRecordForm.errors.labor_cost }}
                                                </div>
                                            </div>

                                        </div>

                                        <div>
                                            <InputLabel value="Total Cost" for="total_cost" />

                                            <input id="total_cost" v-model="serviceRecordForm.total_cost" type="number" min="0"
                                                step="0.01" placeholder="Leave blank to calculate automatically"
                                                class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900" />

                                            <p class="text-xs text-neutral-500 mt-1">
                                                Leave blank to automatically calculate Parts Cost + Labor Cost.
                                            </p>

                                            <div v-if="serviceRecordForm.errors.total_cost" class="text-red-500 text-xs mt-1">
                                                {{ serviceRecordForm.errors.total_cost }}
                                            </div>
                                        </div>

                                    </template>
                                    <div>
                                        <InputLabel :value="selectedStatus === 'closed'
                                            ? 'Closure Reason'
                                            : 'Service Notes'
                                            " for="resolved_message" />

                                        <textarea v-model="form.resolved_message"
                                            class="input-border border-gray-300 focus:neutral-900 w-full border rounded-md p-3 text-sm"
                                            rows="5" :placeholder="selectedStatus === 'closed'
                                                ? 'Explain why this inquiry is being closed...'
                                                : 'Explain what was done...'
                                                "></textarea>

                                        <div v-if="form.errors.resolved_message" class="text-red-500 text-xs mt-1">
                                            {{ form.errors.resolved_message }}
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2">

                                        <button type="button" @click="closeModal"
                                            class="px-4 py-2 text-sm rounded hover:bg-gray-100">
                                            Cancel
                                        </button>

                                        <button type="button" @click="sendStatusUpdate" :disabled="form.processing"
                                            class="px-4 py-2 bg-neutral-900 text-white rounded text-sm disabled:opacity-50">
                                            <span v-if="form.processing">
                                                Saving...
                                            </span>

                                            <span v-else>
                                                Confirm
                                            </span>
                                        </button>

                                    </div>

                                </div>
                            </BaseModal>

                            <button v-if="can.viewInquiryOnly" type="submit" :disabled="form.processing"
                                class="w-full bg-neutral-900 text-white py-2.5 rounded-md text-sm font-semibold hover:bg-neutral-800 transition disabled:opacity-50 flex justify-center items-center">
                                <span v-if="form.processing">
                                    Updating...
                                </span>

                                <span v-else>
                                    Apply Status Change
                                </span>
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
                                            {{ timeLeft(props.inquiry.warranty.expiry_date) }} Left
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