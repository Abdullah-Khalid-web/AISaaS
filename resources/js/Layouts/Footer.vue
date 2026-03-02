<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">AITool</h3>
                    <p class="text-gray-400 text-sm">Empowering your business with cutting-edge AI tools and solutions.</p>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link href="/about" class="text-gray-400 hover:text-white">About Us</Link></li>
                        <li><Link href="/features" class="text-gray-400 hover:text-white">Features</Link></li>
                        <li><Link href="/pricing" class="text-gray-400 hover:text-white">Pricing</Link></li>
                        <li><Link href="/contact" class="text-gray-400 hover:text-white">Contact</Link></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link href="/help" class="text-gray-400 hover:text-white">Help Center</Link></li>
                        <li><Link href="/docs" class="text-gray-400 hover:text-white">Documentation</Link></li>
                        <li><Link href="/api" class="text-gray-400 hover:text-white">API Reference</Link></li>
                        <li><Link href="/status" class="text-gray-400 hover:text-white">System Status</Link></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Legal</h3>
                    <ul class="space-y-2 text-sm">
                        <li><Link href="/privacy" class="text-gray-400 hover:text-white">Privacy Policy</Link></li>
                        <li><Link href="/terms" class="text-gray-400 hover:text-white">Terms of Service</Link></li>
                        <li><Link href="/security" class="text-gray-400 hover:text-white">Security</Link></li>
                        <li><Link href="/cookies" class="text-gray-400 hover:text-white">Cookie Policy</Link></li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 pt-8 border-t border-gray-800 text-center text-gray-400 text-sm">
                <p>&copy; {{ new Date().getFullYear() }} AITool. All rights reserved.</p>
            </div>
        </div>
    </footer>
</template>
