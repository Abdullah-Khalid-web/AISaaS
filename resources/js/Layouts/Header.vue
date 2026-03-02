<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
    isGuest: {
        type: Boolean,
        default: false
    },
    showSidebar: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['toggle-sidebar']);

const user = computed(() => usePage().props.auth?.user);
const showingNavigationDropdown = ref(false);
</script>

<template>
    <header class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Left section with mobile menu button -->
                <div class="flex items-center">
                    <!-- Mobile menu button (only visible when not guest and on mobile) -->
                    <button
                        v-if="!isGuest"
                        @click="emit('toggle-sidebar')"
                        class="md:hidden mr-4 text-gray-500 hover:text-gray-700 focus:outline-none"
                    >
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Logo -->
                    <Link :href="isGuest ? '/' : '/dashboard'" class="flex items-center">
                        <span class="text-2xl font-bold text-indigo-600">AI<span class="text-gray-800">Tool</span></span>
                    </Link>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex space-x-8">
                    <Link href="/" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">Home</Link>
                    <Link href="/features" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">Features</Link>
                    <Link href="/pricing" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">Pricing</Link>
                    <Link href="/about" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">About</Link>
                    <Link href="/contact" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">Contact</Link>
                </nav>

                <!-- Auth Buttons or User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <template v-if="isGuest || !user">
                        <Link href="/login" class="text-gray-700 hover:text-indigo-600 px-3 py-2 text-sm font-medium">
                            Login
                        </Link>
                        <Link href="/register" class="bg-indigo-600 text-white hover:bg-indigo-700 px-4 py-2 rounded-md text-sm font-medium">
                            Get Started
                        </Link>
                    </template>
                    <template v-else>
                        <span class="text-sm text-gray-600">{{ user?.email }}</span>
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition">
                                    <img :src="user?.profile_photo_url || 'https://ui-avatars.com/api/?name='+user?.name+'&color=7F9CF5&background=EBF4FF'"
                                         class="h-8 w-8 rounded-full object-cover">
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                                <DropdownLink :href="route('dashboard')">Dashboard</DropdownLink>
                                <DropdownLink :href="route('billing')">Billing</DropdownLink>
                                <DropdownLink :href="route('api-tokens.index')">API Tokens</DropdownLink>
                                <div class="border-t border-gray-200"></div>
                                <DropdownLink :href="route('logout')" method="post" as="button">Logout</DropdownLink>
                            </template>
                        </Dropdown>
                    </template>
                </div>

                <!-- Mobile menu button (for navigation dropdown) -->
                <div class="flex md:hidden">
                    <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'inline-flex': showingNavigationDropdown, 'hidden': !showingNavigationDropdown }"
                                  stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Dropdown -->
        <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="md:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <ResponsiveNavLink href="/">Home</ResponsiveNavLink>
                <ResponsiveNavLink href="/features">Features</ResponsiveNavLink>
                <ResponsiveNavLink href="/pricing">Pricing</ResponsiveNavLink>
                <ResponsiveNavLink href="/about">About</ResponsiveNavLink>
                <ResponsiveNavLink href="/contact">Contact</ResponsiveNavLink>
            </div>

            <template v-if="isGuest || !user">
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink href="/login">Login</ResponsiveNavLink>
                        <ResponsiveNavLink href="/register">Register</ResponsiveNavLink>
                    </div>
                </div>
            </template>
            <template v-else>
                <div class="pt-4 pb-1 border-t border-gray-200">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800">{{ user?.name }}</div>
                        <div class="font-medium text-sm text-gray-500">{{ user?.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('profile.edit')">Profile</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('dashboard')">Dashboard</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('billing')">Billing</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('api-tokens.index')">API Tokens</ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                            Logout
                        </ResponsiveNavLink>
                    </div>
                </div>
            </template>
        </div>
    </header>
</template>
