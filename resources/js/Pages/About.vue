<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import grid from '../../images/grid-01.svg';
import { 
    Target, 
    ShieldCheck, 
    Cpu, 
    FileText, 
    Zap, 
    ArrowRight 
} from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean
});

const objectives = [
    {
        icon: Target,
        title: 'Mission',
        description: 'To bridge the gap between computer retail shops and customers through a transparent, automated warranty tracking ecosystem.'
    },
    {
        icon: Zap,
        title: 'Efficiency',
        description: 'Replacing time-consuming manual logs with a real-time digital dashboard that verifies claims in seconds.'
    },
    {
        icon: ShieldCheck,
        title: 'Reliability',
        description: 'Ensuring every hardware component is backed by a verifiable history, protecting both the consumer and the provider.'
    }
];
</script>

<template>
    <GuestLayout title="About Us" :canLogin="canLogin" :canRegister="canRegister">
        <section class="relative py-20 bg-white border-b border-gray-300 overflow-hidden">
            <div class="absolute inset-0 z-0 flex justify-between opacity-20 pointer-events-none">
                <img :src="grid" class="h-full object-cover" />
                <img :src="grid" class="h-full object-cover scale-x-[-1]" />
            </div>

            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h1 class="text-5xl md:text-7xl font-bold tracking-tighter text-black mb-8 leading-tight">
                        Redefining Hardware <br /> 
                        <span class="text-neutral-400">Reliability Standards.</span>
                    </h1>
                    <p class="text-xl text-neutral-500 leading-relaxed">
                        In an era where technology drives productivity, the maintenance of computer hardware shouldn't be a burden. 
                        WMS was built to solve the complexities of warranty management through digitalization.
                    </p>
                </div>
            </div>
        </section>

        <section class="py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                    <div class="space-y-6">
                        <h2 class="text-4xl font-bold tracking-tighter text-black">The Problem with Manual Tracking</h2>
                        <p class="text-neutral-600 leading-relaxed">
                            Many retail shops still rely on paper receipts and manual spreadsheets to monitor product warranties. 
                            This leads to misplaced records, missed expiry dates, and slow claim verifications that frustrate both technicians and customers.
                        </p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="obj in objectives" :key="obj.title" class="p-8 bg-white border border-gray-300 rounded-md">
                            <component :is="obj.icon" class="w-8 h-8 text-black mb-4" />
                            <h3 class="text-lg font-bold text-black mb-2">{{ obj.title }}</h3>
                            <p class="text-sm text-neutral-500 leading-relaxed">{{ obj.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white border-t border-gray-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl font-bold tracking-tighter text-black mb-6">Built for Modern Tech</h2>
                    <p class="text-neutral-500 max-w-2xl mx-auto text-lg">
                        We provide the technical background needed to scale operations without the administrative headache.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                    <div class="space-y-4 border border-gray-300 p-6 rounded-md">
                        <div class="text-4xl font-bold text-black">01</div>
                        <h3 class="text-xl font-bold tracking-tight">Centralized Database</h3>
                        <p class="text-neutral-500 text-sm leading-relaxed">
                            A secure cloud-based repository for all hardware serial numbers, purchase dates, and warranty terms.
                        </p>
                    </div>
                    <div class="space-y-4 border border-gray-300 p-6 rounded-md">
                        <div class="text-4xl font-bold text-black">02</div>
                        <h3 class="text-xl font-bold tracking-tight">Automated Lifecycle</h3>
                        <p class="text-neutral-500 text-sm leading-relaxed">
                            From initial purchase to repair history and eventual expiry, every stage is tracked and notified automatically.
                        </p>
                    </div>
                    <div class="space-y-4 border border-gray-300 p-6 rounded-md">
                        <div class="text-4xl font-bold text-black">03</div>
                        <h3 class="text-xl font-bold tracking-tight">Verified Feedback</h3>
                        <p class="text-neutral-500 text-sm leading-relaxed">
                            Integrity-driven reviews that only allow feedback from customers with verified service claims.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>