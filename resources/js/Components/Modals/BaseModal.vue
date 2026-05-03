<script setup>
import { computed } from 'vue';

const props = defineProps({
    show: Boolean,
    title: String,
    subtitle: String,
    size: {
        type: String,
        default: 'md'
    },
    height: {
        type: String,
        default: 'auto'
    }
});

const emit = defineEmits(['close']);
const close = () => emit('close');

const hasHeader = computed(() => {
    return props.title?.trim() || props.subtitle?.trim();
});

const modalSize = computed(() => {
    return {
        sm: 'max-w-xl',
        md: 'max-w-3xl',
        lg: 'max-w-4xl',
        xl: 'max-w-6xl',
        full: 'max-w-full mx-4'
    }[props.size] || 'max-w-2xl';
});

const modalHeight = computed(() => {
    return {
        auto: 'max-h-[90vh]',
        sm: 'max-h-[50vh]',
        md: 'max-h-[75vh]',
        lg: 'max-h-[85vh]',
        full: 'h-[90vh]'
    }[props.height] || 'max-h-[90vh]'
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 flex items-center justify-center p-4 overflow-y-auto z-9999"
            @click.self="close">
            <div class="fixed inset-0 h-full w-full bg-gray-400/10 backdrop-blur-[7px]" @click.self="close"></div>
            <div :class="[modalSize, modalHeight]" class="no-scrollbar relative flex w-full flex-col overflow-y-auto rounded-md bg-white p-6 lg:p-11 shadow-2xl">
                <div class="flex" :class="hasHeader ? 'justify-between mb-5' : 'justify-end mb-0'">
                    <div v-if="hasHeader">
                        <h4 class="text-[24px] font-semibold text-gray-800">{{ props.title }}</h4>
                        <p class="text-sm text-gray-500">{{ props.subtitle }}</p>
                    </div>
                    <button @click="close" type="button" :class="hasHeader ? '' : 'mb-4'"
                        class="z-10 flex h-11 w-11 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 transition-colors">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M18 6L6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <slot v-if="show" />
            </div>
        </div>
    </Teleport>
</template>