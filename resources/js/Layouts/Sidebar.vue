<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(true);
const isUserMenuOpen = ref(false);

const tools = [
    { name: 'Text Generator', icon: 'M4 6h16M4 12h16M4 18h7', route: 'tools.text-generator' },
    { name: 'Image Generator', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', route: 'tools.image-generator' },
    { name: 'Code Assistant', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', route: 'tools.code-assistant' },
    { name: 'Chat Bot', icon: 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', route: 'tools.chat-bot' },
    { name: 'Analytics', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', route: 'tools.analytics' },
];
</script>

<template>
    <aside :class="['bg-gray-800 text-white transition-all duration-300', isOpen ? 'w-64' : 'w-20']">
        <div class="p-4">
            <button @click="isOpen = !isOpen" class="w-full flex justify-end text-gray-400 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="isOpen ? 'M11 19l-7-7 7-7m8 14l-7-7 7-7' : 'M13 5l7 7-7 7M5 5l7 7-7 7'" />
                </svg>
            </button>
        </div>

        <nav class="mt-5 px-2">
            <div class="space-y-1">
                <Link href="/dashboard" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span v-if="isOpen">Dashboard</span>
                </Link>

                <!-- User Management Dropdown -->
                <div v-if="isOpen">
                    <button @click="isUserMenuOpen = !isUserMenuOpen" class="w-full group flex items-center justify-between px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                        <div class="flex items-center">
                            <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span>User Management</span>
                        </div>
                        <svg class="h-5 w-5 transform transition-transform" :class="{ 'rotate-180': isUserMenuOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div v-show="isUserMenuOpen" class="mt-1 space-y-1 pl-11">
                        <Link href="/users" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                            <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <span v-if="isOpen">Users</span>
                        </Link>

                        <Link href="/permissions" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white">
                            <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <span>Permissions</span>
                        </Link>

                        <Link href="/roles" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-400 hover:bg-gray-700 hover:text-white">
                            <svg class="mr-3 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>Roles</span>
                        </Link>
                    </div>
                </div>

                <!-- Collapsed state for User Management (when sidebar is closed) -->
                <div v-if="!isOpen" class="relative group">
                    <button class="w-full flex justify-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- Tooltip for collapsed state -->
                    <div class="absolute left-full ml-2 px-2 py-1 bg-gray-900 text-white text-sm rounded opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all whitespace-nowrap z-50">
                        User Management
                    </div>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-700">
                <Link v-for="tool in tools" :key="tool.name" :href="route(tool.route)"
                      class="group flex items-center px-2 py-2 text-sm font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white">
                    <svg class="mr-3 h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="tool.icon" />
                    </svg>
                    <span v-if="isOpen">{{ tool.name }}</span>
                </Link>
            </div>

            <!-- Usage Stats Section -->
            <div v-if="isOpen" class="mt-8 pt-8 border-t border-gray-700">
                <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Usage</h3>
                <div class="mt-2 px-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-300">API Calls</span>
                        <span class="text-white">2,345 / 10,000</span>
                    </div>
                    <div class="mt-1 h-2 bg-gray-700 rounded-full">
                        <div class="h-2 bg-indigo-600 rounded-full" style="width: 23.45%"></div>
                    </div>
                </div>
                <div class="mt-4 px-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-300">Storage</span>
                        <span class="text-white">1.2 GB / 5 GB</span>
                    </div>
                    <div class="mt-1 h-2 bg-gray-700 rounded-full">
                        <div class="h-2 bg-indigo-600 rounded-full" style="width: 24%"></div>
                    </div>
                </div>
            </div>
        </nav>
    </aside>
</template>
