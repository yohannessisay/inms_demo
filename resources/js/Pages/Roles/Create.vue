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
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    permissions: Array,
});

const form = useForm({
    name: '',
    slug: '',
    description: '',
    permissions: [],
});

const togglePermission = (key) => {
    if (form.permissions.includes(key)) {
        form.permissions = form.permissions.filter((item) => item !== key);
        return;
    }

    form.permissions = [...form.permissions, key];
};

const submit = () => {
    form.post(route('roles.store'));
};
</script>

<template>
    <Head title="Create Role" />

    <AuthenticatedLayout>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">Role</p>
                <h1 class="font-display text-3xl font-semibold">Create Role</h1>
            </div>
            <Link :href="route('roles.index')">
                <Button variant="secondary">Back to roles</Button>
            </Link>
        </div>

        <Card class="mt-8">
            <CardHeader>
                <CardTitle class="text-lg">Role details</CardTitle>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">Role name</Label>
                            <Input id="name" v-model="form.name" placeholder="Editor" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="slug">Slug</Label>
                            <Input id="slug" v-model="form.slug" placeholder="editor" />
                            <InputError :message="form.errors.slug" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">Description</Label>
                        <Textarea id="description" v-model="form.description" rows="3" placeholder="Describe responsibilities" />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Permissions</p>
                            <p class="text-xs text-slate-500">Choose what this role can access.</p>
                        </div>
                        <div class="grid gap-3 md:grid-cols-2">
                            <label
                                v-for="permission in permissions"
                                :key="permission.key"
                                class="flex items-center gap-3 rounded-2xl border border-slate-200/70 bg-white/80 p-3"
                            >
                                <Checkbox
                                    :checked="form.permissions.includes(permission.key)"
                                    @update:checked="togglePermission(permission.key)"
                                />
                                <span class="text-sm text-slate-700">{{ permission.label }}</span>
                            </label>
                        </div>
                        <InputError :message="form.errors.permissions" />
                    </div>

                    <div class="flex items-center justify-end">
                        <Button type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                            Save role
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
