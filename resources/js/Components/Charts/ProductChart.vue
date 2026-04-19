<script setup>
import { computed } from 'vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    labels: Array,
    data: Array
});

const chartOptions = computed(() => ({
    chart: { type: 'donut' },
    labels: props.labels,
    plotOptions: {
        pie: {
            customScale: 0.9,
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'Total Reports',
                        formatter: (w) => w.globals.seriesTotals.reduce((a, b) => a + b, 0)
                    }
                }
            }
        }
    },
    dataLabels: { enabled: false },
    legend: { position: 'bottom' },
    colors: ['#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
    stroke: { show: true, width: 2, colors: ['#fff'] },
}));

const series = computed(() => props.data);
</script>

<template>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-md font-bold text-neutral-900">
            Most Reported Products During Inquiries
        </h3>
    </div>
    <div class="w-full">
        <VueApexCharts type="donut" height="380" :options="chartOptions" :series="series" />
    </div>
</template>