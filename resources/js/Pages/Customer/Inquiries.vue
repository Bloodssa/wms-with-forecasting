<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import EmptyState from '@/Components/EmptyState.vue';

dayjs.extend(relativeTime)

const props = defineProps({
    inquiries: {
        type: Object,
        default: () => ({}),
    },
})

const open = ref(false)
const search = ref('')
const selectedStatus = ref('')

const statuses = [
    { value: '', label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'resolved', label: 'Resolved' },
]

const formatHuman = (date) => dayjs(date).fromNow()

const hasFilters = () => search.value || selectedStatus.value
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

                <!-- Search -->
                <div class="flex-1 min-w-0">
                    <input v-model="search" type="text" placeholder="Search..."
                        class="w-full h-10 px-4 border border-gray-300 rounded-md text-sm" />
                </div>

                <!-- Filter -->
                <div class="relative">
                    <button @click="open = !open"
                        class="h-10 px-4 bg-white border border-gray-300 rounded-md text-sm font-medium w-full">
                        Filter by Status
                    </button>

                    <div v-show="open"
                        class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-md z-50 overflow-hidden shadow">
                        <button v-for="status in statuses" :key="status.value"
                            @click="selectedStatus = status.value; open = false"
                            class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                            :class="selectedStatus === status.value ? 'bg-gray-100 font-semibold' : ''">
                            {{ status.label }}
                        </button>
                    </div>
                </div>

                <!-- New Inquiry -->
                <Link href=""
                    class="h-10 px-4 bg-neutral-900 text-white rounded-md text-sm font-medium inline-flex items-center justify-center">
                    New Inquiry
                </Link>
            </div>
        </div>

        <!-- Inquiry List -->
        <template v-if="inquiries.length">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <Link v-for="inquiry in inquiries" :key="inquiry.id" href=""
                    class="group relative bg-white border border-gray-300 rounded-md cursor-pointer hover:shadow-sm transition">
                    <!-- Top -->
                    <div class="flex justify-between pt-5 px-5">
                        <h3 class="text-lg font-bold text-neutral-900 mb-1">
                            {{ inquiry.warranty.product.name }}
                        </h3>

                        <span class="px-2 py-1 text-xs rounded-full font-medium bg-gray-100 text-gray-700">
                            {{ inquiry.status }}
                        </span>
                    </div>

                    <!-- Message -->
                    <p class="text-sm text-neutral-500 line-clamp-2 px-5 mb-4">
                        {{ inquiry.message }}
                    </p>

                    <!-- Footer -->
                    <div class="flex items-center gap-3 pt-4 border-t p-5 border-gray-300">
                        <img :src="inquiry.warranty.product.product_image_url" :alt="inquiry.warranty.product.name"
                            class="w-14 h-14 rounded-md object-cover border border-neutral-200" />

                        <div class="flex-1">
                            <p class="text-xs font-bold text-neutral-900">
                                {{ inquiry.warranty.product.brand }} -
                                {{ inquiry.warranty.product.category.name }}
                            </p>

                            <p class="text-xs text-neutral-500">
                                Warranty: {{ inquiry.warranty.status }}
                            </p>

                            <p class="text-xs text-neutral-500 font-semibold">
                                Updated {{ formatHuman(inquiry.updated_at) }}
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

        <!-- Empty State -->
        <template v-else>
            <div class="bg-white border border-gray-300 rounded-md">
                <EmptyState :border="false" :message="inquiries.length ? 'No inquiries found' : 'No inquiries at the moment'"/>
            </div>
        </template>
    </CustomerLayout>
</template>
