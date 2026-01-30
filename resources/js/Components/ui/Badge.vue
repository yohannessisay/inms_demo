<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
    },
    class: {
        type: String,
        default: '',
    },
});

const variants = {
    default: 'bg-slate-900 text-white',
    soft: 'bg-slate-100 text-slate-700',
    success: 'bg-emerald-100 text-emerald-800',
    warning: 'bg-amber-100 text-amber-800',
    info: 'bg-sky-100 text-sky-800',
    danger: 'bg-rose-100 text-rose-800',
};
</script>

<template>
    <span
        :class="cn(
            'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide',
            variants[variant],
            props.class,
        )"
    >
        <slot />
    </span>
</template>
