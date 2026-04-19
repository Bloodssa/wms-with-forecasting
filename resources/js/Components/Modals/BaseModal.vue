<script setup>
defineProps({
    show: Boolean,
    maxWidth: {
        type: String,
        default: '2xl'
    }
});

const emit = defineEmits(['close']);
const close = () => {
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="fixed inset-0 z-100 overflow-y-auto px-4 py-6 sm:px-0 flex items-center">
            <div class="fixed inset-0 transform transition-all" @click="close">
                <div class="absolute inset-0 bg-gray-500/50 backdrop-blur-sm" />
            </div>

            <div class="mb-6 bg-white rounded-xl overflow-hidden shadow-2xl transform transition-all sm:w-full sm:mx-auto relative"
                :class="maxWidth === '2xl' ? 'max-w-2xl' : 'max-w-3xl'">
                <button @click="close" class="absolute right-5 top-5 text-gray-400 hover:text-gray-600">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>

                <div class="p-6 lg:p-11">
                    <slot v-if="show" />
                </div>
            </div>
        </div>
    </Teleport>
</template>