<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Search, Filter } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import ProductCard from '@/Components/ProductCard.vue';
import useSearchFilter from '@/composables/useSearchFilter';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [] })
    },
    categories: Object,
    filters: Object
});

// use composables search debounce
const { filters } = useSearchFilter('view-products', props.filters);
</script>

<template>

    <Head title="Shop Products" />

    <CustomerLayout>
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">
                    Shop Products
                </h1>
                <p class="text-sm text-neutral-500">
                    Find durable products backed by real warranty reviews from other customers.
                </p>
            </div>

            <div class="flex flex-col md:flex-row gap-2 w-full md:max-w-150 items-stretch md:items-center">
                <div class="relative flex-1 w-full">
                    <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <TextInput v-model="filters.search" type="text" placeholder="Search..."
                        class="w-full h-10 pl-10 pr-3 border border-gray-300 rounded-md text-sm focus:border-neutral-900" />
                </div>

                <div class="relative">
                    <select v-model="filters.category"
                        class="h-10 pl-4 pr-10 bg-white border border-gray-300 rounded-md text-sm font-medium w-full md:w-48 focus:ring-1 focus:ring-neutral-900 focus:border-neutral-900 appearance-none cursor-pointer">
                        <option :value="null">All Products</option>
                        <option v-for="category in props.categories" :key="category.slug" :value="category.slug">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="props.products.data.length"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <div v-for="product in props.products.data" :key="product.id" class="bg-white border border-gray-300 rounded-md">
                    <ProductCard :product="product" />
                </div>
        </div>

        <div v-else class="mt-10 py-16 bg-white rounded-md border border-gray-300">
            <EmptyState :border="false" message="No products found in this category." />
        </div>
    </CustomerLayout>
</template>