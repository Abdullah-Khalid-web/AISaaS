<!-- resources/js/Pages/Licenses/Show.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'; // Changed from AdminLayout to AppLayout
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    license: {
        type: Object,
        required: true
    },
    isAdmin: {
        type: Boolean,
        default: false
    }
});

// Always use AppLayout - licenses require authentication
const layout = AppLayout;

// State
const showRenewModal = ref(false);
const showRevokeModal = ref(false);
const showApiKey = ref(false);
const activeTab = ref('details');

// Form for renewal
const renewForm = useForm({
    payment_method: 'stripe'
});

// Form for revoke
const revokeForm = useForm({});

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
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
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

// Handle renew
const processRenew = () => {
    renewForm.post(route('subscriptions.renew.process', props.license.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRenewModal.value = false;
        }
    });
};

// Handle revoke
const processRevoke = () => {
    revokeForm.delete(route('subscriptions.cancel', props.license.id), {
        preserveScroll: true,
        onSuccess: () => {
            showRevokeModal.value = false;
        }
    });
};

// Copy to clipboard
const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
    // You could add a toast notification here
    alert('License key copied to clipboard!');
};
</script>

<template>
    <component :is="layout">
        <Head :title="'License: ' + license.license_key" />

        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <Link :href="route('subscriptions.index')" class="text-indigo-600 hover:text-indigo-800 mb-2 inline-block">
                        ← Back to Subscriptions
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">License Details</h2>
                </div>
                <div class="flex space-x-3">
                    <button v-if="license.status === 'active'"
                            @click="showRenewModal = true"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Renew License
                    </button>
                    <button v-if="license.status === 'active' && (isAdmin || license.user_id === $page.props.auth.user?.id)"
                            @click="showRevokeModal = true"
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Revoke License
                    </button>
                </div>
            </div>
        </template>

        <!-- Main Content -->
        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- License Key Card -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg p-6 mb-8 text-white">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-indigo-200 text-sm mb-1">License Key</p>
                            <div class="flex items-center space-x-2">
                                <code class="text-2xl font-mono bg-white bg-opacity-20 px-4 py-2 rounded-lg">
                                    {{ license.license_key }}
                                </code>
                                <button @click="copyToClipboard(license.license_key)"
                                        class="p-2 hover:bg-white hover:bg-opacity-20 rounded-lg transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <span :class="['px-3 py-1 rounded-full text-sm font-medium', getStatusBadgeClass(license.status)]">
                            {{ license.status.toUpperCase() }}
                        </span>
                    </div>
                    <p class="text-indigo-200 text-sm mt-4">
                        Package: <span class="font-mono">{{ license.package_name }}</span>
                    </p>
                </div>

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-6">
                    <nav class="flex space-x-8">
                        <button @click="activeTab = 'details'"
                                :class="['pb-4 px-1 border-b-2 font-medium text-sm',
                                         activeTab === 'details' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            Details
                        </button>
                        <button @click="activeTab = 'usage'"
                                :class="['pb-4 px-1 border-b-2 font-medium text-sm',
                                         activeTab === 'usage' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            Usage & Analytics
                        </button>
                        <button @click="activeTab = 'devices'"
                                :class="['pb-4 px-1 border-b-2 font-medium text-sm',
                                         activeTab === 'devices' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            Devices
                        </button>
                        <button @click="activeTab = 'payments'"
                                :class="['pb-4 px-1 border-b-2 font-medium text-sm',
                                         activeTab === 'payments' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300']">
                            Payment History
                        </button>
                    </nav>
                </div>

                <!-- Tab Content (keep the rest of your content exactly as is) -->
                <div class="min-h-[400px]">
                    <!-- Details Tab -->
                    <div v-if="activeTab === 'details'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- License Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">License Information</h3>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Status</dt>
                                    <dd>
                                        <span :class="['px-2 py-1 text-xs rounded-full', getStatusBadgeClass(license.status)]">
                                            {{ license.status }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Created</dt>
                                    <dd class="font-medium">{{ formatDate(license.created_at) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Expires</dt>
                                    <dd class="font-medium" :class="license.isExpired ? 'text-red-600' : 'text-green-600'">
                                        {{ formatDate(license.expires_at) }}
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Auto-renew</dt>
                                    <dd class="font-medium">
                                        <span :class="license.auto_renew ? 'text-green-600' : 'text-gray-600'">
                                            {{ license.auto_renew ? 'Enabled' : 'Disabled' }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Trial</dt>
                                    <dd class="font-medium">{{ license.is_trial ? 'Yes' : 'No' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Tool Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tool Information</h3>
                            <div class="flex items-center mb-4">
                                <span class="text-4xl mr-3">{{ license.tool?.metadata?.icon || '🤖' }}</span>
                                <div>
                                    <p class="text-xl font-bold text-gray-900">{{ license.tool?.name }}</p>
                                    <p class="text-sm text-gray-500">v{{ license.tool?.version || '1.0' }}</p>
                                </div>
                            </div>
                            <dl class="space-y-3">
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Plan</dt>
                                    <dd class="font-medium">{{ license.plan?.name }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Billing Cycle</dt>
                                    <dd class="font-medium">{{ license.plan?.billing_cycle }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-500">Price</dt>
                                    <dd class="font-medium">{{ formatCurrency(license.plan?.price, license.plan?.currency) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Limits -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Usage Limits</h3>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-600">API Calls</span>
                                        <span class="font-medium">{{ license.api_calls_used?.toLocaleString() }} / {{ license.api_calls_limit?.toLocaleString() || '∞' }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full"
                                             :style="{ width: Math.min((license.api_calls_used / license.api_calls_limit * 100), 100) + '%' }"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Device Limit</span>
                                    <span class="font-medium">{{ license.device_count || 0 }} / {{ license.device_limit || '∞' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Domains Allowed</span>
                                    <span class="font-medium">{{ license.allowed_domains?.length || 0 }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Metadata -->
                        <div v-if="license.metadata" class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                            <dl class="space-y-2">
                                <div v-for="(value, key) in license.metadata" :key="key" class="flex justify-between">
                                    <dt class="text-gray-500 capitalize">{{ key.replace('_', ' ') }}</dt>
                                    <dd class="font-medium text-sm">{{ value }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Usage Tab -->
                    <div v-if="activeTab === 'usage'" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">API Usage Analytics</h3>

                        <!-- Usage Chart Placeholder -->
                        <div class="h-64 bg-gray-50 rounded-lg flex items-center justify-center mb-6">
                            <svg class="w-full h-full" viewBox="0 0 400 200" preserveAspectRatio="none">
                                <polyline points="0,150 50,120 100,80 150,100 200,60 250,90 300,40 350,70 400,30"
                                          stroke="#4F46E5" stroke-width="2" fill="none"/>
                                <polygon points="0,150 0,200 400,200 400,30 350,70 300,40 250,90 200,60 150,100 100,80 50,120 0,150"
                                         fill="#4F46E5" fill-opacity="0.1"/>
                            </svg>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                                <p class="text-sm text-gray-600">Today</p>
                                <p class="text-2xl font-bold text-indigo-600">1,234</p>
                            </div>
                            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                                <p class="text-sm text-gray-600">This Week</p>
                                <p class="text-2xl font-bold text-indigo-600">8,901</p>
                            </div>
                            <div class="text-center p-4 bg-indigo-50 rounded-lg">
                                <p class="text-sm text-gray-600">This Month</p>
                                <p class="text-2xl font-bold text-indigo-600">45,678</p>
                            </div>
                        </div>
                    </div>

                    <!-- Devices Tab -->
                    <div v-if="activeTab === 'devices'" class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Registered Devices</h3>

                        <div v-if="license.device_ids?.length" class="space-y-3">
                            <div v-for="(device, index) in license.device_ids" :key="index"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="font-mono text-sm">{{ device }}</span>
                                </div>
                                <span class="text-xs text-gray-500">Active</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            No devices registered yet
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div v-if="activeTab === 'payments'" class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="payment in license.payments" :key="payment.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(payment.paid_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-500">
                                        {{ payment.transaction_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatCurrency(payment.amount, payment.currency) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ payment.payment_method }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                            {{ payment.status }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Renew Modal -->
        <div v-if="showRenewModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRenewModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Renew License</h3>

                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Select payment method:</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="renewForm.payment_method = 'stripe'"
                                        :class="['border-2 rounded-lg p-3 flex items-center justify-center space-x-2',
                                                 renewForm.payment_method === 'stripe' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200']">
                                    <span>💳</span>
                                    <span>Credit Card</span>
                                </button>
                                <button @click="renewForm.payment_method = 'paypal'"
                                        :class="['border-2 rounded-lg p-3 flex items-center justify-center space-x-2',
                                                 renewForm.payment_method === 'paypal' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200']">
                                    <span>🅿️</span>
                                    <span>PayPal</span>
                                </button>
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">Renewal Amount:</p>
                            <p class="text-2xl font-bold text-indigo-600">
                                {{ formatCurrency(license.plan?.price, license.plan?.currency) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">for another {{ license.plan?.billing_cycle }}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="processRenew"
                                :disabled="renewForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Confirm Renewal
                        </button>
                        <button @click="showRenewModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revoke Modal -->
        <div v-if="showRevokeModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showRevokeModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">
                                    Revoke License
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to revoke this license? This action cannot be undone.
                                        The user will lose access to the tool immediately.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="processRevoke"
                                :disabled="revokeForm.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Revoke License
                        </button>
                        <button @click="showRevokeModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </component>
</template>