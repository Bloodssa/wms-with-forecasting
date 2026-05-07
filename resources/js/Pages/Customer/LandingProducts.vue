<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import useSearchFilter from '@/composables/useSearchFilter';
import { Search, Filter } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { Star, ChevronRight, ShieldCheck } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    products: {
        type: Object,
        default: () => ({ data: [] })
    },
    categories: {
        type: Object
    },
    filters: Object
});

// use composables search debounce
const { filters } = useSearchFilter('landing.products', props.filters);
</script>

<template>
    <GuestLayout title="Products" :canLogin="canLogin" :canRegister="canRegister">
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-10 space-y-10 pb-15 bg-white">
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

            <div v-if="props.products.length"
                class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                <div v-for="product in props.products" :key="product.id"
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
                            <h3 class="text-sm font-semibold text-neutral-900 truncate cursor-pointer"
                                :title="product.name">
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

                        <Link :href="route('landing.details', product.id)"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-medium rounded-md hover:bg-neutral-700 transition-colors">
                        <span>Show Details</span>
                        <ChevronRight class="w-4 h-4" />
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="mt-10 py-16 bg-white rounded-md border border-gray-300">
                <EmptyState :border="false" message="No products found in this category." />
            </div>
        </section>
    </GuestLayout>
</template>
