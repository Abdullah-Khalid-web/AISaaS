<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;
const activeTab = ref('profile');
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    My Profile
                </h2>
                <div class="text-sm text-gray-500">
                    Last login: {{ new Date().toLocaleDateString() }}
                </div>
            </div>
        </template>

        <div class="">
            <div class="mx-auto ">

                <!-- Profile Header Card -->
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                        <div class="flex items-center space-x-4">
                            <!-- Profile Avatar -->
                            <div class="relative">
                                <div class="h-20 w-20 rounded-full bg-white flex items-center justify-center">
                                    <span class="text-3xl font-bold text-indigo-600">
                                        {{ user?.name?.charAt(0).toUpperCase() || 'U' }}
                                    </span>
                                </div>
                                <button class="absolute bottom-0 right-0 bg-white rounded-full p-1 shadow-lg hover:bg-gray-100">
                                    <svg class="h-4 w-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- User Info -->
                            <div class="flex-1">
                                <h3 class="text-2xl font-bold">{{ user?.name }}</h3>
                                <p class="text-indigo-100">{{ user?.email }}</p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="px-2 py-1 bg-white/20 rounded-full text-xs">
                                        {{ user?.roles?.[0]?.name || 'User' }}
                                    </span>
                                    <span class="px-2 py-1 bg-white/20 rounded-full text-xs">
                                        Member since {{ new Date(user?.created_at).toLocaleDateString() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Account Status -->
                            <div class="text-right">
                                <div class="flex items-center space-x-2">
                                    <span class="relative flex h-3 w-3">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                                    </span>
                                    <span class="text-sm">Active</span>
                                </div>
                                <div class="mt-2 text-sm text-indigo-100">
                                    <Link href="/billing" class="underline hover:text-white">
                                        Manage Subscription
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Navigation -->
                <div class="mb-6 border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button
                            @click="activeTab = 'profile'"
                            :class="[
                                activeTab === 'profile'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile Information
                            </span>
                        </button>
                        <button
                            @click="activeTab = 'security'"
                            :class="[
                                activeTab === 'security'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                                Security
                            </span>
                        </button>
                        <button
                            @click="activeTab = 'activity'"
                            :class="[
                                activeTab === 'activity'
                                    ? 'border-indigo-500 text-indigo-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                            ]"
                        >
                            <span class="flex items-center">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Activity & Usage
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="space-y-6">
                    <!-- Profile Information Tab -->
                    <div v-show="activeTab === 'profile'" class="bg-white p-6 shadow sm:rounded-lg">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Profile Information</h3>
                            <span class="text-xs text-gray-500">Last updated: {{ new Date().toLocaleDateString() }}</span>
                        </div>
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                    </div>

                    <!-- Security Tab -->
                    <div v-show="activeTab === 'security'" class="space-y-6">
                        <!-- Password Update -->
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-medium text-gray-900">Change Password</h3>
                                <div class="flex items-center space-x-2">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                    </span>
                                    <span class="text-xs text-gray-500">Password active</span>
                                </div>
                            </div>
                            <UpdatePasswordForm class="max-w-xl" />

                            <!-- Security Tips -->
                            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                                <h4 class="text-sm font-medium text-blue-800 mb-2">🔒 Password Security Tips</h4>
                                <ul class="text-xs text-blue-700 space-y-1">
                                    <li>• Use at least 8 characters</li>
                                    <li>• Include uppercase and lowercase letters</li>
                                    <li>• Add numbers and special characters</li>
                                    <li>• Don't use common words or personal information</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Two Factor Authentication (Optional) -->
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Two Factor Authentication</h3>
                            <p class="text-sm text-gray-600 mb-4">Add additional security to your account using two-factor authentication.</p>
                            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">
                                Enable Two Factor
                            </button>
                        </div>

                        <!-- Delete Account -->
                        <div class="bg-white p-6 shadow sm:rounded-lg border border-red-200">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-red-600">Delete Account</h3>
                                <span class="text-xs text-red-500">⚠️ Irreversible action</span>
                            </div>
                            <DeleteUserForm />
                        </div>
                    </div>

                    <!-- Activity & Usage Tab -->
                    <div v-show="activeTab === 'activity'" class="space-y-6">
                        <!-- Usage Stats -->
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">API Usage</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <div class="bg-indigo-50 p-4 rounded-lg">
                                    <p class="text-sm text-indigo-600">Total API Calls</p>
                                    <p class="text-2xl font-bold text-indigo-800">2,345</p>
                                    <p class="text-xs text-indigo-500">of 10,000 used</p>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <p class="text-sm text-green-600">Storage Used</p>
                                    <p class="text-2xl font-bold text-green-800">1.2 GB</p>
                                    <p class="text-xs text-green-500">of 5 GB</p>
                                </div>
                                <div class="bg-purple-50 p-4 rounded-lg">
                                    <p class="text-sm text-purple-600">Active Projects</p>
                                    <p class="text-2xl font-bold text-purple-800">12</p>
                                    <p class="text-xs text-purple-500">Current month</p>
                                </div>
                            </div>

                            <!-- Progress Bars -->
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>API Calls</span>
                                        <span>23.45%</span>
                                    </div>
                                    <div class="h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 bg-indigo-600 rounded-full" style="width: 23.45%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Storage</span>
                                        <span>24%</span>
                                    </div>
                                    <div class="h-2 bg-gray-200 rounded-full">
                                        <div class="h-2 bg-green-600 rounded-full" style="width: 24%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white p-6 shadow sm:rounded-lg">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                            <div class="space-y-3">
                                <div v-for="i in 5" :key="i" class="flex items-center justify-between py-2 border-b last:border-0">
                                    <div class="flex items-center space-x-3">
                                        <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium">Generated AI Image</p>
                                            <p class="text-xs text-gray-500">{{ i }} hours ago</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">Success</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
