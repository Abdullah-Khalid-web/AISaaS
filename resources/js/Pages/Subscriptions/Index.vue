<!-- resources/js/Pages/Subscriptions/Index.vue -->
<script setup>
import AdminLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    licenses: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    },
    tools: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

// State
const searchQuery = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const toolFilter = ref(props.filters.tool_id || '');
const selectedLicenses = ref([]);
const selectAll = ref(false);
const showBulkActions = ref(false);
const showCancelModal = ref(false);
const showStatusModal = ref(false);
const selectedLicense = ref(null);
const updatingStatus = ref(false);

// Status toggle form
const statusForm = useForm({
    status: ''
});

// Format currency
const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2
    }).format(amount || 0);
};

// Format date
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Get status badge class
const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'expired':
            return 'bg-red-100 text-red-800';
        case 'cancelled':
            return 'bg-gray-100 text-gray-800';
        case 'suspended':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Get status options for admin
const statusOptions = [
    { value: 'active', label: 'Active', color: 'green' },
    { value: 'pending', label: 'Pending', color: 'yellow' },
    { value: 'expired', label: 'Expired', color: 'red' },
    { value: 'cancelled', label: 'Cancelled', color: 'gray' },
    { value: 'suspended', label: 'Suspended', color: 'orange' }
];

// Get days remaining
const getDaysRemaining = (expiresAt) => {
    if (!expiresAt) return '∞';
    const days = Math.ceil((new Date(expiresAt) - new Date()) / (1000 * 60 * 60 * 24));
    return days > 0 ? days : 'Expired';
};

// Handle search
const handleSearch = () => {
    const params = new URLSearchParams(window.location.search);
    if (searchQuery.value) params.set('search', searchQuery.value);
    else params.delete('search');
    if (statusFilter.value) params.set('status', statusFilter.value);
    else params.delete('status');
    if (toolFilter.value) params.set('tool_id', toolFilter.value);
    else params.delete('tool_id');

    window.location.href = `${window.location.pathname}?${params.toString()}`;
};

// Reset filters
const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    toolFilter.value = '';
    handleSearch();
};

// Toggle select all (admin only)
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedLicenses.value = props.licenses.data.map(l => l.id);
    } else {
        selectedLicenses.value = [];
    }
};

// Cancel subscription (for users)
const cancelSubscription = (license) => {
    selectedLicense.value = license;
    showCancelModal.value = true;
};

// Open status modal (for admin)
const openStatusModal = (license) => {
    selectedLicense.value = license;
    statusForm.status = license.status;
    showStatusModal.value = true;
};

// Process status update (for admin)
const processStatusUpdate = () => {
    statusForm.put(route('subscriptions.update-status', selectedLicense.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showStatusModal.value = false;
            selectedLicense.value = null;
            statusForm.reset();
        }
    });
};

// Process cancel
const cancelForm = useForm({});
const processCancel = () => {
    cancelForm.delete(route('subscriptions.cancel', selectedLicense.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            showCancelModal.value = false;
            selectedLicense.value = null;
        }
    });
};


// Helper function to extract license key from flash message
const extractLicenseKey = (message) => {
    const match = message.match(/License key: ([A-Z0-9-]+)/);
    return match ? match[1] : '';
};

// Copy license key to clipboard
const copyLicenseKey = (key) => {
    navigator.clipboard.writeText(key).then(() => {
        // Show temporary success message
        alert('License key copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy license key');
    });
};
// Export CSV (admin only)
const exportData = () => {
    const params = new URLSearchParams();
    if (searchQuery.value) params.set('search', searchQuery.value);
    if (statusFilter.value) params.set('status', statusFilter.value);
    if (toolFilter.value) params.set('tool_id', toolFilter.value);

    window.location.href = route('subscriptions.export') + '?' + params.toString();
};
</script>

<template>
    <AdminLayout>
        <Head :title="isAdmin ? 'Subscriptions Management' : 'My Subscriptions'" />

        <!-- Header -->
        <div class="bg-white shadow">
            <div class="px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <h1 class="text-2xl font-semibold text-gray-900">
                        {{ isAdmin ? 'Subscriptions' : 'My Subscriptions' }}
                    </h1>
                    <div class="flex space-x-3">
                        <button v-if="isAdmin" @click="exportData"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Export CSV
                        </button>
                        <Link v-if="!isAdmin" href="/tools"
                              class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Subscribe to New Tool
                        </Link>
                    </div>
                </div>

                <!-- User Info for non-admin -->
                <div v-if="!isAdmin && licenses.data.length === 0" class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <p class="text-blue-700">
                        You don't have any active subscriptions yet.
                        <Link href="/tools" class="font-medium underline">Browse our AI tools</Link> to get started!
                    </p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        <div v-if="$page.props.flash?.success || $page.props.flash?.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div v-if="$page.props.flash?.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative flex items-center justify-between" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.success }}</span>
                </div>
                <button @click="$page.props.flash.success = null" class="text-green-700 hover:text-green-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div v-if="$page.props.flash?.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative flex items-center justify-between" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">{{ $page.props.flash.error }}</span>
                </div>
                <button @click="$page.props.flash.error = null" class="text-red-700 hover:text-red-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Optional: Add a success banner with copy button for license key -->
        <div v-if="$page.props.flash?.success?.includes('License key:')" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium text-indigo-800">Your License Key</h3>
                        <div class="mt-2 flex items-center space-x-2">
                            <code class="px-4 py-2 bg-white rounded-lg border border-indigo-200 font-mono text-indigo-800">
                                {{ extractLicenseKey($page.props.flash.success) }}
                            </code>
                            <button @click="copyLicenseKey(extractLicenseKey($page.props.flash.success))"
                                    class="inline-flex items-center px-3 py-2 border border-indigo-300 rounded-md text-sm font-medium text-indigo-700 bg-white hover:bg-indigo-50">
                                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                Copy
                            </button>
                        </div>
                        <p class="mt-2 text-sm text-indigo-600">
                            Save this license key. You'll need it to activate your software.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards (Admin Only) -->
        <div v-if="isAdmin && stats" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-indigo-100 rounded-full">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Total Subscriptions</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.total || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-green-100 rounded-full">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Active</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.active || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-red-100 rounded-full">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Expired</p>
                            <p class="text-2xl font-bold text-gray-900">{{ stats.expired || 0 }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 bg-purple-100 rounded-full">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm text-gray-500">Revenue</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.revenue) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters (Admin Only - More filters) -->
        <div v-if="isAdmin" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search by license key, user, tool..."
                                @keyup.enter="handleSearch"
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                            >
                            <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="statusFilter"
                            @change="handleSearch"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>

                    <!-- Tool Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tool</label>
                        <select
                            v-model="toolFilter"
                            @change="handleSearch"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                        >
                            <option value="">All Tools</option>
                            <option v-for="tool in tools" :key="tool.id" :value="tool.id">
                                {{ tool.name }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Active Filters -->
                <div v-if="searchQuery || statusFilter || toolFilter" class="mt-4 flex justify-between items-center">
                    <div class="flex flex-wrap gap-2">
                        <span v-if="searchQuery" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center">
                            Search: "{{ searchQuery }}"
                            <button @click="searchQuery = ''; handleSearch()" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                        <span v-if="statusFilter" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center">
                            Status: {{ statusFilter }}
                            <button @click="statusFilter = ''; handleSearch()" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                        <span v-if="toolFilter" class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center">
                            Tool: {{ tools.find(t => t.id == toolFilter)?.name }}
                            <button @click="toolFilter = ''; handleSearch()" class="ml-2 text-indigo-600 hover:text-indigo-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </span>
                    </div>
                    <button @click="resetFilters" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Clear all filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Simple Search for Non-Admin -->
        <div v-if="!isAdmin" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search your subscriptions..."
                        @keyup.enter="handleSearch"
                        class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                    >
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Subscriptions Table -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Add overflow-x-auto container -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <!-- Admin Checkbox Column -->
                                <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <input type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleSelectAll"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Key</th>
                                <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">API Usage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="license in licenses.data" :key="license.id" class="hover:bg-gray-50">
                                <!-- Admin Checkbox -->
                                <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox"
                                        v-model="selectedLicenses"
                                        :value="license.id"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>

                                <!-- License Key -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-mono text-gray-900">{{ license.license_key }}</div>
                                    <div class="text-xs text-gray-500">{{ license.package_name }}</div>
                                </td>

                                <!-- User Column (Admin Only) -->
                                <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ license.user?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ license.user?.email || 'N/A' }}</div>
                                </td>

                                <!-- Tool -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="text-xl mr-2">{{ license.tool?.metadata?.icon || '🤖' }}</span>
                                        <span class="text-sm text-gray-900">{{ license.tool?.name || 'N/A' }}</span>
                                    </div>
                                </td>

                                <!-- Plan -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ license.plan?.name || 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ formatCurrency(license.plan?.price, license.plan?.currency) }}/{{ license.plan?.billing_cycle }}</div>
                                </td>

                                <!-- Status - Different for Admin vs User -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="isAdmin" class="flex items-center space-x-2">
                                        <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadgeClass(license.status)]">
                                            {{ license.status }}
                                        </span>
                                        <!-- Status Toggle for Admin -->
                                        <button @click="openStatusModal(license)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="Change status">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Regular Status Badge for User -->
                                    <span v-else :class="['px-2 py-1 text-xs rounded-full', getStatusBadgeClass(license.status)]">
                                        {{ license.status }}
                                    </span>
                                </td>

                                <!-- Expires -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ formatDate(license.expires_at) }}</div>
                                    <div class="text-xs" :class="license.status === 'active' ? 'text-green-600' : 'text-gray-500'">
                                        {{ getDaysRemaining(license.expires_at) }} days left
                                    </div>
                                </td>

                                <!-- API Usage -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full"
                                            :style="{ width: (license.api_calls_used / license.api_calls_limit * 100) + '%' }"></div>
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ license.api_calls_used?.toLocaleString() }} / {{ license.api_calls_limit?.toLocaleString() || '∞' }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link :href="route('licenses.show', license.id)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        View
                                    </Link>
                                    <!-- Cancel button only for active licenses and for users or admins -->
                                    <button v-if="license.status === 'active' && (!isAdmin || (isAdmin && license.user_id !== $page.props.auth.user.id))"
                                            @click="cancelSubscription(license)"
                                            class="text-red-600 hover:text-red-900">
                                        Cancel
                                    </button>
                                    <!-- Admin can also cancel any subscription -->
                                    <button v-if="isAdmin && license.status === 'active' && license.user_id === $page.props.auth.user.id"
                                            @click="cancelSubscription(license)"
                                            class="text-red-600 hover:text-red-900">
                                        Cancel
                                    </button>
                                </td>
                            </tr>

                            <!-- Empty State -->
                            <tr v-if="licenses.data.length === 0">
                                <td :colspan="isAdmin ? 9 : 8" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No subscriptions found</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ isAdmin ? 'No subscriptions match your criteria.' : "You haven't subscribed to any tools yet." }}
                                    </p>
                                    <div class="mt-6">
                                        <Link v-if="!isAdmin" href="/tools"
                                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                            </svg>
                                            Browse Tools
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="licenses.data.length > 0" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <a :href="licenses.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Previous
                        </a>
                        <a :href="licenses.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            Next
                        </a>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ licenses.from }}</span>
                                to
                                <span class="font-medium">{{ licenses.to }}</span>
                                of
                                <span class="font-medium">{{ licenses.total }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <a v-for="(link, index) in licenses.links" :key="index"
                                   :href="link.url"
                                   v-html="link.label"
                                   :class="[
                                       'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                       link.active
                                           ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                           : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                       index === 0 ? 'rounded-l-md' : '',
                                       index === licenses.links.length - 1 ? 'rounded-r-md' : ''
                                   ]">
                                </a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancel Subscription Modal (for both users and admins) -->
        <div v-if="showCancelModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showCancelModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Cancel Subscription
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to cancel this subscription?
                                        <span class="font-semibold">{{ selectedLicense?.license_key }}</span> for
                                        <span class="font-semibold">{{ selectedLicense?.tool?.name }}</span> will be deactivated.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="processCancel"
                                type="button"
                                :disabled="cancelForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <svg v-if="cancelForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Cancel Subscription
                        </button>
                        <button @click="showCancelModal = false"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Update Modal (Admin Only) -->
        <div v-if="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showStatusModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Update Subscription Status
                                </h3>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Change status for license: <span class="font-mono font-medium">{{ selectedLicense?.license_key }}</span>
                                    </p>

                                    <select
                                        v-model="statusForm.status"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                        <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                            {{ option.label }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="processStatusUpdate"
                                type="button"
                                :disabled="statusForm.processing || statusForm.status === selectedLicense?.status"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <svg v-if="statusForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Update Status
                        </button>
                        <button @click="showStatusModal = false"
                                type="button"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
