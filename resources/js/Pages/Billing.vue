<!-- resources/js/Pages/Billing.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Billing" />

    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Billing
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h1 class="text-2xl font-bold mb-4">Billing Information</h1>
                        <p class="text-gray-600 mb-4">Manage your subscription and billing details.</p>

                        <div class="border-t border-gray-200 pt-4">
                            <h2 class="text-lg font-semibold mb-2">Current Plan</h2>
                            <p class="text-gray-600">You are currently on the <span class="font-semibold">Free</span> plan.</p>
                            <button class="mt-2 bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Upgrade Plan
                            </button>
                        </div>

                        <div class="border-t border-gray-200 mt-4 pt-4">
                            <h2 class="text-lg font-semibold mb-2">Payment Methods</h2>
                            <p class="text-gray-600">No payment methods on file.</p>
                            <button class="mt-2 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
                                Add Payment Method
                            </button>
                        </div>

                        <div class="border-t border-gray-200 mt-4 pt-4">
                            <h2 class="text-lg font-semibold mb-2">Billing History</h2>
                            <p class="text-gray-600">No billing history available.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
