<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

                <!-- Logo and Title -->
                <div class="text-center mb-6">
                    <Link href="/">
                        <h2 class="text-3xl font-bold text-indigo-600">AI<span class="text-gray-800">Tool</span></h2>
                    </Link>
                    <p class="text-gray-600 mt-2">Create your account to get started</p>
                </div>

                <!-- Registration Form -->
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Name Field -->
                    <div>
                        <InputLabel for="name" value="Full Name" class="text-sm font-medium text-gray-700" />

                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Enter your full name"
                        />

                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="Enter your email"
                        />

                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <!-- Password Field -->
                    <div>
                        <InputLabel for="password" value="Password" class="text-sm font-medium text-gray-700" />

                        <TextInput
                            id="password"
                            type="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a password"
                        />

                        <!-- Password Strength Indicator -->
                        <div class="mt-2">
                            <div class="flex space-x-1">
                                <div class="h-1 w-1/4 rounded" :class="form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <div class="h-1 w-1/4 rounded" :class="form.password.match(/[A-Z]/) && form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <div class="h-1 w-1/4 rounded" :class="form.password.match(/[0-9]/) && form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'"></div>
                                <div class="h-1 w-1/4 rounded" :class="form.password.match(/[^A-Za-z0-9]/) && form.password.length >= 8 ? 'bg-green-500' : 'bg-gray-200'"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Password must be at least 8 characters with 1 uppercase, 1 number & 1 special character
                            </p>
                        </div>

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Confirm Password Field -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirm Password" class="text-sm font-medium text-gray-700" />

                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm your password"
                        />

                        <!-- Password Match Indicator -->
                        <div v-if="form.password && form.password_confirmation" class="mt-1">
                            <p v-if="form.password === form.password_confirmation" class="text-xs text-green-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Passwords match
                            </p>
                            <p v-else class="text-xs text-red-600 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Passwords do not match
                            </p>
                        </div>

                        <InputError class="mt-2" :message="form.errors.password_confirmation" />
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="flex items-center mt-4">
                        <input type="checkbox" id="terms" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" required>
                        <label for="terms" class="ml-2 text-sm text-gray-600">
                            I agree to the
                            <Link href="/terms" class="text-indigo-600 hover:text-indigo-800 hover:underline">Terms of Service</Link>
                            and
                            <Link href="/privacy" class="text-indigo-600 hover:text-indigo-800 hover:underline">Privacy Policy</Link>
                        </label>
                    </div>

                    <!-- Register Button -->
                    <div class="mt-6">
                        <PrimaryButton
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ form.processing ? 'Creating account...' : 'Create Account' }}
                        </PrimaryButton>
                    </div>

                    <!-- Benefits -->
                    <div class="mt-4 bg-indigo-50 rounded-lg p-4">
                        <h4 class="text-sm font-semibold text-indigo-800 mb-2">✨ Benefits of joining:</h4>
                        <ul class="text-xs text-indigo-700 space-y-1">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Access to all AI tools
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                100 free API calls on signup
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                24/7 customer support
                            </li>
                        </ul>
                    </div>

                    <!-- Login Link -->
                    <div class="text-center mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            Already have an account?
                            <Link :href="route('login')" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                Sign in here
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
