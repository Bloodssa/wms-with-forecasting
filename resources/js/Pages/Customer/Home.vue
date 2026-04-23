<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ShieldCheck, AlarmClockMinus, SquareCheckBig } from 'lucide-vue-next';
import Card from '@/Components/Card.vue';
import Summary from '@/Components/Summary.vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    recentlyPurchased: {
        type: Array,
        default: []
    },
    expiringWarranties: {
        type: Array,
        default: []
    }
});

const summaryStats = computed(() => [
    {
        title: 'Active Warranties',
        count: props.stats.activeWarranties,
        icon: ShieldCheck,
        route: route('warranty', { status: 'active' })
    },
    {
        title: 'Expiring Warranties',
        count: props.stats.expWarCount,
        icon: AlarmClockMinus,
        route: route('warranty', { status: 'near-expiry' })
    },
    {
        title: 'Resolved Inquiry',
        count: props.stats.resolvedInquiryCount,
        icon: SquareCheckBig,
        route: route('inquiries', { status: 'resolved' })
    }
]);
</script>

<template>

    <Head title="Home" />
    <CustomerLayout>
        <div>
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-gray-900">
                    Hello, {{ $page.props.auth.user.name.split(' ')[0] ?? 'Guest' }}
                </h1>
                <p class="text-neutral-500 mt-2 text-sm">Monitor your warranties, track claims, and never miss an
                    expiration
                    date.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Card v-for="stat in summaryStats" :key="stat.title" :title="stat.title" :count="stat.count"
                :icon="stat.icon" :route="stat.route" />
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 mt-10 gap-6">
            <Summary title="Recently Purchased" :products="recentlyPurchased"
                :is-empty="recentlyPurchased.length === 0" />
            <Summary title="Expiring Warranties" :products="expiringWarranties" :expiring="true"
                :is-empty="expiringWarranties.length === 0" emptTitle="All your warranties are active" />
        </div>
    </CustomerLayout>
</template>
