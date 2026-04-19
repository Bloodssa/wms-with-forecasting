<script setup>
import { computed } from 'vue';

const props = defineProps({
    inquiryStatus: {
        type: String,
        default: 'open'
    }
});

// Configuration for status mapping
const statusMap = {
    'open': 0,
    'pending': 1,
    'in-progress': 2,
    'resolved': 3,
    'replaced': 3,
    'closed': 3,
};

// The step definitions
const steps = [
    {
        id: 'open',
        label: 'Open',
        path: 'M538.5-138.5Q480-197 480-280t58.5-141.5Q597-480 680-480t141.5 58.5Q880-363 880-280t-58.5 141.5Q763-80 680-80t-141.5-58.5ZM747-185l28-28-75-75v-112h-40v128l87 87Zm-547 65q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h167q11-35 43-57.5t70-22.5q40 0 71.5 22.5T594-840h166q33 0 56.5 23.5T840-760v250q-18-13-38-22t-42-16v-212h-80v120H280v-120h-80v560h212q7 22 16 42t22 38H200Zm308.5-651.5Q520-783 520-800t-11.5-28.5Q497-840 480-840t-28.5 11.5Q440-817 440-800t11.5 28.5Q463-760 480-760t28.5-11.5Z'
    },
    {
        id: 'pending',
        label: 'Pending',
        path: 'm612-292 56-56-148-148v-184h-80v216l172 172ZM480-80q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z'
    },
    {
        id: 'processing',
        label: 'Processing',
        path: 'M314-115q-104-48-169-145T80-479q0-26 2.5-51t8.5-49l-46 27-40-69 191-110 110 190-70 40-54-94q-11 27-16.5 56t-5.5 60q0 97 53 176.5T354-185l-40 70Zm306-485v-80h109q-46-57-111-88.5T480-800q-55 0-104 17t-90 48l-40-70q50-35 109-55t125-20q79 0 151 29.5T760-765v-55h80v220H620ZM594 0 403-110l110-190 69 40-57 98q118-17 196.5-107T800-480q0-11-.5-20.5T797-520h81q1 10 1.5 19.5t.5 20.5q0 135-80.5 241.5T590-95l44 26-40 69Z'
    },
    {
        id: 'finished',
        label: 'Finished',
        path: 'm424-296 282-282-56-56-226 226-114-114-56 56 170 170Zm56 216q-83 0-156-31.5T197-197q-54-54-85.5-127T80-480q0-83 31.5-156T197-763q54-54 127-85.5T480-880q83 0 156 31.5T763-763q54 54 85.5 127T880-480q0 83-31.5 156T763-197q-54 54-127 85.5T480-80Zm0-80q134 0 227-93t93-227q0-134-93-227t-227-93q-134 0-227 93t-93 227q0 134 93 227t227 93Zm0-320Z'
    }
];

const currentIndex = computed(() => statusMap[props.inquiryStatus] ?? 0);
</script>

<template>
    <div class="bg-white border border-gray-300 rounded-md py-6 px-4">
        <div class="flex items-center justify-between max-w-4xl mx-auto relative">
            <template v-for="(step, index) in steps" :key="step.id">

                <div class="flex flex-col items-center z-10">
                    <div class="w-14 h-14 rounded-full flex items-center justify-center border transition-colors duration-500"
                        :class="currentIndex >= index ? 'bg-neutral-900 border-neutral-500' : 'bg-white border-gray-300'">
                        <svg viewBox="0 -960 960 960" class="w-7 h-7"
                            :class="currentIndex >= index ? 'text-white' : 'text-gray-400'">
                            <path fill="currentColor" :d="step.path" />
                        </svg>
                    </div>
                    <p class="mt-2 text-sm font-semibold transition-colors duration-500"
                        :class="currentIndex >= index ? 'text-neutral-900' : 'text-neutral-500'">
                        {{ step.label }}
                    </p>
                </div>

                <div v-if="index < steps.length - 1" class="flex-1 h-0.5 mx-2 bg-gray-300 relative -top-3">
                    <div class="h-full bg-neutral-900 transition-all duration-700 ease-in-out"
                        :style="{ width: currentIndex > index ? '100%' : '0%' }"></div>
                </div>

            </template>
        </div>
    </div>
</template>