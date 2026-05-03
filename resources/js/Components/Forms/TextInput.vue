<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: [String, Number],
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        v-model="model"
        class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-[15px] placeholder:text-neutral-500 
               outline-none transition-all duration-100 hover:border-gray-300 focus:border-neutral-900 focus:ring-4 focus:ring-neutral-300/60 disabled:cursor-not-allowed disabled:opacity-50"
    />
</template>
