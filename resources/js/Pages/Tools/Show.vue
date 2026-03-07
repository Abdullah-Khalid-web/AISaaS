<!-- resources/js/Pages/Tools/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    },
    userLicense: {
        type: Object,
        default: null
    }
});

const activeTab = ref('overview');

const formatCurrency = (price, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
};

const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const getPlatformIcon = (platform) => {
    const icons = {
        web: '🌐',
        ios: '📱',
        android: '🤖',
        windows: '🪟',
        macos: '🍎',
        linux: '🐧',
        api: '🔌'
    };
    return icons[platform] || '📦';
};

const goBack = () => {
    router.get(route('tools.index'));
};

const editTool = () => {
    router.get(route('tools.edit', props.tool.id));
};

const subscribeToPlan = (planId) => {
    router.post(route('subscriptions.create'), {
        plan_id: planId,
        tool_id: props.tool.id
    });
};
</script>

<template>
    <Head :title="tool.name" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <button
                        @click="goBack"
                        class="text-gray-600 hover:text-gray-900 transition"
                        title="Back to Tools"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </button>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ tool.name }}
                    </h2>
                </div>
                <div class="flex gap-3">
                    <Link
                        :href="route('plans.create', { tool_id: tool.id })"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Plan
                    </Link>
                    <button
                        @click="editTool"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Tool
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Tool Header Card -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="p-6" :style="{ borderTop: `4px solid ${tool.metadata?.color || '#4f46e5'}` }">
                        <div class="flex items-start gap-6">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-24 h-24 bg-indigo-100 rounded-2xl flex items-center justify-center text-5xl">
                                    {{ tool.metadata?.icon || '🤖' }}
                                </div>
                            </div>

                            <!-- Basic Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h1 class="text-3xl font-bold text-gray-900">{{ tool.name }}</h1>
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">
                                        v{{ tool.version || '1.0.0' }}
                                    </span>
                                    <span
                                        class="px-3 py-1 rounded-full text-sm font-medium"
                                        :class="tool.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                    >
                                        {{ tool.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <p class="text-gray-600 text-lg mb-4">{{ tool.description || 'No description provided.' }}</p>

                                <!-- Platforms -->
                                <div class="flex flex-wrap gap-2 mb-4">
                                    <span
                                        v-for="platform in tool.supported_platforms || []"
                                        :key="platform"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm"
                                    >
                                        <span class="mr-1">{{ getPlatformIcon(platform) }}</span>
                                        {{ platform.charAt(0).toUpperCase() + platform.slice(1) }}
                                    </span>
                                    <span v-if="!tool.supported_platforms?.length" class="text-sm text-gray-500">
                                        🌐 All Platforms Supported
                                    </span>
                                </div>

                                <!-- Stats -->
                                <div class="grid grid-cols-4 gap-4 mt-4">
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-500">Total Plans</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ tool.plans?.length || 0 }}</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-500">Starting Price</div>
                                        <div class="text-2xl font-bold text-gray-900">
                                            {{ tool.plans?.length ? formatCurrency(Math.min(...tool.plans.map(p => p.price))) : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-500">Total Licenses</div>
                                        <div class="text-2xl font-bold text-gray-900">{{ tool.total_licenses || 0 }}</div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <div class="text-sm text-gray-500">Created</div>
                                        <div class="text-sm font-medium text-gray-900 mt-1">{{ formatDate(tool.created_at) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button
                                @click="activeTab = 'overview'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Overview
                            </button>
                            <button
                                @click="activeTab = 'plans'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'plans' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Pricing Plans
                                <span v-if="tool.plans?.length" class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                                    {{ tool.plans.length }}
                                </span>
                            </button>
                            <button
                                @click="activeTab = 'features'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'features' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Features
                            </button>
                            <button
                                @click="activeTab = 'sdk'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition"
                                :class="activeTab === 'sdk' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                SDK & Docs
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Overview Tab -->
                        <div v-if="activeTab === 'overview'">
                            <div class="prose max-w-none">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">About {{ tool.name }}</h3>
                                <p class="text-gray-600">{{ tool.description || 'No description available.' }}</p>

                                <h4 class="text-md font-medium text-gray-900 mt-6 mb-3">Quick Facts</h4>
                                <dl class="grid grid-cols-2 gap-4">
                                    <div>
                                        <dt class="text-sm text-gray-500">Version</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ tool.version || '1.0.0' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Last Updated</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ formatDate(tool.updated_at) }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">Sort Order</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ tool.sort_order || 0 }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm text-gray-500">ID</dt>
                                        <dd class="text-sm font-medium text-gray-900">{{ tool.id }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Plans Tab -->
                        <div v-if="activeTab === 'plans'">
                            <div v-if="tool.plans?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div
                                    v-for="plan in tool.plans"
                                    :key="plan.id"
                                    class="border rounded-lg overflow-hidden hover:shadow-lg transition relative"
                                    :class="{ 'border-indigo-600 shadow-md': plan.is_popular }"
                                >
                                    <div v-if="plan.is_popular" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 absolute top-0 right-0 rounded-bl-lg">
                                        POPULAR
                                    </div>
                                    <div class="p-6">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                                        <p class="text-gray-500 text-sm mb-4">{{ plan.description || 'No description' }}</p>

                                        <div class="mb-4">
                                            <span class="text-3xl font-bold text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                            <span class="text-gray-500 text-sm">/{{ plan.billing_cycle }}</span>
                                        </div>

                                        <ul class="space-y-2 mb-6">
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.device_limit === 999 ? 'Unlimited' : plan.device_limit }} Device{{ plan.device_limit !== 1 ? 's' : '' }}
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() : 'Unlimited' }} API calls
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.concurrent_users }} concurrent user{{ plan.concurrent_users !== 1 ? 's' : '' }}
                                            </li>
                                        </ul>

                                        <div class="flex gap-2">
                                            <Link
                                                :href="route('plans.edit', plan.id)"
                                                class="flex-1 text-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm"
                                            >
                                                Edit Plan
                                            </Link>
                                            <button
                                                @click="subscribeToPlan(plan.id)"
                                                class="flex-1 text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm"
                                            >
                                                Subscribe
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-4 text-gray-500">No plans created for this tool yet.</p>
                                <Link
                                    :href="route('plans.create', { tool_id: tool.id })"
                                    class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                >
                                    Create First Plan
                                </Link>
                            </div>
                        </div>

                        <!-- Features Tab -->
                        <div v-if="activeTab === 'features'">
                            <div v-if="tool.metadata?.features?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="(feature, index) in tool.metadata.features"
                                    :key="index"
                                    class="flex items-start gap-3 p-4 bg-gray-50 rounded-lg"
                                >
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700">{{ feature }}</span>
                                </div>
                            </div>
                            <div v-else class="text-center py-12">
                                <p class="text-gray-500">No features listed for this tool.</p>
                            </div>
                        </div>

                        <!-- SDK & Docs Tab -->
                        <div v-if="activeTab === 'sdk'">
                            <div class="space-y-6">
                                <!-- SDK Download -->
                                <div v-if="tool.sdk_download_url" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">SDK Download</h4>
                                    <a
                                        :href="tool.sdk_download_url"
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download SDK
                                    </a>
                                </div>

                                <!-- Documentation -->
                                <div v-if="tool.metadata?.documentation_url" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Documentation</h4>
                                    <a
                                        :href="tool.metadata.documentation_url"
                                        target="_blank"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        View Documentation
                                    </a>
                                </div>

                                <div v-if="!tool.sdk_download_url && !tool.metadata?.documentation_url" class="text-center py-12">
                                    <p class="text-gray-500">No SDK or documentation links available.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User License Info (if any) -->
                <div v-if="userLicense" class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-green-800 font-medium">You have an active license for this tool</p>
                            <p class="text-green-600 text-sm">Plan: {{ userLicense.plan?.name }} | Expires: {{ formatDate(userLicense.expires_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
