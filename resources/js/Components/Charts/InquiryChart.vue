<script setup>
import { computed } from 'vue';
import VueApexCharts from "vue3-apexcharts";

const props = defineProps({
    labels: Array,
    data: Array
});

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        toolbar: { show: false }
    },
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '45%',
        }
    },
    dataLabels: { enabled: false },
    colors: ['oklch(20.5% 0 0)'],
    xaxis: {
        categories: props.labels,
    },
    yaxis: {
        title: { text: 'Inquiries Count' }
    },
    tooltip: {
        y: { formatter: (val) => `${val} inquiries` }
    }
}));

const series = computed(() => [{
    name: 'Warranty Inquiries',
    data: props.data
}]);
</script>

<template>
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-md font-bold text-neutral-900">
            Warranty Inquiries Per Month/Week
        </h3>
    </div>
    <div class="w-full">
        <VueApexCharts type="bar" height="350" :options="chartOptions" :series="series" />
    </div>
</template>