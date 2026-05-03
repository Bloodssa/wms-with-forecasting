<script setup>
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ChevronDown } from 'lucide-vue-next';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import MarkLogo from '@/Components/Icons/MarkLogo.vue';
import dayjs from 'dayjs';

const props = defineProps({
    warranties: {
        type: Object,
        default: () => ({})
    },
    email: {
        type: String,
        default: ''
    }
});

const isOpen = ref(false);

const form = useForm({
    name: '',
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onSuccess: () => form.reset('password', 'password_confirmation'),
        onError: () => form.reset('password_confirmation'),
    });
};

const formatDate = (date) => {
    return dayjs(date).format('MMM DD, YYYY');
};
</script>

<template>
    <AuthLayout :showLogo="false">

        <Head title="Register" />

        <form @submit.prevent="submit" class="mb-10">
            <div class="flex flex-col items-center space-y-3">
                <Link href="/">
                    <MarkLogo />
                </Link>
                <h1 class="text-2xl font-bold leading-tight tracking-tight text-neutral-900 md:text-3xl">
                    Register an Account
                </h1>
                <div class="flex flex-col items-center">
                    <p class="text-gray-500 mt-1"> Register to start tracking device warranties.</p>
                </div>
                <div v-if="props.warranties?.length"
                    class="w-full my-2 border border-gray-300 rounded-md overflow-hidden bg-white">
                    <button type="button" @click="isOpen = !isOpen"
                        class="w-full flex items-center justify-between p-4 bg-neutral-50 hover:bg-neutral-100 transition-colors duration-200 text-left">
                        <div class="space-y-1">
                            <p class="text-sm text-neutral-500">
                                Registering Warranty for:
                            </p>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-bold text-neutral-900">
                                    Warranty Registration
                                </span>
                                <span
                                    class="text-[12px] px-2 py-0.5 bg-gray-200 text-gray-600 rounded-full font-medium">
                                    {{ props.warranties.length }} {{ props.warranties.length > 1 ? 'Items' : 'Item' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 text-neutral-400">
                            <span class="text-xs font-medium">{{ isOpen ? 'Hide details' : 'View details' }}</span>
                            <ChevronDown :class="{ 'rotate-180': isOpen }" class="w-5 h-5 transition-transform duration-300" />
                        </div>
                    </button>

                    <div v-show="isOpen"
                        class="border-t border-gray-300 animate-in fade-in slide-in-from-top-2 duration-300">
                        <div class="p-4 space-y-4 max-h-64 overflow-y-auto custom-scrollbar bg-white">
                            <div v-for="item in props.warranties" :key="item.serial_number"
                                class="flex items-center gap-3">
                                <img :src="item.product.image_url"
                                    class="h-10 w-10 object-cover rounded-md border border-gray-300" />
                                <div class="flex-1 min-w-0">
                                    <p class="text-[13px] font-semibold text-neutral-900 truncate">
                                        {{ item.product.name }}
                                    </p>
                                    <p class="text-[12px] text-neutral-500 tracking-tight">
                                        SN: {{ item.serial_number }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <p class="text-[12px] text-neutral-500 font-semibold">Expires</p>
                                    <p class="text-[12px] text-neutral-500">
                                        {{ formatDate(item.expiry_date) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white px-4 py-2 border-t border-gray-200 text-center">
                            <p class="text-[12px] text-neutral-400">
                                Purchased on {{ formatDate(props.warranties[0]?.purchase_date) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" autofocus
                    autocomplete="name" />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email"
                    autocomplete="username" />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password"
                    autocomplete="new-password" />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />

                <TextInput id="password_confirmation" type="password" class="mt-1 block w-full"
                    v-model="form.password_confirmation" autocomplete="new-password" />

                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="flex items-center justify-end mt-4 mb-4">
                <PrimaryButton class="w-full text-center" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    Register
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
                <p class="text-sm font-light text-gray-500 text-center">
                    Already have an account?
                    <Link :href="route('login')" class="font-medium text-neutral-900 hover:underline">
                        Login here
                    </Link>
                </p>
            </div>
        </form>
    </AuthLayout>
</template>
