<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Star, ShieldCheck, MapPin, Building2, ArrowLeftToLine } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import dayjs from 'dayjs';
import VueEasyLightbox from 'vue-easy-lightbox';

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    product: {
        type: Object,
        required: true
    },
    ratingStats: Object
});

const isLightboxOpen = ref(false);
const activeImageIndex = ref(0);
const activeImagesArray = ref([]);

// store imaeg paths
const openPreview = (images, index = 0, isMainImage = false) => {
    if (isMainImage) {
        activeImagesArray.value = images;
    } else {
        activeImagesArray.value = images.map(img => `/storage/${img}`);
    }
    
    activeImageIndex.value = index;
    isLightboxOpen.value = true;
};

const handleHide = () => {
    isLightboxOpen.value = false;
};
</script>

<template>
    <GuestLayout title="Products" :canLogin="canLogin" :canRegister="canRegister">
        <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 mt-10 space-y-10 pb-15 bg-white">
            <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <Link :href="route('landing.products')"
                        class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
                        <ArrowLeftToLine class="w-5 h-5 text-neutral-900" />
                    </Link>
                    <div>
                        <h1 class="text-lg font-bold text-neutral-900 leading-tight">Go Back</h1>
                        <p class="text-xs text-neutral-500 font-medium">Product Lists</p>
                    </div>
                </div>
            </section>
            <div
                class="flex flex-col lg:flex-row my-6 w-full p-5 rounded-md border overflow-hidden border-gray-300 bg-white">
                <div class="w-full lg:w-2/5 shrink-0">
                    <div class="relative group cursor-pointer rounded-md border border-gray-300 overflow-hidden">
                        <div
                            class="max-h-85 aspect-square md:aspect-video lg:aspect-square w-full flex items-center justify-center p-6">
                            <img :src="props.product.image_url" :alt="props.product.name"
                                @click="openPreview([props.product.image_url], 0, true)"
                                class="w-full h-full object-contain mix-blend-multiply" 
                            />
                        </div>
                    </div>
                </div>

                <div class="flex-1 lg:pl-6 py-4 flex flex-col space-y-6">
                    <div class="mb-4">
                        <span class="text-sm font-semibold text-neutral-500">{{ props.product.brand }}</span>
                        <h1 class="text-2xl font-bold text-neutral-900 mt-1">{{ props.product.name }}</h1>
                        <div v-if="props.product.reviews.length" class="flex items-center gap-4 mt-2">
                            <div class="flex items-center gap-1 border-r border-gray-200 pr-4">
                                <span class="text-neutral-900 font-semibold border-neutral-900">{{
                                    props.ratingStats.average }}</span>
                                <div class="flex">
                                    <Star v-for="i in 5" :key="i"
                                        :class="['w-3.5 h-3.5', i <= Math.round(props.ratingStats.average) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                                </div>
                            </div>
                            <span class="text-sm text-neutral-500"><span class="text-neutral-900 font-medium">{{
                                props.ratingStats.total }}</span>
                                Ratings</span>
                        </div>
                        <div v-else>
                            <span class="text-[16px] text-neutral-900">No reviews at the moment</span>
                        </div>
                    </div>

                    <div class="py-4 rounded-md mb-6">
                        <p class="text-3xl font-bold text-neutral-900">₱{{ Number(props.product.price).toLocaleString() }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-4 text-sm">
                        <div class="flex items-start gap-3">
                            <ShieldCheck class="w-5 h-5 text-neutral-900 shrink-0" />
                            <div>
                                <h1 class="text-neutral-500 text-sm">Warranty Period</h1>
                                <p class="text-md text-neutral-900 font-semibold">{{ props.product.warranty_duration }}
                                    Months</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <Building2 class="w-5 h-5 text-neutral-900 shrink-0" />
                            <div>
                                <h1 class="text-neutral-500 text-sm">Service Center</h1>
                                <p class="text-md text-neutral-900 font-semibold">{{ props.product.service_center_name
                                    }}</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3 sm:col-span-2">
                            <MapPin class="w-5 h-5 text-neutral-900 shrink-0" />
                            <div>
                                <h1 class="text-neutral-500 text-sm">Center Address</h1>
                                <p class="text-md text-neutral-900 font-semibold">{{ props.product.service_center_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="rounded-md border border-gray-300 bg-white">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-neutral-900">Product Description</h2>
                </div>

                <div class="px-5 py-6">
                    <p class="whitespace-pre-wrap text-sm text-neutral-700 leading-relaxed tracking-wide">
                        {{ props.product.description }}
                    </p>
                </div>
            </div>
            <div class="bg-white border border-gray-300 rounded-md">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-neutral-900">Product Ratings</h2>
                </div>
                    <div class="px-6 py-5">
                        <div class="flex flex-col md:flex-row items-center justify-center gap-10 bg-neutral-50 p-6 rounded-md border border-neutral-300">
                        <div class="text-center md:pr-10">
                            <p class="text-5xl font-black text-neutral-900">{{ props.ratingStats.average }}</p>
                            <div class="flex justify-center items-center gap-1 my-2">
                                <Star v-for="i in 5" :key="i"
                                    :class="['w-5 h-5', i <= Math.round(props.ratingStats.average) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                            </div>
                            <p class="text-xs font-bold text-neutral-500 uppercase">{{ props.ratingStats.total }} Ratings
                            </p>
                        </div>
                    </div>
                    <div v-if="props.product.reviews.length" class="mt-4 divide-y divide-gray-300">
                        <div class="space-y-6 p-2">
                            <div v-for="review in props.product.reviews" :key="review.id"
                                class="flex gap-4 pb-6 border-b border-gray-300 last:border-0">
                                <Avatar :name="review.user.name.charAt(0)" class="h-8 w-8" />
                                <div class="flex-1">
                                    <div>
                                        <div class="space-y-1 flex-1 w-full">
                                            <div class="flex gap-6 items-start">
                                                <p class="text-xs font-bold text-neutral-900">
                                                    {{ review.user.name }}
                                                </p>
                                            </div>
                                            <div class="flex gap-0.5">
                                                <Star v-for="i in 5" :key="i"
                                                    :class="['w-3 h-3', i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                                            </div>
                                            <p class="text-xs text-neutral-500">{{ dayjs(review.created_at).format('MMM, DD YYYY') }}
                                            </p>
                                            <p class="text-sm text-neutral-700 mt-2">{{ review.comment }}</p>

                                            <div v-if="review.attachments && review.attachments.length"
                                                class="grid grid-cols-3 sm:grid-cols-5 gap-2 mt-3">
                                                <img v-for="(image, index) in review.attachments" 
                                                    :key="index"
                                                    :src="`/storage/${image}`"
                                                    @click="openPreview(review.attachments, index)"
                                                    class="w-full h-24 object-cover rounded-md border border-gray-300 cursor-pointer hover:opacity-80 transition" 
                                                />
                                            </div>
                                            <div v-if="review.edit_at" class="flex gap-1">
                                                <p class="text-sm text-neutral-500">Edited at: </p>
                                                <p class="text-sm text-neutral-900">{{ dayjs(review.edit_at).format('MMM, DD YYYY') }}</p>
                                            </div>
                                            <div v-if="review.staff_reply" class="mt-3 p-3 bg-gray-50 border border-gray-300 rounded-md">
                                                <p class="text-xs font-bold text-neutral-900">Seller Reply</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <template v-else>
                        <EmptyState :border="false" message="No reviews found" />
                    </template>
                </div>
            </div>
        </section>
    </GuestLayout>
    <vue-easy-lightbox
        :visible="isLightboxOpen"
        :imgs="activeImagesArray"
        :index="activeImageIndex"
        @hide="handleHide"
    />
</template>