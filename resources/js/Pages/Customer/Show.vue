<script setup>
import Product from '@/Components/Product.vue';
import Table from '@/Components/Table/Table.vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head , usePoll} from '@inertiajs/vue3';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';
import EmptyState from '@/Components/EmptyState.vue';
import { ref, watch, onMounted } from 'vue';
import Discussion from '@/Components/Discussion.vue';

const props = defineProps({
    warranty: {
        type: Object,
        default: () => ({})
    },
    history: {
        type: Array,
        default: []
    },
    id: {
        type: Number,
        default: null
    },
    messages: {
        type: Array,
        default: []
    },
    containsInquiries: {
        type: Boolean,
        required: true
    }
});

const headers = ['Date', 'Issue', 'Action Taken', 'Status'];

const activeTab = ref('records');

onMounted(() => {
    const saved = localStorage.getItem('warranty_tab')
    if (saved) {
        activeTab.value = saved
    }
});

watch(activeTab, (value) => {
    localStorage.setItem('warranty_tab', value)
});

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
</script>

<template>

    <Head title="Warranty" />
    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="font-semibold text-neutral-900 text-2xl">Warranty Management</h1>
                <p class="text-sm text-neutral-500">Manage your product coverage and support tickets.</p>
            </div>

            <div
                class="inline-flex w-full md:w-auto items-center p-1 space-x-2 bg-white border border-gray-300 rounded-md">
                <button @click="activeTab = 'records'"
                    :class="activeTab === 'records' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                    class="flex-1 font-semibold md:flex-none text-center px-4 py-2 rounded-md">
                    Warranty & History
                </button>
                <button @click="activeTab = 'inquiry'"
                    :class="activeTab === 'inquiry' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                    class="flex-1 font-semibold md:flex-none text-center px-4 py-2 rounded-md">
                    Support Inquiries
                </button>
            </div>
        </div>
        <!-- Product -->
        <template v-if="activeTab === 'records'">
            <div>
                <Product :warranty="props.warranty" />
                <h1 class="font-semibold text-neutral-900 text-xl mt-8">Repair and Services History</h1>
                <div class="w-full rounded-md border border-gray-300 overflow-y-auto bg-white mt-2">
                    <Table v-if="props.history.length" :borderTop="false" :headers="headers">
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-neutral-900">
                                <Badge :type="history.status" size="sm">
                                    {{ formatLabel(history.status) }}
                                </Badge>
                            </td>
                        </tr>
                    </Table>
                    <template v-else>
                        <EmptyState :border="false" message="There is no history of this warranty at the moment" />
                    </template>
                </div>
            </div>
        </template>
        <template v-if="activeTab === 'inquiry'">
            <Discussion :inquiryId="props?.id" :messages="props.messages" :containsInquiries="props.containsInquiries"
                :warranty="props.warranty" />
        </template>
    </CustomerLayout>
</template>
