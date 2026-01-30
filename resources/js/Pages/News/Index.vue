<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Select from '@/Components/ui/Select.vue';
import Card from '@/Components/ui/Card.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import StatusPill from '@/Components/StatusPill.vue';
import StatCard from '@/Components/StatCard.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    articles: Object,
    filters: Object,
    statuses: Array,
    stats: Object,
});

const statusFilter = ref(props.filters.status || 'all');
const userPermissions = usePage().props.auth.user?.permissions || [];
const canCreate = userPermissions.includes('*') || userPermissions.includes('articles.create') || userPermissions.includes('articles.manage');

const applyFilter = () => {
    router.get(
        route('news.index'),
        {
            status: statusFilter.value === 'all' ? null : statusFilter.value,
        },
        { preserveState: true, replace: true },
    );
};

const updateStatus = (articleId, status) => {
    router.patch(
        route('news.status', articleId),
        { status },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Newsroom" />

    <AuthenticatedLayout>
        <section class="space-y-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                        Newsroom
                    </p>
                    <h1 class="font-display text-3xl font-semibold text-slate-900">
                        Article Workflow
                    </h1>
                </div>
                <div class="flex items-center gap-3">
                    <Select v-model="statusFilter" class="w-44" @change="applyFilter">
                        <option value="all">All statuses</option>
                        <option v-for="status in statuses" :key="status" :value="status">
                            {{ status }}
                        </option>
                    </Select>
                    <Link v-if="canCreate" :href="route('news.create')">
                        <Button>New Article</Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <StatCard label="Total" :value="stats.total" hint="All assigned stories" />
                <StatCard label="Drafts" :value="stats.draft" tone="accent" hint="Reporter stage" />
                <StatCard label="Review" :value="stats.review" tone="warning" hint="Editor queue" />
                <StatCard label="Approved" :value="stats.approved" tone="primary" hint="Ready to publish" />
            </div>

            <div class="grid gap-4">
                <Card v-for="article in articles.data" :key="article.id" class="transition hover:-translate-y-0.5">
                    <CardHeader class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <StatusPill :status="article.status" />
                                <span class="text-xs font-semibold text-slate-500">
                                    {{ new Date(article.created_at).toLocaleDateString() }}
                                </span>
                            </div>
                            <Link :href="route('news.show', article.id)">
                                <CardTitle class="font-display text-xl hover:text-[var(--brand)]">
                                    {{ article.title }}
                                </CardTitle>
                            </Link>
                            <p class="max-w-2xl text-sm text-slate-600">
                                {{ article.excerpt || 'No excerpt provided yet.' }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-3">
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">
                                    Author
                                </p>
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ article.author?.name || 'Unknown' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ article.author?.role }}
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <Link
                                    v-if="article.can.edit"
                                    :href="route('news.edit', article.id)"
                                >
                                    <Button size="sm" variant="secondary">Edit</Button>
                                </Link>
                                <Button
                                    v-if="article.can.move_to_review"
                                    size="sm"
                                    variant="outline"
                                    @click="updateStatus(article.id, 'review')"
                                >
                                    Send to Review
                                </Button>
                                <Button
                                    v-if="article.can.approve"
                                    size="sm"
                                    @click="updateStatus(article.id, 'approved')"
                                >
                                    Approve
                                </Button>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="pt-0"></CardContent>
                </Card>

                <div v-if="articles.data.length === 0" class="rounded-3xl border border-dashed border-slate-300/70 bg-white/70 p-10 text-center text-sm text-slate-600">
                    No articles found. Start by drafting a new story.
                </div>
            </div>

            <div v-if="articles.links?.length" class="flex flex-wrap gap-2">
                <Link
                    v-for="link in articles.links"
                    :key="link.label"
                    :href="link.url || ''"
                    class="rounded-full border border-slate-200/70 px-4 py-2 text-xs font-semibold"
                    :class="{
                        'bg-slate-900 text-white': link.active,
                        'text-slate-400 pointer-events-none': !link.url,
                    }"
                >
                    <span v-html="link.label" />
                </Link>
            </div>
        </section>
    </AuthenticatedLayout>
</template>
