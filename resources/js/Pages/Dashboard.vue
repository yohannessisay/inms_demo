<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';

const user = usePage().props.auth.user;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            <Card class="bg-white/80">
                <CardHeader>
                    <CardTitle class="font-display text-2xl">
                        Welcome, {{ user.name }}
                    </CardTitle>
                    <p class="text-sm text-slate-600">
                        Your role: <span class="font-semibold">{{ user.role }}</span>
                    </p>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-slate-600">
                        Use the newsroom workflow to draft, review, and approve election coverage, then explore live regional context on the GIS map.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <Link :href="route('news.index')" class="inline-flex">
                            <Button>Go to Newsroom</Button>
                        </Link>
                        <Link :href="route('map.index')" class="inline-flex">
                            <Button variant="secondary">Open Election Map</Button>
                        </Link>
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card class="bg-gradient-to-br from-amber-100/70 to-white/70">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Today's focus</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-slate-700">
                        Check review queue and verify incoming reports for regional vote counts.
                    </CardContent>
                </Card>
                <Card class="bg-gradient-to-br from-emerald-100/70 to-white/70">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Quick actions</CardTitle>
                    </CardHeader>
                    <CardContent class="text-sm text-slate-700">
                        Draft a story, tag the region, and move it to review once verified.
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
