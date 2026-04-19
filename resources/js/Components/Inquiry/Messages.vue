<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import Avatar from '../Icons/Avatar.vue';

// init days js for diffHuman like carbon in laravel
dayjs.extend(relativeTime);

const props = defineProps({
    messages: Array,
    inquiryId: Number
});

// broadcast init
const allMessages = ref([...props.messages]);

// sync the props if failed in echo
watch(() => props.messages, (newVal) => {
    allMessages.value = newVal;
}, { deep: true });

onMounted(() => { 
    window.Echo.private(`inquiry.${props.inquiryId}`)
        .listen('.ResponseSent', (e) => {
            // check message already exists to prevent duplicates
            const exists = allMessages.value.some(m => m.id === e.response.id);
            if (!exists) {
                allMessages.value.push(e.response);
                // scrollToBottom();
            }
        })
        .listen('.InquiryUpdated', (e) => { // If you have a status update event
            allMessages.value.push(e.update);
            scrollToBottom();
        });
});

// remove chanell
onUnmounted(() => {
    window.Echo.leave(`inquiry.${props.inquiryId}`);
});

const currentTime = ref(dayjs());
let timer = null;

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = dayjs();
    }, 60000); // update time display every 1 minute
});

// remove interval if remve the element in #app
onUnmounted(() => {
    clearInterval(timer);
});

const page = usePage();
const authUser = page.props.auth.user;
const chatContainer = ref(null);

// who is auth then put the message of the auth if its true
const isMe = (msg) => {
    return msg.type === 'message' && msg.user?.id === authUser.id;
};

// format date with dayjs()
const formatTimestamp = (date) => {
    if (!date) return '';
    const d = dayjs(date);
    
    return d.isAfter(currentTime.value.subtract(1, 'day')) 
        ? d.fromNow() 
        : d.format('MMMM D, YYYY, h:mm a');
};

// auto scroll in bottom in msgs
const scrollToBottom = async () => {
    await nextTick();
    if (chatContainer.value) {
        chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
    }
};

onMounted(scrollToBottom);
watch(() => props.messages, scrollToBottom, { deep: true });
</script>

<template>
    <div 
        ref="chatContainer"
        class="flex-1 overflow-y-auto p-5 space-y-4 bg-white scroll-smooth"
    >
        <div v-for="msg in allMessages" :key="msg.id">
            
            <div v-if="msg.type === 'updates'" class="relative flex items-center justify-center my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative bg-white px-4 flex flex-col items-center text-center">
                    <span class="text-sm font-semibold text-neutral-500">{{ msg.message }}</span>
                    <span class="text-xs text-neutral-500 mt-0.5">
                        {{ dayjs(msg.created_at).format('MMM D, h:mm A') }}
                    </span>
                </div>
            </div>

            <div v-else-if="msg.type === 'solution'" class="my-10 flex justify-center px-4">
                <div class="max-w-md w-full border border-gray-300 rounded-md p-6 bg-white shadow-sm">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-neutral-900 rounded-md text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-neutral-900">Inquiry {{ msg.status_label || msg.status }}</h3>
                            <p class="text-xs text-neutral-500">{{ dayjs(msg.created_at).format('MMMM D, YYYY') }}</p>
                        </div>
                    </div>
                    <div class="text-sm text-neutral-900 font-medium italic">
                        "{{ msg.message }}"
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-300 flex justify-between items-center">
                        <span class="text-sm font-semibold text-neutral-500">{{ msg.user?.name }}</span>
                        <span class="px-2 py-1 text-xs font-bold rounded bg-green-100 text-green-800 uppercase">
                            {{ msg.status_label || msg.status }}
                        </span>
                    </div>
                </div>
            </div>

            <div v-else :class="['flex', isMe(msg) ? 'justify-end' : 'justify-start']">
                <div :class="['flex items-end gap-2', isMe(msg) ? 'flex-row-reverse' : 'items-start']">
                    
                    <div v-if="!isMe(msg)" class="shrink-0">
                        <Avatar :name="msg.user?.name?.charAt(0) ?? 'S'" class="h-8 w-8" />
                    </div>

                    <div :class="['flex flex-col', isMe(msg) ? 'items-end' : 'items-start']">
                        <div :class="[
                            'p-3 rounded-2xl text-sm max-w-md wrap-break-word shadow-sm',
                            isMe(msg) ? 'bg-neutral-900 text-white rounded-br-none' : 'bg-gray-100 text-neutral-900 rounded-bl-none'
                        ]">
                            {{ msg.message }}
                        </div>

                        <div v-if="msg.attachments?.length" 
                             :class="['flex flex-wrap gap-1 mt-2', isMe(msg) ? 'justify-end' : 'justify-start']"
                        >
                            <a v-for="(path, index) in msg.attachments" 
                               :key="index" 
                               :href="`/storage/${path}`" 
                               target="_blank"
                            >
                                <img :src="`/storage/${path}`"
                                     class="h-24 w-24 object-cover rounded-md border border-gray-200 hover:opacity-80 transition"
                                />
                            </a>
                        </div>

                        <span class="text-[11px] tracking-wide text-neutral-500 mt-1 px-1">
                            {{ formatTimestamp(msg.created_at) }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>