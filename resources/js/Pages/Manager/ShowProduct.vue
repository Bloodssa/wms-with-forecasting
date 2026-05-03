<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import { Star, ShieldCheck, MapPin, Building2, ArrowLeftToLine } from 'lucide-vue-next';
import EmptyState from '@/Components/EmptyState.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import dayjs from 'dayjs';
import VueEasyLightbox from 'vue-easy-lightbox';

const props = defineProps({
    product: {
        type: Object,
        required: true
    },
    ratingStats: Object
});

// reply
const replyText = ref('');
const replyingTo = ref(null);
const editingId = ref(null);

const startEdit = (review) => {
    editingId.value = review.id;
    replyingTo.value = review.id; // open text area with the reply message
    replyText.value = review.staff_reply;
};

const cancelReply = () => {
    replyingTo.value = null;
    editingId.value = null;
    replyText.value = '';
};

const submitReply = (id) => {
    router.put(route('review-reply', id), {
        staff_reply: replyText.value,
    }, {
        preserveScroll: true,
         onSuccess: () => {
            cancelReply();
        }
    });
};

const deleteReply = (id) => {
    router.delete(route('review-reply-delete', id), {
        preserveScroll: true,
        onSuccess: () => {
            cancelReply();
        }
    });
};

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

    <Head :title="`Products ${props.product.name}`" />

    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <Link :href="route('products')"
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

            <div class="px-5 py-4">
                <p>{{ props.product.description }}</p>
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
                                            <div v-if="editingId !== review.id" class="flex gap-3">
                                                <button @click="startEdit(review)" class="text-xs text-blue-600 hover:underline font-medium">
                                                    Edit
                                                </button>
                                                <button @click="deleteReply(review.id)" class="text-xs text-red-600 hover:underline font-medium">
                                                    Delete
                                                </button>
                                            </div>
                                            <p v-if="editingId !== review.id" class="text-sm text-gray-700">{{ review.staff_reply }}</p>

                                            <!-- Edit -->
                                            <div v-if="editingId === review.id" class="mt-2 space-y-2">
                                                <textarea
                                                    v-model="replyText"
                                                    class="input-border w-full border border-neutral-900 rounded-md p-2 text-sm"
                                                ></textarea>

                                                <div class="flex gap-2">
                                                    <button
                                                        @click="submitReply(review.id)"
                                                        class="px-3 py-1 bg-neutral-900 text-white text-xs rounded-md">
                                                        Save
                                                    </button>

                                                    <button
                                                        @click="cancelReply"
                                                        class="px-3 py-1 border text-xs rounded-md">
                                                        Cancel
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="!review.staff_reply">
                                    <button class="text-xs text-blue-600 hover:underline" @click="replyingTo = review.id">
                                        Reply
                                    </button>
                                    <div v-if="replyingTo === review.id" class="mt-3 space-y-2">
                                        <textarea v-model="replyText" class="input-border w-full border border-neutral-900 rounded-md p-2 text-sm" placeholder="Write reply..."></textarea>

                                        <div class="flex gap-2">
                                            <button class="px-3 py-1 bg-neutral-900 text-white text-xs rounded-md"
                                                @click="submitReply(review.id)">
                                                Send
                                            </button>
                                            <button class="px-3 py-1 text-xs border rounded-md" @click="replyingTo = null">
                                                Cancel
                                            </button>
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
    </ManagerLayout>
    <vue-easy-lightbox
        :visible="isLightboxOpen"
        :imgs="activeImagesArray"
        :index="activeImageIndex"
        @hide="handleHide"
    />
</template>


