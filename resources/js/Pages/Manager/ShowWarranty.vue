<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { ArrowLeftToLine, Eye } from 'lucide-vue-next';
import Product from '@/Components/Product.vue';
import Badge from '@/Components/Badge.vue';
import dayjs from 'dayjs';
import Table from '@/Components/Table/Table.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    warranty: {
        type: Object,
        default: () => ({})
    },
    history: {
        type: Object,
        default: () => ({})
    }
});

const headers = ['Date', 'Issue', 'Action Taken', 'Status'];

// if user id is null use the claim email
const ownerName = computed(() => {
    if (props.warranty?.user) {
        return props.warranty?.user?.name;
    }
    return props.warranty?.claim_email || 'Unclaimed Warranty';
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
</script>

<template>

    <Head title="Warranties" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex gap-2 items-center">
                    <div class="flex items-center gap-4">
                        <Link :href="route('warranties')"
                            class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
                            <ArrowLeftToLine class="w-5 h-5 text-neutral-900" />
                        </Link>
                    </div>
                    <div>
                        <h1 class="font-bold text-neutral-900 text-2xl">Customer Warranty</h1>
                        <p class="text-sm text-neutral-500">Owner: <span class="text-neutral-900 font-semibold">{{ ownerName }}</span></p>
                        <p v-if="!props.warranty.user_id" class="text-sm text-neutral-900 font-semibold">Unclaimed</p>
                    </div>
                </div>
            </div>
        </section>
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
                                {{ history.resolved_message ? limitText(history.resolved_message, 40) : 'The inquiry is currently in review' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                <Badge :type="history.status" size="sm">
                                    {{ formatLabel(history.status) }}
                                </Badge>
                            </td>
                            <td class="flex justify-end px-5 py-2">
                                <Link :href="route('inquiry-action', history.id)"
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
    </ManagerLayout>
</template>