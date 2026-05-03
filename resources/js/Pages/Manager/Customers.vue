<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import Table from '@/Components/Table/Table.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import { Head } from '@inertiajs/vue3';
import Avatar from '@/Components/Icons/Avatar.vue';
import { EllipsisVertical } from 'lucide-vue-next';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    customers: {
        type: Object,
        default: ()=> ({})
    }
});

const headers = ['Customer', 'Email', 'Total Warranties', 'Expired Warranties', 'Last Inquiry'];
</script>

<template>
    <Head title="Products" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Customers</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage all registered customers and their customer assignments.</p>
            </article>
        </section>

        <section>
            <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                <TableSearch 
                    :dropdown="true"
                    :route="route('customers')"
                />
                <Table v-if="customers.data?.length > 0" :headers="headers" :datas="customers">
                    <tr v-for="customer in customers.data" :key="customer.id">
                        <td class="table-text">
                            <div class="flex items-center space-x-2">
                                <Avatar :name="customer.name" class="h-8 w-8" />
                                <span class="font-semibold">{{ customer.name }}</span>
                            </div>
                        </td>
                        <td class="table-text">{{ customer.email }}</td>
                        <td class="table-text">{{ customer.active_warranties_count }}</td>
                        <td class="table-text">{{ customer.expired_warranties_count }}</td>
                        <td class="table-text capitalize">
                            <Badge :type="customer?.last_inquiry_status">
                                {{ customer?.last_inquiry_status ?? 'none' }}
                            </Badge>
                        </td>
                    </tr>
                </Table>
                <EmptyState v-else message="No customer found" />
            </div>
        </section>
    </ManagerLayout>
</template>