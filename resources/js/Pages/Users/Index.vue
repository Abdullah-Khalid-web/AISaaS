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
    users: {
        type: Object,
        default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    },
    roles: {
        type: Array,
        default: () => []
    },
    permissions: {
        type: Array,
        default: () => []
    }
});

// State
const search = ref(props.filters?.search || '');
const selectedUsers = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);
const showUserModal = ref(false);
const showRoleModal = ref(false);
const editingUser = ref(null);
const modalMode = ref('create'); // 'create' or 'edit'

// Form state
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    roles: [],
    permissions: [],
    is_active: true
});

// Role change form
const roleForm = ref({
    user_id: null,
    roles: [],
    permissions: []
});

// Search debounce
const debouncedSearch = debounce((value) => {
    router.get(route('users.index'), { search: value }, {
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
    if (value && props.users?.data) {
        selectedUsers.value = props.users.data.map(user => user.id);
    } else {
        selectedUsers.value = [];
    }
});

// Check if all are selected
watch(selectedUsers, (value) => {
    if (props.users?.data) {
        selectAll.value = value.length === props.users.data.length && props.users.data.length > 0;
    }
}, { deep: true });

// Methods
const openCreateModal = () => {
    modalMode.value = 'create';
    editingUser.value = null;
    form.value = {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [],
        permissions: [],
        is_active: true
    };
    showUserModal.value = true;
};

const openEditModal = (user) => {
    modalMode.value = 'edit';
    editingUser.value = user;
    form.value = {
        name: user.name,
        email: user.email,
        password: '',
        password_confirmation: '',
        roles: user.roles?.map(role => role.name) || [],
        permissions: user.permissions?.map(perm => perm.name) || [],
        is_active: user.is_active ?? true
    };
    showUserModal.value = true;
};

const openRoleModal = (user) => {
    roleForm.value = {
        user_id: user.id,
        roles: user.roles?.map(role => role.name) || [],
        permissions: user.permissions?.map(perm => perm.name) || []
    };
    showRoleModal.value = true;
};

const submitUser = () => {
    if (modalMode.value === 'create') {
        router.post(route('users.store'), form.value, {
            onSuccess: () => {
                showUserModal.value = false;
                form.value = {
                    name: '',
                    email: '',
                    password: '',
                    password_confirmation: '',
                    roles: [],
                    permissions: [],
                    is_active: true
                };
            },
            onError: (errors) => {
                console.error('Create user errors:', errors);
            }
        });
    } else {
        router.put(route('users.update', editingUser.value.id), form.value, {
            onSuccess: () => {
                showUserModal.value = false;
            },
            onError: (errors) => {
                console.error('Update user errors:', errors);
            }
        });
    }
};

const updateRoles = () => {
    router.put(route('users.update', roleForm.value.user_id), {
        roles: roleForm.value.roles,
        permissions: roleForm.value.permissions
    }, {
        onSuccess: () => {
            showRoleModal.value = false;
        },
        onError: (errors) => {
            console.error('Update roles errors:', errors);
        }
    });
};

const toggleStatus = (user) => {
    router.post(route('users.toggle-status', user.id), {}, {
        preserveScroll: true,
        onError: (errors) => {
            console.error('Toggle status errors:', errors);
        }
    });
};

const deleteUser = (user) => {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Delete user errors:', errors);
            }
        });
    }
};

const bulkDelete = () => {
    if (selectedUsers.value.length === 0) return;

    if (confirm(`Are you sure you want to delete ${selectedUsers.value.length} users?`)) {
        router.post(route('users.bulk-destroy'), {
            ids: selectedUsers.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedUsers.value = [];
                showDeleteModal.value = false;
            },
            onError: (errors) => {
                console.error('Bulk delete errors:', errors);
            }
        });
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
    <Head title="User Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    User Management
                </h2>
                <button @click="openCreateModal" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New User
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
                                    placeholder="Search users by name or email..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedUsers.length > 0" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ selectedUsers.length }} selected</span>
                            <button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                Delete Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Roles</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <input
                                        type="checkbox"
                                        v-model="selectedUsers"
                                        :value="user.id"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium text-sm">
                                                    {{ user.name?.charAt(0).toUpperCase() || 'U' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                            <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="role in user.roles"
                                            :key="role.id"
                                            class="px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800"
                                        >
                                            {{ role.name }}
                                        </span>
                                        <span v-if="!user.roles?.length" class="text-xs text-gray-400">No roles</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="toggleStatus(user)"
                                        class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none"
                                        :class="user.is_active ? 'bg-green-600' : 'bg-gray-300'"
                                    >
                                        <span class="sr-only">Toggle status</span>
                                        <span
                                            class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                            :class="user.is_active ? 'translate-x-5' : 'translate-x-0'"
                                        />
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium">
                                    <button @click="openRoleModal(user)" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Manage Roles">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </button>
                                    <button @click="openEditModal(user)" class="text-blue-600 hover:text-blue-900 mr-3" title="Edit User">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteUser(user)" class="text-red-600 hover:text-red-900" title="Delete User">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="!users.data || users.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="users && users.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ users.from || 0 }}</span> to <span class="font-medium">{{ users.to || 0 }}</span> of <span class="font-medium">{{ users.total || 0 }}</span> results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in users.links"
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

        <!-- Create/Edit User Modal -->
        <div v-if="showUserModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showUserModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ modalMode === 'create' ? 'Create New User' : 'Edit User' }}
                        </h3>

                        <form @submit.prevent="submitUser" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div v-if="modalMode === 'create'">
                                <label class="block text-sm font-medium text-gray-700">Password</label>
                                <input
                                    v-model="form.password"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div v-if="modalMode === 'create'">
                                <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                <input
                                    v-model="form.password_confirmation"
                                    type="password"
                                    required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Roles</label>
                                <div class="mt-2 space-y-2">
                                    <div v-for="role in roles" :key="role.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="role.name"
                                            v-model="form.roles"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">{{ role.name }}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <span class="ml-2 text-sm text-gray-700">Active</span>
                                    </label>
                                </div>
                            </div> -->
                        </form>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="submit"
                            @click="submitUser"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            {{ modalMode === 'create' ? 'Create' : 'Update' }}
                        </button>
                        <button
                            @click="showUserModal = false"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Assignment Modal -->
        <div v-if="showRoleModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRoleModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Manage User Roles & Permissions
                        </h3>

                        <form @submit.prevent="updateRoles" class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Assign Roles</label>
                                <div class="space-y-2">
                                    <div v-for="role in roles" :key="role.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="role.name"
                                            v-model="roleForm.roles"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">{{ role.name }}</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Additional Permissions</label>
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    <div v-for="permission in permissions" :key="permission.id" class="flex items-center">
                                        <input
                                            type="checkbox"
                                            :value="permission.name"
                                            v-model="roleForm.permissions"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                        <label class="ml-2 text-sm text-gray-700">{{ permission.name }}</label>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            type="submit"
                            @click="updateRoles"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Update
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
    </AppLayout>
</template>
