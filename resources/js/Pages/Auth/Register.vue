<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout title="Create account">
        <Head title="Register" />

        <Card class="animate-fade-up">
            <CardHeader>
                <CardTitle class="font-display text-2xl">
                    Build your newsroom profile
                </CardTitle>
                <p class="text-sm text-slate-600">
                    Create an account to submit, edit, and approve stories.
                </p>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            type="text"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-1" :message="form.errors.name" />
                    </div>

                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-1" :message="form.errors.email" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-1"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <div class="flex items-center justify-between">
                        <Link
                            :href="route('login')"
                            class="text-sm font-semibold text-slate-600"
                        >
                            Already registered?
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                        >
                            Register
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
