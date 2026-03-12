<!-- resources/js/Pages/Subscriptions/Subscribe.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    }
});

// State
const selectedPlan = ref(null);
const step = ref(1);
const paymentMethod = ref('stripe');
const agreeToTerms = ref(false);

// Form
const form = useForm({
    plan_id: null,
    package_name: '',
    payment_method: 'stripe'
});

// Select plan
const selectPlan = (plan) => {
    selectedPlan.value = plan;
    form.plan_id = plan.id;
    form.package_name = plan.name;
    step.value = 2;
};

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

// Submit form
const submit = () => {
    form.post(route('subscriptions.store', props.tool.id), {
        preserveScroll: true,
        onSuccess: (response) => {
            // Redirect to the license view page
            if (response.props.flash?.license_id) {
                window.location.href = route('licenses.show', response.props.flash.license_id);
            }
        }
    });
};

// Get features list
const features = computed(() => {
    return props.tool.metadata?.features || [];
});

// Payment icons
const paymentIcons = {
    stripe: '💳',
    paypal: '🅿️'
};
</script>

<template>
    <GuestLayout>
        <Head :title="'Subscribe to ' + tool.name" />

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <div class="text-center">
                    <Link :href="route('Alltools.show', tool.id)" class="text-indigo-200 hover:text-white mb-4 inline-block">
                        ← Back to {{ tool.name }}
                    </Link>
                    <h1 class="text-4xl font-bold mb-4">Subscribe to {{ tool.name }}</h1>
                    <p class="text-xl text-indigo-100">Choose your plan and start using AI-powered tools today</p>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8">
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold',
                                     step >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">
                            1
                        </div>
                        <span class="ml-2 font-medium" :class="step >= 1 ? 'text-indigo-600' : 'text-gray-500'">Select Plan</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="flex items-center">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold',
                                     step >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">
                            2
                        </div>
                        <span class="ml-2 font-medium" :class="step >= 2 ? 'text-indigo-600' : 'text-gray-500'">Payment</span>
                    </div>
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                    <div class="flex items-center">
                        <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold',
                                     step >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-600']">
                            3
                        </div>
                        <span class="ml-2 font-medium" :class="step >= 3 ? 'text-indigo-600' : 'text-gray-500'">Confirm</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column - Plan Selection -->
                <div class="lg:col-span-2">
                    <!-- Step 1: Select Plan -->
                    <div v-if="step === 1" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Choose Your Plan</h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div v-for="plan in tool.plans" :key="plan.id"
                                     @click="selectPlan(plan)"
                                     :class="['relative border-2 rounded-lg p-6 cursor-pointer transition hover:shadow-lg',
                                              selectedPlan?.id === plan.id ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300']">

                                    <!-- Popular Badge -->
                                    <div v-if="plan.is_popular"
                                         class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-indigo-600 text-white px-3 py-1 rounded-full text-xs font-medium">
                                        Most Popular
                                    </div>

                                    <!-- Plan Icon -->
                                    <div class="text-4xl mb-4 text-center">{{ plan.id === 1 ? '🚀' : plan.id === 2 ? '⭐' : '👑' }}</div>

                                    <!-- Plan Name -->
                                    <h3 class="text-lg font-bold text-gray-900 text-center mb-2">{{ plan.name }}</h3>

                                    <!-- Price -->
                                    <div class="text-center mb-4">
                                        <span class="text-3xl font-bold text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                        <span class="text-sm text-gray-500">/{{ plan.billing_cycle }}</span>
                                    </div>

                                    <!-- Features -->
                                    <ul class="space-y-2 mb-4">
                                        <li v-for="feature in plan.features?.slice(0, 3)" :key="feature"
                                            class="flex items-center text-sm text-gray-600">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            {{ feature }}
                                        </li>
                                    </ul>

                                    <!-- API Calls -->
                                    <div class="text-sm text-center text-gray-500">
                                        {{ plan.api_call_limit?.toLocaleString() || 'Unlimited' }} API calls
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tool Features -->
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">What's included with {{ tool.name }}</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div v-for="feature in features" :key="feature" class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ feature }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Payment -->
                    <div v-if="step === 2" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment Method</h2>

                            <!-- Payment Methods -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <button @click="paymentMethod = 'stripe'; form.payment_method = 'stripe'"
                                        :class="['border-2 rounded-lg p-4 flex items-center justify-center space-x-2 transition',
                                                 paymentMethod === 'stripe' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300']">
                                    <span class="text-2xl">💳</span>
                                    <span class="font-medium">Credit Card</span>
                                </button>
                                <button @click="paymentMethod = 'paypal'; form.payment_method = 'paypal'"
                                        :class="['border-2 rounded-lg p-4 flex items-center justify-center space-x-2 transition',
                                                 paymentMethod === 'paypal' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-indigo-300']">
                                    <span class="text-2xl">🅿️</span>
                                    <span class="font-medium">PayPal</span>
                                </button>
                            </div>

                            <!-- Card Details (Demo) -->
                            <div v-if="paymentMethod === 'stripe'" class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Card Number</label>
                                    <div class="relative">
                                        <input type="text"
                                               value="4242 4242 4242 4242"
                                               disabled
                                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                        <span class="absolute right-3 top-2 text-sm text-gray-400">Demo</span>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Expiry</label>
                                        <input type="text" value="12/25" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">CVC</label>
                                        <input type="text" value="123" disabled class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                                    </div>
                                </div>
                            </div>

                            <!-- PayPal Demo -->
                            <div v-if="paymentMethod === 'paypal'" class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                                <span class="text-4xl block mb-3">🅿️</span>
                                <p class="text-blue-800">You'll be redirected to PayPal to complete your payment.</p>
                                <p class="text-sm text-blue-600 mt-2">Demo: Click Continue to simulate PayPal payment</p>
                            </div>

                            <!-- Package Name -->
                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Package/License Name (Optional)</label>
                                <input type="text"
                                       v-model="form.package_name"
                                       placeholder="e.g., My Business License"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Confirmation -->
                    <div v-if="step === 3" class="space-y-6">
                        <div class="bg-white rounded-lg shadow-lg p-6">
                            <div class="text-center mb-6">
                                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">Almost Done!</h2>
                                <p class="text-gray-600">Please review your subscription details before confirming</p>
                            </div>

                            <!-- Order Summary -->
                            <div class="bg-gray-50 rounded-lg p-6 mb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

                                <div class="flex items-center mb-4">
                                    <span class="text-3xl mr-3">{{ tool.metadata?.icon || '🤖' }}</span>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ tool.name }}</div>
                                        <div class="text-sm text-gray-500">{{ selectedPlan?.name }} Plan</div>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-4 space-y-2">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span class="font-medium">{{ formatCurrency(selectedPlan?.price, selectedPlan?.currency) }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Tax</span>
                                        <span class="font-medium">$0.00</span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                                        <span>Total</span>
                                        <span class="text-indigo-600">{{ formatCurrency(selectedPlan?.price, selectedPlan?.currency) }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 text-xs text-gray-500">
                                    <p>Billing Cycle: {{ selectedPlan?.billing_cycle }}</p>
                                    <p>Payment Method: {{ paymentMethod === 'stripe' ? 'Credit Card' : 'PayPal' }}</p>
                                    <p v-if="form.package_name">License Name: {{ form.package_name }}</p>
                                </div>
                            </div>

                            <!-- Terms Agreement -->
                            <div class="flex items-center">
                                <input type="checkbox"
                                       v-model="agreeToTerms"
                                       id="terms"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                                <label for="terms" class="ml-2 text-sm text-gray-600">
                                    I agree to the
                                    <a href="#" class="text-indigo-600 hover:text-indigo-800">Terms of Service</a> and
                                    <a href="#" class="text-indigo-600 hover:text-indigo-800">Privacy Policy</a>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Order Summary</h3>

                        <!-- Selected Plan -->
                        <div v-if="selectedPlan" class="mb-6">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="font-medium text-gray-900">{{ selectedPlan.name }}</p>
                                    <p class="text-sm text-gray-500">{{ tool.name }}</p>
                                </div>
                                <span class="text-xl font-bold text-indigo-600">{{ formatCurrency(selectedPlan.price, selectedPlan.currency) }}</span>
                            </div>
                            <p class="text-xs text-gray-500">per {{ selectedPlan.billing_cycle }}</p>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p>Select a plan to continue</p>
                        </div>

                        <!-- Features List -->
                        <div v-if="selectedPlan" class="border-t border-gray-200 pt-4 mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">What's included:</h4>
                            <ul class="space-y-2">
                                <li v-for="feature in selectedPlan.features?.slice(0, 3)" :key="feature"
                                    class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ feature }}
                                </li>
                                <li class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    {{ selectedPlan.api_call_limit?.toLocaleString() || 'Unlimited' }} API calls
                                </li>
                            </ul>
                        </div>

                        <!-- Action Buttons -->
                        <div class="space-y-3">
                            <button v-if="step === 1"
                                    @click="step = 2"
                                    :disabled="!selectedPlan"
                                    class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                                Continue to Payment
                            </button>

                            <button v-if="step === 2"
                                    @click="step = 3"
                                    class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Review Order
                            </button>

                            <button v-if="step === 3"
                                    @click="submit"
                                    :disabled="!agreeToTerms || form.processing"
                                    class="w-full py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50">
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Confirm & Subscribe
                            </button>

                            <button v-if="step > 1"
                                    @click="step--"
                                    class="w-full py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                Back
                            </button>
                        </div>

                        <!-- Security Badge -->
                        <div class="mt-6 text-center">
                            <div class="flex justify-center space-x-2 mb-2">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2C8.13 2 5 5.13 5 9v3c0 .55.45 1 1 1s1-.45 1-1V9c0-2.76 2.24-5 5-5s5 2.24 5 5v3c0 .55.45 1 1 1s1-.45 1-1V9c0-3.87-3.13-7-7-7z"/>
                                    <path d="M12 22c-1.1 0-2-.9-2-2v-2c0-1.1.9-2 2-2s2 .9 2 2v2c0 1.1-.9 2-2 2z"/>
                                </svg>
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z"/>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500">Secure payment powered by Stripe</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
