<script setup>
import { computed } from 'vue';
import Progress from './Inquiry/Progress.vue';
import Messages from './Inquiry/Messages.vue';
import Reply from './Inquiry/Reply.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    inquiryId: Number,
    warranty: {
        type: Object,
        default: () => ({})
    },
    messages: Array,
    inquiry: Object
});

const emit = defineEmits(['cancel'])

// get the latest inquiry
const latestInquiry = computed(() => {
    return props.inquiry || props.warranty?.inquiries?.[0] || null;
});

const finalStatuses = ['resolved', 'replaced', 'closed'];

const isDone = computed(() =>
    finalStatuses.includes(props.inquiry?.status)
);
</script>

<template>
    <div class="mt-6">
        <div v-if="isDone" class="border border-gray-300 rounded-md bg-green-50 text-green-800 p-6 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="font-semibold text-sm">
                        Inquiry {{ props.inquiry.status === 'replaced' ? 'Completed via Replacement' : 'Completed' }}
                    </p>
                    <p class="text-xs opacity-80">
                        This inquiry has been marked as {{ props.inquiry.status }} and is no longer active.
                    </p>
                </div>

                <Badge :type="props.inquiry.status">
                    {{ props.inquiry.status }}
                </Badge>
            </div>

            <p v-if="props.inquiry.resolved_message" class="mt-3 text-sm">
                {{ props.inquiry.resolved_message }}
            </p>
        </div>
        <Progress :inquiryStatus="latestInquiry.status" />
        <div
            class="lg:col-span-2 mt-6 flex flex-col bg-white border border-gray-300 rounded-md min-h-170 max-h-300 overflow-hidden">
            <div class="px-2 flex justify-between items-center py-4 border-b border-gray-300">
                <div class="flex items-center gap-4 px-5">
                    <div class="shrink-0">
                        <img class="h-16 w-16 object-contain border border-gray-300 rounded bg-white"
                            :src="props.warranty.product.image_url" :alt="props.warranty.product.name" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-md font-bold text-neutral-900">
                            {{ props.warranty.product.name }} Inquiry Discussion
                        </p>
                        <p class="text-sm text-neutral-500">Serial Number: {{ props.warranty.serial_number }}</p>
                    </div>
                </div>
                <div>
                    <button
                        v-if="!props.inquiry.status.includes('closed') && !props.inquiry.status.includes('resolved')"
                        @click="emit('cancel')"
                        class="mr-5 px-5 py-3 text-[15px] font-semibold bg-red-700 text-white rounded-md hover:bg-red-600">
                        Cancel Inquiry
                    </button>
                </div>
            </div>
            <Messages :inquiryId="props.inquiryId" :messages="props.messages" />
            <Reply v-if="!isDone" :inquiry-id="props.inquiryId" placeholder="Response to the technician.." />
        </div>
    </div>
</template>