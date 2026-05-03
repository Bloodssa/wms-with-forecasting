<script setup>
import EmptyState from '@/Components/EmptyState.vue'
import CustomerLayout from '@/Layouts/CustomerLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import dayjs from 'dayjs'
import { Check, ShieldX } from 'lucide-vue-next'

const props = defineProps({
    histories: {
        type: Object,
        default: () => [],
    },
})

// ui for check and expired
const getConfig = (type) => {
    switch (type) {
        case 'success':
            return {
                bgClass: 'bg-green-50 border-green-200',
                textClass: 'text-green-800',
                icon: Check,
            }

        case 'expire':
            return {
                bgClass: 'bg-red-50 border-red-200',
                textClass: 'text-red-800',
                icon: ShieldX,
            }

        default:
            return {
                bgClass: 'bg-blue-50 border-blue-200',
                textClass: 'text-blue-700',
                icon: Check,
            }
    }
}

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY')
}
</script>

<template>
    <Head title="History" />
    <CustomerLayout>
        <div>
            <div class="pb-4 mb-4">
                <h2 class="text-2xl font-bold text-gray-900">
                    Account History
                </h2>
                <p class="text-sm text-gray-500">
                    Track your product registrations and support inquiries.
                </p>
            </div>
            <div class="mx-auto">
                <div class="bg-white overflow-hidden border border-gray-300 sm:rounded-md">
                    <div class="divide-y divide-gray-300">
                        <template v-if="histories.length" v-for="history in histories" :key="history.id">
                            <div class="group relative flex items-start gap-x-4 p-4">
                                <div class="relative flex h-10 w-10 flex-none items-center justify-center rounded-full border"
                                    :class="getConfig(history.type).bgClass">
                                    <component :is="getConfig(history.type).icon" class="w-5 h-5"
                                        :class="getConfig(history.type).textClass" />
                                </div>
                                <div class="flex-auto">
                                    <div class="flex justify-between items-baseline gap-x-4">
                                        <h3 class="text-sm font-semibold text-gray-900">
                                            {{ history.title }}
                                        </h3>
                                        <time class="text-xs text-gray-400">
                                            {{ formatDate(history.date) }}
                                        </time>
                                    </div>
                                    <div class="flex justify-between items-start gap-4">
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ history.description }}
                                        </p>
                                        <Link :href="history.url"
                                            class="text-xs font-semibold text-neutral-900 hover:underline whitespace-nowrap">
                                            View More
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template v-else>
                            <EmptyState :border="false" message="No available purchase, inquiry history at the moment" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
</template>