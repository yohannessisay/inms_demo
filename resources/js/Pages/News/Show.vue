<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import StatusPill from '@/Components/StatusPill.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    article: Object,
});

const updateStatus = (status) => {
    router.patch(route('news.status', props.article.id), { status }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="article.title" />

    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    Story view
                </p>
                <h1 class="font-display text-3xl font-semibold">
                    {{ article.title }}
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <StatusPill :status="article.status" />
                <Link :href="route('news.index')">
                    <Button variant="secondary">Back to list</Button>
                </Link>
                <Link v-if="article.can.edit" :href="route('news.edit', article.id)">
                    <Button>Edit</Button>
                </Link>
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[2fr_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Story overview</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <p class="text-sm text-slate-600">
                        {{ article.excerpt || 'No excerpt provided yet.' }}
                    </p>
                    <div class="rounded-2xl border border-slate-200/70 bg-white/70 p-5 text-sm text-slate-700">
                        {{ article.content }}
                    </div>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card class="bg-white/80">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Metadata</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-600">
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Author</p>
                            <p class="font-semibold text-slate-800">{{ article.author?.name || 'Unknown' }}</p>
                            <p class="text-xs text-slate-500">{{ article.author?.role || 'Reporter' }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Created</p>
                            <p>{{ new Date(article.created_at).toLocaleString() }}</p>
                        </div>
                        <div v-if="article.published_at">
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Published</p>
                            <p>{{ new Date(article.published_at).toLocaleString() }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-slate-100/70 to-white/70">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Workflow actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <Button
                            v-if="article.can.move_to_review"
                            size="sm"
                            variant="outline"
                            @click="updateStatus('review')"
                        >
                            Send to Review
                        </Button>
                        <Button
                            v-if="article.can.approve"
                            size="sm"
                            @click="updateStatus('approved')"
                        >
                            Approve Story
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
