<!-- resources/js/Pages/Home.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tools: {
        type: Array,
        default: () => []
    },
    plans: {
        type: Object,
        default: () => ({})
    },
    stats: {
        type: Object,
        default: () => ({
            total_tools: 0,
            active_users: 0,
            api_calls: 0,
            uptime: '99.9%'
        })
    }
});

const billingCycle = ref('monthly');

// Format currency
const formatCurrency = (price, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price);
};

// Get price display with cycle
const getPriceDisplay = (plan) => {
    if (plan.billing_cycle === 'lifetime') {
        return formatCurrency(plan.price, plan.currency);
    } else if (plan.billing_cycle === 'one_time') {
        return formatCurrency(plan.price, plan.currency);
    } else {
        return `${formatCurrency(plan.price, plan.currency)}<span class="text-lg text-gray-600">/${plan.billing_cycle}</span>`;
    }
};

// Filter plans by billing cycle
const filteredPlans = computed(() => {
    return props.plans[billingCycle.value]?.plans || [];
});

// Get all unique billing cycles
const billingCycles = computed(() => {
    return Object.keys(props.plans);
});

// Format large numbers
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    } else if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
};
</script>

<template>
    <GuestLayout>
        <Head title="Welcome - AI Tool Platform" />

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6">
                        AI-Powered Tools for<br>Modern Business
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-indigo-100 max-w-3xl mx-auto">
                        Harness the power of artificial intelligence with our {{ stats.total_tools }}+ professional tools to automate, analyze, and accelerate your workflow
                    </p>

                    <!-- Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto mb-10">
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <div class="text-2xl font-bold">{{ formatNumber(stats.active_users) }}+</div>
                            <div class="text-sm text-indigo-200">Active Users</div>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <div class="text-2xl font-bold">{{ formatNumber(stats.api_calls) }}+</div>
                            <div class="text-sm text-indigo-200">API Calls</div>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <div class="text-2xl font-bold">{{ stats.total_tools }}</div>
                            <div class="text-sm text-indigo-200">AI Tools</div>
                        </div>
                        <div class="bg-white/10 rounded-lg p-4 backdrop-blur-sm">
                            <div class="text-2xl font-bold">{{ stats.uptime }}</div>
                            <div class="text-sm text-indigo-200">Uptime</div>
                        </div>
                    </div>

                    <div class="space-x-4">
                        <Link href="/register" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block">
                            Get Started Free
                        </Link>
                        <Link href="/features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition inline-block">
                            Learn More
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tools Section -->
        <div class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Powerful AI Tools at Your Fingertips</h2>
                    <p class="text-xl text-gray-600">Choose from our growing collection of AI-powered tools</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Dynamic Tools -->
                    <div v-for="tool in tools" :key="tool.id"
                         class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition group">
                        <div class="text-indigo-600 text-4xl mb-4 transform group-hover:scale-110 transition">
                            {{ tool.icon }}
                        </div>
                        <h3 class="text-xl font-semibold mb-2">{{ tool.name }}</h3>
                        <p class="text-gray-600 mb-4">{{ tool.description }}</p>

                        <!-- Features Preview -->
                        <div class="space-y-2 mb-4">
                            <div v-for="(feature, index) in tool.features.slice(0, 3)" :key="index"
                                 class="flex items-center text-sm text-gray-600">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ feature }}
                            </div>
                            <div v-if="tool.features.length > 3" class="text-sm text-indigo-600">
                                +{{ tool.features.length - 3 }} more features
                            </div>
                        </div>

                        <!-- Starting Price -->
                        <div v-if="tool.plans.length > 0" class="mb-4">
                            <span class="text-sm text-gray-500">Starting from</span>
                            <div class="text-2xl font-bold text-indigo-600">
                                {{ formatCurrency(Math.min(...tool.plans.map(p => p.price))) }}
                                <span class="text-sm font-normal text-gray-500">/mo</span>
                            </div>
                        </div>

                        <Link :href="route('tools.show', tool.id)"
                              class="inline-flex items-center text-indigo-600 hover:text-indigo-800 font-medium">
                            Learn more
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </Link>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <Link href="/Alltools"
                          class="inline-flex items-center bg-indigo-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        View All Tools
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h2>
                    <p class="text-xl text-gray-600 mb-8">Choose the plan that's right for you</p>

                    <!-- Billing Cycle Toggle -->
                    <div v-if="billingCycles.length > 1" class="flex justify-center space-x-2 bg-gray-100 p-1 rounded-lg inline-flex">
                        <button v-for="cycle in billingCycles" :key="cycle"
                                @click="billingCycle = cycle"
                                class="px-6 py-2 rounded-md capitalize transition"
                                :class="billingCycle === cycle ? 'bg-white shadow text-indigo-600' : 'text-gray-600 hover:text-gray-900'">
                            {{ cycle }}
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Dynamic Plans -->
                    <div v-for="plan in filteredPlans" :key="plan.id"
                         class="border rounded-lg p-8 relative"
                         :class="{ 'border-2 border-indigo-600 shadow-lg': plan.is_popular }">

                        <!-- Popular Badge -->
                        <span v-if="plan.is_popular"
                              class="absolute top-0 right-0 bg-indigo-600 text-white px-3 py-1 text-sm rounded-bl-lg rounded-tr-lg">
                            Popular
                        </span>

                        <!-- Tool Info -->
                        <div class="flex items-center mb-4">
                            <span class="text-2xl mr-2">{{ plan.tool_icon }}</span>
                            <span class="text-sm font-medium text-gray-500">{{ plan.tool_name }}</span>
                        </div>

                        <h3 class="text-2xl font-bold mb-2">{{ plan.name }}</h3>
                        <p class="text-gray-500 text-sm mb-4">{{ plan.description }}</p>

                        <!-- Price -->
                        <div class="mb-6">
                            <div v-html="getPriceDisplay(plan)" class="text-4xl font-bold"></div>
                        </div>

                        <!-- Features -->
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center text-sm">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() + ' API calls/month' : 'Unlimited API calls' }}
                            </li>
                            <li class="flex items-center text-sm">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ plan.device_limit === 999 ? 'Unlimited devices' : plan.device_limit + ' device' + (plan.device_limit > 1 ? 's' : '') }}
                            </li>
                            <li class="flex items-center text-sm">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ plan.concurrent_users }} concurrent user{{ plan.concurrent_users > 1 ? 's' : '' }}
                            </li>
                            <li v-for="feature in plan.features" :key="feature"
                                class="flex items-center text-sm">
                                <span class="text-green-500 mr-2">✓</span>
                                {{ feature }}
                            </li>
                        </ul>

                        <!-- CTA Button -->
                        <Link v-if="plan.billing_cycle !== 'lifetime' || plan.billing_cycle !== 'one_time'"
                              :href="route('subscriptions.show', plan.tool_id)"
                              class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition"
                              :class="{ 'bg-indigo-600': plan.is_popular }">
                            Get Started
                        </Link>
                        <Link v-else
                              :href="route('contact')"
                              class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                            Contact Sales
                        </Link>
                    </div>
                </div>

                <!-- No Plans Message -->
                <div v-if="filteredPlans.length === 0" class="text-center py-12">
                    <p class="text-gray-500">No plans available for this billing cycle.</p>
                </div>

                <!-- Custom Enterprise -->
                <div class="mt-16 text-center bg-gray-50 rounded-2xl p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Need a custom solution?</h3>
                    <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                        Contact our sales team for custom pricing, dedicated support, and tailored solutions for your business.
                    </p>
                    <Link href="/contact" class="inline-block bg-gray-900 text-white px-8 py-3 rounded-lg font-semibold hover:bg-gray-800 transition">
                        Contact Sales
                    </Link>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-indigo-600 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to get started?</h2>
                <p class="text-xl text-indigo-100 mb-8">Join thousands of satisfied customers already using our AI tools</p>
                <Link href="/register" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition inline-block">
                    Create Your Free Account
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
