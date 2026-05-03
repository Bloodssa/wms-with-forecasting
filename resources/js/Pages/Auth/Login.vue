<script setup>
import Checkbox from '@/Components/Forms/Checkbox.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import MarkLogo from '@/Components/Icons/MarkLogo.vue';

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
    <AuthLayout :showLogo="false">

        <Head title="Log in" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col items-center space-y-3">
                <Link href="/">
                    <MarkLogo />
                </Link>
                <div class="flex flex-col items-center">
                    <h1 class="text-2xl font-bold leading-tight tracking-tight text-neutral-900 md:text-3xl">
                        Welcome Back
                    </h1>
                    <p class="text-gray-500 mt-1">Sign in to access your warranty records</p>
                </div>
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput placeholder="warranty@gmail.com" id="email" type="email" class="mt-1 block w-full" v-model="form.email" required autofocus
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput placeholder="********" id="password" type="password" class="mt-1 block w-full" v-model="form.password" required
                    autocomplete="current-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 flex justify-between">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-neutral-900">Remember me</span>
                </label>

                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-neutral-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Forgot your password?
                </Link>
            </div>

            <div class="flex items-center justify-end mt-4 mb-4">
                <PrimaryButton class="w-full text-center" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Log in
                </PrimaryButton>
            </div>

            <div>
                <div class="border-b h-1 mb-4 border-gray-300 w-full"></div>
            </div>

            <div class="w-full h-10">
                <a :href="route('auth.google')"
                    class="flex items-center justify-center w-full px-4 py-2.5 space-x-3 text-sm outline-none font-medium text-neutral-900 bg-white border border-gray-300 rounded-md hover:bg-gray-100 hover transition-all duration-200 ease-in-out">

                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 48 48">
                        <path fill="#FFC107"
                            d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24s8.955,20,20,20s20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z">
                        </path>
                        <path fill="#FF3D00"
                            d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z">
                        </path>
                        <path fill="#4CAF50"
                            d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z">
                        </path>
                        <path fill="#1976D2"
                            d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z">
                        </path>
                    </svg>

                    <span>Continue with Google</span>
                </a>
            </div>

            <div class="flex justify-center mt-4 space-x-1">
                <p class="text-sm font-light text-gray-500 text-center ">
                    Don’t have an account yet?
                    <Link :href="route('register')" class="font-medium text-neutral-900 hover:underline">Sign up</Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
