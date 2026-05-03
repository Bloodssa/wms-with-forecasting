<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { MegaphoneOff, Bell, ShieldAlert, MessageSquareQuote, Star, ClipboardCheck, CircleAlert } from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

// props and emits to close if other modal is open
const props = defineProps({
    isOpen: Boolean,
    notifications: Array
});

const emit = defineEmits(['toggle', 'close']);
const closeNotification = () => {
    emit('close');
};
const page = usePage();
const notificationRef = ref(null);

const handleClickOutside = (event) => {
    if (notificationRef.value && !notificationRef.value.contains(event.target)) {
        closeNotification(); // tell parent component to close modal
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));

const allNotifications = computed(() => {
    return props.notifications || page.props.notifications || [];
});

const getIcon = (type) => {
    switch (type) {
        case 'danger': return ShieldAlert;
        case 'info': return MessageSquareQuote;
        case 'warning': return Star;
        case 'primary': return ClipboardCheck;
        default: return CircleAlert;
    }
};

const getIconClass = (type) => {
    switch (type) {
        case 'danger': return 'text-red-600 bg-red-50';
        case 'info': return 'text-blue-600 bg-blue-50';
        case 'warning': return 'text-amber-500 bg-amber-50';
        case 'primary': return 'text-emerald-600 bg-emerald-50';
        default: return 'text-gray-600 bg-gray-50';
    }
};
</script>

<template>
    <div class="relative inline-block text-left" ref="notificationRef">
        <button @click.stop="$emit('toggle')"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 hover:bg-gray-100 transition-colors duration-150">
            <Bell class="h-6 w-6" />
        </button>

        <!-- Dropdown Menu -->
        <div v-if="isOpen"
            class="absolute -right-8 sm:right-0 mt-2 w-72 min-h-50 md:w-95 origin-top-right rounded-md border border-gray-300 bg-white z-50 overflow-hidden">
            <div class="flex items-center justify-between border-b border-gray-300 px-4 py-3 bg-white">
                <h3 class="text-md font-semibold text-neutral-900">Notifications</h3>
            </div>

            <div class="max-h-100 overflow-y-auto py-2">
                <div v-if="allNotifications.length" class="divide-y divide-gray-300">
                    <Link v-for="(notif, index) in allNotifications" :key="index" :href="notif.link"
                        @click="closeNotification" class="block py-3 px-4 hover:bg-gray-50 transition-colors">
                        <div class="flex gap-3 items-start">
                            <div :class="['p-2 rounded-full shrink-0', getIconClass(notif.type)]">
                                <component :is="getIcon(notif.type)" class="w-4 h-4" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-neutral-900 leading-snug">
                                    {{ notif.message }}
                                </p>
                                <p class="text-[11px] mt-1 font-medium text-gray-400">
                                    {{ dayjs(notif.date).fromNow() }}
                                </p>
                            </div>
                        </div>
                    </Link>
                </div>
                <div v-else class="flex flex-col items-center justify-center py-12 px-4">
                    <div class="mb-4 rounded-full bg-gray-50 p-4 border border-gray-100">
                        <MegaphoneOff class="h-8 w-8 text-gray-400" />
                    </div>

                    <div class="text-center">
                        <p class="mt-1 text-sm text-gray-500">
                            You don't have any new notifications right now.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>