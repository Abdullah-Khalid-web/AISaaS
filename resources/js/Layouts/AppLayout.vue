<script setup>
import Header from '@/Layouts/Header.vue';
import Sidebar from '@/Layouts/Sidebar.vue';
import Footer from '@/Layouts/Footer.vue';
import { ref, onMounted, onUnmounted } from 'vue';

defineProps({
    title: String
});

const showSidebar = ref(true);
const isMobile = ref(false);

// Check if screen is mobile
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
    // Auto-hide sidebar on mobile by default
    if (isMobile.value) {
        showSidebar.value = false;
    } else {
        showSidebar.value = true;
    }
};

// Add event listener for window resize
onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

// Toggle sidebar function
const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <Header @toggle-sidebar="toggleSidebar" :show-sidebar="showSidebar" />

        <!-- Mobile Overlay (when sidebar is open on mobile) -->
        <div
            v-if="isMobile && showSidebar"
            @click="showSidebar = false"
            class="fixed inset-0 bg-black bg-opacity-50 z-30 transition-opacity"
        ></div>

        <div class="flex flex-1 relative">
            <!-- Sidebar with responsive classes -->
            <Sidebar
                v-if="showSidebar"
                :class="[
                    'transition-all duration-300',
                    isMobile
                        ? 'fixed left-0 top-0 bottom-0 z-40'
                        : 'relative'
                ]"
            />

            <!-- Main Content -->
            <main
                :class="[
                    'flex-1 transition-all duration-300',
                    isMobile && showSidebar ? 'opacity-50' : 'opacity-100'
                ]"
            >
                <div class="py-6">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <!-- Mobile Menu Button (visible on mobile when sidebar is hidden) -->
                        <button
                            v-if="isMobile && !showSidebar"
                            @click="toggleSidebar"
                            class="mb-4 inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
