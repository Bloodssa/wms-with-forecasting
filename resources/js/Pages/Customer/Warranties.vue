<script setup>
import { ref, watch } from 'vue';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Filter, Search, EllipsisVertical } from 'lucide-vue-next';
import TextInput from '@/Components/Forms/TextInput.vue';
import dayjs from 'dayjs';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    warranties: {
        type: Object,
        default: () => ({})
    }
});

const open = ref(false);
const search = ref('');
const status = ref('');

const statuses = [
    { label: 'All', value: '' },
    { label: 'Active', value: 'active' },
    { label: 'Near Expiry', value: 'near-expiry' },
    { label: 'Expired', value: 'expired' },
];

// debounce search
let timeout = null;
watch(search, (value) => {
    clearTimeout(timeout)

    timeout = setTimeout(() => {
        router.get(
            route('warranty'),
            {
                search: value,
                status: status.value,
            },
            {
                preserveState: true,
                replace: true,
            }
        )
    }, 500)
});

// filter change
watch(status, (value) => {
    router.get(
        route('warranty'),
        {
            search: search.value,
            status: value,
        },
        {
            preserveState: true,
            replace: true,
        }
    )
});

const selectStatus = (value) => {
    status.value = value
    open.value = false
};

// show route
const goTo = (id) => {
    const selection = window.getSelection()?.toString()

    if (selection && selection.length > 0) return

    router.visit(route('warranty.show', id))
}

const isExpired = (date) => {
    return dayjs(date).isBefore(dayjs())
}

const daysLeft = (date) => {
    return dayjs(date).diff(dayjs(), 'day')
}

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY')
}
</script>

<template>

    <Head title="Warranty" />
    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center gap-3 mb-6">
            <div class="relative flex-1 w-full">
                <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                <TextInput v-model="search" type="text" placeholder="Search..."
                    class="w-full h-10 pl-10 pr-3 border border-gray-300 rounded-md text-sm focus:border-neutral-900" />
            </div>
            <div class="relative md:w-auto w-full">
                <button @click="open = !open"
                    class="w-full md:w-auto flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 bg-white rounded-md text-sm font-medium hover:bg-gray-100">
                    <Filter class="w-4 h-4" />
                    {{statuses.find(s => s.value === status)?.label || 'All'}}
                </button>
                <div v-show="open"
                    class="absolute right-0 mt-2 w-48 bg-white border border-gray-300 rounded-md shadow z-50 overflow-hidden">
                    <button v-for="item in statuses" :key="item.value" @click="selectStatus(item.value)"
                        class="block w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                        :class="status === item.value ? 'bg-gray-100 font-semibold' : ''">
                        {{ item.label }}
                    </button>
                </div>
            </div>
        </div>
        <h1 class="my-5 font-semibold text-gray-900">Warranties</h1>
        <div class="border border-gray-300 rounded-md overflow-hidden bg-white">
            <table class="w-full border-collapse">
                <tbody class="divide-y divide-gray-300">
                    <tr v-if="warranties.data.length" v-for="warranty in warranties.data" :key="warranty.id" @click="goTo(warranty.id)"
                        class="cursor-pointer hover:bg-gray-50 transition">
                        <td class="px-4 py-3 align-middle">
                            <div class="flex items-center gap-4 min-w-0">
                                <img :src="warranty.product.image_url" alt="{{ warranty.product.name }}"
                                    class="w-15 h-15 rounded-md object-cover border border-gray-300 shrink-0" />
                                <div class="min-w-0 space-y-2">
                                    <p class="text-md font-semibold text-neutral-900 truncate">
                                        {{ warranty.product.name }}
                                    </p>
                                    <p class="text-xs text-neutral-500 truncate">
                                        Serial Number: {{ warranty.serial_number }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="hidden lg:table-cell px-4 py-3 align-middle">
                            <div class="min-w-0 space-y-2">
                                <p class="text-sm text-neutral-900 font-semibold truncate">
                                    {{ warranty.product.category.name }}
                                </p>
                                <p class="text-xs text-neutral-500 whitespace-nowrap">
                                    Purchased {{ formatDate(warranty.purchase_date) }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle text-right">
                            <div class="space-y-2">
                                <template v-if="isExpired(warranty.expiry_date)">
                                    <p class="text-[12px] tracking-wide text-rose-900 font-medium">
                                        Expired
                                    </p>
                                    <p class="text-sm font-semibold text-rose-900 whitespace-nowrap">
                                        {{ formatDate(warranty.expiry_date) }}
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="text-[12px] tracking-wide text-neutral-500 font-medium">
                                        Expires In
                                    </p>
                                    <p class="text-sm font-semibold text-neutral-900 whitespace-nowrap">
                                        {{ daysLeft(warranty.expiry_date) }} days
                                    </p>
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-3 align-middle text-right w-40">
                            <div class="inline-flex w-full justify-end">
                                <Badge size="md" :type="warranty.status">
                                    {{ warranty.status }}
                                </Badge>
                            </div>
                        </td>
                        <td class="px-6 text-right py-4 whitespace-nowrap text-sm text-neutral-900">
                            <button
                                class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                <EllipsisVertical />
                            </button>
                        </td>
                    </tr>
                    <template v-else>
                        <EmptyState :border="false" message="No warranties found at the moment" />
                    </template>
                </tbody>
            </table>
        </div>
    </CustomerLayout>
</template>