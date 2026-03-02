<script setup>
import Header from '@/Layouts/Header.vue';
import Footer from '@/Layouts/Footer.vue';
</script>

<template>
    <div class="min-h-screen bg-gray-100 flex flex-col">
        <!-- Public Header (without user menu) -->
        <Header :is-guest="true" />

        <!-- Page Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer -->
        <Footer />
    </div>
</template>
