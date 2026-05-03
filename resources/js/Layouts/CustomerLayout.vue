<script setup>
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { Link, usePage } from '@inertiajs/vue3';
import MarkLogo from '@/Components/Icons/MarkLogo.vue';
import Dropdown from '@/Components/Nav/Dropdown.vue';
import DropdownLink from '@/Components/Nav/DropdownLink.vue';
import NavLink from '@/Components/Nav/NavLink.vue';
import ResponsiveNavLink from '@/Components/Nav/ResponsiveNavLink.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import { UserPen, LogOut } from 'lucide-vue-next';
import Notification from '@/Components/Nav/Notification.vue';
import { useToast } from "primevue/usetoast";
import Toast from 'primevue/toast';

dayjs.extend(relativeTime);

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);

const isNotificationOpen = ref(false);

const toggleUserDropdown = (event) => {
    if (event) event.stopPropagation();

    showingNavigationDropdown.value = !showingNavigationDropdown.value;
    isNotificationOpen.value = false; // Close notifications when profile opens
};

const toggleNotification = (event) => {
    // stop propagination
    if (event) event.stopPropagation();

    isNotificationOpen.value = !isNotificationOpen.value;
    showingNavigationDropdown.value = false;
};

const closeNotification = () => {
    isNotificationOpen.value = false;
};

const toast = useToast();

const triggerToast = (flash) => {
    if (flash?.success) {
        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: flash.success,
            life: 3000
        });
    }

    if (flash?.error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: flash.error,
            life: 5000
        });
    }

    if (flash?.warning) {
        toast.add({
            severity: 'warn',
            summary: 'Warning',
            detail: flash.warning,
            life: 4000
        });
    }
};

// 2. Handle redirects (Page Initial Load)
onMounted(async () => {
    await nextTick(); // Wait for PrimeVue Toast component to fully load in the DOM
    triggerToast(page.props.flash);
});

// 3. Handle page updates without navigation (e.g., return back())
watch(
    () => page.props.flash,
    async (newFlash) => {
        await nextTick();
        triggerToast(newFlash);
    },
    { deep: true } // Remove `immediate: true` as onMounted now handles the first check
);
</script>

<template>
    <div>
        <Toast :pt="{ 
            root: { class: 'z-[10001]' } 
        }" />
        <div class="min-h-screen bg-gray-100">
            <nav class="border-b border-gray-300 bg-white relative">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-20 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('home')">
                                    <MarkLogo size="h-10 w-10" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('home')" :active="route().current('home')">
                                    Home
                                </NavLink>
                                <NavLink :href="route('warranty')"
                                    :active="route().current('warranty') || route().current('warranty.show')">
                                    My Warranty
                                </NavLink>
                                <NavLink :href="route('inquiries')"
                                    :active="route().current('inquiries') || route().current('inquiry.show') || route().current('create-inquiry')">
                                    Inquiries
                                </NavLink>
                                <NavLink :href="route('view-products')"
                                    :active="route().current('view-products') || route().current('products-details') || route().current('product-reviews')">
                                    Products
                                </NavLink>
                                <NavLink :href="route('history')" :active="route().current('history')">
                                    History
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative flex justify-center items-center ms-3">
                                <div @click.stop>
                                    <Notification :is-open="isNotificationOpen" :notifications="$page.props.notifications" @toggle="toggleNotification"
                                        @close="closeNotification">
                                        
                                    </Notification>
                                </div>

                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none">
                                                <Avatar class="h-9 w-9" :name="user.name" />
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">

                            <div @click.stop>
                                <Notification :is-open="isNotificationOpen" @toggle="toggleNotification"
                                    @close="closeNotification" :notifications="$page.props.notifications">
                                </Notification>

                                <button @click="toggleUserDropdown"
                                    class="lg:hidden p-2 text-neutral-500 hover:bg-gray-100 rounded-md transition-colors">
                                    <svg v-if="!showingNavigationDropdown" xmlns="http://www.w3.org/2000/svg"
                                        class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16m-7 6h7" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{
                    block: showingNavigationDropdown,
                    hidden: !showingNavigationDropdown,
                }" class="sm:hidden absolute top-full left-0 w-full bg-white border-t border-gray-300 shadow-lg z-50">
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink :href="route('home')" :active="route().current('home')">
                            Home
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('warranty')" :active="route().current('warranty')">
                            My Warranty
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('inquiries')" :active="route().current('inquiries') || route().current('create-inquiry')">
                            Inquiries
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('view-products')"
                            :active="route().current('view-products') || route().current('products-details') || route().current('product-reviews')">
                            Products
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('history')" :active="route().current('history')">
                            History
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="border-t border-gray-300 pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink class="text-start" :href="route('profile.edit')">
                                <div class="flex justify-between">
                                    <span>Profile
                                    </span><span>
                                        <UserPen />
                                    </span>
                                </div>
                            </ResponsiveNavLink>
                            <ResponsiveNavLink class="text-start" :href="route('logout')" method="post" as="button">
                                <div class="flex justify-between">
                                    <span>Log Out
                                    </span><span>
                                        <LogOut />
                                    </span>
                                </div>
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                <slot />
            </main>
        </div>
    </div>
</template>
