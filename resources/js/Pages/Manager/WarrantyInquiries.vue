<script setup>
import { Head, Link } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import Table from '@/Components/Table/Table.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    warrantyInquiries: {
        type: Object,
        default: ()=> ({})
    },
    select: {
        type: Object,
        default: () => ({})
    }
});

const headers = ['Customer', 'Product / Issue', 'Serial Number', 'Status', 'Submitted'];
</script>

<template>
    <Head title="Inquiries" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Warranty Inquiries</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Review and manage technical support and repair requests from customers.</p>
            </article>
        </section>

        <section>
            <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                <TableSearch 
                    :select="select"
                    :route="route('warranty-inquiries')"
                />
                <Table v-if="warrantyInquiries.data?.length > 0" :headers="headers" :datas="warrantyInquiries" :action="true">
                    <tr v-for="inquiry in warrantyInquiries.data" :key="inquiry.id">
                        <td class="table-text">
                            <div class="flex items-center space-x-2">
                                <Avatar class="h-8 w-8" :name="inquiry.user.name" />
                                <div>
                                    <p class="font-semibold">{{ inquiry.user.name }}</p>
                                    <p class="font-normal text-xs text-neutral-500">{{ inquiry.user.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="table-text">
                            <div class="text-sm">
                                <p class="font-semibold text-neutral-900 text-xs">
                                    {{ inquiry.warranty.product.name }}
                                </p>
                                <p class="text-neutral-500 truncate max-w-xs">"{{ inquiry.message }}"</p>
                            </div>
                        </td>
                        <td class="table-text">
                            {{ inquiry.warranty.serial_number }}
                        </td>
                        <td class="whitespace-nowrap text-sm text-center text-neutral-900">
                            <Badge :type="inquiry.status">
                                {{ inquiry.status }}
                            </Badge>
                        </td>
                        <td class="table-text">
                            <span>
                                {{ inquiry.submitted_at }}
                            </span>
                        </td>
                        <td class="table-text text-right">
                            <Link :href="route('inquiry-action', { id: inquiry.id })"
                                class="px-4 py-2 text-white bg-neutral-900 rounded-md font-semibold">Respond</Link>
                        </td>
                    </tr>
                </Table>
                <EmptyState v-else :message="warrantyInquiries.data?.length
                    ? 'No Customer warranty inquiry found.'
                    : 'There are no customer warranty inquiry at the moment.'" />
            </div>
        </section>
    </ManagerLayout>
</template>