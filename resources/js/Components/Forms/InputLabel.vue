<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-md font-medium text-neutral-900">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
