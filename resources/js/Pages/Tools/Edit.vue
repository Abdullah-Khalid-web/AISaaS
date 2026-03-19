<!-- resources/js/Pages/Tools/Edit.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    tool: {
        type: Object,
        required: true
    }
});

// Initialize form with existing tool data
const form = useForm({
    name: props.tool.name || '',
    description: props.tool.description || '',
    sdk_download_url: props.tool.sdk_download_url || '',
    version: props.tool.version || '1.0.0',
    supported_platforms: props.tool.supported_platforms || [],
    metadata: {
        icon: props.tool.metadata?.icon || '🤖',
        features: props.tool.metadata?.features || [],
        documentation_files: props.tool.metadata?.documentation_files || [],
        color: props.tool.metadata?.color || '#4f46e5'
    },
    is_active: props.tool.is_active ?? true,
    sort_order: props.tool.sort_order || 0
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

// File upload refs
const documentationFiles = ref([]);
const uploadError = ref('');
const isDragging = ref(false);
const fileInputRef = ref(null);

// Initialize documentation files on mount
onMounted(() => {
    if (form.metadata.documentation_files && form.metadata.documentation_files.length > 0) {
        documentationFiles.value = [...form.metadata.documentation_files];
    }
});

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

// File upload handlers
const handleFileUpload = (event) => {
    const files = Array.from(event.target.files);
    processFiles(files);
};

const handleDrop = (event) => {
    event.preventDefault();
    isDragging.value = false;

    const files = Array.from(event.dataTransfer.files);
    processFiles(files);
};

const processFiles = (files) => {
    uploadError.value = '';

    // Validate file types
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'text/markdown',
        'application/json',
        'text/html',
        'text/css',
        'application/javascript',
        'image/jpeg',
        'image/png',
        'image/svg+xml'
    ];

    const validFiles = files.filter(file => {
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(md|txt|json|html|css|js|py|java|cpp|php|sql|rtf)$/i)) {
            uploadError.value = `File type not allowed: ${file.name}`;
            return false;
        }
        if (file.size > 10 * 1024 * 1024) { // 10MB limit
            uploadError.value = `File too large (max 10MB): ${file.name}`;
            return false;
        }
        return true;
    });

    validFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const fileData = {
                name: file.name,
                type: file.type,
                size: file.size,
                lastModified: file.lastModified,
                content: e.target.result, // Base64 content
                url: URL.createObjectURL(file) // Temporary URL for preview
            };

            if (!form.metadata.documentation_files) {
                form.metadata.documentation_files = [];
            }
            form.metadata.documentation_files.push(fileData);
            documentationFiles.value.push(fileData);
        };
        reader.readAsDataURL(file);
    });
};

const removeFile = (index) => {
    const file = form.metadata.documentation_files[index];
    if (file.url && file.url.startsWith('blob:')) {
        URL.revokeObjectURL(file.url);
    }
    form.metadata.documentation_files.splice(index, 1);
    documentationFiles.value.splice(index, 1);
};

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const submit = () => {
    // Clean up blob URLs before submit
    if (form.metadata.documentation_files) {
        form.metadata.documentation_files.forEach(file => {
            if (file.url && file.url.startsWith('blob:')) {
                URL.revokeObjectURL(file.url);
                delete file.url; // Remove temporary URL before submit
            }
        });
    }

    form.put(route('tools.update', props.tool.id), {
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
    <Head :title="`Edit ${tool.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Edit Tool: {{ tool.name }}
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

                                <!-- Documentation Files Upload -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Documentation Files
                                    </label>

                                    <!-- Drop zone -->
                                    <div
                                        class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition"
                                        :class="[
                                            isDragging ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300 hover:border-gray-400',
                                            uploadError ? 'border-red-300 bg-red-50' : ''
                                        ]"
                                        @dragenter.prevent="isDragging = true"
                                        @dragover.prevent="isDragging = true"
                                        @dragleave.prevent="isDragging = false"
                                        @drop.prevent="handleDrop"
                                        @click="fileInputRef.click()"
                                    >
                                        <input
                                            ref="fileInputRef"
                                            type="file"
                                            multiple
                                            class="hidden"
                                            @change="handleFileUpload"
                                            accept=".pdf,.doc,.docx,.txt,.md,.json,.html,.css,.js,.py,.java,.cpp,.php,.sql,.rtf,.jpg,.jpeg,.png,.gif,.svg"
                                        />

                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                        </svg>

                                        <p class="mt-2 text-sm text-gray-600">
                                            <span class="font-medium text-indigo-600">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500">
                                            PDF, DOC, DOCX, TXT, MD, JSON, HTML, CSS, JS, images (up to 10MB each)
                                        </p>
                                    </div>

                                    <p v-if="uploadError" class="mt-2 text-sm text-red-600">{{ uploadError }}</p>

                                    <!-- File list -->
                                    <div v-if="form.metadata.documentation_files && form.metadata.documentation_files.length > 0" class="mt-4 space-y-2">
                                        <div
                                            v-for="(file, index) in form.metadata.documentation_files"
                                            :key="index"
                                            class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                        >
                                            <div class="flex items-center gap-3">
                                                <!-- File icon based on type -->
                                                <span class="text-2xl">
                                                    {{ file.type?.includes('pdf') ? '📕' :
                                                       file.type?.includes('word') ? '📘' :
                                                       file.type?.includes('text') ? '📃' :
                                                       file.type?.includes('image') ? '🖼️' : '📄' }}
                                                </span>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                                    <p class="text-xs text-gray-500">{{ formatFileSize(file.size) }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <!-- Preview link if applicable -->
                                                <a
                                                    v-if="file.url && file.type?.startsWith('image/')"
                                                    :href="file.url"
                                                    target="_blank"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <button
                                                    type="button"
                                                    @click="removeFile(index)"
                                                    class="text-red-600 hover:text-red-900"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
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
                                {{ form.processing ? 'Updating...' : 'Update Tool' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
