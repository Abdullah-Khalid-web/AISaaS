<!-- resources/js/Pages/Plans/Index.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';  // Added usePage import
import { ref, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    plans: {
        type: Array,  // If it's a simple array
        default: () => []
    },
    // If your plans are paginated, use this structure instead:
    // plans: {
    //     type: Object,
    //     default: () => ({ data: [], from: 0, to: 0, total: 0, links: [] })
    // },
    filters: {
        type: Object,
        default: () => ({ search: '' })
    }
});

const page = usePage();
const user = page.props.auth.user;
const isAdmin = user && (user.roles?.includes('admin') || user.roles?.includes('super-admin'));

// Debug: Log the props to see what's being received
console.log('Plans props:', props.plans);
console.log('Is Admin:', isAdmin);

// State
const search = ref(props.filters?.search || '');
const selectedPlans = ref([]);
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
    router.get(route('plans.index'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
}, 300);

watch(search, (value) => {
    debouncedSearch(value);
});

// Select all functionality - Fixed to handle both array and paginated data
watch(selectAll, (value) => {
    const plansData = Array.isArray(props.plans) ? props.plans : (props.plans?.data || []);
    if (value && plansData.length) {
        selectedPlans.value = plansData.map(plan => plan.id);
    } else {
        selectedPlans.value = [];
    }
});

// Check if all are selected - Fixed to handle both array and paginated data
watch(selectedPlans, (value) => {
    const plansData = Array.isArray(props.plans) ? props.plans : (props.plans?.data || []);
    if (plansData.length) {
        selectAll.value = value.length === plansData.length && plansData.length > 0;
    }
}, { deep: true });

// Methods
const toggleStatus = async (plan) => {
    try {
        const response = await axios.post(route('plans.toggle-status', plan.id));
        plan.is_active = response.data.is_active;
    } catch (error) {
        console.error('Failed to toggle status', error);
    }
};

const deletePlan = (plan) => {
    if (confirm('Are you sure you want to delete this plan?')) {
        router.delete(route('plans.destroy', plan.id), {
            preserveScroll: true,
            onError: (errors) => {
                console.error('Delete plan errors:', errors);
            }
        });
    }
};

const bulkDelete = () => {
    if (selectedPlans.value.length === 0) return;

    if (confirm(`Are you sure you want to delete ${selectedPlans.value.length} plans?`)) {
        router.post(route('plans.bulk-destroy'), {
            ids: selectedPlans.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedPlans.value = [];
                showDeleteModal.value = false;
            },
            onError: (errors) => {
                console.error('Bulk delete errors:', errors);
            }
        });
    }
};

const formatCurrency = (price, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(price);
};

const formatBillingCycle = (cycle) => {
    const cycles = {
        monthly: 'Monthly',
        quarterly: 'Quarterly',
        yearly: 'Yearly',
        lifetime: 'Lifetime',
        one_time: 'One Time'
    };
    return cycles[cycle] || cycle;
};

// Get plans array - helper function to handle both array and paginated data
const getPlansArray = () => {
    return Array.isArray(props.plans) ? props.plans : (props.plans?.data || []);
};
</script>

<template>
    <Head title="Plan Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Plan Management
                </h2>
                <Link
                    v-if="isAdmin"
                    :href="route('plans.create')"
                    class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add New Plan
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
                                    placeholder="Search plans by name or tool..."
                                    class="w-full pl-10 pr-4 py-2 border rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedPlans.length > 0" class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">{{ selectedPlans.length }} selected</span>
                            <button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 text-sm">
                                Delete Selected
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Plans Table -->
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plan Details</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pricing</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limits</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th v-if="isAdmin" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-if="getPlansArray().length > 0">
                                <tr v-for="plan in getPlansArray()" :key="plan.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <input
                                            type="checkbox"
                                            v-model="selectedPlans"
                                            :value="plan.id"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        >
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-10 w-10 flex-shrink-0">
                                                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                                    <span class="text-indigo-600 font-medium text-sm">
                                                        {{ plan.tool?.name?.charAt(0).toUpperCase() || 'T' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ plan.tool?.name || 'Unknown Tool' }}</div>
                                                <div class="text-xs text-gray-500">ID: {{ plan.tool_id }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ plan.name }}</div>
                                        <div class="text-xs text-gray-500 mt-1">{{ plan.description || 'No description' }}</div>
                                        <span v-if="plan.is_popular" class="inline-flex mt-1 items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Popular
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ formatCurrency(plan.price, plan.currency) }}</div>
                                        <div class="text-xs text-gray-500">{{ formatBillingCycle(plan.billing_cycle) }}</div>
                                        <div v-if="plan.duration_days" class="text-xs text-gray-400">{{ plan.duration_days }} days</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div class="text-xs text-gray-600">
                                                <span class="font-medium">Devices:</span> {{ plan.device_limit === 999 ? '∞' : plan.device_limit }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <span class="font-medium">API Calls:</span> {{ plan.api_call_limit ? plan.api_call_limit.toLocaleString() : '∞' }}
                                            </div>
                                            <div class="text-xs text-gray-600">
                                                <span class="font-medium">Concurrent:</span> {{ plan.concurrent_users }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <button
                                            @click="toggleStatus(plan)"
                                            class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none"
                                            :class="plan.is_active ? 'bg-green-600' : 'bg-gray-300'"
                                        >
                                            <span class="sr-only">Toggle status</span>
                                            <span
                                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200"
                                                :class="plan.is_active ? 'translate-x-5' : 'translate-x-0'"
                                            />
                                        </button>
                                        <span class="ml-2 text-xs" :class="plan.is_active ? 'text-green-600' : 'text-gray-400'">
                                            {{ plan.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('plans.edit', plan.id)"
                                            class="text-indigo-600 hover:text-indigo-900 mr-3 inline-block"
                                            title="Edit Plan"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </Link>
                                        <button
                                            @click="deletePlan(plan)"
                                            class="text-red-600 hover:text-red-900 inline-block"
                                            title="Delete Plan"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                            <tr v-else>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm font-medium text-gray-900">No plans found</p>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new plan.</p>
                                    <Link
                                        v-if="isAdmin"
                                        :href="route('plans.create')"
                                        class="mt-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700"
                                    >
                                        Create Your First Plan
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination - Only show if plans is paginated -->
                    <div v-if="!Array.isArray(plans) && plans?.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing <span class="font-medium">{{ plans.from || 0 }}</span> to <span class="font-medium">{{ plans.to || 0 }}</span> of <span class="font-medium">{{ plans.total || 0 }}</span> results
                            </div>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in plans.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="px-3 py-1 rounded-md text-sm"
                                    :class="{
                                        'bg-indigo-600 text-white': link.active,
                                        'text-gray-700 hover:bg-gray-50': !link.active && link.url,
                                        'text-gray-400 cursor-not-allowed': !link.url
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
