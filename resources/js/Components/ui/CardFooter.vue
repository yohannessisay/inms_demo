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
    <div :class="cn('flex items-center justify-between gap-3 px-6 pb-6', props.class)">
        <slot />
    </div>
</template>
