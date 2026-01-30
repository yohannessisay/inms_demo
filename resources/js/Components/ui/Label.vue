<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    class: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <label
        :class="cn('text-sm font-semibold text-slate-700', props.class)"
    >
        <slot />
    </label>
</template>
