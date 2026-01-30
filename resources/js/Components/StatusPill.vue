<script setup>
import Badge from '@/Components/ui/Badge.vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const variants = {
    draft: 'soft',
    review: 'warning',
    approved: 'success',
};

const labels = {
    draft: 'Draft',
    review: 'In Review',
    approved: 'Approved',
};
</script>

<template>
    <Badge :variant="variants[status] || 'soft'">
        {{ labels[status] || status }}
    </Badge>
</template>
