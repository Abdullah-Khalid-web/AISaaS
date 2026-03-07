<!-- resources/js/Layouts/Sidebar.vue -->
<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(true);
const isUserMenuOpen = ref(false);
const isPlanMenuOpen = ref(false); // Add this line
const isToolMenuOpen = ref(false); // Add this line
const isSubscriptionsMenuOpen = ref(false); // Add this line

const tools = [
    { name: 'Text Generator', icon: 'M4 6h16M4 12h16M4 18h7', route: 'tools.text-generator' },
    { name: 'Image Generator', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', route: 'tools.image-generator' },
    { name: 'Code Assistant', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', route: 'tools.code-assistant' },
    { name: 'Chat Bot', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', route: 'tools.chat-bot' },
    { name: 'Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'tools.analytics' },
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
            <!-- Dashboard -->
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

            <!-- User Management -->
            <div class="mt-2">
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

                    <!-- Dropdown Menu -->
                    <div v-show="isUserMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link
                            href="/users"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Users
                        </Link>
                        <Link
                            href="/permissions"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Permissions
                        </Link>
                        <Link
                            href="/roles"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Roles
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

            <!-- Plan Management -->
            <div class="mt-2">
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
                            href="/plans"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Plans
                        </Link>
                        <Link
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

            <!-- Tool Management -->
            <div class="mt-2">
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

            <!-- Subscription Management -->
            <div class="mt-2">
                <!-- Expanded state -->
                <div v-if="isOpen">
                    <button
                        @click="isSubscriptionsMenuOpen = !isSubscriptionsMenuOpen"
                        class="w-full flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white group"
                    >
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
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
                            href="/subscriptions"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            View Subscriptions
                        </Link>
                        <!-- <Link
                            :href="route('subscriptions.create')"
                            class="block px-2 py-2 text-sm text-gray-400 hover:bg-gray-700 hover:text-white rounded-md"
                        >
                            Create Subscription
                        </Link> -->
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
                        Subscription Management
                    </div>
                </div>
            </div>

            <!-- Tools Section -->
            <div class="mt-4 pt-4 border-t border-gray-700">
                <h3 v-if="isOpen" class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">
                    AI Tools
                </h3>
                <div class="space-y-1">
                    <Link
                        v-for="tool in tools"
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

            <!-- Usage Stats -->
            <div v-if="isOpen" class="mt-4 pt-4 border-t border-gray-700">
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
