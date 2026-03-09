<!-- resources/js/Layouts/Sidebar.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const isOpen = ref(true);
const isUserMenuOpen = ref(false);
const isPlanMenuOpen = ref(false);
const isToolMenuOpen = ref(false);
const isSubscriptionsMenuOpen = ref(false);
const isRolesMenuOpen = ref(false);
const isPermissionsMenuOpen = ref(false);

// Get the current user's permissions from the page props
const page = usePage();
const userPermissions = computed(() => page.props.auth?.user?.permissions || []);
const userRoles = computed(() => page.props.auth?.user?.roles || []);

// Permission check function
const can = (permission) => {
    if (!permission) return true;
    if (Array.isArray(permission)) {
        return permission.some(p => userPermissions.value.includes(p));
    }
    return userPermissions.value.includes(permission);
};

// Check if user has any of the given permissions
const canAny = (permissions) => {
    return permissions.some(p => userPermissions.value.includes(p));
};

const tools = [
    { name: 'Text Generator', icon: 'M4 6h16M4 12h16M4 18h7', route: 'tools.text-generator', permission: 'use text generator' },
    { name: 'Image Generator', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', route: 'tools.image-generator', permission: 'use image generator' },
    { name: 'Code Assistant', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', route: 'tools.code-assistant', permission: 'use code assistant' },
    { name: 'Chat Bot', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', route: 'tools.chat-bot', permission: 'use chat bot' },
    { name: 'Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'tools.analytics', permission: 'view analytics' },
];
</script>

<template>
    <aside
        :class="[
            'bg-gray-800 text-white h-screen sticky top-0 overflow-y-auto transition-all duration-300',
            isOpen ? 'w-64' : 'w-20'
        ]"
    >
        <!-- Logo area -->
        <div class="flex items-center justify-between h-16 px-4 border-b border-gray-700">
            <div v-if="isOpen" class="text-xl font-bold text-white">
                AI<span class="text-indigo-400">Tool</span>
            </div>
            <button
                @click="isOpen = !isOpen"
                class="p-1 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none"
                :class="{ 'ml-auto': !isOpen }"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isOpen ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'" />
                </svg>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="mt-5 px-2 pb-20">
            <!-- Dashboard (always visible) -->
            <Link
                href="/dashboard"
                class="flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group mb-1"
                :class="{ 'justify-center': !isOpen }"
            >
                <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span v-if="isOpen" class="flex-1">Dashboard</span>
                <div v-else class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                    Dashboard
                </div>
            </Link>

            <!-- User Management - Only if user has user management permissions -->
            <div v-if="canAny(['view users', 'create users', 'edit users', 'delete users'])" class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isUserMenuOpen = !isUserMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>User Management</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isUserMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu with permission checks -->
                    <div v-show="isUserMenuOpen" class="mt-1 space-y-1 pl-11">
                        <!-- View Users Link -->
                        <Link
                            v-if="can('view users')"
                            href="/users"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Users
                        </Link>

                        <!-- Create User Link -->
                        <Link
                            v-if="can('create users')"
                            href="/users/create"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Create User
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state -->
                <div v-else class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        User Management
                    </div>
                </div>
            </div>

            <!-- Roles & Permissions Management -->
            <div v-if="canAny(['view roles', 'create roles', 'edit roles', 'delete roles', 'view permissions'])" class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isRolesMenuOpen = !isRolesMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            <span>Roles & Permissions</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isRolesMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-show="isRolesMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link
                            v-if="can('view roles')"
                            href="/roles"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Roles
                        </Link>
                        <Link
                            v-if="can('view permissions')"
                            href="/permissions"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Permissions
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state -->
                <div v-else class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        Roles & Permissions
                    </div>
                </div>
            </div>

            <!-- Plan Management - Only if user has plan permissions -->
            <div v-if="canAny(['view plans', 'create plans', 'edit plans', 'delete plans'])" class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isPlanMenuOpen = !isPlanMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Plan Management</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isPlanMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-show="isPlanMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link
                            v-if="can('view plans')"
                            href="/plans"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Plans
                        </Link>
                        <Link
                            v-if="can('create plans')"
                            :href="route('plans.create')"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Create Plans
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state -->
                <div v-else class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </button>
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        Plan Management
                    </div>
                </div>
            </div>

            <!-- Tool Management - Only if user has tool permissions -->
            <div v-if="canAny(['view tools', 'create tools', 'edit tools', 'delete tools'])" class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isToolMenuOpen = !isToolMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span>Tool Management</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isToolMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-show="isToolMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link
                            href="/tools"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Tools
                        </Link>
                        <Link
                            v-if="can('create tools')"
                            :href="route('tools.create')"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Create Tools
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state -->
                <div v-else class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </button>
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        Tool Management
                    </div>
                </div>
            </div>

            <!-- Subscription Management - Only if user has subscription permissions -->
            <div v-if="canAny(['view subscriptions', 'create subscriptions', 'edit subscriptions'])" class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isSubscriptionsMenuOpen = !isSubscriptionsMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                            </svg>
                            <span>Subscription Management</span>
                        </div>
                        <svg class="h-5 w-5 transition-transform duration-200" :class="{ 'rotate-180': isSubscriptionsMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-show="isSubscriptionsMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link
                            v-if="can('view subscriptions')"
                            href="/subscriptions"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Subscriptions
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state -->
                <div v-else class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </button>
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        Subscription Management
                    </div>
                </div>
            </div>

            <!-- Tools Section - Only show tools user has permission to use -->
            <div class="mt-4 pt-4 border-t border-gray-700">
                <h3 v-if="isOpen" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    AI Tools
                </h3>
                <div class="space-y-1">
                    <Link
                        v-for="tool in tools.filter(t => can(t.permission))"
                        :key="tool.name"
                        :href="'/' + tool.route.split('.')[0]"
                        class="flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                        :class="{ 'justify-center': !isOpen }"
                    >
                        <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tool.icon" />
                        </svg>
                        <span v-if="isOpen" class="flex-1">{{ tool.name }}</span>
                        <div v-else class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                            {{ tool.name }}
                        </div>
                    </Link>
                </div>
            </div>

            <!-- Usage Stats - Show based on permission -->
            <div v-if="isOpen && can('view usage stats')" class="mt-4 pt-4 border-t border-gray-700">
                <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Usage</h3>
                <div class="mt-2 px-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">API Calls</span>
                        <span class="text-white font-medium">2,345 / 10k</span>
                    </div>
                    <div class="mt-1 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: 23%"></div>
                    </div>
                </div>
                <div class="mt-3 px-3">
                    <div class="flex justify-between text-xs">
                        <span class="text-gray-400">Storage</span>
                        <span class="text-white font-medium">1.2 / 5 GB</span>
                    </div>
                    <div class="mt-1 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: 24%"></div>
                    </div>
                </div>
            </div>
        </nav>
    </aside>
</template>
