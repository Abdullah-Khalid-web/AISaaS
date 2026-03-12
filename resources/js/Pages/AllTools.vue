<!-- resources/js/Pages/AllTools.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tools: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            category: '',
            search: '',
            sort: 'popular'
        })
    }
});

// State
const searchQuery = ref('');
const selectedCategory = ref('');
const sortBy = ref('popular');
const selectedTool = ref(null);
const showToolModal = ref(false);

// Filtered and sorted tools
const filteredTools = computed(() => {
    let filtered = [...props.tools];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(tool =>
            tool.name.toLowerCase().includes(query) ||
            tool.description.toLowerCase().includes(query) ||
            tool.category?.toLowerCase().includes(query)
        );
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(tool =>
            tool.category === selectedCategory.value ||
            tool.tags?.includes(selectedCategory.value)
        );
    }

    // Sort
    switch (sortBy.value) {
        case 'price-low':
            filtered.sort((a, b) => (a.minPrice || 0) - (b.minPrice || 0));
            break;
        case 'price-high':
            filtered.sort((a, b) => (b.minPrice || 0) - (a.minPrice || 0));
            break;
        case 'name':
            filtered.sort((a, b) => a.name.localeCompare(b.name));
            break;
        case 'popular':
        default:
            filtered.sort((a, b) => (b.popularity || 0) - (a.popularity || 0));
            break;
    }

    return filtered;
});

// Format currency
const formatCurrency = (price, currency = 'USD') => {
    if (!price) return 'Contact for pricing';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
};

// Get tool icon
const getToolIcon = (tool) => {
    return tool.metadata?.icon || '🤖';
};

// Get tool color
const getToolColor = (tool) => {
    return tool.metadata?.color || '#4f46e5';
};

// Get plan count text
const getPlanCountText = (count) => {
    if (count === 0) return 'No plans';
    if (count === 1) return '1 plan';
    return `${count} plans`;
};

// Open tool details modal
const openToolDetails = (tool) => {
    selectedTool.value = tool;
    showToolModal.value = true;
};

// Close modal
const closeModal = () => {
    showToolModal.value = false;
    selectedTool.value = null;
};

// Get unique categories from tools
const uniqueCategories = computed(() => {
    const cats = new Set();
    props.tools.forEach(tool => {
        if (tool.category) cats.add(tool.category);
        if (tool.tags) tool.tags.forEach(tag => cats.add(tag));
    });
    return Array.from(cats);
});
</script>

<template>
    <GuestLayout>
        <Head title="All AI Tools - AI Platform" />

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4">All AI Tools</h1>
                    <p class="text-xl text-indigo-100 max-w-3xl mx-auto">
                        Discover our complete collection of AI-powered tools designed to supercharge your productivity and creativity
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- Filters and Search -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search Tools</label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by name, description, or category..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select
                            v-model="selectedCategory"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Categories</option>
                            <option v-for="category in uniqueCategories" :key="category" :value="category">
                                {{ category }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort By -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
                        <select
                            v-model="sortBy"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="popular">Most Popular</option>
                            <option value="name">Name</option>
                            <option value="price-low">Price: Low to High</option>
                            <option value="price-high">Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters -->
                <div v-if="searchQuery || selectedCategory" class="mt-4 flex flex-wrap gap-2">
                    <span v-if="searchQuery" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center">
                        Search: "{{ searchQuery }}"
                        <button @click="searchQuery = ''" class="ml-2 text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                    <span v-if="selectedCategory" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center">
                        Category: {{ selectedCategory }}
                        <button @click="selectedCategory = ''" class="ml-2 text-indigo-600 hover:text-indigo-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </span>
                </div>
            </div>

            <!-- Results Count -->
            <div class="mb-6">
                <p class="text-gray-600">
                    Showing <span class="font-semibold">{{ filteredTools.length }}</span>
                    {{ filteredTools.length === 1 ? 'tool' : 'tools' }}
                </p>
            </div>

            <!-- Tools Grid -->
            <div v-if="filteredTools.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div v-for="tool in filteredTools" :key="tool.id"
                     class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition group">

                    <!-- Tool Header -->
                    <div class="p-6" :style="{ borderTop: `4px solid ${getToolColor(tool)}` }">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-2xl group-hover:scale-110 transition">
                                    {{ getToolIcon(tool) }}
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ tool.name }}</h3>
                                    <p class="text-sm text-gray-500">v{{ tool.version || '1.0.0' }}</p>
                                </div>
                            </div>
                            <span v-if="tool.is_new" class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                New
                            </span>
                        </div>

                        <!-- Description -->
                        <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ tool.description }}</p>

                        <!-- Tags/Categories -->
                        <div v-if="tool.tags" class="flex flex-wrap gap-1 mb-4">
                            <span v-for="tag in tool.tags.slice(0, 3)" :key="tag"
                                  class="bg-gray-100 text-gray-600 text-xs px-2 py-1 rounded-full">
                                {{ tag }}
                            </span>
                            <span v-if="tool.tags.length > 3" class="text-xs text-gray-400">
                                +{{ tool.tags.length - 3 }}
                            </span>
                        </div>

                        <!-- Features Preview -->
                        <div class="space-y-2 mb-4">
                            <div v-for="(feature, index) in tool.features?.slice(0, 2)" :key="index"
                                 class="flex items-center text-xs text-gray-600">
                                <svg class="w-3 h-3 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ feature }}
                            </div>
                            <div v-if="tool.features?.length > 2" class="text-xs text-indigo-600">
                                +{{ tool.features.length - 2 }} more features
                            </div>
                        </div>

                        <!-- Plans Info -->
                        <div class="border-t border-gray-100 pt-4 mt-2">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm text-gray-500">Starting from</span>
                                    <div class="text-xl font-bold text-indigo-600">
                                        {{ formatCurrency(tool.minPrice) }}
                                        <span v-if="tool.minPrice" class="text-xs font-normal text-gray-500">/mo</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-gray-500">{{ getPlanCountText(tool.plans?.length || 0) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button @click="openToolDetails(tool)"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm">
                                Quick View
                            </button>
                            <Link :href="route('Alltools.show', tool.id)"
                                  class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm text-center">
                                View Details
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No tools found</h3>
                <p class="mt-2 text-gray-500">Try adjusting your search or filter criteria</p>
                <button @click="searchQuery = ''; selectedCategory = ''"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    Clear Filters
                </button>
            </div>

            <!-- Pagination Placeholder (if needed) -->
            <div v-if="filteredTools.length > 0" class="mt-8 flex justify-center">
                <nav class="inline-flex rounded-md shadow">
                    <button class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Previous
                    </button>
                    <button class="px-3 py-2 border-t border-b border-gray-300 bg-indigo-50 text-sm font-medium text-indigo-600">
                        1
                    </button>
                    <button class="px-3 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        2
                    </button>
                    <button class="px-3 py-2 border-t border-b border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        3
                    </button>
                    <button class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Next
                    </button>
                </nav>
            </div>
        </div>

        <!-- Quick View Modal -->
        <div v-if="showToolModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

                <!-- Modal panel -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <!-- Modal Header -->
                                <div class="flex justify-between items-start mb-4">
                                    <div class="flex items-center">
                                        <div class="w-16 h-16 bg-indigo-100 rounded-xl flex items-center justify-center text-3xl">
                                            {{ getToolIcon(selectedTool) }}
                                        </div>
                                        <div class="ml-4">
                                            <h3 class="text-2xl font-bold text-gray-900">{{ selectedTool.name }}</h3>
                                            <p class="text-sm text-gray-500">v{{ selectedTool.version || '1.0.0' }}</p>
                                        </div>
                                    </div>
                                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Description -->
                                <div class="mb-4">
                                    <p class="text-gray-600">{{ selectedTool.description }}</p>
                                </div>

                                <!-- Features -->
                                <div v-if="selectedTool.features?.length" class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Key Features</h4>
                                    <ul class="grid grid-cols-2 gap-2">
                                        <li v-for="feature in selectedTool.features" :key="feature"
                                            class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ feature }}
                                        </li>
                                    </ul>
                                </div>

                                <!-- Plans -->
                                <div v-if="selectedTool.plans?.length" class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Available Plans</h4>
                                    <div class="space-y-2">
                                        <div v-for="plan in selectedTool.plans.slice(0, 3)" :key="plan.id"
                                             class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                            <div>
                                                <span class="font-medium text-gray-900">{{ plan.name }}</span>
                                                <span v-if="plan.is_popular" class="ml-2 text-xs bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full">Popular</span>
                                            </div>
                                            <span class="text-indigo-600 font-bold">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Platforms -->
                                <div v-if="selectedTool.supported_platforms?.length" class="mb-4">
                                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Supported Platforms</h4>
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="platform in selectedTool.supported_platforms" :key="platform"
                                              class="px-2 py-1 bg-gray-100 text-gray-600 rounded text-xs">
                                            {{ platform }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <Link :href="route('tools.show', selectedTool.id)"
                              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            View Full Details
                        </Link>
                        <button @click="closeModal"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
