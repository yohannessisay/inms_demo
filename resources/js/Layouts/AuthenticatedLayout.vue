<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Badge from '@/Components/ui/Badge.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash || {});
const sidebarOpen = ref(false);

const can = (permission) => {
    const permissions = user.value?.permissions || [];
    return permissions.includes('*') || permissions.includes(permission);
};

const navSections = computed(() => {
    const sections = [];

    sections.push({
        title: 'Core',
        items: [
            { name: 'Election Map', route: 'map.index', show: true },
        ],
    });

    if (user.value) {
        sections[0].items.unshift({
            name: 'Newsroom',
            route: 'news.index',
            show: true,
        });

        sections.push({
            title: 'Workspace',
            items: [
                { name: 'Dashboard', route: 'dashboard', show: true },
                { name: 'Profile', route: 'profile.edit', show: true },
            ],
        });

        sections.push({
            title: 'Management',
            items: [
                { name: 'Users', route: 'users.index', show: can('users.manage') },
                { name: 'Roles', route: 'roles.index', show: can('roles.manage') },
            ],
        });
    }

    return sections
        .map((section) => ({
            ...section,
            items: section.items.filter((item) => item.show),
        }))
        .filter((section) => section.items.length > 0);
});

const roleTone = {
    admin: 'success',
    editor: 'info',
    reporter: 'soft',
};
</script>

<template>
    <div class="min-h-screen bg-aurora">
        <div class="pointer-events-none fixed inset-0 opacity-40 pattern-grid" />

        <template v-if="user">
            <div class="relative flex min-h-screen">
                <aside
                    class="relative z-40 hidden w-72 flex-col border-r border-white/60 bg-white/80 px-6 py-8 backdrop-blur lg:flex"
                >
                    <Link :href="route('news.index')" class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-[var(--brand)] text-white shadow-lg">
                            <span class="text-lg font-semibold">IN</span>
                        </div>
                        <div>
                            <p class="font-display text-lg font-semibold">INMS Pulse</p>
                            <p class="text-xs text-slate-500">Newsroom workflow</p>
                        </div>
                    </Link>

                    <div class="mt-8 flex items-center justify-between rounded-2xl border border-white/60 bg-white/70 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">
                                {{ user.name }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ user.email }}
                            </p>
                        </div>
                        <Badge :variant="roleTone[user.role] || 'soft'">
                            {{ user.role }}
                        </Badge>
                    </div>

                    <nav class="mt-8 flex flex-1 flex-col gap-6">
                        <div v-for="section in navSections" :key="section.title" class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                                {{ section.title }}
                            </p>
                            <div class="flex flex-col gap-2">
                                <Link
                                    v-for="item in section.items"
                                    :key="item.name"
                                    v-show="item.show"
                                    :href="route(item.route)"
                                    class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
                                    :class="route().current(item.route)
                                        ? 'bg-slate-900 text-white'
                                        : 'text-slate-700 hover:bg-slate-100'"
                                >
                                    {{ item.name }}
                                </Link>
                            </div>
                        </div>
                    </nav>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="mt-6 w-full rounded-full border border-transparent bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800"
                    >
                        Log out
                    </Link>
                </aside>

                <aside
                    v-if="sidebarOpen"
                    class="fixed inset-0 z-50 bg-black/30 lg:hidden"
                    @click="sidebarOpen = false"
                ></aside>
                <aside
                    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                    class="fixed inset-y-0 left-0 z-50 w-72 transform border-r border-white/60 bg-white/95 px-6 py-8 transition lg:hidden"
                >
                    <div class="flex items-center justify-between">
                        <Link :href="route('news.index')" class="flex items-center gap-3">
                            <div class="grid h-10 w-10 place-items-center rounded-2xl bg-[var(--brand)] text-white shadow-lg">
                                <span class="text-lg font-semibold">IN</span>
                            </div>
                            <div>
                                <p class="font-display text-lg font-semibold">INMS Pulse</p>
                                <p class="text-xs text-slate-500">Newsroom</p>
                            </div>
                        </Link>
                        <button class="text-sm font-semibold text-slate-600" @click="sidebarOpen = false">
                            Close
                        </button>
                    </div>

                    <nav class="mt-6 space-y-6">
                        <div v-for="section in navSections" :key="section.title" class="space-y-3">
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">
                                {{ section.title }}
                            </p>
                            <div class="flex flex-col gap-2">
                                <Link
                                    v-for="item in section.items"
                                    :key="item.name"
                                    v-show="item.show"
                                    :href="route(item.route)"
                                    class="rounded-2xl px-4 py-2 text-sm font-semibold transition"
                                    :class="route().current(item.route)
                                        ? 'bg-slate-900 text-white'
                                        : 'text-slate-700 hover:bg-slate-100'"
                                >
                                    {{ item.name }}
                                </Link>
                            </div>
                        </div>
                    </nav>
                </aside>

                <div class="flex-1">
                    <header class="sticky top-0 z-30 flex items-center justify-between border-b border-white/60 bg-white/70 px-6 py-4 backdrop-blur lg:hidden">
                        <button
                            class="rounded-full border border-slate-200/70 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-700"
                            @click="sidebarOpen = true"
                        >
                            Menu
                        </button>
                        <Badge :variant="roleTone[user.role] || 'soft'">
                            {{ user.role }}
                        </Badge>
                    </header>

                    <main class="relative mx-auto max-w-6xl px-6 py-10">
                        <div
                            v-if="flash.success"
                            class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
                        >
                            {{ flash.success }}
                        </div>
                        <div
                            v-if="flash.error"
                            class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900"
                        >
                            {{ flash.error }}
                        </div>

                        <slot name="header" />
                        <div class="mt-6">
                            <slot />
                        </div>
                    </main>
                </div>
            </div>
        </template>

        <template v-else>
            <div class="relative">
                <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
                    <Link :href="route('welcome')" class="flex items-center gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-2xl bg-[var(--brand)] text-white shadow-lg">
                            <span class="text-lg font-semibold">IN</span>
                        </div>
                        <div>
                            <p class="font-display text-lg font-semibold">INMS Pulse</p>
                            <p class="text-xs text-slate-500">Public map</p>
                        </div>
                    </Link>
                    <div class="flex items-center gap-2">
                        <Link
                            :href="route('login')"
                            class="rounded-full border border-slate-200/70 px-3 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-full border border-transparent bg-slate-900 px-3 py-1 text-xs font-semibold text-white hover:bg-slate-800"
                        >
                            Register
                        </Link>
                    </div>
                </header>

                <main class="mx-auto max-w-6xl px-6 py-10">
                    <slot />
                </main>
            </div>
        </template>
    </div>
</template>
