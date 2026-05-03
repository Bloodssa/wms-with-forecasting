<script setup>
import Pagination from '@/Components/Pagination.vue';

defineProps({
    headers: {
        type: Array,
        required: true
    },
    datas: {
        type: Object,
        default: null
    },
    action: {
        type: Boolean,
        default: false
    },
    borderTop: {
        type: Boolean,
        default: true
    }
})
</script>

<template>
    <div class="w-full">
        <div class="overflow-x-auto shadow-sm">
            <table class="min-w-full divide-y divide-gray-300" :class="{ 'border-t border-gray-300': borderTop }">
                <thead>
                    <tr>
                        <th v-for="(header, index) in headers" :key="index"
                            class="px-6 py-3 text-left text-sm font-semibold text-neutral-900 capitalize">
                            {{ header }}
                        </th>
                        <th v-if="action" class="px-6 py-3 text-right text-sm font-semibold text-neutral-900">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-300">
                    <slot />
                </tbody>
            </table>
            <!-- Paginate -->
            <div v-if="datas?.links?.length > 3" class="px-3 py-4 w-full border-t border-gray-300">
                <Pagination :links="datas.links" />
            </div>
        </div>
    </div>
</template>