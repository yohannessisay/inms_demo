<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    align: {
        type: String,
        default: 'right',
    },
    width: {
        type: String,
        default: '48',
    },
    contentClasses: {
        type: String,
        default: 'py-1 bg-white',
    },
});

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

const open = ref(false);
const triggerRef = ref(null);
const contentRef = ref(null);
const menuStyles = ref({});

const widthPx = computed(() => {
    const value = Number(props.width);
    return Number.isFinite(value) ? value * 4 : 192;
});

const updatePosition = () => {
    if (!open.value || !triggerRef.value) return;
    const rect = triggerRef.value.getBoundingClientRect();
    const menuWidth = widthPx.value || contentRef.value?.offsetWidth || 192;
    let left = rect.left;

    if (props.align === 'right') {
        left = rect.right - menuWidth;
    } else if (props.align === 'center') {
        left = rect.left + rect.width / 2 - menuWidth / 2;
    }

    const padding = 8;
    left = Math.max(padding, Math.min(left, window.innerWidth - menuWidth - padding));

    menuStyles.value = {
        top: `${rect.bottom + 8}px`,
        left: `${left}px`,
    };
};

const closeOnEscape = (e) => {
    if (open.value && e.key === 'Escape') {
        open.value = false;
    }
};

const toggle = async () => {
    open.value = !open.value;
    if (open.value) {
        await nextTick();
        updatePosition();
    }
};

watch(open, async (value) => {
    if (value) {
        await nextTick();
        updatePosition();
    }
});

const handleViewportChange = () => {
    if (open.value) {
        updatePosition();
    }
};

onMounted(() => {
    document.addEventListener('keydown', closeOnEscape);
    window.addEventListener('resize', handleViewportChange);
    window.addEventListener('scroll', handleViewportChange, true);
});

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);
    window.removeEventListener('resize', handleViewportChange);
    window.removeEventListener('scroll', handleViewportChange, true);
});
</script>

<template>
    <div class="relative">
        <div ref="triggerRef" @click="toggle">
            <slot name="trigger" />
        </div>
    </div>

    <Teleport to="body">
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="open"
                class="fixed inset-0 z-[90]"
                @click="open = false"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                ref="contentRef"
                class="fixed z-[100] rounded-md shadow-lg"
                :class="widthClass"
                :style="menuStyles"
                @click.stop="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
