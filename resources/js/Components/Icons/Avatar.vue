<script setup>
import { computed } from 'vue';

const props = defineProps({
    name: {
        type: String,
        default: 'G'
    }
});

const firstLetter = computed(() => ( props.name || 'G' ).substring(0, 1).toUpperCase());
</script>

<template>
    <div class="relative cursor-pointer inline-flex items-center justify-center overflow-hidden bg-neutral-900 rounded-full">
        <span class="font-semibold text-white leading-none">
            {{ firstLetter }}
        </span>
    </div>
</template>