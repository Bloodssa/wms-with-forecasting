<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { ShieldCheck, Users, MessageSquare, MailWarning } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import WarrantyChart from '@/Components/Charts/WarrantyChart.vue';
import EmptyState from '@/Components/EmptyState.vue';
import Table from '@/Components/Table/Table.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Badge from '@/Components/Badge.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    chart: {
        type: Object,
        required: true
    },
    mostReportedProducts: {
        type: Array,
        default: []
    },
    latestInquiries: {
        type: Array,
        default: []
    },
    pendingInquiries: {
        type: Array,
        default: []
    }
});

// get copy of the data of the stats for the card and add a title, icons...
const summaryStats = computed(() => [
    {
        title: 'Active Warranties',
        count: props.stats.activeWarranty,
        icon: ShieldCheck,
        href: route('warranties')
    },
    {
        title: 'Total Customers',
        count: props.stats.totalCustomer,
        icon: Users,
        href: route('customers')
    },
    {
        title: 'Open Inquiries',
        count: props.stats.openInquiries,
        icon: MessageSquare,
        href: route('warranty-inquiries')
    },
    {
        title: 'Unread Messages',
        count: props.stats.unreadMessages,
        icon: MailWarning,
        href: route('warranty-inquiries')
    }
]);
</script>

<template>
    <Head title="Dashboard" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">{{ $page.props.auth.user.role
                    }} Dashboard</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Monitor warranties, manage customers, and respond to
                    repair inquiries in one place</p>
            </article>
        </section>
        <!-- Cards -->
        <section class="mt-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 w-full">
                <Card v-for="stat in summaryStats" :key="stat.title" :title="stat.title" :count="stat.count"
                    :icon="stat.icon" :href="stat.href" />
            </div>
        </section>

        <!-- Chart -->
        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 grow overflow-hidden rounded-md border border-gray-300 bg-white p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-900">
                        Warranty Status
                    </h3>
                </div>
                <div class="max-w-full overflow-x-auto custom-scrollbar">
                    <div class="min-w-125 xl:min-w-full">
                        <WarrantyChart :chart="chart" />
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-300 rounded-md">
                <div class="px-5 py-4 border-b border-gray-300">
                    <h1 class="text-neutral-900 font-semibold text-base">
                        Most Reported Products
                    </h1>
                </div>
                <div class="divide-y divide-gray-300">
                    <template v-if="mostReportedProducts.length > 0" v-for="reportedProducts in mostReportedProducts" :key="reportedProducts.id">
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 w-full min-w-0">
                                <div class="w-12 h-12 border border-gray-300 rounded-md overflow-hidden shrink-0">
                                    <img :src="`/storage/${reportedProducts.product_image_url}`"
                                        :alt="reportedProducts.name" class="w-full h-full object-cover" />
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-neutral-900 text-base font-semibold leading-tight truncate">
                                        {{ reportedProducts.name }}
                                    </h3>
                                    <p class="text-neutral-500 text-xs mt-0.5">
                                        {{ reportedProducts.category_name }}
                                    </p>
                                </div>
                            </div>
                            <div class="pl-6 flex flex-col items-end justify-center text-right">
                                <span class="text-neutral-500 text-[13px] font-semibold">
                                    Reports
                                </span>
                                <span class="text-neutral-900 font-bold text-sm tabular-nums">
                                    {{ reportedProducts.total_inquiries }}
                                </span>
                            </div>
                        </div>
                    </template>
                    <div v-else>
                        <EmptyState message="There is no pending inquiries at the moment" />
                    </div>
                </div>
            </div>
        </section>

        <section>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                    <div class="px-5 py-4">
                        <h1 class="text-neutral-900 font-semibold text-base">
                            Latest Warranty Inquiries
                        </h1>
                    </div>
                    <div class="overflow-x-auto">
                        <Table v-if="latestInquiries.length > 0" :data="latestInquiries"
                            :headers="['Customer', 'Product', 'Status']">
                            <tr v-for="latest in latestInquiries" :key="latest.id">
                                <td class="table-text">
                                    <div class="flex items-center space-x-2">
                                        <Avatar :name="latest.user.name" class="h-8 w-8" />
                                        <span class="font-semibold">{{ latest.user.name }}</span>
                                    </div>
                                </td>
                                <td class="table-text">
                                    {{ latest.warranty.product.name }}
                                </td>
                                <td class="table-text">
                                    <Badge :type="latest.status">
                                        {{ latest.status }}
                                    </Badge>
                                </td>
                            </tr>
                        </Table>
                        <EmptyState v-else message="There is no latest inquiries at the moment" />
                    </div>
                </div>
                <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                    <div class="px-5 py-4">
                        <h1 class="text-neutral-900 font-semibold text-base">
                            Pending Inquiry Requests
                        </h1>
                    </div>
                    <div class="overflow-x-auto">
                        <Table v-if="pendingInquiries.length > 0" :data="pendingInquiries"
                            :headers="['Customer', 'Product', 'Inquire Date']">
                            <tr v-for="pending in pendingInquiries" :key="pending.id">
                                <td class="table-text">
                                    <div class="flex items-center space-x-2">
                                        <Avatar :name="pending.name" class="h-8 w-8" />
                                        <span class="font-semibold">{{ pending.name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                    {{ pending.product }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                    {{ pending.inquiry_date }}
                                </td>
                            </tr>
                        </Table>
                        <EmptyState v-else message="There is no pending inquiries at the moment" />
                    </div>
                </div>
            </div>
        </section>
    </ManagerLayout>
</template>