<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Stats Cards -->
                <div class="bg-indigo-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-indigo-800">API Calls</h3>
                    <p class="text-3xl font-bold text-indigo-600">2,345</p>
                    <p class="text-sm text-indigo-500">of 10,000 used</p>
                </div>

                <div class="bg-green-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-green-800">Projects</h3>
                    <p class="text-3xl font-bold text-green-600">12</p>
                    <p class="text-sm text-green-500">Active projects</p>
                </div>

                <div class="bg-purple-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-purple-800">Team Members</h3>
                    <p class="text-3xl font-bold text-purple-600">5</p>
                    <p class="text-sm text-purple-500">Collaborators</p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold">Recent Activity</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-600">Your recent AI tool usage will appear here.</p>
                </div>
            </div>
        </div>
        <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
    </AppLayout>
</template>
