<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ShieldCheck,
    TrendingUp,
    TrendingDown,
    Minus,
    AlertTriangle,
    Wallet,
    Info,
    ChevronRight
} from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import Table from '@/Components/Table/Table.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    summary: {
        type: Object,
        required: true
    },
    products: {
        type: Array,
        default: () => []
    },
    disclaimer: {
        type: String,
        default: ''
    },
    insufficientData: {
        type: Boolean,
        default: false
    }
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '—';
    return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const summaryStats = computed(() => [
    {
        title: 'Active Warranties',
        count: props.summary.activeWarranties,
        icon: ShieldCheck,
        href: route('warranties')
    },
    {
        title: 'Predicted Claims (Next 3 Mo.)',
        count: props.summary.predictedClaims,
        icon: TrendingUp
    },
    {
        title: 'Estimated Repair Cost',
        count: formatCurrency(props.summary.estimatedRepairCost),
        icon: Wallet
    },
    {
        title: 'High Risk Products',
        count: props.summary.highRiskProducts,
        icon: AlertTriangle
    }
]);

const headers = ['Product', 'Risk', 'Claims (3mo / total)', 'Trend', 'Predicted Claims', 'Avg. Repair Cost', 'Confidence', ''];

const riskBadgeClass = (level) => ({
    high: 'bg-red-50 text-red-700 border-red-200',
    medium: 'bg-amber-50 text-amber-700 border-amber-200',
    low: 'bg-blue-50 text-blue-700 border-blue-200',
    minimal: 'bg-gray-50 text-gray-600 border-gray-200'
}[level] ?? 'bg-gray-50 text-gray-600 border-gray-200');

const confidenceBadgeClass = (confidence) => ({
    high: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    medium: 'bg-amber-50 text-amber-700 border-amber-200',
    low: 'bg-gray-50 text-gray-600 border-gray-200',
    insufficient_data: 'bg-gray-50 text-gray-400 border-gray-200'
}[confidence] ?? 'bg-gray-50 text-gray-600 border-gray-200');

const confidenceLabel = (confidence) => ({
    high: 'High',
    medium: 'Medium',
    low: 'Low',
    insufficient_data: 'Insufficient data'
}[confidence] ?? confidence);

const trendIcon = (direction) => ({
    increasing: TrendingUp,
    decreasing: TrendingDown,
    stable: Minus
}[direction] ?? Minus);

const trendClass = (direction) => ({
    increasing: 'text-red-600',
    decreasing: 'text-emerald-600',
    stable: 'text-neutral-400'
}[direction] ?? 'text-neutral-400');
</script>

<template>

    <Head title="Warranty Forecast" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Warranty Forecast</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Predicted claims, repair costs, and product risk.</p>
            </article>
        </section>

        <section class="flex items-start gap-2 rounded-md border border-gray-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
            <Info class="h-4 w-4 mt-0.5 shrink-0 text-neutral-400" />
            <p>{{ disclaimer }}</p>
        </section>

        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <Card v-for="stat in summaryStats" :key="stat.title" :title="stat.title" :count="stat.count"
                :icon="stat.icon" :href="stat.href" :link="false" />
        </section>

        <section class="bg-white border border-gray-300 rounded-md overflow-hidden">
            <div class="px-5 py-4">
                <h1 class="text-neutral-900 font-semibold text-base">
                    Product Risk Ranking
                </h1>
            </div>

            <Table v-if="!insufficientData && products.length > 0" :headers="headers">
                <tr v-for="item in products" :key="item.product.id">
                    <td class="table-text">
                        <div class="flex items-center gap-3">
                            <img v-if="item.product.image_url" :src="item.product.image_url"
                                class="h-9 w-9 rounded-md object-cover border border-gray-200" :alt="item.product.name" />
                            <div>
                                <p class="font-semibold text-neutral-900">{{ item.product.name }}</p>
                                <p class="text-neutral-500 text-xs">{{ item.product.brand }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="table-text">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium capitalize"
                                :class="riskBadgeClass(item.riskLevel)">
                                {{ item.riskLevel }}
                            </span>
                            <span class="text-neutral-400 text-xs">{{ item.riskScore }}/100</span>
                        </div>
                    </td>
                    <td class="table-text text-neutral-700">
                        {{ item.recentClaims }} / {{ item.historicalClaims }}
                    </td>
                    <td class="table-text">
                        <div class="flex items-center gap-1" :class="trendClass(item.trend.direction)">
                            <component :is="trendIcon(item.trend.direction)" class="h-4 w-4" />
                            <span class="text-xs font-medium">
                                {{ item.trend.percentChange !== null ? `${item.trend.percentChange}%` : item.trend.direction }}
                            </span>
                        </div>
                    </td>
                    <td class="table-text text-neutral-700">{{ item.predictedClaims }}</td>
                    <td class="table-text text-neutral-700">
                        <span v-if="item.costDataAvailable">{{ formatCurrency(item.averageRepairCost) }}</span>
                        <span v-else class="text-neutral-400 italic text-xs">Not enough data</span>
                    </td>
                    <td class="table-text">
                        <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                            :class="confidenceBadgeClass(item.confidence)">
                            {{ confidenceLabel(item.confidence) }}
                        </span>
                    </td>
                    <td class="table-text">
                        <Link :href="route('manager.warranty-forecast.show', item.product.id)"
                            class="flex items-center justify-end text-neutral-400 hover:text-neutral-900 transition">
                            <ChevronRight class="h-4 w-4" />
                        </Link>
                    </td>
                </tr>
            </Table>
            <EmptyState v-else message="Not enough warranty claim history yet to generate a forecast." />
        </section>
    </ManagerLayout>
</template>
