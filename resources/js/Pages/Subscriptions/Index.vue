<!-- resources/js/Pages/Subscriptions/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    licenses: {
        type: Array,
        default: () => []
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            tool_id: ''
        })
    },
    tools: {
        type: Array,
        default: () => []
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            expired: 0,
            revenue: 0
        })
    }
});

const page = usePage();
const user = page.props.auth.user;

// Check if user is admin or super-admin
const isAdmin = computed(() => {
    if (!user) return false;
    const roles = user.roles || [];
    return roles.some(role =>
        role === 'admin' ||
        role === 'super-admin' ||
        role === 'super_admin' ||
        role?.name === 'admin' ||
        role?.name === 'super-admin'
    );
});

// State
const search = ref(props.filters?.search || '');
const statusFilter = ref(props.filters?.status || '');
const toolFilter = ref(props.filters?.tool_id || '');
const selectedLicenses = ref([]);
const selectAll = ref(false);
const showRevokeModal = ref(false);
const revokingLicense = ref(null);

// Status options
const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active', color: 'green' },
    { value: 'expired', label: 'Expired', color: 'red' },
    { value: 'revoked', label: 'Revoked', color: 'gray' }
];

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

// Apply filters
const applyFilters = () => {
    router.get(route('subscriptions.index'), {
        search: search.value,
        status: statusFilter.value,
        tool_id: toolFilter.value
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

// Search debounce
const debouncedSearch = debounce(() => {
    applyFilters();
}, 300);

watch(search, () => {
    debouncedSearch();
});

watch([statusFilter, toolFilter], () => {
    applyFilters();
});

// Select all functionality
watch(selectAll, (value) => {
    if (value && props.licenses.length) {
        selectedLicenses.value = props.licenses.map(license => license.id);
    } else {
        selectedLicenses.value = [];
    }
});

// Check if all are selected
watch(selectedLicenses, (value) => {
    if (props.licenses.length) {
        selectAll.value = value.length === props.licenses.length && props.licenses.length > 0;
    }
}, { deep: true });

// Format currency
const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

// Format date
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
        revoked: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
};

// Check if license is expiring soon
const isExpiringSoon = (license) => {
    if (!license.expires_at || license.status !== 'active') return false;
    const daysLeft = Math.ceil((new Date(license.expires_at) - new Date()) / (1000 * 60 * 60 * 24));
    return daysLeft > 0 && daysLeft <= 7;
};

// Get days left
const getDaysLeft = (license) => {
    if (!license.expires_at) return 'Never';
    const daysLeft = Math.ceil((new Date(license.expires_at) - new Date()) / (1000 * 60 * 60 * 24));
    if (daysLeft < 0) return 'Expired';
    if (daysLeft === 0) return 'Today';
    return `${daysLeft} days`;
};

// View license details
const viewLicense = (license) => {
    router.get(route('licenses.show', license.id));
};

// Revoke license
const revokeLicense = (license) => {
    revokingLicense.value = license;
    showRevokeModal.value = true;
};

const confirmRevoke = async () => {
    if (!revokingLicense.value) return;

    try {
        await axios.post(route('licenses.revoke', revokingLicense.value.id));
        showRevokeModal.value = false;
        revokingLicense.value = null;
        applyFilters(); // Refresh the list
    } catch (error) {
        console.error('Failed to revoke license:', error);
    }
};

// Renew license
const renewLicense = (license) => {
    router.get(route('subscriptions.renew', license.id));
};

// Export subscriptions
const exportSubscriptions = () => {
    window.location.href = route('subscriptions.export', {
        search: search.value,
        status: statusFilter.value,
        tool_id: toolFilter.value
    });
};
</script>

<template>
    <Head title="Subscriptions" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">
                        {{ isAdmin ? 'All Subscriptions' : 'My Subscriptions' }}
                    </h2>
                    <p v-if="!isAdmin" class="text-sm text-gray-500 mt-1">
                        Manage your active licenses and subscriptions
                    </p>
                </div>
                <div class="flex gap-3">
                    <!-- Export Button (Admin only) -->
                    <button
                        v-if="isAdmin"
                        @click="exportSubscriptions"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                    </button>

                    <!-- Browse Tools Button -->
                    <Link
                        :href="route('tools.index')"
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Browse Tools
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards (Admin only) -->
                <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total Subscriptions</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total }}</p>
                            </div>
                            <div class="bg-indigo-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Active</p>
                                <p class="text-2xl font-bold text-green-600">{{ stats.active }}</p>
                            </div>
                            <div class="bg-green-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Expired</p>
                                <p class="text-2xl font-bold text-red-600">{{ stats.expired }}</p>
                            </div>
                            <div class="bg-red-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total Revenue</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.revenue) }}</p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-lg">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <!-- Search -->
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <div class="relative">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search by license key, user, or tool..."
                                        class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                    >
                                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                            </div>

                            <!-- Status Filter -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    v-model="statusFilter"
                                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

                            <!-- Tool Filter (Admin only) -->
                            <div v-if="isAdmin">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tool</label>
                                <select
                                    v-model="toolFilter"
                                    class="w-full border rounded-lg px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Tools</option>
                                    <option v-for="tool in tools" :key="tool.id" :value="tool.id">
                                        {{ tool.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscriptions Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th v-if="isAdmin" class="px-6 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                    >
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License</th>
                                <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool / Plan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usage</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-if="licenses.length > 0">
                                <tr v-for="license in licenses" :key="license.id" class="hover:bg-gray-50">
                                    <td v-if="isAdmin" class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            v-model="selectedLicenses"
                                            :value="license.id"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-mono text-gray-900">{{ license.license_key }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ license.id }}</div>
                                        <div v-if="license.package_name" class="text-xs text-gray-400">{{ license.package_name }}</div>
                                    </td>

                                    <!-- User Info (Admin only) -->
                                    <td v-if="isAdmin" class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                                <span class="text-indigo-600 font-medium text-sm">
                                                    {{ license.user?.name?.charAt(0) || 'U' }}
                                                </span>
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ license.user?.name || 'Unknown' }}</div>
                                                <div class="text-xs text-gray-500">{{ license.user?.email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tool & Plan -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 flex-shrink-0 bg-indigo-100 rounded flex items-center justify-center text-lg">
                                                {{ license.tool?.metadata?.icon || '🤖' }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ license.tool?.name || 'Unknown Tool' }}</div>
                                                <div class="text-xs text-gray-500">{{ license.plan?.name || 'No Plan' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getStatusClass(license.status)">
                                            {{ license.status }}
                                        </span>
                                        <span v-if="isExpiringSoon(license)" class="ml-2 text-xs text-orange-600 font-medium">
                                            Expiring soon!
                                        </span>
                                    </td>

                                    <!-- Expiry -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ formatDate(license.expires_at) }}</div>
                                        <div class="text-xs" :class="license.status === 'active' && license.expires_at ? 'text-gray-500' : 'text-red-500'">
                                            {{ getDaysLeft(license) }}
                                        </div>
                                    </td>

                                    <!-- Usage -->
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ license.api_calls_used || 0 }} / {{ license.api_calls_limit || '∞' }}
                                        </div>
                                        <div class="w-24 h-1.5 bg-gray-200 rounded-full mt-1">
                                            <div
                                                class="h-1.5 rounded-full"
                                                :class="(license.api_calls_used / license.api_calls_limit) > 0.8 ? 'bg-orange-500' : 'bg-green-500'"
                                                :style="{ width: Math.min((license.api_calls_used / (license.api_calls_limit || 1)) * 100, 100) + '%' }"
                                            ></div>
                                        </div>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="viewLicense(license)"
                                            class="text-blue-600 hover:text-blue-900 mr-3 inline-block"
                                            title="View Details"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>

                                        <button
                                            v-if="license.status === 'active' && license.expires_at"
                                            @click="renewLicense(license)"
                                            class="text-green-600 hover:text-green-900 mr-3 inline-block"
                                            title="Renew License"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>

                                        <button
                                            v-if="isAdmin && license.status === 'active'"
                                            @click="revokeLicense(license)"
                                            class="text-red-600 hover:text-red-900 inline-block"
                                            title="Revoke License"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td :colspan="isAdmin ? 8 : 7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-900">No subscriptions found</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ isAdmin ? 'No users have subscribed to any tools yet.' : "You haven't subscribed to any tools yet." }}
                                    </p>
                                    <Link
                                        :href="route('tools.index')"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    >
                                        {{ isAdmin ? 'View Tools' : 'Browse Tools' }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Revoke License Modal -->
        <div v-if="showRevokeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Revoke License</h3>
                    <p class="text-gray-600 mb-6">
                        Are you sure you want to revoke this license for <strong>{{ revokingLicense?.tool?.name }}</strong>?
                        This action cannot be undone.
                    </p>
                    <div class="flex justify-end gap-3">
                        <button
                            @click="showRevokeModal = false"
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                        >
                            Cancel
                        </button>
                        <button
                            @click="confirmRevoke"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                        >
                            Revoke License
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
