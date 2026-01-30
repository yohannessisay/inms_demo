<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Select from '@/Components/ui/Select.vue';
import Label from '@/Components/ui/Label.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    role: props.user.role,
    is_active: !!props.user.is_active,
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>

<template>
    <Head title="Edit User" />

    <AuthenticatedLayout>
        <div class="flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">User</p>
                <h1 class="font-display text-3xl font-semibold">Edit User</h1>
            </div>
            <Link :href="route('users.index')">
                <Button variant="secondary">Back to users</Button>
            </Link>
        </div>

        <Card class="mt-8">
            <CardHeader>
                <CardTitle class="text-lg">{{ user.name }}</CardTitle>
                <p class="text-sm text-slate-500">{{ user.email }}</p>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <Label for="role">Role</Label>
                        <Select id="role" v-model="form.role">
                            <option v-for="role in roles" :key="role.slug" :value="role.slug">
                                {{ role.name }}
                            </option>
                        </Select>
                        <InputError :message="form.errors.role" />
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-600">
                        <Checkbox v-model:checked="form.is_active" />
                        Active account
                    </label>
                    <InputError :message="form.errors.is_active" />

                    <div class="flex items-center justify-end">
                        <Button type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                            Save changes
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </AuthenticatedLayout>
</template>
