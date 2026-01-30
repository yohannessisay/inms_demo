<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    rows: {
        type: Number,
        default: 6,
    },
    class: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <textarea
        :rows="rows"
        :value="modelValue"
        @input="emit('update:modelValue', $event.target.value)"
        :class="cn(
            'w-full rounded-2xl border border-slate-200/70 bg-white/80 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-[var(--brand)] focus:ring-2 focus:ring-[var(--brand)]/20',
            props.class,
        )"
    />
</template>
