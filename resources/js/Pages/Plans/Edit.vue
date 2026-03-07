<!-- resources/js/Pages/Plans/Edit.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plan: Object,
    tools: Array
});

const form = useForm({
    tool_id: props.plan.tool_id,
    name: props.plan.name,
    description: props.plan.description || '',
    price: props.plan.price,
    currency: props.plan.currency || 'USD',
    billing_cycle: props.plan.billing_cycle,
    duration_days: props.plan.duration_days,
    device_limit: props.plan.device_limit,
    api_call_limit: props.plan.api_call_limit,
    concurrent_users: props.plan.concurrent_users,
    feature_flags: props.plan.feature_flags || [],
    limitations: props.plan.limitations || [],
    is_popular: props.plan.is_popular || false,
    is_active: props.plan.is_active,
    sort_order: props.plan.sort_order || 0
});

const featureInput = ref('');
const limitationInput = ref('');

const addFeature = () => {
    if (featureInput.value.trim()) {
        form.feature_flags.push(featureInput.value.trim());
        featureInput.value = '';
    }
};

const removeFeature = (index) => {
    form.feature_flags.splice(index, 1);
};

const addLimitation = () => {
    if (limitationInput.value.trim()) {
        form.limitations.push(limitationInput.value.trim());
        limitationInput.value = '';
    }
};

const removeLimitation = (index) => {
    form.limitations.splice(index, 1);
};

const submit = () => {
    form.put(route('plans.update', props.plan.id));
};
</script>

<template>
    <Head title="Edit Plan" />
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Plan: {{ plan.name }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Same form fields as Create.vue -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tool *</label>
                            <select v-model="form.tool_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                                <option value="">Select a tool</option>
                                <option v-for="tool in tools" :key="tool.id" :value="tool.id">
                                    {{ tool.name }}
                                </option>
                            </select>
                            <div v-if="form.errors.tool_id" class="text-red-500 text-sm mt-1">{{ form.errors.tool_id }}</div>
                        </div>

                        <!-- Copy all other form fields from Create.vue here -->
                        <!-- ... -->

                        <div class="flex justify-end gap-4 mt-6">
                            <Link :href="route('plans.index')" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                                Cancel
                            </Link>
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700" :disabled="form.processing">
                                {{ form.processing ? 'Updating...' : 'Update Plan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
