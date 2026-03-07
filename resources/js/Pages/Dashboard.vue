<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: Object,
    licenses: Array,
    recentActivity: Array,
    availableTools: Array
});

// Format date helper
const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Get status badge class
const getStatusClass = (status) => {
    const classes = {
        active: 'bg-green-100 text-green-800',
        expired: 'bg-red-100 text-red-800',
        pending: 'bg-yellow-100 text-yellow-800',
        suspended: 'bg-orange-100 text-orange-800',
        revoked: 'bg-gray-100 text-gray-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Welcome Message -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-2">Welcome back!</h1>
                        <p class="text-gray-600">Here's what's happening with your AI tools today.</p>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Active Licenses -->
                    <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl">🔑</div>
                            <span class="text-4xl font-bold">{{ stats.activeLicenses }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Active Licenses</h3>
                        <p class="text-indigo-100 text-sm">Total: {{ stats.totalLicenses }}</p>
                    </div>

                    <!-- Expiring Soon -->
                    <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl">⏰</div>
                            <span class="text-4xl font-bold">{{ stats.expiringSoon }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Expiring Soon</h3>
                        <p class="text-yellow-100 text-sm">Next 7 days</p>
                    </div>

                    <!-- API Calls -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl">📊</div>
                            <span class="text-4xl font-bold">{{ stats.totalApiCalls }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Total API Calls</h3>
                        <p class="text-green-100 text-sm">All time usage</p>
                    </div>

                    <!-- Available Tools -->
                    <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
                        <div class="flex items-center justify-between mb-4">
                            <div class="text-3xl">🛠️</div>
                            <span class="text-4xl font-bold">{{ stats.totalTools }}</span>
                        </div>
                        <h3 class="text-lg font-semibold">Available Tools</h3>
                        <p class="text-purple-100 text-sm">AI tools ready</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-gray-50 border-b">
                        <h3 class="text-lg font-semibold">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="recentActivity.length > 0">
                            <div v-for="activity in recentActivity" :key="activity.id"
                                 class="flex items-center py-3 border-b last:border-0">
                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-indigo-600 text-sm">📊</span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm">
                                        <span class="font-medium">{{ activity.license?.tool?.name }}</span>
                                        - {{ activity.event_name || activity.event_type }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ new Date(activity.created_at).toLocaleString() }}
                                    </p>
                                </div>
                                <span :class="[
                                    'text-xs px-2 py-1 rounded',
                                    activity.was_successful ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                ]">
                                    {{ activity.was_successful ? 'Success' : 'Failed' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No recent activity to display.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
