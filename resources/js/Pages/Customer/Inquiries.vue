<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Search } from 'lucide-vue-next';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import EmptyState from '@/Components/EmptyState.vue';
import Badge from '@/Components/Badge.vue';
import TextInput from '@/Components/Forms/TextInput.vue';

dayjs.extend(relativeTime);

const props = defineProps({
    inquiries: {
        type: Object,
        default: () => ({}),
    },
    filters: Object,
    select: {
        type: [Object, Array],
        default: () => []
    }
});

const open = ref(false);
const search = ref(props.filters?.search || '');
const selectedStatus = ref(props.filters?.status || '');

const now = ref(Date.now());
let interval;

onMounted(() => {
    interval = setInterval(() => {
        now.value = Date.now();
    }, 60000);
});

onUnmounted(() => {
    clearInterval(interval);
});

const formatTimestamp = (date) => {
    const update = now.value; // force update date

    return dayjs(date).fromNow();
};

// watch search input and req based on the value
watch([search, selectedStatus], ([newSearch, newStatus]) => {
    router.get(route('inquiries'),
        {
            search: newSearch,
            status: newStatus
        }, {
        preserveState: true,
        replace: true
    });
});
</script>

<template>

    <Head title="Inquiries" />

    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">
                    Support Inquiries
                </h1>
                <p class="text-sm text-neutral-500">
                    Track and manage your warranty claims.
                </p>
            </div>

            <div class="flex flex-col md:flex-row gap-2 w-full md:max-w-2xl items-stretch md:items-center">
                <div class="relative flex-1 w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <TextInput v-model="search" type="text" placeholder="Search..."
                        class="w-full h-10 pl-10 pr-3 border border-gray-300 rounded-md text-sm focus:border-neutral-900" />
                </div>

                <div class="relative">
                    <select v-model="selectedStatus" class="h-10 pl-4 pr-10 bg-white border border-gray-300 rounded-md text-sm font-medium w-full md:w-48 focus:ring-1 focus:ring-neutral-900 focus:border-neutral-900 appearance-none cursor-pointer">
                        <option value="">All Statuses</option>
                        <option v-for="(label, value) in props.select" :key="value" :value="value">
                            {{ label }}
                        </option>
                    </select>
                </div>

                <Link href=""
                    class="h-10 px-4 bg-neutral-900 text-white rounded-md text-sm font-medium inline-flex items-center justify-center">
                    New Inquiry
                </Link>
            </div>
        </div>

        <!-- Inquiry List -->
        <template v-if="inquiries.data.length">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Link :href="route('inquiry.show', { id: inquiry.id })" v-for="inquiry in inquiries.data"
                    :key="inquiry.id" href=""
                    class="group relative bg-white border border-gray-300 hover:border-2 hover:border-neutral-900 rounded-md cursor-pointer transition">
                    <!-- Top -->
                    <div class="flex justify-between pt-5 px-5">
                        <h3 class="text-lg font-bold text-neutral-900 mb-1">
                            {{ inquiry.warranty.product.name }}
                        </h3>

                        <span class="px-2 py-1 text-xs">
                            <Badge :type="inquiry.status">
                                {{ inquiry.status }}
                            </Badge>
                        </span>
                    </div>

                    <!-- Message -->
                    <p class="text-sm text-neutral-500 line-clamp-2 px-5 mb-4">
                        {{ inquiry.message }}
                    </p>

                    <!-- Footer -->
                    <div class="flex items-center gap-3 pt-4 border-t p-5 border-gray-300">
                        <img :src="inquiry.warranty.product.image_url" :alt="inquiry.warranty.product.name"
                            class="w-14 h-14 rounded-md object-cover border border-neutral-200" />

                        <div class="flex-1">
                            <p class="text-xs font-bold text-neutral-900">
                                {{ inquiry.warranty.product.brand }} -
                                {{ inquiry.warranty.product.category.name }}
                            </p>

                            <p class="text-xs capitalize text-neutral-500">
                                Warranty: {{ inquiry.warranty.status }}
                            </p>

                            <p class="text-xs text-neutral-500 font-semibold">
                                Updated {{ formatTimestamp(inquiry.updated_at) }}
                            </p>
                        </div>

                        <div class="text-right text-neutral-900">
                            <p class="text-sm font-semibold">
                                Serial Number
                            </p>
                            <p class="text-sm">
                                {{ inquiry.warranty.serial_number }}
                            </p>
                        </div>
                    </div>
                </Link>
            </div>
        </template>
        <template v-else>
            <div class="bg-white border border-gray-300 rounded-md">
                <EmptyState :border="false"
                    :message="inquiries.data.length ? 'No inquiries found' : 'No inquiries at the moment'" />
            </div>
        </template>
    </CustomerLayout>
</template>
