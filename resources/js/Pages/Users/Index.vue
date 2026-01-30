<script setup>
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Input from '@/Components/ui/Input.vue';
import Select from '@/Components/ui/Select.vue';
import Badge from '@/Components/ui/Badge.vue';
import Drawer from '@/Components/ui/Drawer.vue';
import Label from '@/Components/ui/Label.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import Dropdown from '@/Components/Dropdown.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: Object,
    roles: Array,
    filters: Object,
});

const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const statusFilter = ref(props.filters.status || '');

const showDeactivateModal = ref(false);
const pendingUser = ref(null);
const showDrawer = ref(false);
const editingUser = ref(null);

const page = usePage();
const currentUserId = computed(() => page.props.auth.user?.id);
const defaultRole = computed(() => props.roles[0]?.slug || '');

const form = useForm({
    name: '',
    email: '',
    role: defaultRole.value,
    is_active: true,
    password: '',
    password_confirmation: '',
});

const applyFilters = () => {
    router.get(
        route('users.index'),
        {
            search: search.value || null,
            role: roleFilter.value || null,
            status: statusFilter.value || null,
        },
        { preserveState: true, replace: true },
    );
};

const openDeactivate = (user) => {
    pendingUser.value = user;
    showDeactivateModal.value = true;
};

const confirmDeactivate = () => {
    if (!pendingUser.value) return;

    router.post(route('users.deactivate', pendingUser.value.id), {}, {
        preserveScroll: true,
        onFinish: () => {
            showDeactivateModal.value = false;
            pendingUser.value = null;
        },
    });
};

const reactivateUser = (user) => {
    router.post(route('users.reactivate', user.id), {}, { preserveScroll: true });
};

const resetFormForCreate = () => {
    form.name = '';
    form.email = '';
    form.role = defaultRole.value;
    form.is_active = true;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
};

const openCreate = () => {
    editingUser.value = null;
    resetFormForCreate();
    showDrawer.value = true;
};

const openEdit = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.role = user.role;
    form.is_active = user.is_active;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    showDrawer.value = true;
};

const closeDrawer = () => {
    showDrawer.value = false;
    editingUser.value = null;
    form.clearErrors();
};

const submit = () => {
    if (editingUser.value) {
        form.put(route('users.update', editingUser.value.id), {
            preserveScroll: true,
            onSuccess: closeDrawer,
        });
        return;
    }

    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: closeDrawer,
    });
};

const roleLabel = (slug) => props.roles.find((role) => role.slug === slug)?.name || slug;
</script>

<template>
    <Head title="Users" />

    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    User Management
                </p>
                <h1 class="font-display text-3xl font-semibold">Users</h1>
            </div>
            <Button @click="openCreate">New user</Button>
        </div>

        <Card class="mt-8">
            <CardHeader>
                <CardTitle class="text-lg">Directory</CardTitle>
            </CardHeader>
            <CardContent>
                <div class="flex flex-wrap gap-3">
                    <Input
                        v-model="search"
                        placeholder="Search by name or email"
                        class="min-w-[220px]"
                    />
                    <Select v-model="roleFilter" class="w-48" @change="applyFilters">
                        <option value="">All roles</option>
                        <option v-for="role in roles" :key="role.slug" :value="role.slug">
                            {{ role.name }}
                        </option>
                    </Select>
                    <Select v-model="statusFilter" class="w-48" @change="applyFilters">
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </Select>
                    <Button variant="secondary" @click="applyFilters">Apply</Button>
                </div>

                <div class="relative mt-6 overflow-x-auto overflow-y-visible">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ user.name }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ user.email }}</td>
                                <td class="px-4 py-3">
                                    <Badge variant="soft">{{ roleLabel(user.role) }}</Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :variant="user.is_active ? 'success' : 'danger'">
                                        {{ user.is_active ? 'Active' : 'Inactive' }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ new Date(user.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Dropdown align="right" width="48" content-classes="py-2 bg-white">
                                        <template #trigger>
                                            <button class="rounded-full px-2 py-1 text-lg text-slate-500 hover:bg-slate-100">
                                                ...
                                            </button>
                                        </template>
                                        <template #content>
                                            <button
                                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                                @click="openEdit(user)"
                                            >
                                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-9.193 9.193a1 1 0 01-.414.263l-3.25 1.083a.5.5 0 01-.632-.632l1.083-3.25a1 1 0 01.263-.414l9.315-9.071z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <button
                                                v-if="user.is_active"
                                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                :disabled="user.id === currentUserId"
                                                @click="openDeactivate(user)"
                                            >
                                                <svg class="h-4 w-4 text-rose-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.536-10.95a1 1 0 00-1.414-1.414L6.05 11.707a1 1 0 101.414 1.414l6.072-6.072z" clip-rule="evenodd" />
                                                </svg>
                                                Deactivate
                                            </button>
                                            <button
                                                v-else
                                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50"
                                                @click="reactivateUser(user)"
                                            >
                                                <svg class="h-4 w-4 text-emerald-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm4.293-9.707a1 1 0 00-1.414-1.414L9 11.586 7.121 9.707a1 1 0 10-1.414 1.414l2.586 2.586a1 1 0 001.414 0l4.586-4.586z" clip-rule="evenodd" />
                                                </svg>
                                                Reactivate
                                            </button>
                                        </template>
                                    </Dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="users.links?.length" class="mt-6 flex flex-wrap gap-2">
                    <Link
                        v-for="link in users.links"
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
            </CardContent>
        </Card>

        <Drawer :show="showDrawer" maxWidth="lg" @close="closeDrawer">
            <form @submit.prevent="submit" class="flex h-full flex-col">
                <div class="border-b border-slate-200/70 px-6 py-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                        {{ editingUser ? 'Edit user' : 'Create user' }}
                    </p>
                    <h2 class="font-display text-2xl font-semibold text-slate-900">
                        {{ editingUser ? editingUser.name : 'New user profile' }}
                    </h2>
                </div>

                <div class="flex-1 space-y-8 overflow-y-auto px-6 py-6">
                    <div class="grid gap-6 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="user-name">Full name</Label>
                            <Input id="user-name" v-model="form.name" placeholder="Aster Tadesse" />
                            <InputError :message="form.errors.name" />
                        </div>
                        <div class="space-y-2">
                            <Label for="user-email">Email address</Label>
                            <Input id="user-email" type="email" v-model="form.email" placeholder="aster@inms.et" />
                            <InputError :message="form.errors.email" />
                        </div>
                        <div class="space-y-2">
                            <Label for="user-role">Role</Label>
                            <Select id="user-role" v-model="form.role">
                                <option value="" disabled>Select a role</option>
                                <option v-for="role in roles" :key="role.slug" :value="role.slug">
                                    {{ role.name }}
                                </option>
                            </Select>
                            <InputError :message="form.errors.role" />
                        </div>
                        <div class="space-y-2">
                            <Label>Status</Label>
                            <label class="flex items-center gap-3 rounded-2xl border border-slate-200/70 bg-white/80 p-3">
                                <Checkbox
                                    :checked="form.is_active"
                                    @update:checked="(value) => (form.is_active = value)"
                                />
                                <span class="text-sm text-slate-700">Active account</span>
                            </label>
                            <InputError :message="form.errors.is_active" />
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="user-password">Password</Label>
                                <Input
                                    id="user-password"
                                    type="password"
                                    v-model="form.password"
                                    placeholder="Create a password"
                                />
                                <InputError :message="form.errors.password" />
                            </div>
                            <div class="space-y-2">
                                <Label for="user-password-confirmation">Confirm password</Label>
                                <Input
                                    id="user-password-confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    placeholder="Confirm password"
                                />
                                <InputError :message="form.errors.password_confirmation" />
                            </div>
                        </div>
                        <p v-if="editingUser" class="text-xs text-slate-500">
                            Leave password fields blank to keep the current password.
                        </p>
                    </div>
                </div>

                <div class="border-t border-slate-200/70 px-6 py-4">
                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="secondary" @click="closeDrawer">Cancel</Button>
                        <Button type="submit" :disabled="form.processing" :class="{ 'opacity-50': form.processing }">
                            {{ editingUser ? 'Save changes' : 'Create user' }}
                        </Button>
                    </div>
                </div>
            </form>
        </Drawer>

        <Modal :show="showDeactivateModal" maxWidth="md" @close="showDeactivateModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-slate-900">Deactivate user</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Are you sure you want to deactivate <span class="font-semibold">{{ pendingUser?.name }}</span>? This will block access until reactivated.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <Button variant="secondary" @click="showDeactivateModal = false">Cancel</Button>
                    <Button variant="danger" @click="confirmDeactivate">Deactivate</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
