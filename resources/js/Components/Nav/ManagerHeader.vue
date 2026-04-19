<script setup>
import { Link, usePage, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue';
import Avatar from '@/Components/Icons/Avatar.vue';

const vClickOutside = {
    mounted(el, binding) {
        el.clickOutsideEvent = (event) => {
            // check if the click event was outside of the element and its children of the element
            if(!(el === event.target || el.contains(event.target))) {
                binding.value();
            }
        };
        document.addEventListener("click", el.clickOutsideEvent);
    },
    unmounted(el) {
        document.removeEventListener("click", el.clickOutsideEvent);
    }
};

const props = defineProps({
    sideBarOpen: Boolean,
    isLargeScreen: Boolean
});

const emit = defineEmits(['toggle-sidebar']); // nofity the other component is this is toggled to close asidebar
const page = usePage();
const user = computed(() => page.props.auth.user);
const dropDownOpen = ref(false);

const logout = () => router.post(route('logout'));
</script>

<template>
    <header
        class="fixed top-0 right-0 z-30 flex h-19 items-center justify-between border-b border-gray-300 bg-white px-4 transition-all duration-300 ease-in-out md:px-8"
        :class="isLargeScreen && sideBarOpen ? 'lg:left-60' : 'left-0'">
        <div class="flex items-center gap-4">
            <button @click.stop="emit('toggle-sidebar')"
                class="w-11 h-11 p-2 border border-gray-300 rounded-md hover:bg-gray-50 transition-all cursor-pointer">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M3.25 6C3.25 5.58579 3.58579 5.25 4 5.25L20 5.25C20.4142 5.25 20.75 5.58579 20.75 6C20.75 6.41421 20.4142 6.75 20 6.75L4 6.75C3.58579 6.75 3.25 6.41422 3.25 6ZM3.25 18C3.25 17.5858 3.58579 17.25 4 17.25L20 17.25C20.4142 17.25 20.75 17.5858 20.75 18C20.75 18.4142 20.4142 18.75 20 18.75L4 18.75C3.58579 18.75 3.25 18.4142 3.25 18ZM4 11.25C3.58579 11.25 3.25 11.5858 3.25 12C3.25 12.4142 3.58579 12.75 4 12.75L12 12.75C12.4142 12.75 12.75 12.4142 12 11.25L4 11.25Z" />
                </svg>
            </button>
        </div>

        <div class="relative">
            <div class="flex space-x-3 items-center">
                <button
                    class="border border-gray-300 p-2 rounded-full hover:bg-gray-100 transition-colors duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="text-neutral-900" width="23" height="23"
                        fill="currentColor" viewBox="0 0 16 16">
                        <path
                            d="M8 16a2 2 0 0 0 2-2H6a2 2 0 0 0 2 2M8 1.918l-.797.161A4 4 0 0 0 4 6c0 .628-.134 2.197-.459 3.742-.16.767-.376 1.566-.663 2.258h10.244c-.287-.692-.502-1.49-.663-2.258C12.134 8.197 12 6.628 12 6a4 4 0 0 0-3.203-3.92zM14.22 12c.223.447.481.801.78 1H1c.299-.199.557-.553.78-1C2.68 10.2 3 6.88 3 6c0-2.42 1.72-4.44 4.005-4.901a1 1 0 1 1 1.99 0A5 5 0 0 1 13 6c0 .88.32 4.2 1.22 6" />
                    </svg>
                </button>

                <div class="flex items-center text-black space-x-2" @click.stop="dropDownOpen = !dropDownOpen">
                    <Avatar :name="user.name" class="h-9 w-9" />

                    <span class="mr-1 block cursor-pointer font-semibold">
                        {{ user.role.substring(0, 1).toUpperCase() + user.role.substring(1) }}
                    </span>

                    <svg :class="{ 'rotate-180': dropDownOpen }" class="stroke-black transition-transform duration-200"
                        width="18" height="20" viewBox="0 0 18 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M4.3125 8.65625L9 13.3437L13.6875 8.65625" stroke="" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
            
            <div v-if="dropDownOpen" v-click-outside="() => dropDownOpen = false"
                class="absolute right-0 mt-4.25 flex w-65 flex-col rounded-md border border-gray-300 bg-white p-2">
                <div>
                    <span class="font-medium block text-black">
                        {{ user.name }}
                    </span>
                    <span class="text-theme-xs block text-gray-500">
                        {{ user.email }}
                    </span>
                </div>

                <ul class="flex flex-col mt-2">
                    <li>
                        <Link :href="route('profile.edit')"
                            class="group flex items-center gap-3 rounded-md px-3 py-2 font-normal text-black hover:bg-gray-100 hover:text-black">
                        <svg fill="currentColor" class="h-6 w-6" viewBox="0 0 24 24">
                            <path
                                d="M12 3.5C7.30558 3.5 3.5 7.30558 3.5 12C3.5 14.1526 4.3002 16.1184 5.61936 17.616C6.17279 15.3096 8.24852 13.5955 10.7246 13.5955H13.2746C15.7509 13.5955 17.8268 15.31 18.38 17.6167C19.6996 16.119 20.5 14.153 20.5 12C20.5 7.30558 16.6944 3.5 12 3.5ZM17.0246 18.8566V18.8455C17.0246 16.7744 15.3457 15.0955 13.2746 15.0955H10.7246C8.65354 15.0955 6.97461 16.7744 6.97461 18.8455V18.856C8.38223 19.8895 10.1198 20.5 12 20.5C13.8798 20.5 15.6171 19.8898 17.0246 18.8566ZM2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11.9991 7.25C10.8847 7.25 9.98126 8.15342 9.98126 9.26784C9.98126 10.3823 10.8847 11.2857 11.9991 11.2857C13.1135 11.2857 14.0169 10.3823 14.0169 9.26784C14.0169 8.15342 13.1135 7.25 11.9991 7.25ZM8.48126 9.26784C8.48126 7.32499 10.0563 5.75 11.9991 5.75C13.9419 5.75 15.5169 7.32499 15.5169 9.26784C15.5169 11.2107 13.9419 12.7857 11.9991 12.7857C10.0563 12.7857 8.48126 11.2107 8.48126 9.26784Z" />
                        </svg>
                        Edit Profile
                        </Link>
                    </li>
                    <li>
                        <button @click="logout"
                            class="group w-full flex items-center gap-3 rounded-md px-3 py-2 font-normal text-black hover:bg-gray-100 hover:text-black">
                            <svg fill="currentColor" class="h-6 w-6" viewBox="0 0 24 24">
                                <path
                                    d="M15.1007 19.247C14.6865 19.247 14.3507 18.9112 14.3507 18.497L14.3507 14.245H12.8507V18.497C12.8507 19.7396 13.8581 20.747 15.1007 20.747H18.5007C19.7434 20.747 20.7507 19.7396 20.7507 18.497L20.7507 5.49609C20.7507 4.25345 19.7433 3.24609 18.5007 3.24609H15.1007C13.8581 3.24609 12.8507 4.25345 12.8507 5.49609V9.74501L14.3507 9.74501V5.49609C14.3507 5.08188 14.6865 4.74609 15.1007 4.74609L18.5007 4.74609C18.9149 4.74609 19.2507 5.08188 19.2507 5.49609L19.2507 18.497C19.2507 18.9112 18.9149 19.247 18.5007 19.247H15.1007ZM3.25073 11.9984C3.25073 12.2144 3.34204 12.4091 3.48817 12.546L8.09483 17.1556C8.38763 17.4485 8.86251 17.4487 9.15549 17.1559C9.44848 16.8631 9.44863 16.3882 9.15583 16.0952L5.81116 12.7484L16.0007 12.7484C16.4149 12.7484 16.7507 12.4127 16.7507 11.9984C16.7507 11.5842 16.4149 11.2484 16.0007 11.2484L5.81528 11.2484L9.15585 7.90554C9.44864 7.61255 9.44847 7.13767 9.15547 6.84488C8.86248 6.55209 8.3876 6.55226 8.09481 6.84525L3.52309 11.4202C3.35673 11.5577 3.25073 11.7657 3.25073 11.9984Z" />
                            </svg>
                            Sign Out
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>
</template>