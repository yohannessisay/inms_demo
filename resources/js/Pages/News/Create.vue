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
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    excerpt: '',
    content: '',
});

const submit = () => {
    form.post(route('news.store'));
};
</script>

<template>
    <Head title="Create Article" />

    <AuthenticatedLayout>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    Draft
                </p>
                <h1 class="font-display text-3xl font-semibold">
                    New Article
                </h1>
            </div>
            <Link :href="route('news.index')">
                <Button variant="secondary">Back to list</Button>
            </Link>
        </div>

        <Card class="mt-8">
            <CardHeader>
                <CardTitle class="text-lg">Story details</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="title">Headline</Label>
                        <Input id="title" v-model="form.title" placeholder="Election commission releases update" />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="space-y-2">
                        <Label for="excerpt">Excerpt</Label>
                        <Textarea id="excerpt" v-model="form.excerpt" rows="3" placeholder="Short summary for editors" />
                        <InputError :message="form.errors.excerpt" />
                    </div>

                    <div class="space-y-2">
                        <Label for="content">Full story</Label>
                        <Textarea id="content" v-model="form.content" rows="10" placeholder="Write the story body..." />
                        <InputError :message="form.errors.content" />
                    </div>

                    <div class="flex items-center justify-between">
                        <p class="text-xs text-slate-500">
                            New articles start as Draft and can be sent to Review when ready.
                        </p>
                        <Button type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                            Save Draft
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
