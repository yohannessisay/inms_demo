<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import Textarea from '@/Components/ui/Textarea.vue';
import StatusPill from '@/Components/StatusPill.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    article: Object,
    statuses: Array,
    can: Object,
});

const form = useForm({
    title: props.article.title,
    excerpt: props.article.excerpt || '',
    content: props.article.content || '',
    refresh_slug: false,
});

const submit = () => {
    form.put(route('news.update', props.article.id));
};

const updateStatus = (status) => {
    router.patch(route('news.status', props.article.id), { status }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="`Edit: ${article.title}`" />

    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    Editing
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
            </div>
        </div>

        <div class="mt-8 grid gap-6 lg:grid-cols-[2fr_1fr]">
            <Card>
                <CardHeader>
                    <CardTitle class="text-lg">Update story</CardTitle>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <Label for="title">Headline</Label>
                            <Input id="title" v-model="form.title" />
                            <InputError :message="form.errors.title" />
                        </div>

                        <div class="space-y-2">
                            <Label for="excerpt">Excerpt</Label>
                            <Textarea id="excerpt" v-model="form.excerpt" rows="3" />
                            <InputError :message="form.errors.excerpt" />
                        </div>

                        <div class="space-y-2">
                            <Label for="content">Full story</Label>
                            <Textarea id="content" v-model="form.content" rows="10" />
                            <InputError :message="form.errors.content" />
                        </div>

                        <label class="flex items-center gap-2 text-sm text-slate-600">
                            <Checkbox v-model:checked="form.refresh_slug" />
                            Regenerate slug from headline
                        </label>

                        <div class="flex items-center justify-between">
                            <p class="text-xs text-slate-500">Current slug: {{ article.slug }}</p>
                            <Button type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                                Save changes
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <div class="space-y-4">
                <Card class="bg-white/80">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Workflow actions</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3 text-sm text-slate-600">
                        <p>Move the story along the approval chain when it is ready.</p>
                        <div class="flex flex-col gap-2">
                            <Button
                                v-if="can.move_to_review"
                                size="sm"
                                variant="outline"
                                @click="updateStatus('review')"
                            >
                                Send to Review
                            </Button>
                            <Button
                                v-if="can.approve"
                                size="sm"
                                @click="updateStatus('approved')"
                            >
                                Approve Story
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card class="bg-gradient-to-br from-slate-100/70 to-white/60">
                    <CardHeader>
                        <CardTitle class="text-base font-semibold">Guidelines</CardTitle>
                    </CardHeader>
                    <CardContent class="text-xs text-slate-600">
                        Keep the excerpt concise and ensure all facts are verified before approval.
                    </CardContent>
                </Card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
