<script setup>
import { Head, Link } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import { Star, ShieldCheck, MapPin, Building2, ChevronRight, ArrowLeftToLine } from 'lucide-vue-next';
import Avatar from '@/Components/Icons/Avatar.vue';
import EmptyState from '@/Components/EmptyState.vue';
import ProductCard from '@/Components/ProductCard.vue';
import dayjs from 'dayjs';
import VueEasyLightbox from 'vue-easy-lightbox';
import { ref } from 'vue'

const props = defineProps({
    product: {
        type: Object,
        default: () => ({})
    },
    relatedProducts: {
        type: Object,
        default: () => ({})
    },
    ratingStats: Object,
    relatedProducts: {
        type: Object,
        default: () => ({})
    }
});

const isLightboxOpen = ref(false);
const activeImageIndex = ref(0);
const activeImagesArray = ref([]);

const openReviewPreview = (attachments, index) => {
    activeImagesArray.value = attachments.map(path => `/storage/${path}`);
    activeImageIndex.value = index;
    isLightboxOpen.value = true;
};

const openSinglePreview = (url) => {
    activeImagesArray.value = [url];
    activeImageIndex.value = 0;
    isLightboxOpen.value = true;
};

const handleHide = () => {
    isLightboxOpen.value = false;
};
</script>

<template>

    <Head :title="`Product - ${props.product.name}`" />
    <CustomerLayout>
        <div class="max-w-6xl mx-auto py-8 space-y-6 -mt-10 md:-mt-4">
            <div class="flex items-center gap-4 mb-8">
                <Link :href="route('view-products')"
                    class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
                    <ArrowLeftToLine class="w-5 h-5 text-neutral-900" />
                </Link>
                <div>
                    <h1 class="text-lg font-bold text-neutral-900 leading-tight">Go Back</h1>
                    <p class="text-xs text-neutral-500 font-medium">Product Lists</p>
                </div>
            </div>
            <div
                class="flex flex-col lg:flex-row my-6 w-full p-5 rounded-md border overflow-hidden border-gray-300 bg-white">
                <div class="w-full lg:w-2/5 shrink-0">
                    <div class="relative group cursor-pointer rounded-md border border-gray-300 overflow-hidden"
                        @click="openSinglePreview(props.product.image_url)">
                        <div
                            class="max-h-85 aspect-square md:aspect-video lg:aspect-square w-full flex items-center justify-center p-6 hover:bg-gray-100">
                            <img :src="props.product.image_url" :alt="props.product.name"
                                class="w-full h-full object-contain mix-blend-multiply transition-transform duration-300" />
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
                        <p class="text-3xl font-bold text-neutral-900">₱{{ Number(props.product.price).toLocaleString()
                            }}</p>
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
                                <p class="text-md text-neutral-900 font-semibold">{{
                                    props.product.service_center_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-gray-300 bg-white">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-neutral-900">Product Description</h2>
                </div>

                <div class="px-5 py-4">
                    <p>{{ props.product.description }}</p>
                </div>
            </div>

            <div class="rounded-md border border-gray-300 bg-white">
                <div class="flex justify-between items-center px-5 py-4 border-b border-gray-300">
                    <h2 class="text-lg font-bold text-neutral-900">Product Ratings</h2>
                    <Link :href="route('product-reviews', { id: props.product.id })"
                        class="text-[12px] font-bold text-neutral-900 flex items-center hover:underline">
                        View All
                        <ChevronRight class="w-4 h-4" />
                    </Link>
                </div>

                <div class="space-y-6 p-6">
                    <div v-if="props.product.reviews.length" v-for="review in props.product.reviews" :key="review.id"
                        class="flex gap-4 pb-6 border-b border-gray-300 last:border-0">
                        <Avatar :name="review.user.name.charAt(0)" class="h-8 w-8" />
                        <div class="space-y-1">
                            <p class="text-xs font-bold text-neutral-900">{{ review.user.name }}</p>
                            <div class="flex gap-0.5">
                                <Star v-for="i in 5" :key="i"
                                    :class="['w-3 h-3', i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                            </div>
                            <p class="text-xs text-neutral-500">{{ dayjs(review.created_at).format('MMM, DD YYYY') }}
                            </p>
                            <p class="text-sm text-neutral-700 mt-2">{{ review.comment }}</p>

                            <div v-if="review.attachments && review.attachments.length"
                                class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">
                                <img v-for="(image, index) in review.attachments" :key="index"
                                    :src="`/storage/${image}`"
                                    class="w-full h-24 object-cover rounded-md border border-gray-200 cursor-pointer hover:brightness-90 transition"
                                    @click="openReviewPreview(review.attachments, index)" />
                            </div>
                            <div v-if="review.edit_at" class="flex gap-1">
                                <p class="text-sm text-neutral-500">Edited at: </p>
                                <p class="text-sm text-neutral-900">{{ dayjs(review.edit_at).format('MMM, DD YYYY') }}
                                </p>
                            </div>
                            <div v-if="review.staff_reply"
                                class="mt-3 p-3 bg-gray-50 border border-gray-300 rounded-md">
                                <p class="text-xs font-bold text-neutral-900">Seller Reply</p>
                                <p class="text-sm text-gray-700">{{ review.staff_reply }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <EmptyState height="h-55" :border="false" message="No reviews found" />
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <h2 class="text-lg font-bold text-neutral-900">You May Also Like</h2>
                <div v-if="props.relatedProducts.length"
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    <div v-for="product in props.relatedProducts" :key="product.id"
                        class="bg-white border border-gray-300 rounded-md">
                        <ProductCard :product="product" />
                    </div>
                </div>
            </div>
        </div>
    </CustomerLayout>
    <vue-easy-lightbox :visible="isLightboxOpen" :imgs="activeImagesArray" :index="activeImageIndex"
        @hide="handleHide" />
</template>