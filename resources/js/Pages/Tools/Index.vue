<!-- resources/js/Pages/Tools/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    tools: {
        type: Array,
        default: () => []
    },
    userLicenses: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
});

const page = usePage();
const user = page.props.auth.user;
const isAdmin = computed(() => user && (user.roles?.includes('admin') || user.roles?.includes('super-admin')));

// Debug: Log the props
console.log('Tools props:', props.tools);
console.log('Is Admin:', isAdmin.value);

// State
const search = ref(props.filters?.search || '');
const selectedTools = ref([]);
const selectAll = ref(false);
const showDeleteModal = ref(false);

// Custom debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search debounce
const debouncedSearch = debounce((value) => {
    router.get(route('tools.index'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

// Select all functionality
watch(selectAll, (value) => {
    if (value && props.tools.length) {
        selectedTools.value = props.tools.map(tool => tool.id);
    } else {
        selectedTools.value = [];
    }
});

// Check if all are selected
watch(selectedTools, (value) => {
    if (props.tools.length) {
        selectAll.value = value.length === props.tools.length && props.tools.length > 0;
    }
}, { deep: true });

// Methods
const toggleStatus = async (tool) => {
    try {
        const response = await axios.post(route('tools.toggle-status', tool.id));
        tool.is_active = response.data.is_active;
    } catch (error) {
        console.error('Failed to toggle status', error);
    }
};

const deleteTool = (tool) => {
    if (confirm(`Are you sure you want to delete "${tool.name}"?`)) {
        router.delete(route('tools.destroy', tool.id), {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Delete tool errors:', errors);
            }
        });
    }
};

const bulkDelete = () => {
    if (selectedTools.value.length === 0) return;

    if (confirm(`Are you sure you want to delete ${selectedTools.value.length} tools?`)) {
        router.post(route('tools.bulk-destroy'), {
            ids: selectedTools.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedTools.value = [];
                showDeleteModal.value = false;
            },
            onError: (errors) => {
                console.error('Bulk delete errors:', errors);
            }
        });
    }
};

const formatPlatforms = (platforms) => {
    if (!platforms || platforms.length === 0) return 'Not specified';
    return platforms.join(', ');
};

const getPlanCount = (tool) => {
    return tool.plans?.length || 0;
};

const getLowestPrice = (tool) => {
    if (!tool.plans || tool.plans.length === 0) return null;
    const prices = tool.plans.map(p => p.price);
    return Math.min(...prices);
};

const formatCurrency = (price, currency = 'USD') => {
    if (price === null) return 'No plans';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
};

const getUserLicenseForTool = (toolId) => {
    return props.userLicenses.find(l => l.tool_id === toolId);
};
</script>

<template>
    <Head title="AI Tools Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    AI Tools Management
                </h2>
                <Link
                    v-if="isAdmin"
                    :href="route('tools.create')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Tool
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 flex flex-col sm:flex-row gap-4 justify-between">
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search tools by name or description..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedTools.length > 0" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ selectedTools.length }} selected</span>
                            <button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                Delete Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tools Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Platforms</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plans</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th v-if="isAdmin" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-if="tools.length > 0">
                                <tr v-for="tool in tools" :key="tool.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            v-model="selectedTools"
                                            :value="tool.id"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-12 w-12 flex-shrink-0 bg-indigo-100 rounded-lg flex items-center justify-center text-2xl">
                                                {{ tool.metadata?.icon || '🤖' }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ tool.name }}</div>
                                                <div class="text-xs text-gray-500">v{{ tool.version || '1.0.0' }}</div>
                                                <div class="text-xs text-gray-400">ID: {{ tool.id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">{{ tool.description || 'No description' }}</div>
                                        <div v-if="tool.sdk_download_url" class="text-xs text-indigo-600 mt-1">
                                            <a :href="tool.sdk_download_url" target="_blank" class="hover:underline">Download SDK</a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <span v-for="platform in tool.supported_platforms || []"
                                                  :key="platform"
                                                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ platform }}
                                            </span>
                                            <span v-if="!tool.supported_platforms?.length" class="text-xs text-gray-400">
                                                All platforms
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ getPlanCount(tool) }} {{ getPlanCount(tool) === 1 ? 'plan' : 'plans' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            From {{ formatCurrency(getLowestPrice(tool)) }}
                                        </div>

                                        <!-- User License Status -->
                                        <div v-if="getUserLicenseForTool(tool.id)" class="mt-2">
                                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                                Active License
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="toggleStatus(tool)"
                                            class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none"
                                            :class="tool.is_active ? 'bg-green-600' : 'bg-gray-300'"
                                        >
                                            <span class="sr-only">Toggle status</span>
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                                :class="tool.is_active ? 'translate-x-5' : 'translate-x-0'"
                                            />
                                        </button>
                                        <span class="ml-2 text-xs" :class="tool.is_active ? 'text-green-600' : 'text-gray-400'">
                                            {{ tool.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('tools.edit', tool.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3 inline-block"
                                            title="Edit Tool"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('tools.show', tool.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3 inline-block"
                                            title="View Tool"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('plans.create', { tool_id: tool.id })"
                                            class="text-green-600 hover:text-green-900 mr-3 inline-block"
                                            title="Add Plan"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deleteTool(tool)"
                                            class="text-red-600 hover:text-red-900 inline-block"
                                            title="Delete Tool"
                                            :disabled="tool.plans?.length > 0"
                                            :class="{ 'opacity-50 cursor-not-allowed': tool.plans?.length > 0 }"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td :colspan="isAdmin ? 7 : 6" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-900">No tools found</p>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new AI tool.</p>
                                    <Link
                                        v-if="isAdmin"
                                        :href="route('tools.create')"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    >
                                        Create Your First Tool
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
