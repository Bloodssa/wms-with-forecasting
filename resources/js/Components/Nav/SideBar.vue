<script setup>
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref, onUnmounted, computed } from 'vue';
import SidebarLink from './SidebarLink.vue';
import ApplicationLogo from '../Icons/ApplicationLogo.vue';
import MarkLogo from '../Icons/MarkLogo.vue';
import { TrendingUp } from 'lucide-vue-next';

defineProps({
    sideBarOpen: Boolean
});

const isMobile = ref(false);
const emit = defineEmits(['toggle-sidebar']);
const updateWidth = () => {
    isMobile.value = window.innerWidth < 1025; // check if its a desktop size
};

onMounted(() => {
    updateWidth(); // check init width
    window.addEventListener('resize', updateWidth); // if resize or phone size update widthh
});

// if its not mount on the #app remove event
onUnmounted(() => {
    window.removeEventListener('resize', updateWidth);
});

const page = usePage();
const isUrl = (url) => page.url.startsWith(url);

const can = computed(() => page.props.can ?? {});

const inquiryPages = [
    'Manager/WarrantyInquiries',
    'Manager/InquiryResponse'
];
</script>

<template>
    <aside
        class="fixed left-0 top-0 z-50 h-screen w-60 flex flex-col bg-white border-r border-gray-300 transition-transform duration-300 ease-in-out"
        :class="sideBarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="sticky top-0 z-10 flex items-center h-19 px-6 border-b border-gray-300 bg-white shrink-0">
            <div v-if="!isMobile">
                <ApplicationLogo class="w-40" />
            </div>
            <div v-else class="w-full flex justify-between items-center">
                <MarkLogo size="h-14 w-10" />

                <span @click="emit('toggle-sidebar')"
                    class="p-2 rounded-md border border-gray-300 hover:bg-gray-100 transition duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </span>
            </div>
        </div>

        <div class="flex flex-col overflow-y-auto no-scrollbar flex-1">
            <nav class="px-4 py-6">
                <h3 class="mb-4 ml-2 text-md font-semibold text-neutral-900 text-[15px]">Warranty Management</h3>
                <ul class="flex flex-col gap-1 -mt-2">
                    <li>
                        <SidebarLink :href="route('dashboard')" :active="page.component === 'Manager/Dashboard'">
                            <svg fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Dashboard'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            Dashboard
                        </SidebarLink>
                    </li>
                    <li v-if="can.canRegisterWarranty">
                        <SidebarLink :href="route('register-warranty')" :active="page.component === 'Manager/Register'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Register'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M183.5-183.5Q160-207 160-240t23.5-56.5Q207-320 240-320t56.5 23.5Q320-273 320-240t-23.5 56.5Q273-160 240-160t-56.5-23.5Zm0-240Q160-447 160-480t23.5-56.5Q207-560 240-560t56.5 23.5Q320-513 320-480t-23.5 56.5Q273-400 240-400t-56.5-23.5Zm0-240Q160-687 160-720t23.5-56.5Q207-800 240-800t56.5 23.5Q320-753 320-720t-23.5 56.5Q273-640 240-640t-56.5-23.5Zm240 0Q400-687 400-720t23.5-56.5Q447-800 480-800t56.5 23.5Q560-753 560-720t-23.5 56.5Q513-640 480-640t-56.5-23.5Zm240 0Q640-687 640-720t23.5-56.5Q687-800 720-800t56.5 23.5Q800-753 800-720t-23.5 56.5Q753-640 720-640t-56.5-23.5Zm-240 240Q400-447 400-480t23.5-56.5Q447-560 480-560t56.5 23.5Q560-513 560-480t-23.5 56.5Q513-400 480-400t-56.5-23.5ZM520-160v-123l221-220q9-9 20-13t22-4q12 0 23 4.5t20 13.5l37 37q8 9 12.5 20t4.5 22q0 11-4 22.5T863-380L643-160H520Zm300-263-37-37 37 37ZM580-220h38l121-122-18-19-19-18-122 121v38Zm141-141-19-18 37 37-18-19Z" />
                            </svg>
                            Register Warranty
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('warranties')"
                            :active="page.component === 'Manager/Warranties' || page.component === 'Manager/ShowWarranty'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Warranties' || page.component === 'Manager/ShowWarranty'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M200-200v-560 454-85 191Zm0 80q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v320h-80v-320H200v560h280v80H200Zm494 40L552-222l57-56 85 85 170-170 56 57L694-80ZM348.5-451.5Q360-463 360-480t-11.5-28.5Q337-520 320-520t-28.5 11.5Q280-497 280-480t11.5 28.5Q303-440 320-440t28.5-11.5Zm0-160Q360-623 360-640t-11.5-28.5Q337-680 320-680t-28.5 11.5Q280-657 280-640t11.5 28.5Q303-600 320-600t28.5-11.5ZM440-440h240v-80H440v80Zm0-160h240v-80H440v80Z" />
                            </svg>
                            Warranties
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('warranty-inquiries')"
                            :active="inquiryPages.includes(page.component)">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="inquiryPages.includes(page.component)
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M280-720v-40q0-33 23.5-56.5T360-840h240q33 0 56.5 23.5T680-760v40h28q24 0 43.5 13.5T780-672l94 216q3 8 4.5 16t1.5 16v184q0 33-23.5 56.5T800-160H160q-33 0-56.5-23.5T80-240v-184q0-8 1.5-16t4.5-16l94-216q9-21 28.5-34.5T252-720h28Zm80 0h240v-40H360v40Zm-80 240v-40h80v40h240v-40h80v40h96l-68-160H252l-68 160h96Zm0 80H160v160h640v-160H680v40h-80v-40H360v40h-80v-40Zm200-40Zm0-40Zm0 80Z" />
                            </svg>
                            Warranty Inquiries
                        </SidebarLink>
                    </li>
                </ul>

                <hr class="my-3 border-gray-300">
                <h3 class="mb-4 ml-2 text-md font-semibold text-neutral-900 text-[15px]">Resources</h3>

                <ul class="flex flex-col gap-1 -mt-2">
                    <li>
                        <SidebarLink :href="route('products')"
                            :active="page.component === 'Manager/Products' || page.component === 'Manager/ShowProduct'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/ShowProduct' || page.component === 'Manager/Products'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M160-120q-33 0-56.5-23.5T80-200v-560q0-33 23.5-56.5T160-840h560q33 0 56.5 23.5T800-760v80h80v80h-80v80h80v80h-80v80h80v80h-80v80q0 33-23.5 56.5T720-120H160Zm0-80h560v-560H160v560Zm80-80h200v-160H240v160Zm240-280h160v-120H480v120Zm-240 80h200v-200H240v200Zm240 200h160v-240H480v240ZM160-760v560-560Z" />
                            </svg>
                            Products
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('customers')" :active="page.component === 'Manager/Customers'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Customers'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M555-435q-35-35-35-85t35-85q35-35 85-35t85 35q35 35 35 85t-35 85q-35 35-85 35t-85-35ZM400-160v-76q0-21 10-40t28-30q45-27 95.5-40.5T640-360q56 0 106.5 13.5T842-306q18 11 28 30t10 40v76H400Zm86-80h308q-35-20-74-30t-80-10q-41 0-80 10t-74 30Zm182.5-251.5Q680-503 680-520t-11.5-28.5Q657-560 640-560t-28.5 11.5Q600-537 600-520t11.5 28.5Q623-480 640-480t28.5-11.5ZM640-520Zm0 280ZM120-400v-80h320v80H120Zm0-320v-80h480v80H120Zm324 160H120v-80h360q-14 17-22.5 37T444-560Z" />
                            </svg>
                            Customers
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('manager.warranty-forecast')"
                            :active="page.component === 'Manager/WarrantyForecast' || page.component === 'Manager/WarrantyForecastProduct'">
                            <TrendingUp class="w-5 h-5 transition-colors" :class="page.component === 'Manager/WarrantyForecast' || page.component === 'Manager/WarrantyForecastProduct'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'" />
                            Warranty Forecast
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('reports')" :active="page.component === 'Manager/Reports'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Reports'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M280-280h80v-200h-80v200Zm320 0h80v-400h-80v400Zm-160 0h80v-120h-80v120Zm0-200h80v-80h-80v80ZM200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm0-80h560v-560H200v560Zm0-560v560-560Z" />
                            </svg>
                            Reports
                        </SidebarLink>
                    </li>
                </ul>

                <hr class="my-3 border-gray-300">
                <h3 class="mb-4 ml-2 text-md font-semibold text-neutral-900 text-[15px]">{{ can.viewAdminArea ?
                    'Administration' : 'Settings' }}</h3>

                <ul class="flex flex-col gap-1 -mt-2">
                    <li v-if="can.viewAdminArea">
                        <SidebarLink :href="route('staff-accounts')"
                            :active="page.component === 'Manager/StaffAccounts'">
                            <svg fill="currentColor" viewBox="0 -960 960 960" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/StaffAccounts'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path
                                    d="M440-120v-80h320v-284q0-117-81.5-198.5T480-764q-117 0-198.5 81.5T200-484v244h-40q-33 0-56.5-23.5T80-320v-80q0-21 10.5-39.5T120-469l3-53q8-68 39.5-126t79-101q47.5-43 109-67T480-840q68 0 129 24t109 66.5Q766-707 797-649t40 126l3 52q19 9 29.5 27t10.5 38v92q0 20-10.5 38T840-249v49q0 33-23.5 56.5T760-120H440ZM331.5-411.5Q320-423 320-440t11.5-28.5Q343-480 360-480t28.5 11.5Q400-457 400-440t-11.5 28.5Q377-400 360-400t-28.5-11.5Zm240 0Q560-423 560-440t11.5-28.5Q583-480 600-480t28.5 11.5Q640-457 640-440t-11.5 28.5Q617-400 600-400t-28.5-11.5ZM241-462q-7-106 64-182t177-76q89 0 156.5 56.5T720-519q-91-1-167.5-49T435-698q-16 80-67.5 142.5T241-462Z" />
                            </svg>
                            Staff Accounts
                        </SidebarLink>
                    </li>
                    <li>
                        <SidebarLink :href="route('manager.profile')" :active="page.component === 'Manager/Profile'">
                            <svg fill="currentColor" class="w-5 h-5 transition-colors" :class="page.component === 'Manager/Profile'
                                ? 'text-neutral-900'
                                : 'text-neutral-500 group-hover:text-neutral-900'">
                                <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Profile
                        </SidebarLink>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
</template>