<script setup>
import Product from '@/Components/Product.vue';
import Table from '@/Components/Table/Table.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, usePoll, Link, router, usePage } from '@inertiajs/vue3';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';
import EmptyState from '@/Components/EmptyState.vue';
import { computed, watch, onMounted } from 'vue';
import { ArrowLeftToLine, Eye, Star } from 'lucide-vue-next';

const props = defineProps({
    warranty: {
        type: Object,
        default: () => ({})
    },
    history: {
        type: Array,
        default: []
    },
    latestInquiry: Object,
    activeInquiry: Object,
    review: Object,
    isExpired: Boolean
});

const headers = ['Date', 'Issue', 'Action Taken', 'Status'];

const hasActiveInquiry = computed(() => !!props.activeInquiry);
const hasReview = computed(() => !!props.review);

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY')
}

const limitText = (text, limit = 20) => {
    if (!text) return ''
    return text.length > limit
        ? text.substring(0, limit) + '...'
        : text
}

const formatLabel = (text) => {
    return text
        .replace(/-/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase())
}

// note use polling if broadcast host

// poll every five seconds
// usePoll(5000, {
//     only: ['messages', 'inquiry'],
//     preserveScroll: true,
// });

// if active tab is press and unread count is > 0 update all to read
// const page = usePage();

// const activeTab = computed(() => {
//     return page.props.tab || 'records';
// });

// const setTab = (value) => {
//     router.get(route('warranty.show', props.warranty.id), {
//         tab: value,
//     }, {
//         preserveState: true,
//         replace: true,
//         onSuccess: () => {
//             if (value === 'inquiry') {
//                 router.post(route('inquiry.mark-read', props.id), {}, {
//                     preserveScroll: true,
//                     preserveState: true,
//                     onSuccess: () => {
//                         router.reload({ only: ['unreadCount'] }); // only reload the count
//                     }
//                 });
//             }
//         }
//     });
// };

// onMounted(() => {
//     const saved = localStorage.getItem('warranty_tab')
//     if (saved && saved !== activeTab.value) {
//         activeTab.value = saved
//     }
// });

</script>

<template>

    <Head title="Warranty" />
    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex gap-2 items-center">
                <div class="flex items-center gap-4">
                    <Link :href="route('warranty')"
                        class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
                        <ArrowLeftToLine class="w-5 h-5 text-neutral-900" />
                    </Link>
                </div>
                <div>
                    <h1 class="font-bold text-neutral-900 text-2xl">Warranty Management</h1>
                    <p class="text-sm text-neutral-500">Manage your product coverage and support tickets.</p>
                </div>
            </div>

            <div class="inline-flex w-full md:w-auto items-center space-x-2 mt-5">
                <template v-if="!props.isExpired">
                    <template v-if="!hasActiveInquiry">
                        <Link :href="route('create-inquiry', props.warranty.id)"
                            class="flex items-center gap-2 px-4 py-2 text-md font-bold text-white bg-neutral-900 hover:bg-neutral-700 transition-colors duration-150 rounded-md">
                            Make An Inquiry
                        </Link>
                    </template>
                    <template v-else>
                        <Link :href="route('inquiry.show', props.activeInquiry.id)"
                            class="flex items-center gap-2 px-4 py-2 text-md font-bold text-white bg-neutral-900 hover:bg-neutral-700 transition-colors duration-150 rounded-md">
                            View Inquiry <span class="capitalize">({{ props.activeInquiry.status }})</span>
                        </Link>
                    </template>
                </template>
                <Link v-if="!hasReview" :href="route('product-reviews', props.warranty.product_id)"
                    class="flex items-center gap-2 px-4 py-2 text-md font-bold text-white bg-neutral-900 hover:bg-neutral-700 transition-colors duration-150 rounded-md">
                    <Star class="text-white fill-white h-5 w-5" />
                    <span>Rate Product</span>
                </Link>
                <Link v-else :href="route('product-reviews', props.review.id)"
                    class="flex items-center gap-2 px-4 py-2 text-md font-bold text-white bg-neutral-900 hover:bg-neutral-700 transition-colors duration-150 rounded-md">
                    <Star class="text-white fill-white h-5 w-5" />
                    <span>Edit Your Review</span>
                </Link>
            </div>
        </div>
        <!-- Product -->
        <div>
            <Product :warranty="props.warranty" />
            <h1 class="font-semibold text-neutral-900 text-xl mt-8">Repair and Services History</h1>
            <div class="w-full rounded-md border border-gray-300 overflow-y-auto bg-white mt-2">
                <Table v-if="props.history.length" :action="true" :borderTop="false" :headers="headers">
                    <tr v-for="history in props.history">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                            {{ formatDate(history.created_at) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                            {{ limitText(history.message, 15) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                            {{
                                history.resolved_message
                                    ? limitText(history.resolved_message, 40)
                                    : 'The inquiry is currently in review'
                            }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                            <Badge :type="history.status" size="sm">
                                {{ formatLabel(history.status) }}
                            </Badge>
                        </td>
                        <td class="flex justify-end px-5 py-2">
                            <Link :href="route('inquiry.show', history.id)"
                                class="p-2 border border-gray-300 rounded-md bg-white hover:bg-gray-200 transition duration-150">
                                <Eye class="hover:text-neutral-700" />
                            </Link>
                        </td>
                    </tr>
                </Table>
                <template v-else>
                    <EmptyState :border="false" message="There is no history of this warranty at the moment" />
                </template>
            </div>
        </div>
    </CustomerLayout>
</template>
