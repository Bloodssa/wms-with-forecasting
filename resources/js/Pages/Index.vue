<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import grid from '../../images/grid-01.svg';
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Minus, ShieldCheck, Bell, History, MessageSquare, Database, ClipboardList, UserCheck, FileSearch, X, CheckCircle2 } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean
});

const benifits = ref([
    {
        icon: Database,
        title: 'Centralized Records',
        subtitle: 'Eliminate manual spreadsheets. Store all purchase details and warranty statuses in a secure, digital database'
    },
    {
        icon: Bell,
        title: 'Automated Alerts',
        subtitle: 'Never miss a warranty with automated notifications sent 30 days before a product warranty expires'
    },
    {
        icon: ClipboardList,
        title: 'Authorized History',
        subtitle: 'Maintain a verified history of every repair or replacement, providing transparency for both shop and customer'
    },
    {
        icon: UserCheck,
        title: 'Improve Productivity',
        subtitle: 'Eliminate manual spreadsheets. Store all purchase details and warranty statuses in a secure, digital database. Ensure feedback quality with a system that only allows reviews from customers with verified warranty claims'
    },
    {
        icon: MessageSquare,
        title: 'Inquiry Discussions',
        subtitle: 'Report hardware issues directly via a ticketing system where technicians will provide updates'
    },
    {
        icon: FileSearch,
        title: 'Track History',
        subtitle: 'Document every step of a resolution process, ensuring all technical inquiries are resolved, closed, replaced'
    }
]);

const faqs = ref([
    {
        question: "How does the system notify me of expiring warranties?",
        answer: "WMS automatically monitors your products and sends a Gmail notification to both the customer once a warranty reaches its 30-day near-expiry period..",
        open: true
    },
    {
        question: "Can I track multiple products under one account?",
        answer: "Yes. Our centralized dashboard allows you to monitor multiple computer components, keeping all purchase dates and service histories in one place..",
        open: false
    },
    {
        question: "How do I submit a inquiry or claim?",
        answer: "Users can submit an inquiry through the inquiry page for a specific product. This opens a direct discussion ticket where technicians provide updates until the issue is documented as resolved..",
        open: false
    },
    {
        question: "Are the product reviews verified?",
        answer: "Absolutely. To maintain system integrity and prevent fake feedback, only customers with a verified and claimed warranty for a specific products",
        open: false
    }
]);
</script>

<template>
    <GuestLayout title="Home" :canLogin="canLogin" :canRegister="canRegister">
        <section
            class="relative min-h-[90vh] md:min-h-[70vh] flex flex-col items-center justify-center bg-white border-b border-gray-300 overflow-hidden">
            <div class="absolute inset-0 z-0 flex justify-between opacity-30 pointer-events-none">
                <img :src="grid" class="h-full object-cover" />
                <img :src="grid" class="h-full object-cover scale-x-[-1]" />
            </div>
            <div class="hidden lg:block absolute inset-0 z-10 pointer-events-none overflow-hidden max-w-7xl mx-auto">
                <div class="absolute top-[20%] left-[10%] rotate-12 animate-floating">
                    <div class="p-4 bg-white border border-gray-300 rounded-md">
                        <ShieldCheck class="w-6 h-6 text-black" />
                    </div>
                </div>
                <div class="absolute bottom-[25%] left-[15%] -rotate-6 animate-floating-delayed">
                    <div class="p-4 bg-white border border-gray-300 rounded-md">
                        <History class="w-6 h-6 text-black" />
                    </div>
                </div>
                <div class="absolute top-[15%] right-[10%] rotate-12 animate-floating-delayed">
                    <div class="p-4 bg-white border border-gray-300 rounded-md">
                        <Bell class="w-6 h-6 text-black" />
                    </div>
                </div>
                <div class="absolute bottom-[20%] right-[12%] -rotate-6 animate-floating">
                    <div class="p-4 bg-white border border-gray-300 rounded-md">
                        <MessageSquare class="w-6 h-6 text-black" />
                    </div>
                </div>
            </div>

            <div class="relative z-20 text-center px-4 max-w-4xl">
                <h1 class="text-5xl md:text-7xl font-bold tracking-tighter text-black mb-8 leading-[1.1]">
                    Track Product <span class="underline decoration-gray-300 underline-offset-8">Warranties</span>,
                    Claims, and Reliability
                </h1>
                <p class="max-w-2xl mx-auto text-lg text-neutral-500 mb-10 leading-relaxed">
                    A centralized dashboard that allows computer retail stores and customers to replace manual logs with
                    organized digital records. Monitor warranty periods and handle service claims efficiently.
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <Link :href="route('register')"
                        class="px-8 py-4 bg-black text-white rounded-md font-bold hover:bg-neutral-800 transition-all hover:shadow-black/10">
                        Start Monitoring Warranty
                    </Link>
                </div>
            </div>
        </section>
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-20">
                    <h2 class="text-5xl md:text-6xl font-bold tracking-tighter text-black mb-6">
                        Benefits That Improve <br /> Warranty Monitoring
                    </h2>
                    <p class="text-neutral-500 max-w-2xl mx-auto text-lg leading-relaxed">
                        Our platform helps computer retail shops transition from manual logs to a centralized
                        digital system.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="(benifit, index) in benifits" :key="index"
                        class="bg-white border border-gray-300 rounded-md p-8 hover:border-neutral-900">
                        <div class="w-12 h-12 p-3 rounded-md border border-gray-300 flex items-center justify-center">
                            <component :is="benifit.icon" class="w-6 h-6 text-black" />
                        </div>
                        <h3 class="text-xl font-semibold mt-2">{{ benifit.title }}</h3>
                        <p class="text-neutral-500 text-sm leading-relaxed mt-2">
                            {{ benifit.subtitle }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="py-24 bg-white border-t border-gray-300">
            <div class="text-center mb-16 px-4">
                <div
                    class="inline-block px-3 py-1 mb-4 text-xs font-semibold border border-gray-300 rounded-md text-neutral-900">
                    WHY WMS?
                </div>
                <h2 class="text-5xl font-bold tracking-tighter text-black mb-6">
                    A Smarter Way To <br /> Monitor Warranties
                </h2>
                <p class="text-neutral-500 max-w-xl mx-auto">
                    Turn scattered paper receipts and manual logs into clear, organized digital records that work for
                    everyone.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start px-5 md:px-0 max-w-5xl mx-auto">
                <div class="p-10 border border-gray-300 rounded-xl bg-white">
                    <h3 class="text-xl font-bold mb-8 text-neutral-400">Manual Tracking</h3>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4 text-neutral-500">
                            <X class="w-5 h-5 text-red-700 shrink-0 mt-0.5" />
                            <span class="text-sm">Warranty tracked on paper or spreadsheets</span>
                        </li>
                        <li class="flex items-start gap-4 text-neutral-500">
                            <X class="w-5 h-5 text-red-700 shrink-0 mt-0.5" />
                            <span class="text-sm">Expiry dates checked manually.</span>
                        </li>
                        <li class="flex items-start gap-4 text-neutral-500">
                            <X class="w-5 h-5 text-red-700 shrink-0 mt-0.5" />
                            <span class="text-sm">No real-time visibility for technicians.</span>
                        </li>
                        <li class="flex items-start gap-4 text-neutral-500">
                            <X class="w-5 h-5 text-red-700 shrink-0 mt-0.5" />
                            <span class="text-sm">Claims take days to verify manually.</span>
                        </li>
                        <li class="flex items-start gap-4 text-neutral-500">
                            <X class="w-5 h-5 text-red-700 shrink-0 mt-0.5" />
                            <span class="text-sm">Hard to track recurring product failures.</span>
                        </li>
                    </ul>
                </div>
                <div class="p-10 border-2 border-black rounded-xl bg-white scale-105">
                    <h3 class="text-xl font-bold mb-8 text-black">WMS Platform</h3>
                    <ul class="space-y-6">
                        <li class="flex items-start gap-4 text-black">
                            <CheckCircle2 />
                            <span class="text-sm font-medium">All warranty data in one centralized platform.</span>
                        </li>
                        <li class="flex items-start gap-4 text-black">
                            <CheckCircle2 />
                            <span class="text-sm font-medium">Automatic near-expiry email notifications.</span>
                        </li>
                        <li class="flex items-start gap-4 text-black">
                            <CheckCircle2 />
                            <span class="text-sm font-medium">Real-time dashboard for technicians and users.</span>
                        </li>
                        <li class="flex items-start gap-4 text-black">
                            <CheckCircle2 />
                            <span class="text-sm font-medium">Instant verification of claim eligibility.</span>
                        </li>
                        <li class="flex items-start gap-4 text-black">
                            <CheckCircle2 />
                            <span class="text-sm font-medium">Detailed history of every service resolution.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="border-t border-gray-300 py-24 ">
            <div class="text-center mb-16 px-4">
                <h2 class="text-4xl font-bold tracking-tighter text-black mb-4">
                    Common Questions
                </h2>
                <p class="text-neutral-500">
                    Everything you need to know about modernizing your warranty management.
                </p>
            </div>

            <div class="space-y-4">
                <div class="max-w-3xl mx-auto px-4 space-y-4">
                    <div v-for="(faq, index) in faqs" :key="index" :class="{ 'border-black': faq.open }"
                        class="bg-white border border-gray-300 rounded-md overflow-hidden transition-all duration-300">
                        <button @click="faq.open = !faq.open"
                            class="w-full p-6 flex justify-between items-center text-left hover:bg-neutral-50 transition-colors">
                            <span class="font-semibold text-black">{{ faq.question }}</span>
                            <div class="shrink-0 ml-4">
                                <Plus v-if="!faq.open" class="w-5 h-5 text-neutral-400" />
                                <Minus v-else class="w-5 h-5 text-black" />
                            </div>
                        </button>

                        <div v-show="faq.open"
                            class="px-6 pb-6 text-sm text-neutral-500 leading-relaxed border-t border-neutral-100 pt-4">
                            {{ faq.answer }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </GuestLayout>
</template>

<style scoped>
@keyframes floating {

    0%,
    100% {
        transform: translateY(0px);
    }

    50% {
        transform: translateY(-20px);
    }
}

.animate-floating {
    animation: floating 4s ease-in-out infinite;
}

.animate-floating-delayed {
    animation: floating 5s ease-in-out 1s infinite;
}
</style>