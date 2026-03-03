<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

// Custom debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Props
const props = defineProps({
    roles: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    },
    permissions: {
        type: Array,
        default: () => []
    }
});

// State
const search = ref(props.filters?.search || '');
const selectedRoles = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const showRoleModal = ref(false);
const showPermissionsModal = ref(false);
const editingRole = ref(null);
const modalMode = ref('create'); // 'create' or 'edit'

// Form state
const form = ref({
    name: '',
    guard_name: 'web',
    permissions: []
});

// Permissions form
const permissionsForm = ref({
    role_id: null,
    role_name: '',
    permissions: []
});

// Search debounce
const debouncedSearch = debounce((value) => {
    router.get(route('roles.index'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

// Select all functionality
watch(selectAll, (value) => {
    if (value && props.roles?.data) {
        selectedRoles.value = props.roles.data.map(role => role.id);
    } else {
        selectedRoles.value = [];
    }
});

// Check if all are selected
watch(selectedRoles, (value) => {
    if (props.roles?.data) {
        selectAll.value = value.length === props.roles.data.length && props.roles.data.length > 0;
    }
}, { deep: true });

// Methods
const openCreateModal = () => {
    modalMode.value = 'create';
    editingRole.value = null;
    form.value = {
        name: '',
        guard_name: 'web',
        permissions: []
    };
    showRoleModal.value = true;
};

const openEditModal = (role) => {
    modalMode.value = 'edit';
    editingRole.value = role;
    form.value = {
        name: role.name,
        guard_name: role.guard_name,
        permissions: role.permissions?.map(p => p.name) || []
    };
    showRoleModal.value = true;
};

const openPermissionsModal = (role) => {
    permissionsForm.value = {
        role_id: role.id,
        role_name: role.name,
        permissions: role.permissions?.map(p => p.name) || []
    };
    showPermissionsModal.value = true;
};

const submitRole = () => {
    if (modalMode.value === 'create') {
        router.post(route('roles.store'), form.value, {
            onSuccess: () => {
                showRoleModal.value = false;
                form.value = {
                    name: '',
                    guard_name: 'web',
                    permissions: []
                };
            },
            onError: (errors) => {
                console.error('Create role errors:', errors);
            }
        });
    } else {
        router.put(route('roles.update', editingRole.value.id), form.value, {
            onSuccess: () => {
                showRoleModal.value = false;
            },
            onError: (errors) => {
                console.error('Update role errors:', errors);
            }
        });
    }
};

const updatePermissions = () => {
    router.put(route('roles.update', permissionsForm.value.role_id), {
        name: permissionsForm.value.role_name,
        permissions: permissionsForm.value.permissions
    }, {
        onSuccess: () => {
            showPermissionsModal.value = false;
        },
        onError: (errors) => {
            console.error('Update permissions errors:', errors);
        }
    });
};

const deleteRole = (role) => {
    if (role.name === 'admin' || role.name === 'super-admin') {
        alert('System roles cannot be deleted.');
        return;
    }

    if (confirm(`Are you sure you want to delete the "${role.name}" role? Users with this role may be affected.`)) {
        router.delete(route('roles.destroy', role.id), {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Delete role errors:', errors);
            }
        });
    }
};

const bulkDelete = () => {
    if (selectedRoles.value.length === 0) return;

    if (confirm(`Are you sure you want to delete ${selectedRoles.value.length} roles?`)) {
        alert('Bulk delete for roles not implemented yet');
        selectedRoles.value = [];
        showDeleteModal.value = false;
    }
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Role Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Role Management
                </h2>
                <button @click="openCreateModal" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Role
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 flex flex-col sm:flex-row gap-4 justify-between">
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search roles..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedRoles.length > 0" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ selectedRoles.length }} selected</span>
                            <button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                Delete Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Roles Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guard</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="role in roles.data" :key="role.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        v-model="selectedRoles"
                                        :value="role.id"
                                        :disabled="role.name === 'admin' || role.name === 'super-admin'"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0 rounded-full flex items-center justify-center" :class="{
                                            'bg-purple-100': role.name === 'super-admin',
                                            'bg-blue-100': role.name === 'admin',
                                            'bg-gray-100': role.name !== 'admin' && role.name !== 'super-admin'
                                        }">
                                            <svg class="h-5 w-5" :class="{
                                                'text-purple-600': role.name === 'super-admin',
                                                'text-blue-600': role.name === 'admin',
                                                'text-gray-600': role.name !== 'admin' && role.name !== 'super-admin'
                                            }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ role.name }}</div>
                                            <div class="text-xs text-gray-500">ID: {{ role.id }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                        {{ role.guard_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <span
                                            v-for="permission in role.permissions?.slice(0, 3)"
                                            :key="permission.id"
                                            class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800"
                                        >
                                            {{ permission.name }}
                                        </span>
                                        <span v-if="role.permissions?.length > 3" class="text-xs text-gray-500">
                                            +{{ role.permissions.length - 3 }} more
                                        </span>
                                        <span v-if="!role.permissions?.length" class="text-xs text-gray-400">
                                            No permissions
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(role.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button @click="openPermissionsModal(role)" class="text-green-600 hover:text-green-900 mr-3" title="Manage Permissions">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                    <button @click="openEditModal(role)" class="text-blue-600 hover:text-blue-900 mr-3" title="Edit Role">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteRole(role)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete Role"
                                        :disabled="role.name === 'admin' || role.name === 'super-admin'"
                                        :class="{ 'opacity-50 cursor-not-allowed': role.name === 'admin' || role.name === 'super-admin' }"
                                    >
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!roles.data || roles.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No roles found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="roles && roles.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ roles.from || 0 }}</span> to <span class="font-medium">{{ roles.to || 0 }}</span> of <span class="font-medium">{{ roles.total || 0 }}</span> results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in roles.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="px-3 py-1 rounded-md text-sm"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                        'text-gray-400 cursor-not-allowed': !link.url
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Role Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRoleModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ modalMode === 'create' ? 'Create New Role' : 'Edit Role' }}
                        </h3>

                        <form @submit.prevent="submitRole" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Role Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    placeholder="e.g., editor, moderator"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Guard Name</label>
                                <select
                                    v-model="form.guard_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="web">Web</option>
                                    <option value="api">API</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assign Permissions</label>
                                <div class="space-y-2 max-h-60 overflow-y-auto border rounded-lg p-3">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="permission.name"
                                            v-model="form.permissions"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">{{ permission.name }}</label>
                                    </div>
                                    <div v-if="permissions.length === 0" class="text-sm text-gray-500 text-center py-2">
                                        No permissions available. Create permissions first.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="submit"
                            @click="submitRole"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ modalMode === 'create' ? 'Create' : 'Update' }}
                        </button>
                        <button
                            @click="showRoleModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manage Permissions Modal -->
        <div v-if="showPermissionsModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showPermissionsModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Manage Permissions for "{{ permissionsForm.role_name }}"
                        </h3>

                        <form @submit.prevent="updatePermissions" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Permissions</label>
                                <div class="space-y-2 max-h-96 overflow-y-auto border rounded-lg p-3">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="permission.name"
                                            v-model="permissionsForm.permissions"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">{{ permission.name }}</label>
                                    </div>
                                    <div v-if="permissions.length === 0" class="text-sm text-gray-500 text-center py-2">
                                        No permissions available.
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="submit"
                            @click="updatePermissions"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Update Permissions
                        </button>
                        <button
                            @click="showPermissionsModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
