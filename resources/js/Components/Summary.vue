<script setup>
import { Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import EmptyState from './EmptyState.vue';

dayjs.extend(relativeTime)

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    expiring: {
        type: Boolean,
        default: false,
    },
    isEmpty: {
        type: Boolean,
        default: true,
    },
    products: {
        type: Array,
        default: () => [],
    },
    emptyTitle: {
        type: String,
        default: 'No recently purchased product',
    },
})

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY')
}

const daysLeft = (date) => {
    return dayjs(date).diff(dayjs(), 'day')
}
</script>

<template>
    <div class="border border-gray-300 bg-white rounded-md flex flex-col">
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-300">
            <h1 class="text-neutral-900 font-semibold text-md">
                {{ title }}
            </h1>
        </div>

        <!-- Content -->
        <div :class="[
            'divide-y divide-gray-300',
            isEmpty && products.length === 0
                ? 'py-10 flex flex-col items-center justify-center'
                : ''
        ]">
            <template v-if="products.length > 0">
                <div v-for="product in products" :key="product.id" class="flex flex-row p-3">
                    <!-- Image -->
                    <div class="w-16 h-16 bg-white border border-gray-100 rounded-md overflow-hidden shrink-0">
                        <img :src="product.product.image_url" :alt="product.product.name"
                            class="w-full h-full object-cover" />
                    </div>

                    <!-- Info -->
                    <div class="flex-1 ml-4 flex justify-between items-center">
                        <div>
                            <h1 class="text-neutral-900 text-md font-semibold">
                                {{ product.product.name }}
                            </h1>

                            <p class="text-neutral-500 text-sm">
                                Purchased:
                                {{ formatDate(product.purchase_date) }}
                            </p>
                        </div>

                        <!-- Expiring Mode -->
                        <template v-if="expiring">
                            <div class="text-right">
                                <template v-if="product.status === 'near_expiry'">
                                    <h1 class="text-neutral-900 text-xs">
                                        Expires in
                                    </h1>

                                    <p class="text-neutral-900 text-lg font-bold">
                                        {{ daysLeft(product.expiry_date) }} Days
                                    </p>
                                </template>

                                <template v-else>
                                    <h1 class="text-red-500 text-xl font-semibold">
                                        Expired
                                    </h1>
                                </template>
                            </div>
                        </template>

                        <!-- Normal Mode -->
                        <template v-else>
                            <Link href=""
                                class="text-sm text-neutral-900 hover:underline font-medium">
                                Details
                            </Link>
                        </template>
                    </div>
                </div>
            </template>

            <!-- Empty State -->
            <template v-else>
                <EmptyState :message="emptyTitle" />
            </template>
        </div>
    </div>
</template>