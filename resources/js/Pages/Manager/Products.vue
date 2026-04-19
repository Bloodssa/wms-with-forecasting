<script setup>
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import Table from '@/Components/Table/Table.vue';
import { EllipsisVertical, Plus } from 'lucide-vue-next';
import { Head } from '@inertiajs/vue3';
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    products: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Object,
        default: () => ({})
    },
    categoriesForFilter: {
        type: Object,
        default: () => ({})
    }
});

const headers = ['Product', 'Category', 'Brand', 'Warranty Duration'];
</script>

<template>

    <Head title="Products" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Product Management</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage your warranty data and configurations.</p>
            </article>
        </section>

        <section>
            <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                <TableSearch name="category" :select="categoriesForFilter" :route="route('products')">
                    <button class="flex items-center justify-center gap-1 px-4 py-1.5 bg-neutral-900 text-white text-sx font-meduim rounded-md hover:bg-neutral-800 transition">
                        <span>
                            <Plus class="h-5" />
                        </span><span>Add Product</span>
                    </button>
                </TableSearch>
                <Table v-if="products.data?.length > 0" :headers="headers" :datas="products" :action="true">
                    <tr v-for="product in products.data" :key="product.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="h-10 w-10 shrink-0">
                                    <img class="h-10 w-10 rounded-md object-cover border border-gray-200"
                                        :src="product?.image_url" alt="Product">
                                </div>
                                <div class="text-sm font-semibold text-gray-900">{{ product.name }}</div>
                            </div>
                        </td>
                        <td class="table-text">
                            {{ product.category?.name }}
                        </td>
                        <td class="table-text">
                            {{ product.brand }}
                        </td>
                        <td class="table-text">
                            {{ product.warranty_duration }}
                        </td>
                        <td class="px-6 text-right py-4 whitespace-nowrap text-sm text-neutral-900">
                            <button
                                class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                <EllipsisVertical />
                            </button>
                        </td>
                    </tr>
                </Table>
                <EmptyState v-else
                    :message="products.data?.length ? 'No products found' : 'There is no product at the moment'" />
            </div>
        </section>
    </ManagerLayout>
</template>