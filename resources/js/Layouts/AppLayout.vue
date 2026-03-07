<!-- resources/js/Layouts/AppLayout.vue -->
<script setup>
import Header from '@/Layouts/Header.vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import Footer from '@/Layouts/Footer.vue';
import { ref, onMounted } from 'vue';

defineProps({
    title: String
});

const showSidebar = ref(true);
const isMobile = ref(false);

// Check if screen is mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <Header />

        <div class="flex flex-1">
            <!-- Sidebar - hidden on mobile by default -->
            <Sidebar v-if="showSidebar && !isMobile" class="hidden md:block" />

            <!-- Mobile menu button -->
            <button
                v-if="isMobile && !showSidebar"
                @click="showSidebar = true"
                class="fixed bottom-4 right-4 z-50 bg-indigo-600 text-white p-3 rounded-full shadow-lg md:hidden"
            >
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Mobile sidebar overlay -->
            <div
                v-if="isMobile && showSidebar"
                @click="showSidebar = false"
                class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
            ></div>

            <!-- Mobile sidebar -->
            <Sidebar
                v-if="isMobile && showSidebar"
                class="fixed left-0 top-0 bottom-0 z-50 md:hidden"
            />

            <!-- Main Content -->
            <main :class="['flex-1 transition-all duration-300', isMobile && showSidebar ? 'opacity-50' : 'opacity-100']">
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <!-- Mobile menu button when sidebar is hidden -->
                        <button
                            v-if="isMobile && !showSidebar"
                            @click="showSidebar = true"
                            class="mb-4 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 md:hidden"
                        >
                            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            Menu
                        </button>

                        <!-- Page Header -->
                        <div v-if="$slots.header" class="mb-6">
                            <slot name="header" />
                        </div>

                        <!-- Page Content -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <slot />
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <Footer />
    </div>
</template>
