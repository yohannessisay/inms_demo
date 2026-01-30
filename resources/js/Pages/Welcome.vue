<script setup>
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="INMS Pulse" />

    <div class="min-h-screen bg-aurora">
        <div class="pointer-events-none fixed inset-0 opacity-30 pattern-grid" />
        <header class="relative mx-auto flex max-w-6xl items-center justify-between px-6 py-8">
            <div class="flex items-center gap-4">
                <div
                    class="grid h-12 w-12 place-items-center rounded-2xl bg-[var(--brand)] text-white shadow-lg"
                >
                    <span class="text-lg font-semibold">IN</span>
                </div>
                <div>
                    <p class="font-display text-xl font-semibold">INMS Pulse</p>
                    <p class="text-xs text-slate-500">Election newsroom module</p>
                </div>
            </div>
            <nav class="flex items-center gap-3">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('news.index')"
                >
                    <Button size="sm">Go to Newsroom</Button>
                </Link>
                <template v-else>
                    <Link v-if="canLogin" :href="route('login')">
                        <Button size="sm" variant="secondary">Log in</Button>
                    </Link>
                    <Link v-if="canRegister" :href="route('register')">
                        <Button size="sm">Register</Button>
                    </Link>
                </template>
            </nav>
        </header>

        <main class="relative mx-auto grid max-w-6xl gap-10 px-6 pb-20 pt-6 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full bg-white/70 px-4 py-2 text-xs font-semibold text-slate-600 shadow-sm">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                    Live editorial flow for election coverage
                </div>
                <h1 class="font-display text-4xl font-semibold leading-tight text-slate-900 md:text-5xl">
                    Coordinate the newsroom, visualize the vote, and keep stories moving.
                </h1>
                <p class="max-w-xl text-base text-slate-600">
                    INMS Pulse combines role-based editorial workflows with a lightweight GIS map to help teams draft, review, and approve election stories with confidence.
                </p>
                <div class="flex flex-wrap gap-3">
                    <Link :href="route('news.index')" class="inline-flex">
                        <Button>Enter the Newsroom</Button>
                    </Link>
                    <Link :href="route('map.index')" class="inline-flex">
                        <Button variant="secondary">Explore the Map</Button>
                    </Link>
                </div>
            </section>

            <section class="grid gap-6">
                <Card class="animate-fade-up">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Workflow stages</CardTitle>
                    </CardHeader>
                    <CardContent class="grid gap-3 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <span>Draft</span>
                            <span class="font-semibold">Reporter writes</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Review</span>
                            <span class="font-semibold">Editor checks</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Approved</span>
                            <span class="font-semibold">Admin publishes</span>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-emerald-100/70 to-white/70">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Interactive GIS map</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-slate-600">
                        Click regions to reveal live metrics like population, votes, and turnout with a clear legend and filters.
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-amber-100/70 to-white/70">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Role-based access</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-slate-600">
                        Reporters draft. Editors review. Admins approve. Every action is tracked.
                    </CardContent>
                </Card>
            </section>
        </main>
    </div>
</template>
