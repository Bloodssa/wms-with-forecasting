<script setup>
import dayjs from 'dayjs';
import Badge from './Badge.vue';

const props = defineProps({
    warranty: Object
});

const isExpired = (date) => {
    return dayjs(date).isBefore(dayjs(), 'day')
}

const daysLeft = (date) => {
    return dayjs(date).diff(dayjs(), 'day')
}

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY');
}
</script>

<template>
    <div class="flex flex-col lg:flex-row my-6 w-full p-5 rounded-md border overflow-hidden border-gray-300 bg-white">
        <div class="w-full lg:w-2/5 shrink-0">
            <div class="relative group cursor-pointer rounded-md border border-gray-300 overflow-hidden">
                <div class="aspect-square md:aspect-video lg:aspect-square w-full flex items-center justify-center p-6">
                    <img :src="props.warranty.product.image_url" :alt="props.warranty.product.name"
                        class="w-full h-full object-contain mix-blend-multiply" />
                </div>
            </div>
        </div>
        <div class="flex-1 lg:pl-6 py-4 flex flex-col space-y-6">
            <div>
                <h1 class="text-neutral-500">Product Name</h1>
                <p class="text-2xl text-neutral-900 font-bold">{{ props.warranty.product.name }}</p>
            </div>
            <div>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <h1 class="text-neutral-500 text-sm">Category</h1>
                        <p class="text-md text-neutral-900 font-semibold">
                            {{ props.warranty.product.category.name }}</p>
                    </div>
                    <div class="block">
                        <h1 class="text-neutral-500 text-sm">Warranty Status</h1>
                        <Badge :type="warranty.status">
                            {{ props.warranty.status }}
                        </Badge>
                    </div>

                    <div>
                        <h1 class="text-neutral-500 text-sm">Date Purchased</h1>
                        <p class="text-md text-neutral-900 font-semibold">
                            {{ formatDate(props.warranty.purchase_date) }}</p>
                    </div>
                    <div>
                        <h1 class="text-neutral-500 text-sm">Warranty Expiration</h1>
                        <p class="text-md text-neutral-900 font-semibold">
                            {{ formatDate(props.warranty.expiry_date) }}
                        </p>
                    </div>

                    <div>
                        <h1 class="text-neutral-500 text-sm">Service Center Name</h1>
                        <p class="text-md text-neutral-900 font-semibold">
                            {{ warranty.product.service_center_name }}</p>
                    </div>
                    <div>
                        <h1 class="text-neutral-500 text-sm">Service Center Address</h1>
                        <p class="text-md text-neutral-900 font-semibold">{{
                            warranty.product.service_center_address }}
                        </p>
                    </div>
                    <div>
                        <h1 class="text-neutral-500 text-sm">Serial Number</h1>
                        <p class="text-md text-neutral-900 font-semibold">{{ warranty.serial_number }}</p>
                    </div>
                    <div class="col-span-2 border-t border-gray-300 pt-4 mt-2">
                        <h1 class="text-neutral-500 text-md">Coverage</h1>
                        <div class="flex items-center space-x-2 mt-2">
                            <template v-if="isExpired(warranty.expiry_date)">
                                <!-- <CircleBadge :type="warranty.status" size="lg" /> -->
                                <h1 class="text-rose-900 text-lg font-semibold">
                                    No longer covered by warranty
                                </h1>
                            </template>
                            <template v-else>
                                <!-- <CircleBadge :type="warranty.status" size="lg" /> -->
                                <p class="text-md text-neutral-900 font-semibold">
                                    {{ daysLeft(warranty.expiry_date) }} Days Left
                                </p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>