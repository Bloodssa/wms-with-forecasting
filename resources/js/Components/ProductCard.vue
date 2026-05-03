<script setup>
import { Link } from '@inertiajs/vue3';
import { Star, ChevronRight, ShieldCheck } from 'lucide-vue-next';

const props = defineProps({
    product: {
        type: Object,
        required: true
    }
});

</script>

<template>
    <div class="w-full overflow-hidden p-4">
        <div class="aspect-square w-full overflow-hidden bg-neutral-50 relative">
            <img :src="product.image_url" :alt="product.name" class="h-full w-full object-cover" />
            <div class="absolute top-0 right-0">
                <span
                    class="px-2 py-1 bg-white backdrop-blur-sm text-2xs font-bold text-neutral-900 rounded-xxs border border-gray-300">
                    {{ product.category?.name }}
                </span>
            </div>
        </div>
    </div>

    <div class="p-3 flex flex-col flex-1">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-neutral-900 truncate cursor-pointer" :title="product.name">
                {{ product.name }}
            </h3>
            <p class="text-xs text-neutral-500 font-medium">{{ product.brand }}</p>
        </div>

        <div v-if="product?.averageRating" class="flex items-center gap-1 mb-1">
            <Star class="w-5 h-5 fill-yellow-400 text-yellow-400" />
            <span class="text-sm font-semibold text-neutral-900">{{ product.averageRating ?
                Number(product.averageRating).toFixed(1) : '0.0' }}</span>
        </div>
        <div v-else class="mb-1">
            <span class="text-[15px] text-neutral-900">No reviews</span>
        </div>
        <p class="text-md font-bold text-neutral-900 mb-2">₱{{ product.price }}</p>

        <div
            class="mt-auto mb-4 flex items-center gap-2 text-neutral-600 bg-neutral-50 p-2 rounded-md border border-neutral-100">
            <ShieldCheck class="w-4 h-4 text-neutral-900" />
            <span class="text-xs font-semibold">
                {{ product.warranty_duration }} Months Warranty
            </span>
        </div>

        <Link :href="route('products-details', { id: product.id })"
            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-medium rounded-md hover:bg-neutral-700 transition-colors">
        <span>Show Details</span>
        <ChevronRight class="w-4 h-4" />
        </Link>
    </div>
</template>