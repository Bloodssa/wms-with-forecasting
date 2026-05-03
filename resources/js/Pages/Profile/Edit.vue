<script setup>
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';

const tab = ref('profile');

const setTab = (tabName) => {
    tab.value = tabName;
};

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    isLogInViaGmail: Boolean,
    hasPassword: Boolean
});

const form = useForm({
    password: '',
    confirm_password: ''
});

const showSetPasswordForm = computed(() => {
    return props.isLogInViaGmail && !props.hasPassword;
});

// submit added password
const addNewPassword = () => {
    form.put(route('profile.set-password'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        }
    });
};

// form for fallback user to cliam warranty
const claimForm = useForm({
    serial_number: '',
    purchase_email: '', 
});

// auth customer claim via serial number if the invation link expired
const submitClaim = () => {
    claimForm.patch(route('warranty-claim'), { 
        preserveScroll: true,
        onSuccess: () => {
            claimForm.reset();
        },
    });
};
</script>

<template>

    <Head title="Profile" />

    <CustomerLayout>
        <!-- <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800"
            >
                Profile
            </h2>
        </template>

<div class="py-12">
    <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status" class="max-w-xl" />
        </div>

        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <UpdatePasswordForm class="max-w-xl" />
        </div>

        <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
            <DeleteUserForm class="max-w-xl" />
        </div>
    </div>
</div> -->
        <section class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Account Settings</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage your profile and security information.</p>
            </article>
        </section>

        <section class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <aside class="lg:col-span-1 space-y-6">
                <div class="bg-white border border-gray-300 rounded-md p-6">
                    <div class="flex flex-col items-center text-center">
                        <div
                            class="w-24 h-24 bg-neutral-900 rounded-full mb-4 flex items-center justify-center text-white text-2xl font-bold">
                            {{ $page.props.auth.user.name.charAt(0) }}
                        </div>
                        <span class="rounded-full text-sm text-neutral-900 font-semibold capitalize font-meduim">
                            {{ $page.props.auth.user.role }}
                        </span>
                        <h2 class="text-lg font-bold text-neutral-900">{{ $page.props.auth.user.name }}</h2>
                        <p class="text-sm text-neutral-500">{{ $page.props.auth.user.email }}</p>
                    </div>

                    <hr class="my-6 border-gray-300">

                    <nav class="space-y-1">
                        <button @click="setTab('profile')" :class="[
                            'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition',
                            tab === 'profile'
                                ? 'bg-neutral-900 text-white'
                                : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'
                        ]">
                            General Profile
                        </button>

                        <button @click="setTab('claim')" :class="[
                            'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition',
                            tab === 'claim'
                                ? 'bg-neutral-900 text-white'
                                : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'
                        ]">
                            Claim A Warranty
                        </button>

                        <button @click="setTab('security')" :class="[
                            'w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition',
                            tab === 'security'
                                ? 'bg-neutral-900 text-white'
                                : 'text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900'
                        ]">
                            Security & Password
                        </button>
                    </nav>
                </div>
            </aside>

            <main class="lg:col-span-2 space-y-6">
                <div v-if="tab === 'profile'" class="bg-white border border-gray-300 rounded-md">
                    <div class="border-b border-gray-300 p-6">
                        <h3 class="text-lg font-semibold text-neutral-900">Profile Information</h3>
                    </div>
                    <div class="w-full border-gray-200 rounded-lg p-6">
                        <UpdateProfileInformationForm :must-verify-email="mustVerifyEmail" :status="status"
                            class="max-w-xl" />
                    </div>
                </div>

                <div v-if="tab === 'security'" class="bg-white border border-gray-300 rounded-md">
                    <div v-if="showSetPasswordForm">
                        <div class="w-full border-gray-200 rounded-lg py-2">
                            <h1 class="py-3 px-4 font-semibold text-neutral-900 text-md border-b border-gray-300">You
                                Are
                                Currently Log In Via Gmail</h1>
                            <form @submit.prevent="addNewPassword" class="p-4">
                                <InputLabel for="password" value="New Password" />
                                <TextInput placeholder="Password" id="password" ref="currentPassword"
                                    v-model="form.password" type="password" class="mt-1 block w-full"
                                    autocomplete="current-password" />
                                <InputError :message="form.errors.password" class="mt-2" />

                                <InputLabel for="confirm_password" value="Confirm Password" />
                                <TextInput placeholder="Confirm Password" id="confirm_password"
                                    ref="currentPasswordInput" v-model="form.confirm_password" type="password"
                                    class="mt-1 block w-full" autocomplete="current-password" />
                                <InputError :message="form.errors.confirm_password" class="mt-2" />
                                <PrimaryButton class="mt-3">
                                    Add Password
                                </PrimaryButton>
                            </form>
                        </div>
                    </div>
                    <div v-else>
                        <div class="w-full border border-gray-300 rounded-lg p-6">
                            <UpdatePasswordForm class="max-w-xl" />
                        </div>

                    </div>
                    <div class="border-t border-gray-300">
                        <div class="w-full border-gray-200 rounded-lg p-6">
                            <DeleteUserForm class="max-w-xl" />
                        </div>
                    </div>
                </div>
                <div v-if="tab === 'claim'">
                    <form @submit.prevent="submitClaim" class="space-y-4 bg-white border w-full border-gray-300 rounded-md p-6">
                        <div>
                            <InputLabel for="purchase_email" value="Email used during purchase" />
                            <TextInput id="purchase_email" v-model="claimForm.purchase_email" type="email"
                                class="mt-1 block w-full" placeholder="The email where you received the invitation" />
                            <InputError :message="claimForm.errors.purchase_email" class="mt-2" />
                        </div>

                        <div>
                            <InputLabel for="serial_number" value="Product Serial Number" />
                            <TextInput placeholder="D352-DSR83" id="serial_number" v-model="claimForm.serial_number" type="text"
                                class="mt-1 block w-full" />
                            <InputError :message="claimForm.errors.serial_number" class="mt-2" />
                        </div>

                        <PrimaryButton :disabled="claimForm.processing">
                            Link to My Account
                        </PrimaryButton>
                    </form>
                </div>
            </main>
        </section>
    </CustomerLayout>
</template>
