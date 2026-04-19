<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, Link, useForm, usePoll } from '@inertiajs/vue3';
import { ArrowLeftFromLine } from 'lucide-vue-next';
import Progress from '@/Components/Inquiry/Progress.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Messages from '@/Components/Inquiry/Messages.vue';
import Reply from '@/Components/Inquiry/Reply.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';

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

const form = useForm({
    status: props.inquiry.status?.value || props.inquiry.status
});

const submitStatus = () => {
    form.patch(route('inquiry-status', props.inquiry.id), {
        preserveScroll: true,
        onSuccess: () => {
            // toast after refinement of other uis
        }
    });
};

// poll every five seconds
// usePoll(5000, {
//     only: ['messages', 'inquiry'],
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
                    <Reply :inquiry-id="props.inquiry.id" />
                </div>
                <div class="space-y-4">
                    <div class="bg-white border border-gray-300 rounded-md">
                        <div class="px-5 py-4 border-b border-gray-300">
                            <h1 class="text-neutral-900 font-semibold text-base">
                                Update Progress
                            </h1>
                        </div>
                        <form @submit.prevent="submitStatus" class="space-y-4 p-5">
                            <div>
                                <InputLabel value="Current Status" for="status" />
                                <select id="status" v-model="form.status"
                                    class="w-full border-gray-300 rounded-md text-sm focus:ring-neutral-900 focus:border-neutral-900 transition">
                                    <option value="pending">Pending</option>
                                    <option value="in-progress">In-Progress</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="replaced">Replaced</option>
                                    <option value="closed">Closed</option>
                                </select>
                                <div v-if="form.errors.status" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.status }}
                                </div>
                            </div>

                            <button type="submit" :disabled="form.processing"
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