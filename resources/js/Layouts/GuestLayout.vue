<script setup>
import { Head } from '@inertiajs/vue3';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/Icons/ApplicationLogo.vue';
import { ref, computed } from 'vue';
import GuestNavBar from '@/Components/Nav/GuestNavBar.vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    title: {
        type: String
    }
});

const page = usePage();
const currentUrl = computed(() => page.url); // make it read only with computed

const isActiveLink = (path) => currentUrl.value === path;
const isMobileNavActive = ref(false); // init dropdown mobile nav

const toggleNavMobile = () => isMobileNavActive.value = !isMobileNavActive.value;
</script>

<template>
    <Head :title="title" />
    <header class="w-full bg-white border-b border-gray-300 sticky top-0 z-50">
        <div class="mx-auto h-18 flex justify-between items-center max-w-360.5 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center space-x-6">
                <ApplicationLogo />

                <nav class="hidden lg:flex items-center space-x-3">
                    <GuestNavBar />    
                </nav>
            </div>

            <div class="flex items-center justify-end gap-2 shrink-0">
                <div class="hidden lg:flex items-center gap-2">
                    <template v-if="canLogin">
                        <Link :href="route('login')" class="px-5 py-1.5 border border-gray-300 hover:bg-gray-100 transition text-neutral-900 rounded-md text-sm font-semibold">
                            Log in
                        </Link>

                        <Link v-if="canRegister" :href="route('register')" class="px-5 py-1.5 bg-black hover:bg-black/80 transition duration-200 text-white rounded-md text-sm font-semibold">
                            Register
                        </Link>
                    </template>
                </div>

                <button @click="toggleNavMobile" class="lg:hidden p-2 text-neutral-600 hover:bg-gray-100 rounded-md transition-colors">
                    <svg v-if="!isMobileNavActive" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
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

        <Transition 
            enter-active-class="transition duration-200" enter-from-class="-translate-y-2 opacity-0" enter-to-class="opacity-100 translate-y-0">
            <div v-show="isMobileNavActive" class="relative">
                <div class="lg:hidden absolute left-0 right-0 w-screen bg-white border-b border-gray-300 px-4 py-4 space-y-4">
                    <div class="flex flex-col gap-2 lg:hidden">
                        <Link :href="route('login')" class="w-full text-center py-2 border border-gray-300 rounded-md text-sm font-semibold text-neutral-900">Log in</Link>
                        <Link :href="route('register')" class="w-full text-center py-2 bg-black rounded-md text-sm font-semibold text-white">Register</Link>
                    </div>
                    <hr class="border-gray-300">
                    <nav class="flex flex-col space-y-3">
                        <Link href="/" :class="[isActiveLink('/') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-md', 'font-medium', 'py-1', 'px-2', 'text-center', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">Home</Link>
                        <Link href="/about" :class="[isActiveLink('/about') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-md', 'font-medium', 'py-1', 'px-2', 'text-center', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">About</Link>
                        <Link href="/products" :class="[isActiveLink('/products') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-md', 'font-medium', 'py-1', 'px-2', 'text-center', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">Products</Link>
                        <Link href="/faq" :class="[isActiveLink('/faq') ? 'bg-gray-200 text-neutral-900' : 'text-neutral-500', 'text-md', 'font-medium', 'py-1', 'px-2', 'text-center', 'rounded-md', 'hover:bg-gray-100', 'hover:text-neutral-700', 'transition-colors']">FAQ</Link>
                    </nav>
                </div>
            </div>
        </Transition>
        
    </header>

    <main class="mt-10 max-w-337.5 mx-auto space-y-10 pb-15">
        <slot />
    </main>
</template>