<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: {
        type: String,
        default: 'active'
    },
    size: {
        type: String,
        default: 'sm'
    }
})

const typeClasses = {
    active: 'bg-emerald-50 border-emerald-200 text-emerald-900',
    'near-expiry': 'bg-orange-50 border-orange-900 text-orange-900',
    expired: 'bg-rose-50 border-rose-200 text-rose-900',
    open: 'bg-blue-100 border-blue-200 text-blue-700',
    pending: 'bg-white border-gray-300 text-heading',
    'in-progress': 'bg-orange-50 border-orange-600 text-orange-900',
    resolved: 'bg-emerald-50 border-emerald-200 text-emerald-900',
    closed: 'bg-gray-200 border-gray-300 text-gray-800',
    replaced: 'bg-blue-100 border-blue-200 text-blue-700'
}

const dotClasses = {
    active: 'bg-emerald-700',
    'near-expiry': 'bg-orange-900',
    expired: 'bg-rose-900',
    open: 'bg-blue-900',
    pending: 'bg-heading',
    'in-progress': 'bg-orange-900',
    resolved: 'bg-emerald-700',
    closed: 'bg-heading',
    replaced: 'bg-blue-900'
}

const sizeClasses = {
    sm: 'px-2 py-0.5 text-xs',
    md: 'px-3 py-1 text-sm',
    lg: 'px-4 py-1.5 text-base'
}

const dotSizeClasses = {
    sm: 'h-1.5 w-1.5',
    md: 'h-2 w-2',
    lg: 'h-2.5 w-2.5'
}

const normalizedType = computed(() => {
    return (props.type || '')
        .toString()
        .trim()
        .toLowerCase()
});

const classes = computed(() => {
    const type = normalizedType.value;
    return [
        'inline-flex items-center border font-medium rounded',
        typeClasses[type] || typeClasses.pending,
        sizeClasses[props.size] || sizeClasses.sm
    ].join(' ')
})

const dot = computed(() => {
    const type = normalizedType.value;

    return [
        'rounded-full mr-1.5',
        dotClasses[type] || dotClasses.pending,
        dotSizeClasses[props.size] || dotSizeClasses.sm
    ].join(' ')
})
</script>

<template>
    <span :class="[classes, 'capitalize']">
        <span :class="dot"></span>
        <slot />
    </span>
</template>