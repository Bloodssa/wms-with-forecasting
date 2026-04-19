<script setup>
import { computed } from 'vue'
import VueApexCharts from "vue3-apexcharts"

const props = defineProps({
    chart: {
        type: Object,
        required: true
    }
})

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        height: 350,
        toolbar: { show: false }
    },
    colors: [
        'oklch(39.3% 0.095 152.535)',
        'oklch(83.7% 0.128 66.29)',
        'oklch(50.5% 0.213 27.518)'
    ],
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5,
            borderRadiusApplication: 'end'
        }
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: props.chart.labels
    },
    yaxis: {
        title: {
            text: 'Warranties per month'
        }
    },
    tooltip: {
        y: {
            formatter: (val) => `${val} warranties`
        }
    }
}))

const series = computed(() => [
    {
        name: 'Active',
        data: props.chart.active
    },
    {
        name: 'Near Expiry',
        data: props.chart.nearExpiry
    },
    {
        name: 'Expired',
        data: props.chart.expired
    }
])
</script>

<template>
    <VueApexCharts
        type="bar"
        height="350"
        :options="chartOptions"
        :series="series"
    />
</template>