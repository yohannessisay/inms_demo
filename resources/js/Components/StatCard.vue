<script setup>
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        required: true,
    },
    hint: {
        type: String,
        default: '',
    },
    tone: {
        type: String,
        default: 'neutral',
    },
});

const tones = {
    neutral: 'from-slate-100/80 to-white/60 text-slate-900',
    primary: 'from-emerald-100/80 to-white/60 text-emerald-900',
    warning: 'from-amber-100/80 to-white/60 text-amber-900',
    accent: 'from-sky-100/80 to-white/60 text-sky-900',
};
</script>

<template>
    <Card :class="`bg-gradient-to-br ${tones[tone] || tones.neutral}`">
        <CardHeader class="pb-2">
            <CardTitle class="text-sm uppercase tracking-widest text-slate-600">
                {{ label }}
            </CardTitle>
        </CardHeader>
        <CardContent class="pt-0">
            <div class="text-3xl font-semibold">
                {{ value }}
            </div>
            <p v-if="hint" class="mt-2 text-xs text-slate-600">
                {{ hint }}
            </p>
        </CardContent>
    </Card>
</template>
