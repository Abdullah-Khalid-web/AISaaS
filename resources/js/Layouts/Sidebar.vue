<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const isOpen = ref(true);

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
