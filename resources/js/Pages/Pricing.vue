<!-- resources/js/Pages/Pricing.vue -->
<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tools: Array
});
</script>

<template>
    <GuestLayout>
        <Head title="Pricing - AI Tool Platform" />

        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h1 class="text-4xl font-bold mb-4">Simple, Transparent Pricing</h1>
                <p class="text-xl text-indigo-100">Choose the perfect plan for your needs</p>
            </div>
        </div>

        <!-- Pricing by Tool -->
        <div class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div v-for="tool in tools" :key="tool.id" class="mb-16 last:mb-0">
                    <div class="text-center mb-8">
                        <div class="text-4xl mb-2">{{ tool.metadata?.icon || '🤖' }}</div>
                        <h2 class="text-3xl font-bold text-gray-900">{{ tool.name }}</h2>
                        <p class="text-gray-600">{{ tool.description }}</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div v-for="plan in tool.plans" :key="plan.id"
                             :class="[
                                 'border rounded-lg p-8 relative',
                                 plan.is_popular ? 'border-2 border-indigo-600 shadow-lg' : 'hover:shadow-lg transition'
                             ]">
                            <div v-if="plan.is_popular"
                                 class="absolute top-0 right-0 bg-indigo-600 text-white px-3 py-1 text-sm rounded-bl-lg rounded-tr-lg">
                                Most Popular
                            </div>

                            <h3 class="text-2xl font-bold mb-2">{{ plan.name }}</h3>
                            <p class="text-gray-600 mb-4">{{ plan.description }}</p>

                            <div class="mb-6">
                                <span class="text-4xl font-bold">${{ plan.price }}</span>
                                <span class="text-gray-600">/{{ plan.billing_cycle }}</span>
                            </div>

                            <ul class="space-y-3 mb-8">
                                <li class="flex items-center">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() + ' API calls' : 'Unlimited API calls' }}
                                </li>
                                <li class="flex items-center">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ plan.device_limit }} device{{ plan.device_limit > 1 ? 's' : '' }}
                                </li>
                                <li v-for="feature in plan.feature_flags" :key="feature"
                                    class="flex items-center">
                                    <span class="text-green-500 mr-2">✓</span>
                                    {{ feature }}
                                </li>
                            </ul>

                            <Link :href="route('register')"
                                  class="block text-center bg-indigo-600 text-white px-4 py-3 rounded-md hover:bg-indigo-700 transition">
                                Get Started
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="bg-gray-50 py-16">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-center mb-12">Frequently Asked Questions</h2>

                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Can I switch plans later?</h3>
                        <p class="text-gray-600">Yes, you can upgrade or downgrade your plan at any time. Changes will be reflected in your next billing cycle.</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-2">What payment methods do you accept?</h3>
                        <p class="text-gray-600">We accept all major credit cards, PayPal, and bank transfers for enterprise plans.</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-2">Is there a free trial?</h3>
                        <p class="text-gray-600">Yes, we offer a 14-day free trial on all plans. No credit card required.</p>
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold mb-2">What happens if I exceed my API call limit?</h3>
                        <p class="text-gray-600">We'll notify you when you're approaching your limit. You can upgrade your plan or purchase additional API calls.</p>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
