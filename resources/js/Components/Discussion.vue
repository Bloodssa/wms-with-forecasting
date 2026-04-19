<script setup>
import { computed } from 'vue';
import Inquire from './Inquiry/Inquire.vue';
import Progress from './Inquiry/Progress.vue';
import Messages from './Inquiry/Messages.vue';
import Reply from './Inquiry/Reply.vue';

const props = defineProps({
    inquiryId: Number,
    warranty: {
        type: Object,
        default: () => ({})
    },
    containsInquiries: {
        type: Boolean,
        required: true
    },
    messages: Array
});

// get the latest inquiry
const latestInquiry = computed(() => {
    return props.warranty?.inquiries?.at(-1) || null;
});
</script>

<template>
    <template v-if="props.containsInquiries && latestInquiry">
        <div class="mt-6">
            <Progress :inquiryStatus="latestInquiry.status" />
            <div
                class="lg:col-span-2 mt-6 flex flex-col bg-white border border-gray-300 rounded-md min-h-170 max-h-300 overflow-hidden">
                <div class="px-2 py-4 border-b border-gray-300">
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
                </div>
                <Messages :inquiryId="props.inquiryId" :messages="props.messages" />
                <Reply :inquiry-id="props.inquiryId" placeholder="Response to the technician.." />
            </div>
        </div>
    </template>
    <template v-else>
        <Inquire :warranty="warranty" />
    </template>
</template>