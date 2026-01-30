<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
    size: {
        type: String,
        default: 'md',
    },
    class: {
        type: String,
        default: '',
    },
});

const variants = {
    primary:
        'bg-[var(--brand)] text-white shadow-[0_10px_30px_rgba(15,118,110,0.35)] hover:bg-[var(--brand-strong)]',
    secondary:
        'bg-white/80 text-slate-900 border border-slate-200/70 hover:bg-white',
    outline:
        'border border-slate-300/80 text-slate-900 hover:border-slate-400/90 hover:bg-slate-50',
    ghost: 'text-slate-700 hover:bg-slate-100/80',
    danger: 'bg-rose-600 text-white hover:bg-rose-700',
};

const sizes = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2 text-sm',
    lg: 'px-5 py-2.5 text-sm',
};
</script>

<template>
    <button
        :type="type"
        :class="cn(
            'inline-flex items-center justify-center gap-2 rounded-full font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand)]/60 focus-visible:ring-offset-2',
            variants[variant],
            sizes[size],
            props.class,
        )"
    >
        <slot />
    </button>
</template>
