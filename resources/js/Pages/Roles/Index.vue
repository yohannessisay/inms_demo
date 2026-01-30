<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Button from '@/Components/ui/Button.vue';
import Card from '@/Components/ui/Card.vue';
import CardContent from '@/Components/ui/CardContent.vue';
import CardHeader from '@/Components/ui/CardHeader.vue';
import CardTitle from '@/Components/ui/CardTitle.vue';
import Badge from '@/Components/ui/Badge.vue';
import Dropdown from '@/Components/Dropdown.vue';
import Modal from '@/Components/Modal.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    roles: Array,
    permissions: Array,
});

const permissionLabel = (key) => {
    return props.permissions.find((permission) => permission.key === key)?.label || key;
};

const showDeleteModal = ref(false);
const pendingRole = ref(null);

const openDelete = (role) => {
    pendingRole.value = role;
    showDeleteModal.value = true;
};

const confirmDelete = () => {
    if (!pendingRole.value) return;

    router.delete(route('roles.destroy', pendingRole.value.id), {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            pendingRole.value = null;
        },
    });
};
</script>

<template>
    <Head title="Roles" />

    <AuthenticatedLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-500">
                    Role Management
                </p>
                <h1 class="font-display text-3xl font-semibold">Roles & Permissions</h1>
            </div>
            <Link :href="route('roles.create')">
                <Button>Create Role</Button>
            </Link>
        </div>

        <Card class="mt-8">
            <CardHeader>
                <CardTitle class="text-lg">Available roles</CardTitle>
            </CardHeader>
            <CardContent class="overflow-visible">
                <div class="relative overflow-x-auto overflow-y-visible">
                    <table class="min-w-full text-left text-sm">
                        <thead class="text-xs uppercase tracking-[0.2em] text-slate-500">
                            <tr>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3">Slug</th>
                                <th class="px-4 py-3">Users</th>
                                <th class="px-4 py-3">Permissions</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/70">
                            <tr v-for="role in roles" :key="role.id" class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-900">
                                    {{ role.name }}
                                    <span v-if="role.is_system" class="ml-2 text-xs text-slate-400">System</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ role.slug }}</td>
                                <td class="px-4 py-3">
                                    <Badge variant="soft">{{ role.users_count }} users</Badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <Badge v-if="role.permissions.includes('*')" variant="success">All access</Badge>
                                        <Badge
                                            v-for="permission in role.permissions"
                                            :key="permission"
                                            v-if="permission !== '*'"
                                            variant="soft"
                                        >
                                            {{ permissionLabel(permission) }}
                                        </Badge>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <Dropdown align="right" width="48" content-classes="py-2 bg-white">
                                        <template #trigger>
                                            <button class="rounded-full px-2 py-1 text-lg text-slate-500 hover:bg-slate-100">
                                                ...
                                            </button>
                                        </template>
                                        <template #content>
                                            <Link
                                                :href="route('roles.edit', role.id)"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100"
                                            >
                                                <svg class="h-4 w-4 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-9.193 9.193a1 1 0 01-.414.263l-3.25 1.083a.5.5 0 01-.632-.632l1.083-3.25a1 1 0 01.263-.414l9.315-9.071z" />
                                                </svg>
                                                Edit
                                            </Link>
                                            <button
                                                class="flex w-full items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                :disabled="!role.can_delete"
                                                @click="openDelete(role)"
                                            >
                                                <svg class="h-4 w-4 text-rose-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M6 8a1 1 0 011 1v6a1 1 0 11-2 0V9a1 1 0 011-1zm4 1a1 1 0 10-2 0v6a1 1 0 102 0V9zm3-1a1 1 0 011 1v6a1 1 0 11-2 0V9a1 1 0 011-1z" clip-rule="evenodd" />
                                                    <path d="M4 6h12v2H4V6zm2-3h8a1 1 0 011 1v1H5V4a1 1 0 011-1z" />
                                                </svg>
                                                Delete
                                            </button>
                                        </template>
                                    </Dropdown>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <Modal :show="showDeleteModal" maxWidth="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-slate-900">Delete role</h2>
                <p class="mt-2 text-sm text-slate-600">
                    Are you sure you want to delete <span class="font-semibold">{{ pendingRole?.name }}</span>? This will archive the role. Users must be reassigned first.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <Button variant="secondary" @click="showDeleteModal = false">Cancel</Button>
                    <Button variant="danger" @click="confirmDelete">Delete</Button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
