<!-- resources/js/Pages/ApiTokens.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="API Tokens" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                API Tokens
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-4">API Tokens</h1>
                        <p class="text-gray-600 mb-4">Manage your API tokens for programmatic access.</p>

                        <button class="mb-4 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Create New Token
                        </button>

                        <div class="border-t border-gray-200 pt-4">
                            <p class="text-gray-500 text-center py-8">No API tokens created yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
