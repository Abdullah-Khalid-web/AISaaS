<!-- resources/js/Pages/Plans/Show.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    plan: {
        type: Object,
        required: true
    }
});

// State
const showSubscribeModal = ref(false);
const paymentMethod = ref('stripe');
const agreeToTerms = ref(false);

// Form
const form = useForm({
    plan_id: props.plan.id,
    package_name: props.plan.name,
    payment_method: 'stripe'
});

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

// Format number
const formatNumber = (num) => {
    if (!num) return 'Unlimited';
    return num.toLocaleString();
};

// Get price display
const getPriceDisplay = () => {
    const plan = props.plan;
    if (plan.billing_cycle === 'lifetime') {
        return formatCurrency(plan.price, plan.currency);
    } else if (plan.billing_cycle === 'one_time') {
        return formatCurrency(plan.price, plan.currency);
    } else {
        return `${formatCurrency(plan.price, plan.currency)}<span class="text-lg text-gray-600">/${plan.billing_cycle}</span>`;
    }
};

// Get billing cycle badge
const getBillingCycleBadge = (cycle) => {
    switch(cycle) {
        case 'monthly': return 'bg-blue-100 text-blue-800';
        case 'quarterly': return 'bg-purple-100 text-purple-800';
        case 'yearly': return 'bg-green-100 text-green-800';
        case 'lifetime': return 'bg-yellow-100 text-yellow-800';
        case 'one_time': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

// Submit subscription
const subscribe = () => {
    form.post(route('subscriptions.store', props.plan.tool_id), {
        preserveScroll: true,
        onSuccess: () => {
            showSubscribeModal.value = false;
        }
    });
};

// Features list
const features = computed(() => {
    return [
        {
            name: 'API Calls',
            value: props.plan.api_call_limit ? `${formatNumber(props.plan.api_call_limit)} per month` : 'Unlimited',
            icon: '📡'
        },
        {
            name: 'Devices',
            value: props.plan.device_limit === 999 ? 'Unlimited devices' : `${props.plan.device_limit} device${props.plan.device_limit > 1 ? 's' : ''}`,
            icon: '💻'
        },
        {
            name: 'Concurrent Users',
            value: props.plan.concurrent_users ? `${props.plan.concurrent_users} user${props.plan.concurrent_users > 1 ? 's' : ''}` : '1 user',
            icon: '👥'
        },
        {
            name: 'Support',
            value: props.plan.metadata?.support_level || 'Email Support',
            icon: '🎯'
        },
        {
            name: 'Data Retention',
            value: props.plan.metadata?.data_retention || '30 days',
            icon: '💾'
        },
        {
            name: 'Custom Features',
            value: props.plan.metadata?.custom_features ? 'Available' : 'Standard',
            icon: '⚙️'
        }
    ];
});
</script>

<template>
    <GuestLayout>
        <Head :title="plan.name + ' Plan - ' + plan.tool?.name" />

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="flex justify-between items-center">
                    <div>
                        <Link :href="route('Alltools.show', plan.tool_id)" class="text-indigo-200 hover:text-white mb-4 inline-block">
                            ← Back to {{ plan.tool?.name }}
                        </Link>
                        <div class="flex items-center mt-2">
                            <span class="text-4xl mr-3">{{ plan.tool?.metadata?.icon || '🤖' }}</span>
                            <h1 class="text-4xl font-bold">{{ plan.name }} Plan</h1>
                        </div>
                        <p class="text-xl text-indigo-100 mt-4 max-w-2xl">{{ plan.description }}</p>
                    </div>
                    <span :class="['px-3 py-1 rounded-full text-sm font-medium', getBillingCycleBadge(plan.billing_cycle)]">
                        {{ plan.billing_cycle }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Plan Details -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Price Card -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">Plan Pricing</h2>
                                <p class="text-gray-600">Choose this plan for access to all features</p>
                            </div>
                            <div class="text-right">
                                <div v-html="getPriceDisplay()" class="text-5xl font-bold text-indigo-600"></div>
                                <p v-if="plan.billing_cycle === 'monthly' || plan.billing_cycle === 'yearly'" class="text-sm text-gray-500 mt-1">
                                    Billed {{ plan.billing_cycle }}
                                </p>
                            </div>
                        </div>

                        <!-- CTA Buttons -->
                        <div class="mt-8 flex space-x-4">
                            <button @click="showSubscribeModal = true"
                                    class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                                Subscribe Now
                            </button>
                            <Link :href="route('contact')"
                                  class="flex-1 border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition text-center">
                                Contact Sales
                            </Link>
                        </div>
                    </div>

                    <!-- Features Grid -->
                    <div class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Plan Features</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div v-for="feature in features" :key="feature.name"
                                 class="flex items-start p-4 bg-gray-50 rounded-lg">
                                <span class="text-2xl mr-3">{{ feature.icon }}</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ feature.name }}</h3>
                                    <p class="text-sm text-gray-600">{{ feature.value }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Features -->
                        <div v-if="plan.features && plan.features.length" class="mt-8 border-t border-gray-200 pt-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Included Benefits</h3>
                            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <li v-for="feature in plan.features" :key="feature"
                                    class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ feature }}
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Limitations (if any) -->
                    <div v-if="plan.limitations && plan.limitations.length" class="bg-white rounded-lg shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Plan Limitations</h2>
                        <ul class="space-y-3">
                            <li v-for="limitation in plan.limitations" :key="limitation"
                                class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ limitation }}
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Right Column - Tool Info & Comparison -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Tool Info Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">About {{ plan.tool?.name }}</h3>

                        <div class="flex items-center mb-4">
                            <span class="text-3xl mr-3">{{ plan.tool?.metadata?.icon || '🤖' }}</span>
                            <div>
                                <p class="font-medium text-gray-900">{{ plan.tool?.name }}</p>
                                <p class="text-sm text-gray-500">v{{ plan.tool?.version || '1.0' }}</p>
                            </div>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">{{ plan.tool?.description }}</p>

                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Quick Stats</h4>
                            <dl class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-500">Total Plans</dt>
                                    <dd class="font-medium">{{ plan.tool?.plans_count || 'Multiple' }}</dd>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-500">Support</dt>
                                    <dd class="font-medium">{{ plan.tool?.metadata?.support_type || '24/7' }}</dd>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <dt class="text-gray-500">Rating</dt>
                                    <dd class="font-medium">{{ plan.tool?.metadata?.rating || '4.8/5' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Other Plans Link -->
                        <div class="mt-6 pt-4 border-t border-gray-200">
                            <Link :href="route('Alltools.show', plan.tool_id)"
                                  class="text-indigo-600 hover:text-indigo-800 text-sm font-medium flex items-center">
                                View all plans for {{ plan.tool?.name }}
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </Link>
                        </div>
                    </div>

                    <!-- Trust Badges -->
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg shadow-lg p-6">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Why Choose This Plan?</h4>
                        <ul class="space-y-3">
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                No hidden fees
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Cancel anytime
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                30-day money-back guarantee
                            </li>
                            <li class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Secure payment processing
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscribe Modal -->
        <div v-if="showSubscribeModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showSubscribeModal = false"></div>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    Subscribe to {{ plan.name }}
                                </h3>

                                <!-- Plan Summary -->
                                <div class="bg-indigo-50 rounded-lg p-4 mb-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <p class="text-sm text-indigo-600 font-medium">{{ plan.tool?.name }}</p>
                                            <p class="text-lg font-bold text-gray-900">{{ plan.name }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-2xl font-bold text-indigo-600">
                                                {{ formatCurrency(plan.price, plan.currency) }}
                                            </span>
                                            <span class="text-sm text-gray-500">/{{ plan.billing_cycle }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Method -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <button @click="paymentMethod = 'stripe'; form.payment_method = 'stripe'"
                                                :class="['border-2 rounded-lg p-3 flex items-center justify-center space-x-2',
                                                         paymentMethod === 'stripe' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200']">
                                            <span>💳</span>
                                            <span>Credit Card</span>
                                        </button>
                                        <button @click="paymentMethod = 'paypal'; form.payment_method = 'paypal'"
                                                :class="['border-2 rounded-lg p-3 flex items-center justify-center space-x-2',
                                                         paymentMethod === 'paypal' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200']">
                                            <span>🅿️</span>
                                            <span>PayPal</span>
                                        </button>
                                    </div>
                                </div>

                                <!-- Demo Card Details -->
                                <div v-if="paymentMethod === 'stripe'" class="mb-4 space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Card Number</label>
                                        <input type="text" value="4242 4242 4242 4242" disabled
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Expiry</label>
                                            <input type="text" value="12/25" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">CVC</label>
                                            <input type="text" value="123" disabled class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-500">
                                        </div>
                                    </div>
                                </div>

                                <!-- Package Name -->
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">License Name (Optional)</label>
                                    <input type="text" v-model="form.package_name"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <!-- Terms -->
                                <div class="flex items-center">
                                    <input type="checkbox" v-model="agreeToTerms" id="modal-terms"
                                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                    <label for="modal-terms" class="ml-2 text-sm text-gray-600">
                                        I agree to the Terms of Service
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button @click="subscribe" :disabled="!agreeToTerms || form.processing"
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Confirm Subscription
                        </button>
                        <button @click="showSubscribeModal = false"
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
