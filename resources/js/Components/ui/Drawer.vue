<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: 'md',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (event) => {
    if (event.key === 'Escape' && props.show) {
        event.preventDefault();
        close();
    }
};

watch(
    () => props.show,
    (value) => {
        document.body.style.overflow = value ? 'hidden' : '';
    },
);

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <div v-show="show" class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-slate-900/30 backdrop-blur-sm" @click="close" />
        <Transition
            enter-active-class="transition ease-out duration-300"
            enter-from-class="translate-x-full opacity-0"
            enter-to-class="translate-x-0 opacity-100"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="translate-x-0 opacity-100"
            leave-to-class="translate-x-full opacity-0"
        >
            <aside
                v-show="show"
                class="absolute right-0 top-0 h-full w-full overflow-y-auto bg-white shadow-2xl"
                :class="maxWidthClass"
                role="dialog"
                aria-modal="true"
            >
                <slot />
            </aside>
        </Transition>
    </div>
</template>
