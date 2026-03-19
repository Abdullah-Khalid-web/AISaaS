<!-- resources/js/Pages/ViewTool.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    },
    relatedTools: {
        type: Array,
        default: () => []
    }
});

// State
const selectedPlan = ref(null);
const showPlanModal = ref(false);
const activeTab = ref('overview');
const showFullDescription = ref(false);
const selectedFile = ref(null);
const showFilePreview = ref(false);
const textContent = ref(null);
const previewLoading = ref(false);
const previewError = ref(null);

// Format currency
const formatCurrency = (price, currency = 'USD') => {
    if (!price || price === 0) return 'Free';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
};

// Format file size
const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Get file icon based on type
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

// Check if file is an image
const isImageFile = (file) => {
    return file.type?.startsWith('image/') ||
           file.name?.match(/\.(jpg|jpeg|png|gif|svg)$/i);
};

// Check if file is a text document that can be previewed
const isTextFile = (file) => {
    if (!file || !file.name) return false;

    const textExtensions = [
        'txt', 'md', 'json', 'html', 'htm', 'css', 'js', 'jsx', 'ts', 'tsx',
        'xml', 'yaml', 'yml', 'ini', 'cfg', 'conf', 'log', 'csv', 'sql',
        'php', 'py', 'rb', 'java', 'c', 'cpp', 'h', 'hpp', 'go', 'rs', 'swift'
    ];

    const extension = file.name.split('.').pop()?.toLowerCase();
    return file.type?.startsWith('text/') || textExtensions.includes(extension || '');
};

// Preview file
const previewFile = async (file) => {
    if (file.content) {
        selectedFile.value = file;
        showFilePreview.value = true;

        // Reset states
        textContent.value = null;
        previewLoading.value = false;
        previewError.value = null;

        // If it's a text file, decode it
        if (isTextFile(file)) {
            await decodeTextContent(file);
        }
    } else if (file.url) {
        window.open(file.url, '_blank');
    }
};

// Decode text content with better error handling
const decodeTextContent = async (file) => {
    if (!file.content) {
        previewError.value = 'No content available';
        return;
    }

    previewLoading.value = true;

    try {
        let content = file.content;
        let decodedContent = '';

        // Handle data URLs
        if (content.startsWith('data:')) {
            // Extract the base64 part
            const base64Match = content.match(/^data:([^;]+);base64,(.+)$/);

            if (base64Match && base64Match.length === 3) {
                // It's a proper data URL with base64
                const base64Content = base64Match[2];

                try {
                    // Decode base64 to binary string
                    const binaryString = atob(base64Content);

                    // Convert to Uint8Array for proper UTF-8 handling
                    const bytes = new Uint8Array(binaryString.length);
                    for (let i = 0; i < binaryString.length; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }

                    // Try to decode as UTF-8
                    const decoder = new TextDecoder('utf-8');
                    decodedContent = decoder.decode(bytes);

                } catch (e) {
                    console.error('Base64 decode error:', e);

                    // Fallback: try simple atob
                    try {
                        decodedContent = atob(base64Content);
                    } catch (e2) {
                        throw new Error('Failed to decode base64 content');
                    }
                }
            } else {
                // Try to get content after comma
                const commaIndex = content.indexOf(',');
                if (commaIndex !== -1) {
                    const base64Content = content.substring(commaIndex + 1);
                    try {
                        decodedContent = atob(base64Content);
                    } catch (e) {
                        // If it's not base64, use as is
                        decodedContent = base64Content;
                    }
                } else {
                    decodedContent = content;
                }
            }
        } else {
            // Assume it's already plain text
            decodedContent = content;
        }

        // Clean up the content (remove null bytes, etc.)
        decodedContent = decodedContent.replace(/\0/g, '');

        textContent.value = decodedContent;
        previewError.value = null;

    } catch (e) {
        console.error('Error decoding text file:', e);
        previewError.value = 'Error loading file content. You can download the file instead.';
        textContent.value = null;
    } finally {
        previewLoading.value = false;
    }
};

// Download file
const downloadFile = (file) => {
    if (file.content) {
        const link = document.createElement('a');
        link.href = file.content;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } else if (file.url) {
        window.open(file.url, '_blank');
    }
};

// Close preview
const closePreview = () => {
    showFilePreview.value = false;
    selectedFile.value = null;
    textContent.value = null;
    previewLoading.value = false;
    previewError.value = null;
};

// Get plan badge color
const getPlanBadgeColor = (plan) => {
    if (plan.is_popular) return 'bg-indigo-100 text-indigo-800';
    if (plan.price === 0) return 'bg-green-100 text-green-800';
    return 'bg-gray-100 text-gray-800';
};

// Get tool icon
const getToolIcon = () => {
    return props.tool.metadata?.icon || '🤖';
};

// Get tool color
const getToolColor = () => {
    return props.tool.metadata?.color || '#4f46e5';
};

// Get features list
const features = computed(() => {
    return props.tool.metadata?.features || [];
});

// Get use cases
const useCases = computed(() => {
    return props.tool.metadata?.use_cases || [];
});

// Get integrations
const integrations = computed(() => {
    return props.tool.metadata?.integrations || [];
});

// Get FAQs
const faqs = computed(() => {
    return props.tool.metadata?.faqs || [];
});

// Get documentation files
const documentationFiles = computed(() => {
    return props.tool.metadata?.documentation_files || [];
});

// Get supported languages
const languages = computed(() => {
    return props.tool.metadata?.languages || ['English'];
});

// Subscribe form
const form = useForm({
    plan_id: null,
    billing_cycle: 'monthly'
});

const selectPlan = (plan) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    showPlanModal.value = true;
};

const subscribe = () => {
    form.post(route('subscriptions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showPlanModal.value = false;
        }
    });
};

// Stats
const stats = computed(() => {
    return [
        { label: 'Active Users', value: props.tool.metadata?.active_users || '10k+', icon: 'users' },
        { label: 'API Calls/mo', value: props.tool.metadata?.api_calls || '1M+', icon: 'chart' },
        { label: 'Rating', value: props.tool.metadata?.rating || '4.8/5', icon: 'star' },
        { label: 'Uptime', value: props.tool.metadata?.uptime || '99.9%', icon: 'check' }
    ];
});
</script>

<template>
    <GuestLayout>
        <Head :title="tool.name + ' - AI Tool'" />

        <!-- Hero Section with SVG Background -->
        <div class="relative overflow-hidden bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 text-white">
            <!-- Animated SVG Background -->
            <div class="absolute inset-0 opacity-10">
                <svg class="absolute left-0 top-0 h-full w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 800">
                    <g fill="none" stroke="currentColor" stroke-width="1">
                        <circle cx="400" cy="400" r="200" stroke-dasharray="2 10" />
                        <circle cx="400" cy="400" r="300" stroke-dasharray="5 15" />
                        <circle cx="400" cy="400" r="400" stroke-dasharray="10 20" />
                        <path d="M400 100 L400 700 M100 400 L700 400" stroke-dasharray="5 10" />
                    </g>
                </svg>
            </div>

            <!-- Floating Orbs -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-yellow-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
            </div>

            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <!-- Breadcrumb -->
                <nav class="flex mb-8 text-sm text-indigo-100">
                    <Link href="/" class="hover:text-white transition">Home</Link>
                    <span class="mx-2">/</span>
                    <Link :href="route('Alltools.public')" class="hover:text-white transition">All Tools</Link>
                    <span class="mx-2">/</span>
                    <span class="text-white">{{ tool.name }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                    <!-- Left Column - Tool Info -->
                    <div>
                        <!-- Badges -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <span v-if="tool.is_new"
                                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-400 text-green-900">
                                🆕 New Release
                            </span>
                            <span v-for="tag in tool.tags?.slice(0, 3)" :key="tag"
                                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-white/20 backdrop-blur-sm text-white">
                                {{ tag }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h1 class="text-5xl font-bold mb-6 flex items-center">
                            <span class="mr-4 text-6xl">{{ getToolIcon() }}</span>
                            {{ tool.name }}
                        </h1>

                        <!-- Description -->
                        <p class="text-xl text-indigo-100 mb-8">{{ tool.description }}</p>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                            <div v-for="stat in stats" :key="stat.label"
                                 class="bg-white/10 backdrop-blur-sm rounded-lg p-4">
                                <dt class="text-sm text-indigo-200">{{ stat.label }}</dt>
                                <dd class="text-2xl font-bold">{{ stat.value }}</dd>
                            </div>
                        </div>

                        <!-- Version and Platforms -->
                        <div class="flex flex-wrap items-center gap-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                </svg>
                                <span>Version {{ tool.version || '1.0.0' }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span v-for="(platform, index) in tool.supported_platforms?.slice(0, 3)" :key="platform">
                                    {{ platform }}{{ index < tool.supported_platforms?.slice(0, 3).length - 1 ? ', ' : '' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Hero SVG Illustration -->
                    <div class="relative">
                        <!-- Main SVG Illustration -->
                        <svg class="w-full h-auto" viewBox="0 0 400 400" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Background Circle -->
                            <circle cx="200" cy="200" r="180" fill="url(#gradient)" fill-opacity="0.2"/>

                            <!-- Abstract AI Elements -->
                            <g class="animate-pulse">
                                <circle cx="200" cy="200" r="100" stroke="white" stroke-width="2" stroke-dasharray="5 5" fill="none"/>
                                <circle cx="200" cy="200" r="140" stroke="white" stroke-width="1" stroke-dasharray="10 10" fill="none"/>
                            </g>

                            <!-- Neural Network Pattern -->
                            <g stroke="white" stroke-width="1.5" stroke-opacity="0.3">
                                <line x1="120" y1="120" x2="280" y2="280"/>
                                <line x1="280" y1="120" x2="120" y2="280"/>
                                <line x1="120" y1="200" x2="280" y2="200"/>
                                <line x1="200" y1="120" x2="200" y2="280"/>
                            </g>

                            <!-- Floating Nodes -->
                            <g fill="white">
                                <circle cx="120" cy="120" r="8" fill="white" fill-opacity="0.8"/>
                                <circle cx="280" cy="120" r="8" fill="white" fill-opacity="0.8"/>
                                <circle cx="120" cy="280" r="8" fill="white" fill-opacity="0.8"/>
                                <circle cx="280" cy="280" r="8" fill="white" fill-opacity="0.8"/>
                                <circle cx="200" cy="200" r="12" fill="white" fill-opacity="0.9"/>
                            </g>

                            <!-- Data Flow Animation -->
                            <g class="animate-pulse">
                                <circle cx="160" cy="160" r="4" fill="#60A5FA"/>
                                <circle cx="240" cy="160" r="4" fill="#60A5FA"/>
                                <circle cx="160" cy="240" r="4" fill="#60A5FA"/>
                                <circle cx="240" cy="240" r="4" fill="#60A5FA"/>
                            </g>

                            <!-- Gradient Definition -->
                            <defs>
                                <radialGradient id="gradient">
                                    <stop offset="0%" stop-color="white"/>
                                    <stop offset="100%" stop-color="transparent"/>
                                </radialGradient>
                            </defs>
                        </svg>

                        <!-- Floating Feature Cards -->
                        <div class="absolute top-10 -right-10 bg-white/10 backdrop-blur-lg rounded-lg p-3 animate-bounce">
                            <div class="flex items-center space-x-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                                <span class="text-sm">Real-time Processing</span>
                            </div>
                        </div>
                        <div class="absolute bottom-10 -left-10 bg-white/10 backdrop-blur-lg rounded-lg p-3 animate-bounce animation-delay-1000">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="text-sm">4.8/5 Rating</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Tabs Navigation -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="flex space-x-8 overflow-x-auto">
                    <button @click="activeTab = 'overview'"
                            :class="['pb-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
                                     activeTab === 'overview' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                        Overview
                    </button>
                    <button @click="activeTab = 'features'"
                            :class="['pb-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
                                     activeTab === 'features' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                        Features
                    </button>
                    <button @click="activeTab = 'documentation'"
                            :class="['pb-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
                                     activeTab === 'documentation' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                        Documentation
                        <span v-if="documentationFiles.length" class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">
                            {{ documentationFiles.length }}
                        </span>
                    </button>
                    <button @click="activeTab = 'pricing'"
                            :class="['pb-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
                                     activeTab === 'pricing' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                        Pricing & Plans
                    </button>
                    <button @click="activeTab = 'faq'"
                            :class="['pb-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap',
                                     activeTab === 'faq' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                        FAQ
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="min-h-[500px]">
                <!-- Overview Tab -->
                <div v-if="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Overview Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Detailed Description -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">About {{ tool.name }}</h2>
                            <div class="prose prose-indigo max-w-none">
                                <p :class="{'line-clamp-3': !showFullDescription}">
                                    {{ tool.metadata?.detailed_description || tool.description }}
                                </p>
                                <button @click="showFullDescription = !showFullDescription"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium mt-2">
                                    {{ showFullDescription ? 'Show less' : 'Read more' }}
                                </button>
                            </div>
                        </div>

                        <!-- Quick Documentation Preview -->
                        <div v-if="documentationFiles.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-bold text-gray-900">Documentation</h2>
                                <button @click="activeTab = 'documentation'"
                                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                    View All
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="(file, index) in documentationFiles.slice(0, 4)" :key="index"
                                     class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div class="flex items-center gap-3">
                                        <span class="text-2xl">{{ getFileIcon(file.name, file.type) }}</span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 truncate max-w-[150px]">{{ file.name }}</p>
                                            <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button @click="previewFile(file)"
                                                class="text-indigo-600 hover:text-indigo-900 p-1"
                                                title="Preview">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button @click="downloadFile(file)"
                                                class="text-green-600 hover:text-green-900 p-1"
                                                title="Download">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- How It Works -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">How It Works</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <!-- Step 1 SVG -->
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto mb-4">
                                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="40" cy="40" r="36" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4"/>
                                            <path d="M40 20 L40 40 L55 55" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                                            <circle cx="40" cy="40" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900">1. Connect</h3>
                                    <p class="text-sm text-gray-500">Connect your account or API</p>
                                </div>
                                <!-- Step 2 SVG -->
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto mb-4">
                                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <rect x="20" y="20" width="40" height="40" rx="8" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4"/>
                                            <circle cx="40" cy="40" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                            <path d="M30 40 L38 48 L50 32" stroke="#4F46E5" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900">2. Configure</h3>
                                    <p class="text-sm text-gray-500">Set your preferences and parameters</p>
                                </div>
                                <!-- Step 3 SVG -->
                                <div class="text-center">
                                    <div class="w-20 h-20 mx-auto mb-4">
                                        <svg viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M20 60 L40 20 L60 60" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4" fill="none"/>
                                            <circle cx="40" cy="40" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                            <circle cx="30" cy="50" r="3" fill="#4F46E5"/>
                                            <circle cx="50" cy="50" r="3" fill="#4F46E5"/>
                                        </svg>
                                    </div>
                                    <h3 class="font-semibold text-gray-900">3. Generate</h3>
                                    <p class="text-sm text-gray-500">Get AI-powered results instantly</p>
                                </div>
                            </div>
                        </div>

                        <!-- Use Cases -->
                        <div v-if="useCases.length" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Use Cases</h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div v-for="useCase in useCases" :key="useCase"
                                     class="flex items-start p-4 bg-gray-50 rounded-lg">
                                    <svg class="w-5 h-5 text-indigo-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-700">{{ useCase }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Integrations -->
                        <div v-if="integrations.length" class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Integrations</h2>
                            <div class="flex flex-wrap gap-4">
                                <div v-for="integration in integrations" :key="integration"
                                     class="flex items-center px-4 py-2 bg-gray-100 rounded-full">
                                    <svg class="w-4 h-4 mr-2 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/>
                                    </svg>
                                    {{ integration }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Quick Info Card -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Info</h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Version</span>
                                    <span class="font-medium">{{ tool.version || '1.0.0' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Last Updated</span>
                                    <span class="font-medium">{{ tool.metadata?.last_updated || '2024' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Languages</span>
                                    <span class="font-medium">{{ languages.join(', ') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Support</span>
                                    <span class="font-medium">{{ tool.metadata?.support_type || '24/7 Email' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Documents</span>
                                    <span class="font-medium">{{ documentationFiles.length }} files</span>
                                </div>
                            </div>
                        </div>

                        <!-- Documentation Summary Card -->
                        <div v-if="documentationFiles.length > 0" class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg shadow-sm p-6">
                            <svg class="w-full h-24 mb-4" viewBox="0 0 200 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M40 20 L40 60 L100 60 L100 20" stroke="#4F46E5" stroke-width="2" fill="none"/>
                                <path d="M100 20 L160 20 L160 60 L100 60" stroke="#4F46E5" stroke-width="2" fill="none" stroke-dasharray="4 4"/>
                                <rect x="45" y="25" width="10" height="5" fill="#4F46E5" fill-opacity="0.2"/>
                                <rect x="60" y="25" width="30" height="5" fill="#4F46E5" fill-opacity="0.4"/>
                                <rect x="45" y="35" width="45" height="5" fill="#4F46E5" fill-opacity="0.2"/>
                                <rect x="45" y="45" width="25" height="5" fill="#4F46E5" fill-opacity="0.3"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Documentation Available</h3>
                            <p class="text-sm text-gray-600 mb-4">
                                {{ documentationFiles.length }} file{{ documentationFiles.length > 1 ? 's' : '' }} including guides, API references, and examples
                            </p>
                            <button @click="activeTab = 'documentation'"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                Browse Documentation
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>

                        <!-- Quick Start Guide SVG -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <svg class="w-full h-32 mb-4" viewBox="0 0 200 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="10" y="20" width="30" height="60" rx="4" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                <rect x="50" y="20" width="30" height="60" rx="4" fill="#4F46E5" fill-opacity="0.4" stroke="#4F46E5" stroke-width="2"/>
                                <rect x="90" y="20" width="30" height="60" rx="4" fill="#4F46E5" fill-opacity="0.6" stroke="#4F46E5" stroke-width="2"/>
                                <rect x="130" y="20" width="30" height="60" rx="4" fill="#4F46E5" fill-opacity="0.8" stroke="#4F46E5" stroke-width="2"/>
                                <path d="M25 90 L95 90 L95 80 L105 90 L95 100 L95 90" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Quick Start Guide</h3>
                            <p class="text-sm text-gray-600 mb-4">Get up and running in minutes with our comprehensive guide</p>
                            <a href="#" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                View Guide
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Features Tab -->
                <div v-if="activeTab === 'features'" class="space-y-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(feature, index) in features" :key="index"
                             class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition">
                            <!-- Feature SVG Icon -->
                            <div class="w-12 h-12 mb-4">
                                <svg v-if="index % 3 === 0" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="24" cy="24" r="20" stroke="#4F46E5" stroke-width="2" stroke-dasharray="3 3"/>
                                    <path d="M24 12 L24 36 M12 24 L36 24" stroke="#4F46E5" stroke-width="2"/>
                                    <circle cx="24" cy="24" r="6" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                </svg>
                                <svg v-else-if="index % 3 === 1" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="8" y="8" width="32" height="32" rx="8" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4"/>
                                    <circle cx="24" cy="24" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                    <path d="M24 16 L24 32 M16 24 L32 24" stroke="#4F46E5" stroke-width="2"/>
                                </svg>
                                <svg v-else viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 36 L24 12 L36 36" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4" fill="none"/>
                                    <circle cx="24" cy="24" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                                    <circle cx="18" cy="30" r="3" fill="#4F46E5"/>
                                    <circle cx="30" cy="30" r="3" fill="#4F46E5"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ feature }}</h3>
                            <p class="text-gray-600 text-sm">
                                Advanced AI-powered {{ feature.toLowerCase() }} capabilities with real-time processing and optimization.
                            </p>
                        </div>
                    </div>

                    <!-- Feature Comparison SVG -->
                    <div class="bg-white rounded-lg shadow-sm p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Feature Comparison</h2>
                        <svg class="w-full h-64" viewBox="0 0 600 200" preserveAspectRatio="none">
                            <!-- Bars -->
                            <rect x="50" y="150" width="60" height="50" fill="#4F46E5" fill-opacity="0.3" rx="4"/>
                            <rect x="130" y="100" width="60" height="100" fill="#4F46E5" fill-opacity="0.5" rx="4"/>
                            <rect x="210" y="70" width="60" height="130" fill="#4F46E5" fill-opacity="0.7" rx="4"/>
                            <rect x="290" y="40" width="60" height="160" fill="#4F46E5" fill-opacity="0.9" rx="4"/>
                            <rect x="370" y="20" width="60" height="180" fill="#4F46E5" rx="4"/>

                            <!-- Labels -->
                            <text x="60" y="180" fill="#4B5563" font-size="12">Basic</text>
                            <text x="140" y="180" fill="#4B5563" font-size="12">Standard</text>
                            <text x="220" y="180" fill="#4B5563" font-size="12">Pro</text>
                            <text x="305" y="180" fill="#4B5563" font-size="12">Business</text>
                            <text x="380" y="180" fill="#4B5563" font-size="12">Enterprise</text>
                        </svg>
                    </div>
                </div>

                <!-- Documentation Tab -->
                <div v-if="activeTab === 'documentation'" class="space-y-8">
                    <!-- Documentation Header -->
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg p-8 text-white">
                        <div class="flex items-center gap-4 mb-4">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <div>
                                <h2 class="text-3xl font-bold">Documentation</h2>
                                <p class="text-indigo-100">Comprehensive guides, API references, and examples</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                {{ documentationFiles.length }} Files
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                {{ documentationFiles.filter(f => isImageFile(f)).length }} Images
                            </span>
                            <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-sm">
                                {{ documentationFiles.filter(f => f.name?.endsWith('.pdf')).length }} PDFs
                            </span>
                        </div>
                    </div>

                    <!-- SDK Download Section -->
                    <div v-if="tool.sdk_download_url" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">SDK Download</h3>
                        <a :href="tool.sdk_download_url"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download SDK
                        </a>
                    </div>

                    <!-- Documentation Files Grid -->
                    <div v-if="documentationFiles.length > 0" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Documentation Files</h3>

                        <!-- Files Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(file, index) in documentationFiles" :key="index"
                                 class="border rounded-lg p-4 hover:shadow-md transition group">
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">{{ getFileIcon(file.name, file.type) }}</span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 truncate" :title="file.name">
                                            {{ file.name }}
                                        </p>
                                        <p class="text-sm text-gray-500">{{ formatFileSize(file.size) }}</p>

                                        <!-- File Type Badge -->
                                        <span class="inline-block mt-2 text-xs bg-gray-100 px-2 py-1 rounded">
                                            {{ file.name?.split('.').pop()?.toUpperCase() || 'FILE' }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2 mt-4 pt-3 border-t border-gray-100">
                                    <button @click="previewFile(file)"
                                            class="flex-1 flex items-center justify-center px-3 py-2 bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 transition text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Preview
                                    </button>
                                    <button @click="downloadFile(file)"
                                            class="flex-1 flex items-center justify-center px-3 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition text-sm">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        Download
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- External Documentation Link -->
                    <div v-if="tool.metadata?.documentation_url" class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">External Documentation</h3>
                        <a :href="tool.metadata.documentation_url"
                           target="_blank"
                           rel="noopener noreferrer"
                           class="inline-flex items-center px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Visit Documentation Website
                        </a>
                    </div>

                    <!-- No Documentation Message -->
                    <div v-if="!tool.sdk_download_url && !documentationFiles.length && !tool.metadata?.documentation_url"
                         class="bg-gray-50 rounded-lg p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Documentation Available</h3>
                        <p class="text-gray-500">Check back later for documentation and guides.</p>
                    </div>
                </div>

                <!-- Pricing Tab -->
                <div v-if="activeTab === 'pricing'" class="space-y-8">
                    <!-- Pricing Header SVG -->
                    <div class="text-center">
                        <svg class="w-24 h-24 mx-auto mb-4" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="50" cy="50" r="40" stroke="#4F46E5" stroke-width="2" stroke-dasharray="5 5"/>
                            <path d="M50 20 L50 80 M20 50 L80 50" stroke="#4F46E5" stroke-width="2"/>
                            <circle cx="50" cy="50" r="10" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                            <text x="50" y="55" text-anchor="middle" fill="#4F46E5" font-size="20" font-weight="bold">$</text>
                        </svg>
                        <h2 class="text-3xl font-bold text-gray-900 mb-4">Flexible Pricing Plans</h2>
                        <p class="text-xl text-gray-600 mb-8">Choose the plan that best fits your needs</p>
                    </div>

                    <!-- Pricing Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="plan in tool.plans" :key="plan.id"
                             :class="['relative bg-white rounded-lg shadow-sm overflow-hidden transform transition hover:-translate-y-1 hover:shadow-xl',
                                      plan.is_popular ? 'border-2 border-indigo-500' : 'border border-gray-200']">

                            <!-- Popular Badge -->
                            <div v-if="plan.is_popular"
                                 class="absolute top-0 right-0 bg-indigo-500 text-white px-3 py-1 text-sm font-medium rounded-bl-lg">
                                Most Popular
                            </div>

                            <!-- Plan Header -->
                            <div class="p-6" :style="{ background: `linear-gradient(135deg, ${getToolColor()}10, transparent)` }">
                                <h3 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h3>
                                <div class="flex items-baseline">
                                    <span class="text-4xl font-bold text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                    <span class="text-gray-500 ml-2">/{{ plan.billing_cycle }}</span>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">{{ plan.description }}</p>
                            </div>

                            <!-- Plan Features -->
                            <div class="p-6">
                                <ul class="space-y-3">
                                    <li class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ plan.api_call_limit?.toLocaleString() || 'Unlimited' }} API calls</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ plan.device_limit || 'Unlimited' }} devices</span>
                                    </li>
                                    <li class="flex items-center text-sm">
                                        <svg class="w-4 h-4 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>Email support</span>
                                    </li>
                                </ul>

                                <!-- CTA Button -->
                            <Link v-if="plan.billing_cycle !== 'lifetime' || plan.billing_cycle !== 'one_time'"
                                :href="route('plans.show', plan.id)"
                                class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition"
                                :class="{ 'bg-indigo-600': plan.is_popular }">
                                View Plan
                            </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Enterprise Contact -->
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-8 text-center">
                        <svg class="w-16 h-16 mx-auto mb-4" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="20" y="20" width="40" height="40" rx="8" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4"/>
                            <path d="M30 35 L50 35 M30 45 L45 45" stroke="#4F46E5" stroke-width="2"/>
                            <circle cx="60" cy="30" r="5" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                        </svg>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Need a custom enterprise plan?</h3>
                        <p class="text-gray-600 mb-4">Contact us for custom pricing and dedicated support</p>
                        <Link href="/contact" class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            Contact Sales
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </Link>
                    </div>
                </div>

                <!-- FAQ Tab -->
                <div v-if="activeTab === 'faq'" class="max-w-3xl mx-auto">
                    <div class="text-center mb-8">
                        <svg class="w-20 h-20 mx-auto mb-4" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="40" cy="40" r="32" stroke="#4F46E5" stroke-width="2" stroke-dasharray="4 4"/>
                            <circle cx="40" cy="40" r="8" fill="#4F46E5" fill-opacity="0.2" stroke="#4F46E5" stroke-width="2"/>
                            <path d="M30 30 L50 50 M50 30 L30 50" stroke="#4F46E5" stroke-width="2"/>
                        </svg>
                        <h2 class="text-3xl font-bold text-gray-900">Frequently Asked Questions</h2>
                    </div>

                    <div class="space-y-4">
                        <div v-for="(faq, index) in faqs" :key="index"
                             class="bg-white rounded-lg shadow-sm overflow-hidden">
                            <details class="group">
                                <summary class="flex justify-between items-center p-6 cursor-pointer list-none">
                                    <h3 class="text-lg font-medium text-gray-900">{{ faq.question }}</h3>
                                    <svg class="w-5 h-5 text-gray-500 group-open:rotate-180 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </summary>
                                <div class="px-6 pb-6 text-gray-600">
                                    {{ faq.answer }}
                                </div>
                            </details>
                        </div>
                    </div>

                    <!-- Still Have Questions -->
                    <div class="mt-8 bg-gray-50 rounded-lg p-6 text-center">
                        <p class="text-gray-600 mb-4">Still have questions? We're here to help!</p>
                        <Link href="/contact" class="text-indigo-600 hover:text-indigo-800 font-medium">
                            Contact Support →
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Related Tools -->
            <div v-if="relatedTools.length" class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Tools You Might Like</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Link v-for="related in relatedTools" :key="related.id"
                          :href="route('Alltools.show', related.id)"
                          class="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition group">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-2">{{ related.metadata?.icon || '🤖' }}</span>
                            <h3 class="font-semibold text-gray-900">{{ related.name }}</h3>
                        </div>
                        <p class="text-sm text-gray-500 line-clamp-2">{{ related.description }}</p>
                    </Link>
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
                                    <button @click="closePreview" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Image Preview -->
                                <div v-if="isImageFile(selectedFile)" class="flex justify-center">
                                    <img :src="selectedFile.content || selectedFile.url"
                                         :alt="selectedFile.name"
                                         class="max-w-full max-h-[70vh] object-contain rounded-lg" />
                                </div>

                                <!-- Text File Preview with loading and error states -->
                                <div v-else-if="isTextFile(selectedFile)" class="bg-gray-50 p-4 rounded-lg max-h-[70vh] overflow-auto">
                                    <!-- Loading State -->
                                    <div v-if="previewLoading" class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <p class="mt-2 text-gray-500">Loading content...</p>
                                    </div>

                                    <!-- Error State -->
                                    <div v-else-if="previewError" class="text-center py-8">
                                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <p class="mt-2 text-red-600">{{ previewError }}</p>
                                        <p class="text-sm text-gray-500 mt-1">You can download the file instead</p>
                                    </div>

                                    <!-- Content -->
                                    <pre v-else-if="textContent" class="text-sm text-gray-800 whitespace-pre-wrap font-mono">{{ textContent }}</pre>

                                    <!-- No Content -->
                                    <div v-else class="text-center py-8">
                                        <p class="text-gray-500">No content available</p>
                                    </div>
                                </div>

                                <!-- PDF Preview (using iframe) -->
                                <div v-else-if="selectedFile.name?.endsWith('.pdf')" class="h-[70vh]">
                                    <iframe :src="selectedFile.content || selectedFile.url"
                                            class="w-full h-full rounded-lg"
                                            frameborder="0">
                                    </iframe>
                                </div>

                                <!-- Other File Types -->
                                <div v-else class="text-center py-12">
                                    <span class="text-6xl block mb-4">{{ getFileIcon(selectedFile.name, selectedFile.type) }}</span>
                                    <p class="text-gray-500 mb-4">Preview not available for this file type</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="downloadFile(selectedFile)"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Download
                        </button>
                        <button @click="closePreview"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
