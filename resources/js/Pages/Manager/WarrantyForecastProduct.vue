<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    TrendingUp,
    TrendingDown,
    Minus,
    Info,
    ShieldCheck,
    Wallet,
    CircleAlert
} from 'lucide-vue-next';

const props = defineProps({
    forecast: {
        type: Object,
        required: true
    },
    disclaimer: {
        type: String,
        default: ''
    }
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '—';
    return '₱' + Number(value).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const riskBadgeClass = (level) => ({
    high: 'bg-red-50 text-red-700 border-red-200',
    medium: 'bg-amber-50 text-amber-700 border-amber-200',
    low: 'bg-blue-50 text-blue-700 border-blue-200',
    minimal: 'bg-gray-50 text-gray-600 border-gray-200'
}[level] ?? 'bg-gray-50 text-gray-600 border-gray-200');

const riskRingClass = (level) => ({
    high: 'text-red-600',
    medium: 'text-amber-600',
    low: 'text-blue-600',
    minimal: 'text-neutral-400'
}[level] ?? 'text-neutral-400');

const confidenceBadgeClass = (confidence) => ({
    high: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    medium: 'bg-amber-50 text-amber-700 border-amber-200',
    low: 'bg-gray-50 text-gray-600 border-gray-200',
    insufficient_data: 'bg-gray-50 text-gray-400 border-gray-200'
}[confidence] ?? 'bg-gray-50 text-gray-600 border-gray-200');

const confidenceLabel = (confidence) => ({
    high: 'High confidence',
    medium: 'Medium confidence',
    low: 'Low confidence',
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

    <Head :title="`Forecast — ${forecast.product.name}`" />
    <ManagerLayout>
        <section class="lg:mt-6 space-y-4">
            <Link :href="route('manager.warranty-forecast')"
                class="inline-flex items-center gap-1 text-sm text-neutral-500 hover:text-neutral-900 transition">
                <ArrowLeft class="h-4 w-4" />
                Back to Forecast
            </Link>

            <div class="flex flex-col md:flex-row md:items-center gap-4">
                <img v-if="forecast.product.image_url" :src="forecast.product.image_url"
                    class="h-16 w-16 rounded-md object-cover border border-gray-300" :alt="forecast.product.name" />
                <article class="space-y-1">
                    <h1 class="text-neutral-900 text-[22px] lg:text-2xl font-bold">{{ forecast.product.name }}</h1>
                    <p class="text-neutral-500 text-[13px] lg:text-sm">
                        {{ forecast.product.brand }}
                        <span v-if="forecast.product.category?.name"> · {{ forecast.product.category.name }}</span>
                    </p>
                </article>
            </div>
        </section>

        <section class="flex items-start gap-2 rounded-md border border-gray-300 bg-neutral-50 px-4 py-3 text-sm text-neutral-600">
            <Info class="h-4 w-4 mt-0.5 shrink-0 text-neutral-400" />
            <p>{{ disclaimer }}</p>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Risk score -->
            <div class="bg-white border border-gray-300 rounded-md p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-neutral-900 font-semibold text-base">Risk Level</h2>
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium capitalize"
                        :class="riskBadgeClass(forecast.riskLevel)">
                        {{ forecast.riskLevel }}
                    </span>
                </div>

                <div class="flex items-baseline gap-2">
                    <span class="text-4xl font-bold" :class="riskRingClass(forecast.riskLevel)">{{ forecast.riskScore }}</span>
                    <span class="text-neutral-400 text-sm">/ 100</span>
                </div>

                <ul class="space-y-2 pt-2 border-t border-gray-200">
                    <li v-for="(reason, index) in forecast.reasons" :key="index"
                        class="flex items-start gap-2 text-sm text-neutral-600">
                        <CircleAlert class="h-4 w-4 mt-0.5 shrink-0 text-neutral-400" />
                        {{ reason }}
                    </li>
                </ul>
            </div>

            <!-- Claim forecast -->
            <div class="bg-white border border-gray-300 rounded-md p-6 space-y-4">
                <h2 class="text-neutral-900 font-semibold text-base">Claim Forecast</h2>

                <dl class="space-y-3">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-neutral-500">Historical Claims</dt>
                        <dd class="text-sm font-semibold text-neutral-900">{{ forecast.historicalClaims }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-neutral-500">Recent Claims (3 mo.)</dt>
                        <dd class="text-sm font-semibold text-neutral-900">{{ forecast.recentClaims }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-neutral-500">Trend</dt>
                        <dd class="flex items-center gap-1 text-sm font-semibold" :class="trendClass(forecast.trend.direction)">
                            <component :is="trendIcon(forecast.trend.direction)" class="h-4 w-4" />
                            <span class="capitalize">{{ forecast.trend.direction }}</span>
                            <span v-if="forecast.trend.percentChange !== null">({{ forecast.trend.percentChange }}%)</span>
                        </dd>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                        <dt class="text-sm text-neutral-500">Predicted Claims (Next 3 Mo.)</dt>
                        <dd class="text-lg font-bold text-neutral-900">{{ forecast.predictedClaims }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Cost forecast -->
            <div class="bg-white border border-gray-300 rounded-md p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-neutral-900 font-semibold text-base">Repair Cost Forecast</h2>
                    <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-medium"
                        :class="confidenceBadgeClass(forecast.costConfidence)">
                        {{ confidenceLabel(forecast.costConfidence) }}
                    </span>
                </div>

                <template v-if="forecast.costDataAvailable">
                    <dl class="space-y-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-neutral-500">Average Repair Cost</dt>
                            <dd class="text-sm font-semibold text-neutral-900">{{ formatCurrency(forecast.averageRepairCost) }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-sm text-neutral-500">Cost Range</dt>
                            <dd class="text-sm font-semibold text-neutral-900">
                                {{ formatCurrency(forecast.repairCostRange.min) }} – {{ formatCurrency(forecast.repairCostRange.max) }}
                            </dd>
                        </div>
                        <div class="flex items-center justify-between pt-3 border-t border-gray-200">
                            <dt class="text-sm text-neutral-500">Estimated Repair Expense</dt>
                            <dd class="text-lg font-bold text-neutral-900">{{ formatCurrency(forecast.estimatedRepairCost) }}</dd>
                        </div>
                    </dl>
                </template>
                <div v-else class="flex flex-col items-start gap-2 py-4 text-sm text-neutral-500">
                    <Wallet class="h-5 w-5 text-neutral-300" />
                    <p>Not enough recorded repair costs yet to estimate a reliable range for this product.</p>
                </div>
            </div>
        </section>

        <!-- Warranty status -->
        <section class="bg-white border border-gray-300 rounded-md p-6">
            <h2 class="text-neutral-900 font-semibold text-base mb-4 flex items-center gap-2">
                <ShieldCheck class="h-4 w-4 text-neutral-400" />
                Warranty Status
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-2 gap-6">
                <div>
                    <p class="text-neutral-500 text-sm">Active Warranties</p>
                    <p class="text-2xl font-bold text-neutral-900">{{ forecast.activeWarranties }}</p>
                </div>
                <div>
                    <p class="text-neutral-500 text-sm">Near-Expiry Warranties</p>
                    <p class="text-2xl font-bold text-neutral-900">{{ forecast.nearExpiryWarranties }}</p>
                </div>
            </div>
        </section>
    </ManagerLayout>
</template>
