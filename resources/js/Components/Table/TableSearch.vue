<script setup>
import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import TextInput from '../Forms/TextInput.vue';
import { Search } from 'lucide-vue-next';

const props = defineProps({
    select: {
        type: Object,
    },
    route: {
        type: String,
        default: '/'
    },
    name: {
        type: String,
        default: 'status'
    },
    placeholder: {
        type: String,
        default: 'Search'
    },
    dropdown: {
        type: Boolean,
        default: false
    },
    px: {
        type: Boolean,
        default: false
    },
    all: {
        type: Boolean,
        default: false
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const search = ref(props.filters.search || '')
const selected = ref(props.filters[props.name] || '')

// debounce search
let timeout = null

watch(search, (value) => {
    clearTimeout(timeout)

    timeout = setTimeout(() => {
        router.get(
            props.route,
            {
                search: value,
                [props.name]: selected.value
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true
            }
        )
    }, 400)
})

// filter select change
watch(selected, (value) => {
    router.get(
        props.route,
        {
            search: search.value,
            [props.name]: value
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    )
})
</script>

<template>
    <div class="py-3 flex flex-col md:flex-row gap-4 justify-between items-center bg-gray-50/50 w-full"
        :class="px ? '' : 'px-4'">
        <!-- Search -->
        <div class="relative w-full md:w-96">
            <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-neutral-500 z-10">
                <Search class="w-4 h-5 " />
            </div>

            <TextInput id="search" v-model="search" placeholder="Seach here" class="pl-10" />
        </div>

        <!-- Filter -->
        <div v-if="!dropdown" class="w-full flex items-center gap-2 md:w-auto">
            <select v-model="selected"
                class="w-full rounded-md border-gray-300 text-sm focus:ring-neutral-900 focus:border-neutral-900">
                <option value="">All</option>

                <option class="capitalize" v-for="(option, index) in select" :key="index"
                    :value="option.value ?? option">
                    {{ option.label ?? option }}
                </option>
            </select>
            <div class="flex-1 md:flex-none flex">
                <slot />
            </div>
        </div>
    </div>
</template>