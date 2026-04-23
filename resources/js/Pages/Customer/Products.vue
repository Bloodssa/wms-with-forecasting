<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Search, Filter, ChevronRight, ShieldCheck } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [] })
    },
});

const viewProduct = (id) => {
    router.visit(route('review-products', { id: id }));
}

</script>

<template>

    <Head title="Shop Products" />

    <CustomerLayout>
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-neutral-900">Shop Products</h2>
            <p class="mt-2 text-neutral-500 max-w-2xl">
                Find durable products backed by real warranty reviews from other customers.
            </p>
        </div>

        <div v-if="props.products.data.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <div v-for="product in props.products.data" :key="product.id"
                class="bg-white border border-gray-300 rounded-md">
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

                    <div class="mt-auto mb-4 flex items-center gap-2 text-neutral-600 bg-neutral-50 p-2 rounded-md border border-neutral-100">
                        <ShieldCheck class="w-4 h-4 text-neutral-900" />
                        <span class="text-xs font-semibold uppercase tracking-tight">
                            {{ product.warranty_duration }} Month Warranty
                        </span>
                    </div>

                    <button @click="viewProduct(product.id)" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-medium rounded-md hover:bg-neutral-800 transition-colors active:scale-[0.98]">
                        <span>Show Details</span>
                        <ChevronRight class="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="mt-10 py-16 bg-neutral-50 rounded-md border border-dashed border-gray-300">
            <EmptyState :border="false" message="No products found in this category." />
        </div>
    </CustomerLayout>
</template>