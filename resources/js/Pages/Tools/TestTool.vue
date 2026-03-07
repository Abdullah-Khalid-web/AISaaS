<!-- resources/js/Pages/Tools/TestTool.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    hasAccess: Boolean,
    license: Object
});

const prompt = ref('');
const type = ref('text');
const result = ref(null);
const loading = ref(false);
const error = ref(null);

const generate = async () => {
    if (!prompt.value) return;

    loading.value = true;
    error.value = null;

    try {
        const response = await axios.post(route('test-tool.generate'), {
            prompt: prompt.value,
            type: type.value
        });

        result.value = response.data;
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to generate';
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <Head title="Test AI Tool" />
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Test AI Tool
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Access Denied -->
                <div v-if="!hasAccess" class="bg-white rounded-lg shadow-md p-8 text-center">
                    <div class="text-6xl mb-4">🔒</div>
                    <h3 class="text-2xl font-bold mb-2">No Active License</h3>
                    <p class="text-gray-600 mb-6">
                        You need an active license to use this tool.
                    </p>
                    <Link href="/pricing" class="bg-indigo-600 text-white px-6 py-3 rounded-md hover:bg-indigo-700">
                        View Plans
                    </Link>
                </div>

                <!-- Test Tool Interface -->
                <div v-else class="space-y-6">
                    <!-- Usage Info -->
                    <div class="bg-indigo-50 rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <span class="text-sm text-indigo-600">API Usage</span>
                            <div class="text-lg font-semibold">
                                {{ license.api_calls_used }} / {{ license.api_calls_limit || '∞' }}
                            </div>
                        </div>
                        <div>
                            <span class="text-sm text-indigo-600">License Key</span>
                            <div class="font-mono text-sm">{{ license.license_key }}</div>
                        </div>
                    </div>

                    <!-- Input Form -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Try Our AI Tool</h3>

                        <div class="space-y-4">
                            <!-- Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Generation Type
                                </label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" v-model="type" value="text" class="mr-2">
                                        Text
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" v-model="type" value="image" class="mr-2">
                                        Image
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" v-model="type" value="code" class="mr-2">
                                        Code
                                    </label>
                                </div>
                            </div>

                            <!-- Prompt Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Enter your prompt
                                </label>
                                <textarea
                                    v-model="prompt"
                                    rows="4"
                                    placeholder="Describe what you want to generate..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <!-- Generate Button -->
                            <button @click="generate"
                                    :disabled="!prompt || loading"
                                    class="w-full bg-indigo-600 text-white py-3 rounded-md hover:bg-indigo-700 transition disabled:opacity-50">
                                {{ loading ? 'Generating...' : 'Generate' }}
                            </button>

                            <!-- Error Message -->
                            <div v-if="error" class="bg-red-50 text-red-600 p-3 rounded-md">
                                {{ error }}
                            </div>
                        </div>
                    </div>

                    <!-- Result -->
                    <div v-if="result" class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Result</h3>

                        <!-- Text Result -->
                        <div v-if="type === 'text'" class="bg-gray-50 p-4 rounded-md whitespace-pre-wrap">
                            {{ result.result }}
                        </div>

                        <!-- Image Result -->
                        <div v-else-if="type === 'image'" class="text-center">
                            <img :src="result.result" alt="Generated" class="mx-auto rounded-lg shadow">
                        </div>

                        <!-- Code Result -->
                        <div v-else-if="type === 'code'" class="bg-gray-900 text-white p-4 rounded-md overflow-x-auto">
                            <pre>{{ result.result }}</pre>
                        </div>

                        <!-- Usage Info -->
                        <div class="mt-4 text-sm text-gray-500">
                            Used {{ result.usage.used }} of {{ result.usage.limit || 'unlimited' }} calls
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
