<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">

                <!-- Logo and Title -->
                <div class="text-center mb-6">
                    <Link href="/">
                        <h2 class="text-3xl font-bold text-indigo-600">AI<span class="text-gray-800">Tool</span></h2>
                    </Link>
                    <p class="text-gray-600 mt-2">Welcome back! Please login to your account.</p>
                </div>

                <!-- Status Message -->
                <div v-if="status" class="mb-4 text-sm font-medium text-green-600 bg-green-50 border border-green-200 rounded-lg p-3">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email Field -->
                    <div>
                        <InputLabel for="email" value="Email Address" class="text-sm font-medium text-gray-700" />

                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            v-model="form.email"
                            required
                            autofocus
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
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        />

                        <InputError class="mt-2" :message="form.errors.password" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span class="ms-2 text-sm text-gray-600">Remember me</span>
                        </label>

                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-indigo-600 hover:text-indigo-800 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Forgot password?
                        </Link>
                    </div>

                    <!-- Login Button -->
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
                            {{ form.processing ? 'Logging in...' : 'Sign in' }}
                        </PrimaryButton>
                    </div>

                    <!-- Demo Credentials -->
                    <div class="mt-4 text-center">
                        <p class="text-xs text-gray-500 mb-2">Demo Credentials:</p>
                        <div class="flex flex-col space-y-1 text-xs text-gray-600">
                            <div><span class="font-medium">Super Admin:</span> superadmin@example.com / admin</div>
                            <div><span class="font-medium">Admin:</span> admin@example.com / password</div>
                            <div><span class="font-medium">User:</span> user@example.com / password</div>
                        </div>
                    </div>

                    <!-- Register Link -->
                    <div class="text-center mt-4 pt-4 border-t border-gray-200">
                        <p class="text-sm text-gray-600">
                            Don't have an account?
                            <Link :href="route('register')" class="font-medium text-indigo-600 hover:text-indigo-800 hover:underline">
                                Register here
                            </Link>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
/* Optional: Add custom styles if needed */
</style>
