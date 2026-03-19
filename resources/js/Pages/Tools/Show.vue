<!-- resources/js/Pages/Tools/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    },
    userLicense: {
        type: Object,
        default: null
    },
    can: {
        type: Object,
        default: () => ({
            editTools: false,
            createPlans: false,
            editPlans: false,
            subscribe: true
        })
    }
});

const activeTab = ref('overview');
const selectedPlan = ref(null);
const showSubscribeModal = ref(false);
const paymentMethod = ref('stripe');
const agreeToTerms = ref(false);
const selectedFile = ref(null);
const showFilePreview = ref(false);

// Subscription form
const form = useForm({
    plan_id: null,
    package_name: '',
    payment_method: 'stripe'
});

const formatCurrency = (price, currency = 'USD') => {
    if (!price || price === 0) return 'Free';
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

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
    return icons[platform.toLowerCase()] || '📦';
};

const getFileIcon = (fileName, fileType) => {
    const extension = fileName?.split('.').pop()?.toLowerCase();

    const icons = {
        pdf: '📕',
        doc: '📘',
        docx: '📘',
        txt: '📃',
        md: '📝',
        json: '🔧',
        html: '🌐',
        css: '🎨',
        js: '💻',
        py: '🐍',
        java: '☕',
        cpp: '⚙️',
        php: '🐘',
        sql: '🗄️',
        rtf: '📄',
        jpg: '🖼️',
        jpeg: '🖼️',
        png: '🖼️',
        gif: '🎨',
        svg: '🎨'
    };

    if (fileType?.startsWith('image/')) return '🖼️';
    return icons[extension] || '📄';
};

const isImageFile = (file) => {
    return file.type?.startsWith('image/') ||
           file.name?.match(/\.(jpg|jpeg|png|gif|svg)$/i);
};

const previewFile = (file) => {
    if (file.content) {
        // If we have base64 content, create a blob URL
        if (file.content.startsWith('data:')) {
            selectedFile.value = file;
            showFilePreview.value = true;
        }
    } else if (file.url) {
        // If we have a URL (like from storage)
        window.open(file.url, '_blank');
    }
};

const downloadFile = (file) => {
    if (file.content) {
        // Download from base64 content
        const link = document.createElement('a');
        link.href = file.content;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else if (file.url) {
        // Download from URL
        window.open(file.url, '_blank');
    }
};

const closePreview = () => {
    showFilePreview.value = false;
    selectedFile.value = null;
};

const goBack = () => {
    router.get(route('tools.index'));
};

const editTool = () => {
    router.get(route('tools.edit', props.tool.id));
};

const openSubscribeModal = (plan) => {
    if (!props.can.subscribe) {
        router.get(route('login'));
        return;
    }
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    form.package_name = plan.name;
    showSubscribeModal.value = true;
};

const subscribe = () => {
    form.post(route('subscriptions.store', props.tool.id), {
        preserveScroll: true,
        onSuccess: () => {
            showSubscribeModal.value = false;
            selectedPlan.value = null;
        }
    });
};

const viewLicense = () => {
    if (props.userLicense) {
        router.get(route('licenses.show', props.userLicense.id));
    }
};

const getPlanBadgeColor = (plan) => {
    if (plan.is_popular) return 'bg-indigo-600 text-white';
    if (plan.price === 0) return 'bg-green-100 text-green-800';
    return 'bg-gray-100 text-gray-600';
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
                    <!-- Add Plan Button - Only for users with createPlans permission -->
                    <Link
                        v-if="can.createPlans"
                        :href="route('plans.create', { tool_id: tool.id })"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Plan
                    </Link>

                    <!-- Edit Tool Button - Only for users with editTools permission -->
                    <button
                        v-if="can.editTools"
                        @click="editTool"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Tool
                    </button>

                    <!-- View License Button - Only if user has active license -->
                    <button
                        v-if="userLicense"
                        @click="viewLicense"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        View My License
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
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
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
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
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
                        <nav class="flex -mb-px overflow-x-auto">
                            <button
                                @click="activeTab = 'overview'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition whitespace-nowrap"
                                :class="activeTab === 'overview' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Overview
                            </button>
                            <button
                                @click="activeTab = 'plans'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition whitespace-nowrap"
                                :class="activeTab === 'plans' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Pricing Plans
                                <span v-if="tool.plans?.length" class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                                    {{ tool.plans.length }}
                                </span>
                            </button>
                            <button
                                @click="activeTab = 'features'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition whitespace-nowrap"
                                :class="activeTab === 'features' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            >
                                Features
                            </button>
                            <button
                                @click="activeTab = 'sdk'"
                                class="py-4 px-6 text-sm font-medium border-b-2 transition whitespace-nowrap"
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
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                    class="border rounded-lg overflow-hidden hover:shadow-lg transition relative flex flex-col"
                                    :class="{ 'border-indigo-600 shadow-md': plan.is_popular }"
                                >
                                    <div v-if="plan.is_popular" class="bg-indigo-600 text-white text-xs font-bold px-3 py-1 absolute top-0 right-0 rounded-bl-lg">
                                        POPULAR
                                    </div>
                                    <div class="p-6 flex-1">
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                                        <p class="text-gray-500 text-sm mb-4">{{ plan.description || 'No description' }}</p>

                                        <div class="mb-4">
                                            <span class="text-3xl font-bold text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                            <span class="text-gray-500 text-sm">/{{ plan.billing_cycle }}</span>
                                        </div>

                                        <ul class="space-y-2 mb-6">
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ plan.device_limit === 999 ? 'Unlimited' : plan.device_limit }} Device{{ plan.device_limit !== 1 ? 's' : '' }}</span>
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() : 'Unlimited' }} API calls</span>
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span>{{ plan.concurrent_users }} concurrent user{{ plan.concurrent_users !== 1 ? 's' : '' }}</span>
                                            </li>
                                        </ul>

                                        <div class="flex gap-2 mt-auto">
                                            <!-- Edit Plan Button - Only for users with editPlans permission -->
                                            <Link
                                                v-if="can.editPlans"
                                                :href="route('plans.edit', plan.id)"
                                                class="flex-1 text-center bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-sm"
                                            >
                                                Edit Plan
                                            </Link>

                                            <!-- Subscribe Button - Shows different states based on license -->
                                            <button
                                                v-if="userLicense && userLicense.plan_id === plan.id"
                                                @click="viewLicense"
                                                class="flex-1 text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm"
                                            >
                                                View License
                                            </button>
                                            <button
                                                v-else-if="!userLicense"
                                                @click="openSubscribeModal(plan)"
                                                class="flex-1 text-center bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 text-sm"
                                            >
                                                Subscribe
                                            </button>
                                            <button
                                                v-else
                                                disabled
                                                class="flex-1 text-center bg-gray-300 text-gray-500 px-4 py-2 rounded-lg text-sm cursor-not-allowed"
                                            >
                                                Different Plan Active
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
                                    v-if="can.createPlans"
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
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download SDK
                                    </a>
                                </div>

                                <!-- Documentation Files -->
                                <div v-if="tool.metadata?.documentation_files?.length" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Documentation Files</h4>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div
                                            v-for="(file, index) in tool.metadata.documentation_files"
                                            :key="index"
                                            class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition"
                                        >
                                            <div class="flex items-center gap-3">
                                                <!-- File icon -->
                                                <span class="text-2xl">{{ getFileIcon(file.name, file.type) }}</span>

                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <!-- Preview button for images -->
                                                <button
                                                    v-if="isImageFile(file)"
                                                    @click="previewFile(file)"
                                                    class="text-indigo-600 hover:text-indigo-900 p-1"
                                                    title="Preview"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </button>

                                                <!-- Download button -->
                                                <button
                                                    @click="downloadFile(file)"
                                                    class="text-green-600 hover:text-green-900 p-1"
                                                    title="Download"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- External Documentation Link -->
                                <div v-if="tool.metadata?.documentation_url" class="bg-gray-50 rounded-lg p-6">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">External Documentation</h4>
                                    <a
                                        :href="tool.metadata.documentation_url"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                        </svg>
                                        View Online Documentation
                                    </a>
                                </div>

                                <div v-if="!tool.sdk_download_url && !tool.metadata?.documentation_files?.length && !tool.metadata?.documentation_url" class="text-center py-12">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-4 text-gray-500">No SDK or documentation files available.</p>
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
                            <p class="text-green-600 text-sm">
                                Plan: {{ userLicense.plan?.name }} |
                                Expires: {{ formatDate(userLicense.expires_at) }} |
                                <button @click="viewLicense" class="underline hover:text-green-800">View Details</button>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- File Preview Modal -->
        <div v-if="showFilePreview && selectedFile" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closePreview"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        {{ selectedFile.name }}
                                    </h3>
                                    <button
                                        @click="closePreview"
                                        class="text-gray-400 hover:text-gray-500"
                                    >
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Image Preview -->
                                <div v-if="isImageFile(selectedFile)" class="flex justify-center">
                                    <img
                                        :src="selectedFile.content || selectedFile.url"
                                        :alt="selectedFile.name"
                                        class="max-w-full max-h-[70vh] object-contain rounded-lg"
                                    />
                                </div>

                                <!-- Text File Preview (if possible) -->
                                <div v-else-if="selectedFile.type?.startsWith('text/') || selectedFile.name?.match(/\.(txt|md|json|html|css|js|xml)$/i)" class="bg-gray-50 p-4 rounded-lg max-h-[70vh] overflow-auto">
                                    <pre class="text-sm text-gray-800 whitespace-pre-wrap font-mono">{{ atob(selectedFile.content?.split(',')[1] || '') }}</pre>
                                </div>

                                <!-- Other file types -->
                                <div v-else class="text-center py-12">
                                    <span class="text-6xl block mb-4">{{ getFileIcon(selectedFile.name, selectedFile.type) }}</span>
                                    <p class="text-gray-500 mb-4">Preview not available for this file type</p>
                                    <button
                                        @click="downloadFile(selectedFile)"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
                                    >
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download File
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="downloadFile(selectedFile)"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Download
                        </button>
                        <button
                            @click="closePreview"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
