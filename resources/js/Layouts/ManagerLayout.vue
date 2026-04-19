<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import ManagerHeader from '@/Components/Nav/ManagerHeader.vue';
import SideBar from '@/Components/Nav/SideBar.vue';

const isLargeScreen = ref(false);
const sideBarOpen = ref(false);

const checkScreenSize = () => {
    isLargeScreen.value = window.innerWidth >= 1025;
    // Keep sidebar open by default on large screens, closed on mobile
    sideBarOpen.value = isLargeScreen.value;
};

onMounted(() => {
    checkScreenSize();
    window.addEventListener('resize', checkScreenSize);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkScreenSize);
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div v-if="sideBarOpen && !isLargeScreen" 
             @click="sideBarOpen = false"
             class="fixed inset-0 z-40 bg-black/50 transition-opacity duration-300 lg:hidden">
        </div>

        <SideBar :sideBarOpen="sideBarOpen" @toggle-sidebar="sideBarOpen = !sideBarOpen" />

        <ManagerHeader 
            :sideBarOpen="sideBarOpen" 
            :isLargeScreen="isLargeScreen"
            @toggle-sidebar="sideBarOpen = !sideBarOpen" 
        />

        <main :class="isLargeScreen && sideBarOpen ? 'lg:pl-60' : 'pl-0'" 
              class="pt-24 h-auto transition-all duration-300 ease-in-out pb-20">
            <div class="px-4 md:px-6 lg:px-10 lg:max-w-6xl lg:mx-auto space-y-6">
                <slot />
            </div>
        </main>
    </div>
</template>