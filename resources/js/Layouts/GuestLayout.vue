<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="min-h-screen bg-aurora">
        <div class="pointer-events-none fixed inset-0 opacity-40 pattern-grid" />
        <div class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="absolute inset-x-0 top-10 mx-auto flex max-w-6xl items-center justify-between px-6">
                <Link :href="route('welcome')" class="flex items-center gap-3">
                    <div
                        class="grid h-11 w-11 place-items-center rounded-2xl bg-[var(--brand)] text-white shadow-lg"
                    >
                        <span class="text-lg font-semibold">IN</span>
                    </div>
                    <div>
                        <p class="font-display text-lg font-semibold">
                            INMS Pulse
                        </p>
                        <p class="text-xs text-slate-500">Newsroom</p>
                    </div>
                </Link>
                <div class="text-xs font-semibold text-slate-600">
                    {{ title }}
                </div>
            </div>

            <div class="w-full max-w-lg">
                <slot />
            </div>
        </div>
    </div>
</template>
