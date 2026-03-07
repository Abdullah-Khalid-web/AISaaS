<!-- resources/js/Pages/Tools/Create.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = useForm({
    name: '',
    description: '',
    sdk_download_url: '',
    version: '1.0.0',
    supported_platforms: [],
    metadata: {
        icon: '🤖',
        features: [],
        documentation_url: '',
        color: '#4f46e5'
    },
    is_active: true,
    sort_order: 0
});

const platformOptions = [
    { value: 'web', label: 'Web' },
    { value: 'ios', label: 'iOS' },
    { value: 'android', label: 'Android' },
    { value: 'windows', label: 'Windows' },
    { value: 'macos', label: 'macOS' },
    { value: 'linux', label: 'Linux' },
    { value: 'api', label: 'API Only' }
];

const iconOptions = [
    '🤖', '🎨', '📝', '💻', '🔮', '⚡', '🧠', '🎯', '🚀', '💡', '🔧', '📊', '🎮', '📱', '🌐', '⚙️'
];

const featureInput = ref('');
const showAdvanced = ref(false);

const addFeature = () => {
    if (featureInput.value.trim()) {
        if (!form.metadata.features) {
            form.metadata.features = [];
        }
        form.metadata.features.push(featureInput.value.trim());
        featureInput.value = '';
    }
};

const removeFeature = (index) => {
    form.metadata.features.splice(index, 1);
};

const togglePlatform = (platform) => {
    const index = form.supported_platforms.indexOf(platform);
    if (index === -1) {
        form.supported_platforms.push(platform);
    } else {
        form.supported_platforms.splice(index, 1);
    }
};

const submit = () => {
    form.post(route('tools.store'), {
        preserveScroll: true,
        onSuccess: () => {
            router.get(route('tools.index'));
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
        }
    });
};

const cancel = () => {
    router.get(route('tools.index'));
};
</script>

<template>
    <Head title="Create AI Tool" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create New AI Tool
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Error Display -->
                        <div v-if="Object.keys(form.errors).length > 0" class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                            <strong class="font-bold">Please fix the following errors:</strong>
                            <ul class="mt-2 list-disc list-inside">
                                <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                            </ul>
                        </div>

                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Basic Information</h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Tool Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.name"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        placeholder="e.g., Text Generator Pro"
                                        required
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                                </div>

                                <!-- Version -->
                                <div class="col-span-2 md:col-span-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Version
                                    </label>
                                    <input
                                        type="text"
                                        v-model="form.version"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="1.0.0"
                                    />
                                </div>

                                <!-- Icon Selection -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Icon
                                    </label>
                                    <div class="flex flex-wrap gap-3">
                                        <button
                                            v-for="icon in iconOptions"
                                            :key="icon"
                                            type="button"
                                            @click="form.metadata.icon = icon"
                                            class="w-12 h-12 text-2xl rounded-lg border-2 flex items-center justify-center transition"
                                            :class="form.metadata.icon === icon ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-gray-300'"
                                        >
                                            {{ icon }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Description
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="4"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.description }"
                                        placeholder="Describe what this tool does and its main features..."
                                    ></textarea>
                                    <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Features -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Key Features</h3>

                            <div class="space-y-3">
                                <div class="flex gap-2">
                                    <input
                                        v-model="featureInput"
                                        type="text"
                                        class="flex-1 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Add a feature (e.g., Unlimited API calls)"
                                        @keyup.enter="addFeature"
                                    />
                                    <button
                                        type="button"
                                        @click="addFeature"
                                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-1"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Add
                                    </button>
                                </div>

                                <div v-if="form.metadata.features && form.metadata.features.length > 0" class="flex flex-wrap gap-2 mt-3">
                                    <span
                                        v-for="(feature, index) in form.metadata.features"
                                        :key="index"
                                        class="bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm flex items-center gap-1"
                                    >
                                        {{ feature }}
                                        <button
                                            type="button"
                                            @click="removeFeature(index)"
                                            class="text-indigo-600 hover:text-indigo-900 ml-1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </span>
                                </div>
                                <p v-else class="text-sm text-gray-500 italic">
                                    No features added yet. Add some key features of your tool.
                                </p>
                            </div>
                        </div>

                        <!-- Platforms & SDK -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b">Platforms & SDK</h3>

                            <div class="space-y-6">
                                <!-- Supported Platforms -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Supported Platforms
                                    </label>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                        <label
                                            v-for="platform in platformOptions"
                                            :key="platform.value"
                                            class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                                            :class="{ 'border-indigo-600 bg-indigo-50': form.supported_platforms.includes(platform.value) }"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="form.supported_platforms.includes(platform.value)"
                                                @change="togglePlatform(platform.value)"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700">{{ platform.label }}</span>
                                        </label>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Leave empty for all platforms</p>
                                </div>

                                <!-- SDK Download URL -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        SDK Download URL
                                    </label>
                                    <input
                                        type="url"
                                        v-model="form.sdk_download_url"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="https://example.com/sdk.zip"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Optional: Link to download the SDK</p>
                                </div>

                                <!-- Documentation URL -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Documentation URL
                                    </label>
                                    <input
                                        type="url"
                                        v-model="form.metadata.documentation_url"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="https://docs.example.com"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="mb-8">
                            <button
                                type="button"
                                @click="showAdvanced = !showAdvanced"
                                class="flex items-center gap-2 text-gray-700 hover:text-gray-900 w-full text-left"
                            >
                                <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': showAdvanced }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                                <span class="font-medium">Advanced Settings</span>
                            </button>

                            <div v-show="showAdvanced" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Sort Order -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Sort Order
                                    </label>
                                    <input
                                        type="number"
                                        v-model.number="form.sort_order"
                                        class="w-full rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                        min="0"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Lower numbers appear first</p>
                                </div>

                                <!-- Accent Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Accent Color
                                    </label>
                                    <div class="flex gap-2">
                                        <input
                                            type="color"
                                            v-model="form.metadata.color"
                                            class="h-10 w-20 rounded border-gray-300"
                                        />
                                        <input
                                            type="text"
                                            v-model="form.metadata.color"
                                            class="flex-1 rounded-lg border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="#4f46e5"
                                        />
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="col-span-2">
                                    <label class="flex items-center gap-2">
                                        <input
                                            type="checkbox"
                                            v-model="form.is_active"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <span class="text-sm text-gray-700">Active (visible to users)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex justify-end gap-3 pt-4 border-t">
                            <button
                                type="button"
                                @click="cancel"
                                class="px-6 py-2 bg-white border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 transition"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2 bg-indigo-600 border border-transparent rounded-lg font-medium text-white hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                            >
                                <svg v-if="form.processing" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ form.processing ? 'Creating...' : 'Create Tool' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
