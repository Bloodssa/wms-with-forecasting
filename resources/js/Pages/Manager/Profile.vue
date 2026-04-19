<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import UpdateProfileInformationForm from '../Profile/Partials/UpdateProfileInformationForm.vue';
import UpdatePasswordForm from '../Profile/Partials/UpdatePasswordForm.vue';
import DeleteUserForm from '../Profile/Partials/DeleteUserForm.vue';

const tab = ref('profile');

const setTab = (tabName) => {
    tab.value = tabName;
};
</script>

<template>

    <Head title="Profile" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Account Settings</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage your profile and security information.</p>
            </article>
        </section>

        <section class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
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
                    <div>
                        <div class="w-full border-gray-200 rounded-lg p-6">
                            <UpdatePasswordForm class="max-w-xl" />
                        </div>
                    </div>
                    <div class="border-t border-gray-300">
                        <div class="w-full border-gray-200 rounded-lg p-6">
                            <DeleteUserForm class="max-w-xl" />
                        </div>
                    </div>
                </div>
            </main>
        </section>
    </ManagerLayout>
</template>