<!-- resources/js/Pages/Subscriptions/Create.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    }
});

const form = useForm({
    plan_id: '',
    package_name: '',
    payment_method: 'stripe'
});

const selectedPlan = computed(() => {
    return props.tool.plans?.find(p => p.id === form.plan_id);
});

const formatCurrency = (price, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
};

const selectPlan = (planId) => {
    form.plan_id = planId;
    form.package_name = props.tool.plans?.find(p => p.id === planId)?.name || '';
};

const submit = () => {
    form.post(route('subscriptions.store', props.tool.id), {
        onSuccess: () => {
            // Redirect handled by controller
        }
    });
};

const cancel = () => {
    router.get(route('tools.show', props.tool.id));
};
</script>

<template>
    <Head :title="`Subscribe to ${tool.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('tools.show', tool.id)"
                    class="text-gray-600 hover:text-gray-900"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Subscribe to {{ tool.name }}
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- Error Display -->
                        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                            </ul>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- Plan Selection -->
                            <div class="mb-8">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Select a Plan</h3>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div
                                        v-for="plan in tool.plans"
                                        :key="plan.id"
                                        class="border rounded-lg p-6 cursor-pointer transition hover:shadow-lg relative"
                                        :class="{
                                            'border-indigo-600 ring-2 ring-indigo-600': form.plan_id === plan.id,
                                            'border-gray-200': form.plan_id !== plan.id
                                        }"
                                        @click="selectPlan(plan.id)"
                                    >
                                        <div v-if="plan.is_popular" class="absolute top-0 right-0 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">
                                            POPULAR
                                        </div>

                                        <h4 class="text-xl font-bold text-gray-900 mb-2">{{ plan.name }}</h4>
                                        <p class="text-gray-500 text-sm mb-4">{{ plan.description || 'No description' }}</p>

                                        <div class="mb-4">
                                            <span class="text-3xl font-bold text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</span>
                                            <span class="text-gray-500 text-sm">/{{ plan.billing_cycle }}</span>
                                        </div>

                                        <ul class="space-y-2 mb-6">
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.device_limit === 999 ? 'Unlimited' : plan.device_limit }} Device{{ plan.device_limit !== 1 ? 's' : '' }}
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() : 'Unlimited' }} API calls
                                            </li>
                                            <li class="flex items-center text-sm text-gray-600">
                                                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                {{ plan.concurrent_users }} concurrent user{{ plan.concurrent_users !== 1 ? 's' : '' }}
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div v-if="form.errors.plan_id" class="text-red-500 text-sm mt-2">{{ form.errors.plan_id }}</div>
                            </div>

                            <!-- Package Name -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Package Name (Optional)</label>
                                <input
                                    v-model="form.package_name"
                                    type="text"
                                    class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="e.g., Enterprise License"
                                />
                                <p class="text-xs text-gray-500 mt-1">Custom name for your license</p>
                            </div>

                            <!-- Payment Method -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label
                                        class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                                        :class="{ 'border-indigo-600 bg-indigo-50': form.payment_method === 'stripe' }"
                                    >
                                        <input type="radio" v-model="form.payment_method" value="stripe" class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-6 h-6 text-indigo-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 7.179 0 4.048 2.619 4.048 6.203c0 4.085 3.774 5.506 6.605 6.337 2.759.807 3.676 1.513 3.676 2.649 0 .93-.79 1.485-2.446 1.485-2.46 0-5.145-1.115-6.979-2.126l-.973 5.495c1.896.99 5.092 1.957 8.592 1.957 5.035 0 8.535-2.697 8.535-6.668-.002-4.385-3.785-5.943-7.062-7.027z"/>
                                            </svg>
                                            <div>
                                                <div class="font-medium text-gray-900">Stripe</div>
                                                <div class="text-xs text-gray-500">Credit / Debit Card</div>
                                            </div>
                                        </div>
                                    </label>

                                    <label
                                        class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50"
                                        :class="{ 'border-indigo-600 bg-indigo-50': form.payment_method === 'paypal' }"
                                    >
                                        <input type="radio" v-model="form.payment_method" value="paypal" class="sr-only">
                                        <div class="flex items-center gap-3">
                                            <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.72c.045-.25.258-.44.51-.44h7.24c3.75 0 6.38 1.738 7.18 4.953.15.604.234 1.228.234 1.867 0 4.97-3.74 9.237-9.533 9.237h-2.39l-.722 4.52c-.036.22-.235.376-.45.376zM9.62 9.242l-1.002 6.293h1.968c2.922 0 5.018-1.57 5.624-4.28.25-1.118.055-2.042-.614-2.754-.633-.672-1.72-1.02-3.204-1.02h-2.772z"/>
                                            </svg>
                                            <div>
                                                <div class="font-medium text-gray-900">PayPal</div>
                                                <div class="text-xs text-gray-500">Pay with PayPal</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Order Summary -->
                            <div v-if="selectedPlan" class="mb-8 bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Plan:</span>
                                        <span class="font-medium text-gray-900">{{ selectedPlan.name }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Billing Cycle:</span>
                                        <span class="font-medium text-gray-900">{{ selectedPlan.billing_cycle }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600">Price:</span>
                                        <span class="font-medium text-gray-900">{{ formatCurrency(selectedPlan.price, selectedPlan.currency) }}</span>
                                    </div>
                                    <div class="border-t border-gray-200 pt-3 mt-3">
                                        <div class="flex justify-between">
                                            <span class="text-base font-medium text-gray-900">Total:</span>
                                            <span class="text-xl font-bold text-indigo-600">{{ formatCurrency(selectedPlan.price, selectedPlan.currency) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-4">
                                <button
                                    type="button"
                                    @click="cancel"
                                    class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing || !form.plan_id"
                                    class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ form.processing ? 'Processing...' : 'Subscribe Now' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
