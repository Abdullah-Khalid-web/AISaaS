<!-- resources/js/Pages/Plans/Create.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tools: {
        type: Array,
        default: () => []
    }
});

// Form state with all plan fields
const form = useForm({
    tool_id: '',
    name: '',
    description: '',
    price: '',
    currency: 'USD',
    billing_cycle: 'monthly',
    duration_days: '',
    device_limit: 1,
    api_call_limit: '',
    concurrent_users: 1,
    feature_flags: [],
    limitations: [],
    is_popular: false,
    is_active: true,
    sort_order: 0
});

// UI State
const showAdvanced = ref(false);
const newFeatureFlag = ref('');
const newLimitation = ref('');
const showSuccessMessage = ref(false);

// Currency options
const currencies = [
    { value: 'USD', label: 'USD ($)', symbol: '$' },
    { value: 'EUR', label: 'EUR (€)', symbol: '€' },
    { value: 'GBP', label: 'GBP (£)', symbol: '£' },
    { value: 'JPY', label: 'JPY (¥)', symbol: '¥' },
    { value: 'CAD', label: 'CAD (C$)', symbol: 'C$' },
    { value: 'AUD', label: 'AUD (A$)', symbol: 'A$' },
];

// Billing cycle options
const billingCycles = [
    { value: 'monthly', label: 'Monthly', description: 'Billed every month' },
    { value: 'quarterly', label: 'Quarterly', description: 'Billed every 3 months' },
    { value: 'yearly', label: 'Yearly', description: 'Billed annually' },
    { value: 'lifetime', label: 'Lifetime', description: 'One-time payment for lifetime access' },
    { value: 'one_time', label: 'One Time', description: 'Single payment for permanent access' },
];

// Computed properties
const selectedTool = computed(() => {
    return props.tools.find(tool => tool.id === parseInt(form.tool_id));
});

const formattedPrice = computed({
    get: () => form.price,
    set: (value) => {
        // Remove non-numeric characters except decimal point
        const cleaned = value.toString().replace(/[^0-9.]/g, '');
        form.price = cleaned;
    }
});

// Methods
const submit = () => {
    form.post(route('plans.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showSuccessMessage.value = true;
            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 3000);
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            // Scroll to top to show errors
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });
};

const addFeatureFlag = () => {
    if (newFeatureFlag.value.trim()) {
        form.feature_flags.push(newFeatureFlag.value.trim());
        newFeatureFlag.value = '';
    }
};

const removeFeatureFlag = (index) => {
    form.feature_flags.splice(index, 1);
};

const addLimitation = () => {
    if (newLimitation.value.trim()) {
        form.limitations.push(newLimitation.value.trim());
        newLimitation.value = '';
    }
};

const removeLimitation = (index) => {
    form.limitations.splice(index, 1);
};

const setNumericValue = (field, value) => {
    const numericValue = value === '' ? '' : Number(value);
    form[field] = numericValue;
};

const generateDurationDays = () => {
    const cycle = form.billing_cycle;
    if (cycle === 'monthly') form.duration_days = 30;
    else if (cycle === 'quarterly') form.duration_days = 90;
    else if (cycle === 'yearly') form.duration_days = 365;
    else form.duration_days = '';
};

const cancel = () => {
    router.get(route('plans.index'));
};
</script>

<template>
    <Head title="Create New Plan" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create New Plan
                </h2>
                <Link
                    :href="route('plans.index')"
                    class="text-gray-600 hover:text-gray-900 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Plans
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="showSuccessMessage" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">Plan created successfully!</span>
                </div>

                <!-- Error Summary -->
                <div v-if="Object.keys(form.errors).length > 0" class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    <strong class="font-bold">Please fix the following errors:</strong>
                    <ul class="mt-2 list-disc list-inside">
                        <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                    </ul>
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Basic Information</h3>
                            <p class="text-sm text-gray-600">Set up the fundamental details of your plan.</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Tool Selection -->
                            <div>
                                <label for="tool_id" class="block text-sm font-medium text-gray-700 mb-1">
                                    Tool <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="tool_id"
                                    v-model="form.tool_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    :class="{ 'border-red-500': form.errors.tool_id }"
                                >
                                    <option value="">Select a tool</option>
                                    <option v-for="tool in tools" :key="tool.id" :value="tool.id">
                                        {{ tool.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.tool_id" class="mt-1 text-sm text-red-600">{{ form.errors.tool_id }}</p>
                            </div>

                            <!-- Plan Name and Description -->
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                        Plan Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        type="text"
                                        v-model="form.name"
                                        placeholder="e.g., Professional Plan, Enterprise Plan"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                        Description
                                    </label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Describe what this plan offers..."
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    />
                                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                                </div>
                            </div>

                            <!-- Pricing -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                        Price <span class="text-red-500">*</span>
                                    </label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">$</span>
                                        </div>
                                        <input
                                            id="price"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            v-model="form.price"
                                            class="pl-7 block w-full border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-500': form.errors.price }"
                                        />
                                    </div>
                                    <p v-if="form.errors.price" class="mt-1 text-sm text-red-600">{{ form.errors.price }}</p>
                                </div>

                                <div>
                                    <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">
                                        Currency
                                    </label>
                                    <select
                                        id="currency"
                                        v-model="form.currency"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    >
                                        <option v-for="currency in currencies" :key="currency.value" :value="currency.value">
                                            {{ currency.label }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 mb-1">
                                        Billing Cycle
                                    </label>
                                    <select
                                        id="billing_cycle"
                                        v-model="form.billing_cycle"
                                        @change="generateDurationDays"
                                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                                    >
                                        <option v-for="cycle in billingCycles" :key="cycle.value" :value="cycle.value">
                                            {{ cycle.label }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">{{ billingCycles.find(c => c.value === form.billing_cycle)?.description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Limits Card -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Usage Limits</h3>
                            <p class="text-sm text-gray-600">Define the restrictions and allowances for this plan.</p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="device_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        Device Limit
                                    </label>
                                    <input
                                        id="device_limit"
                                        type="number"
                                        min="1"
                                        v-model="form.device_limit"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.device_limit }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Number of devices allowed (use 999 for unlimited)</p>
                                    <p v-if="form.errors.device_limit" class="mt-1 text-sm text-red-600">{{ form.errors.device_limit }}</p>
                                </div>

                                <div>
                                    <label for="concurrent_users" class="block text-sm font-medium text-gray-700 mb-1">
                                        Concurrent Users
                                    </label>
                                    <input
                                        id="concurrent_users"
                                        type="number"
                                        min="1"
                                        v-model="form.concurrent_users"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.concurrent_users }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Maximum simultaneous users</p>
                                    <p v-if="form.errors.concurrent_users" class="mt-1 text-sm text-red-600">{{ form.errors.concurrent_users }}</p>
                                </div>

                                <div>
                                    <label for="api_call_limit" class="block text-sm font-medium text-gray-700 mb-1">
                                        API Call Limit
                                    </label>
                                    <input
                                        id="api_call_limit"
                                        type="number"
                                        min="0"
                                        v-model="form.api_call_limit"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.api_call_limit }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited</p>
                                    <p v-if="form.errors.api_call_limit" class="mt-1 text-sm text-red-600">{{ form.errors.api_call_limit }}</p>
                                </div>

                                <div>
                                    <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-1">
                                        Duration (Days)
                                    </label>
                                    <input
                                        id="duration_days"
                                        type="number"
                                        min="1"
                                        v-model="form.duration_days"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-500': form.errors.duration_days }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Leave empty for indefinite</p>
                                    <p v-if="form.errors.duration_days" class="mt-1 text-sm text-red-600">{{ form.errors.duration_days }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Features & Limitations Card -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900">Features & Limitations</h3>
                            <p class="text-sm text-gray-600">Add the key features and any limitations for this plan.</p>
                        </div>
                        <div class="p-6 space-y-6">
                            <!-- Feature Flags -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Feature Flags</label>
                                <div class="flex gap-2 mb-2">
                                    <input
                                        type="text"
                                        v-model="newFeatureFlag"
                                        @keyup.enter="addFeatureFlag"
                                        placeholder="Add a feature (e.g., Advanced Analytics)"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    <button
                                        type="button"
                                        @click="addFeatureFlag"
                                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(feature, index) in form.feature_flags"
                                        :key="index"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800"
                                    >
                                        {{ feature }}
                                        <button
                                            type="button"
                                            @click="removeFeatureFlag(index)"
                                            class="ml-2 inline-flex items-center p-0.5 rounded-full text-indigo-400 hover:text-indigo-600 focus:outline-none"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>

                            <!-- Limitations -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Limitations</label>
                                <div class="flex gap-2 mb-2">
                                    <input
                                        type="text"
                                        v-model="newLimitation"
                                        @keyup.enter="addLimitation"
                                        placeholder="Add a limitation (e.g., No API access)"
                                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    <button
                                        type="button"
                                        @click="addLimitation"
                                        class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Add
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <span
                                        v-for="(limitation, index) in form.limitations"
                                        :key="index"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
                                    >
                                        {{ limitation }}
                                        <button
                                            type="button"
                                            @click="removeLimitation(index)"
                                            class="ml-2 inline-flex items-center p-0.5 rounded-full text-red-400 hover:text-red-600 focus:outline-none"
                                        >
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Advanced Settings (Collapsible) -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div
                            @click="showAdvanced = !showAdvanced"
                            class="px-6 py-4 bg-gray-50 border-b border-gray-200 cursor-pointer hover:bg-gray-100"
                        >
                            <div class="flex justify-between items-center">
                                <h3 class="text-lg font-medium text-gray-900">Advanced Settings</h3>
                                <svg
                                    class="w-5 h-5 text-gray-500 transform transition-transform"
                                    :class="{ 'rotate-180': showAdvanced }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                        <div v-show="showAdvanced" class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">
                                        Sort Order
                                    </label>
                                    <input
                                        id="sort_order"
                                        type="number"
                                        v-model="form.sort_order"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                </div>

                                <div class="flex items-center space-x-6">
                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_popular"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">Mark as popular</span>
                                    </label>

                                    <label class="inline-flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">Active</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-end gap-3">
                        <button
                            type="button"
                            @click="cancel"
                            class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Creating...' : 'Create Plan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type="number"] {
    -moz-appearance: textfield;
}
</style>
