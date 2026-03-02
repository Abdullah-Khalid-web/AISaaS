<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <GuestLayout>
        <Head title="Welcome - AI Tool Platform" />

        <!-- Hero Section -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold mb-6">
                        AI-Powered Tools for<br>Modern Business
                    </h1>
                    <p class="text-xl md:text-2xl mb-8 text-indigo-100">
                        Harness the power of artificial intelligence to automate, analyze, and accelerate your workflow
                    </p>
                    <div class="space-x-4">
                        <Link href="/register" class="bg-white text-indigo-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                            Get Started Free
                        </Link>
                        <Link href="/features" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-indigo-600 transition">
                            Learn More
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Powerful AI Tools at Your Fingertips</h2>
                    <p class="text-xl text-gray-600">Everything you need to supercharge your productivity</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-indigo-600 text-4xl mb-4">📝</div>
                        <h3 class="text-xl font-semibold mb-2">Text Generation</h3>
                        <p class="text-gray-600">Generate high-quality content, blog posts, and marketing copy with AI</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-indigo-600 text-4xl mb-4">🎨</div>
                        <h3 class="text-xl font-semibold mb-2">Image Generation</h3>
                        <p class="text-gray-600">Create stunning images and artwork from text descriptions</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white p-6 rounded-lg shadow-md">
                        <div class="text-indigo-600 text-4xl mb-4">💻</div>
                        <h3 class="text-xl font-semibold mb-2">Code Assistant</h3>
                        <p class="text-gray-600">Get help with coding, debugging, and code optimization</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pricing Section -->
        <div class="py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Simple, Transparent Pricing</h2>
                    <p class="text-xl text-gray-600">Choose the plan that's right for you</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Basic Plan -->
                    <div class="border rounded-lg p-8">
                        <h3 class="text-2xl font-bold mb-4">Basic</h3>
                        <div class="text-4xl font-bold mb-4">$29<span class="text-lg text-gray-600">/mo</span></div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                1,000 API calls/month
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Basic AI models
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Email support
                            </li>
                        </ul>
                        <Link href="/register" class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Get Started
                        </Link>
                    </div>

                    <!-- Pro Plan -->
                    <div class="border-2 border-indigo-600 rounded-lg p-8 relative">
                        <span class="absolute top-0 right-0 bg-indigo-600 text-white px-3 py-1 text-sm rounded-bl-lg rounded-tr-lg">Popular</span>
                        <h3 class="text-2xl font-bold mb-4">Pro</h3>
                        <div class="text-4xl font-bold mb-4">$79<span class="text-lg text-gray-600">/mo</span></div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                10,000 API calls/month
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Advanced AI models
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Priority support
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Team collaboration
                            </li>
                        </ul>
                        <Link href="/register" class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Get Started
                        </Link>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="border rounded-lg p-8">
                        <h3 class="text-2xl font-bold mb-4">Enterprise</h3>
                        <div class="text-4xl font-bold mb-4">$199<span class="text-lg text-gray-600">/mo</span></div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Unlimited API calls
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                Custom AI models
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                24/7 phone support
                            </li>
                            <li class="flex items-center">
                                <span class="text-green-500 mr-2">✓</span>
                                SLA guarantee
                            </li>
                        </ul>
                        <Link href="/contact" class="block text-center bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            Contact Sales
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
