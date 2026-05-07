<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Download } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import { ShieldCheck, LaptopMinimalCheck, MessageSquareShare } from 'lucide-vue-next';
import InquiryChart from '@/Components/Charts/InquiryChart.vue';
import ProductChart from '@/Components/Charts/ProductChart.vue';
import Table from '@/Components/Table/Table.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import dayjs from 'dayjs';

const props = defineProps({
    reports: {
        type: Object,
        required: true
    }
});

const period = ref(props.reports.selectedPeriod || '12');

// interval periods
const periods = {
    '7': 'Last 7 Days',
    '30': 'Last 30 Days',
    '12': 'Last 12 Months'
};

// update interval every change of the dropdown
const handleInervalFilter = () => {
    router.get(route('reports'), { period: period.value });
};

// download pdf
const downloadPDF = computed(() => route('generate', { period: period.value }));

const summaryStats = [
    {
        title: 'Active Warranties',
        count: props.reports.stats.activeWarranty,
        icon: ShieldCheck,
        route: route('warranties')
    },
    {
        title: 'Total Warranty Claims',
        count: props.reports.stats.warrantyClaimCount,
        icon: MessageSquareShare,
        route: route('warranty-inquiries')
    },
    {
        title: 'Successfull Inquiries',
        count: props.reports.stats.resolvedInquiry,
        icon: LaptopMinimalCheck,
        route: route('warranty-inquiries')
    }
];
const headers = ['Product', 'Customer', 'Expiry Date'];

const daysLeft = (date) => {
    const now = new Date();
    const expiry = new Date(date);

    const diffTime = expiry - now;

    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
};
</script>

<template>

    <Head title="Reports" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Warranty Reports</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">System analytics and reliability logs.</p>
            </article>

            <div class="flex flex-col lg:flex-row space-x-3 space-y-3 md:space-y-0">
                <select v-model="period" @change="handleInervalFilter"
                    class="w-full rounded-md border-gray-300 text-sm focus:ring-neutral-900 focus:border-neutral-900">
                    <option v-for="(label, val) in periods" :key="val" :value="val">{{ label }}</option>
                </select>

                <a :href="downloadPDF"
                    class="flex items-center justify-center space-x-2 bg-neutral-900 text-white text-sm font-semibold px-4 py-2 rounded-md hover:bg-neutral-800 transition">
                    <span>
                        <Download class="h-5" />
                    </span>
                    <span>
                        Download
                    </span>
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card v-for="stat in summaryStats" :key="stat.title" :title="stat.title" :count="stat.count"
                :icon="stat.icon" :href="stat.href" />
        </section>

        <section>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="lg:col-span-3 overflow-hidden rounded-md border border-gray-300 bg-white p-6">
                    <div class="overflow-x-auto">
                        <div class="min-w-150 lg:min-w-full">
                            <InquiryChart v-if="reports.chartsData?.inquiries"
                                :labels="reports.chartsData.inquiries.labels"
                                :data="reports.chartsData.inquiries.data" />
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white border border-gray-300 rounded-md p-6">
                    <div class="overflow-x-auto">
                        <div class="min-w-75 lg:min-w-full">
                            <ProductChart v-if="reports.chartsData?.reportedProducts"
                                :labels="reports.chartsData.reportedProducts.labels"
                                :data="reports.chartsData.reportedProducts.data" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white border border-gray-300 rounded-md overflow-hidden">
            <div class="px-5 py-4">
                <h1 class="text-neutral-900 font-semibold text-base">
                    Warranty Near-Expiry
                </h1>
            </div>
            <Table v-if="props.nearExpiryWarranties?.length > 0" :headers="headers">
                <tr v-for="nearExpiry in props.reports.nearExpiryWarranties" :key="nearExpiry">
                    <td class="table-text">
                        {{ nearExpiry.product.name }}
                    </td>
                    <td class="table-text">
                        <div class="flex items-center space-x-2">
                            <Avatar class="h-8 w-8" :name="nearExpiry?.user.name" />
                            <p class="font-semibold">{{ nearExpiry?.user.name }}</p>
                        </div>
                    </td>
                    <td class="table-text">{{ dayjs(nearExpiry?.expiry_date).format('MMM, DD YYYY') }}</td>
                </tr>
            </Table>
            <EmptyState v-else message="No near expiry warranty of customers" />
        </section>
    </ManagerLayout>
</template>