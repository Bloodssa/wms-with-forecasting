<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import Table from '@/Components/Table/Table.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Badge from '@/Components/Badge.vue';
import { Eye, SearchAlert } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    warranties: {
        type: Object,
        default: () => ({})
    },
    select: {
        type: Object,
        default: () => ({})
    }
});

// table headers
const headers = ['Products', 'Customer', 'Serial Number', 'Status', 'Purchase Date', 'Warranty Duration'];

// format date for purchased date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    })
};

// check if its expired
const isExpired = (date) => {
    return new Date(date) < new Date();
};

// format for days left
const daysLeft = (date) => {
    const now = new Date();
    const expiry = new Date(date);

    const diffTime = expiry - now;

    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
</script>

<template>

    <Head title="Warranties" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Customer Warranties</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">View all active warranties, their customers, and
                    status details.</p>
            </article>
        </section>

        <section>
            <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                <TableSearch :select="select" :route="route('warranties')" />
                <Table v-if="warranties.data?.length > 0" :headers="headers" :datas="warranties" :action="true">
                    <tr v-for="warranty in warranties.data" :key="warranty.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="h-10 w-10 shrink-0">
                                    <img class="h-10 w-10 rounded-md object-cover border border-gray-200"
                                        :src="warranty.product?.image_url" />
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ warranty.product.name }}</div>
                            </div>
                        </td>
                        <td class="table-text">
                            <div class="flex items-center space-x-2">
                                <div v-if="warranty.is_claimed">
                                    <Avatar class="h-8 w-8" :name="warranty.user.name" />
                                </div>
                                <div v-else
                                    class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 bg-neutral-50 shrink-0">
                                    <SearchAlert class="w-4 h-4 text-neutral-900" />
                                </div>
                                <span>{{ warranty.user?.name ?? warranty.claim_email }}</span>
                            </div>
                        </td>
                        <td class="table-text">
                            {{ warranty.serial_number }}
                        </td>
                        <td class="table-text">
                            <Badge :type="warranty.status">
                                {{ warranty.status }}
                            </Badge>
                        </td>
                        <td class="table-text">
                            {{ formatDate(warranty.purchase_date) }}
                        </td>
                        <td class="table-text">
                            <div v-if="isExpired(warranty.expiry_date)">
                                <p class="text-[12px] tracking-wide text-fg-danger-strong font-medium">Expired</p>
                                <p class="text-sm font-semibold text-fg-danger-strong whitespace-nowrap">
                                    {{ formatDate(warranty.expiry_date) }}
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-[12px] tracking-wide text-neutral-500 font-medium">Expires In</p>
                                <p class="text-sm font-semibold text-neutral-900 whitespace-nowrap">
                                    {{ daysLeft(warranty.expiry_date) }} days
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                            <div class="flex justify-end">
                                <Link :href="route('view-warranty', warranty.id)"
                                    class="p-2 border border-gray-300 rounded-md bg-white hover:bg-gray-200 transition duration-150">
                                    <Eye class="hover:text-neutral-700" />
                                </Link>
                            </div>
                        </td>
                    </tr>
                </Table>
                <EmptyState v-else :message="warranties.data?.length
                    ? 'No Customer warranties found.'
                    : 'There are no customer warranties at the moment.'" />
            </div>
        </section>
    </ManagerLayout>
</template>