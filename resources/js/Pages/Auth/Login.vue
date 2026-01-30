<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Label from '@/Components/ui/Label.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout title="Sign in">
        <Head title="Log in" />

        <Card class="animate-fade-up">
            <CardHeader>
                <CardTitle class="font-display text-2xl">
                    Welcome back
                </CardTitle>
                <p class="text-sm text-slate-600">
                    Log in to manage newsroom workflows and election coverage.
                </p>
            </CardHeader>
            <CardContent>
                <div
                    v-if="status"
                    class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            required
                            autofocus
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
                            autocomplete="current-password"
                        />
                        <InputError class="mt-1" :message="form.errors.password" />
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-slate-600">
                            <Checkbox name="remember" v-model:checked="form.remember" />
                            Remember me
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="font-semibold text-[var(--brand)] hover:text-[var(--brand-strong)]"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <div class="flex items-center justify-between">
                        <Link
                            :href="route('register')"
                            class="text-sm font-semibold text-slate-600"
                        >
                            Need an account?
                        </Link>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            :class="{ 'opacity-50': form.processing }"
                        >
                            Log in
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
