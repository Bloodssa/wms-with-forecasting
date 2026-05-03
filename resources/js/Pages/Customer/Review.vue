<script setup>
import { ref, computed } from 'vue';
import dayjs from 'dayjs';
import { Head, Link, useForm } from '@inertiajs/vue3';
import CustomerLayout from '@/Layouts/CustomerLayout.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import { Star, ArrowLeftToLine, CircleX, User, Filter } from 'lucide-vue-next';
import BaseModal from '@/Components/Modals/BaseModal.vue';
import EmptyState from '@/Components/EmptyState.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import InputError from '@/Components/Forms/InputError.vue';
import VueEasyLightbox from 'vue-easy-lightbox';

const props = defineProps({
    auth: Object,
    product: {
        type: Object,
        default: () => ({})
    },
    reviews: {
        type: Object,
        default: () => ({})
    },
    canRateProduct: {
        type: Boolean,
        default: false
    },
    ratingStats: Object,
    myReview: Object
});

const selectedStar = ref('all');

const handleFiles = (event) => {
    const selectedFiles = Array.from(event.target.files);

    selectedFiles.forEach(file => {
        files.value.push(file);
        form.attachments.push(file);

        const reader = new FileReader();
        reader.onload = (e) => {
            images.value.push({
                src: e.target.result,
                type: file.type
            });
        };
        reader.readAsDataURL(file);
    });

    syncFiles();
}

const removedExisting = ref([]);

const removeImage = (index) => {
    const img = images.value[index];

    if (img.existing) {
        removedExisting.value.push(img.path); // track the remove attchment
    } else {
        files.value.splice(index, 1);
        form.attachments.splice(index, 1);
    }

    images.value.splice(index, 1);
    syncFiles();
}

const fileInput = ref(null);

const syncFiles = () => {
    const dt = new DataTransfer();

    files.value.forEach(file => {
        dt.items.add(file);
    })

    if (fileInput.value) {
        fileInput.value.files = dt.files;
    }
};

const form = useForm({
    rating: null,
    comment: '',
    attachments: [],
    removed_attachments: [] // paths of the deleteimage index
});

const rateModal = ref(false);
const isEditing = ref(false);
const hoverRating = ref(0);
const files = ref([]);
const images = ref([]);

// edit modal
const editReview = () => {
    isEditing.value = true;
    form.rating = props.myReview.rating;
    form.comment = props.myReview.comment;

    // load images or the attachment so user can edit
    images.value = props.myReview.attachments.map(path => ({
        src: `/storage/${path}`,
        type: 'image/*',
        existing: true,
        path: path
    }));

    // for new uploads array
    files.value = [];
    form.attachments = [];

    rateModal.value = true;
};

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    images.value = [];
    rateModal.value = true;
};

const submitReview = () => {
    if (isEditing.value) { // patch
        form.removed_attachments = removedExisting.value; // attach the remove attachments
        form.put(route('update-review', props.myReview.id), {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    } else { // post to make a rating
        form.post(route('store-review', props.product.id), {
            forceFormData: true,
            onSuccess: () => closeModal(),
        });
    }
};

const closeModal = () => {
    form.reset();
    images.value = [];
    files.value = [];
    removedExisting.value = [];
    rateModal.value = false;
};

const reviews = computed(() => props.reviews);

// filter star
const filteredReviews = computed(() => {
    if (selectedStar.value === 'all') {
        return reviews.value;
    }

    return reviews.value.filter(
        review => review.rating === selectedStar.value
    );
});

const isLightboxOpen = ref(false);
const activeImageIndex = ref(0);
const activeImagesArray = ref([]);

const openPreview = (attachments, index) => {
    activeImagesArray.value = attachments.map(path => `/storage/${path}`);
    activeImageIndex.value = index;
    isLightboxOpen.value = true;
};

const handleHide = () => {
    isLightboxOpen.value = false;
};
</script>

<template>

    <Head :title="`All Reviews - ${product.name}`" />
    <CustomerLayout>
        <div class="max-w-4xl mx-auto">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4 mb-5">
                    <Link :href="route('products-details', props.product.id)"
                        class="p-3 bg-white rounded-md hover:bg-neutral-200 border border-gray-300">
                        <ArrowLeftToLine class="w-5 h-5 text-neutral-900" />
                    </Link>
                    <div>
                        <h1 class="text-lg font-bold text-neutral-900 leading-tight">All Reviews</h1>
                        <p class="text-xs text-neutral-500 font-medium"> {{ props.product.name }} </p>
                    </div>
                </div>

                <div v-if="canRateProduct">
                    <button @click="rateModal = true"
                        class="flex items-center gap-2 px-4 py-2 text-md font-bold text-white bg-neutral-900 hover:bg-neutral-700 transition-colors duration-150 rounded-md">
                        <Star class="text-white fill-white h-5 w-5" />
                        <span>Rate Product</span>
                    </button>
                </div>
            </div>

            <div class="bg-white border border-gray-300 rounded-md px-6 py-4">
                <div
                    class="flex flex-col md:flex-row items-center justify-center gap-10 bg-neutral-50 p-6 rounded-md border border-neutral-300">
                    <div class="text-center md:pr-10">
                        <p class="text-5xl font-black text-neutral-900">{{ props.ratingStats.average }}</p>
                        <div class="flex justify-center gap-1 my-2">
                            <Star v-for="i in 5" :key="i"
                                :class="['w-5 h-5', i <= Math.round(props.ratingStats.average) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                        </div>
                        <p class="text-xs font-bold text-neutral-500 uppercase">{{ props.ratingStats.total > 1 ? 'Ratings' : 'Rating' }}
                        </p>
                    </div>

                    <!-- <div class="flex-1 space-y-4 w-full">
                        <span class="text-xs font-bold text-neutral-500 uppercase tracking-tighter">Filter by
                            Rating</span>
                        <div class="flex flex-wrap gap-2">
                            <button @click="selectedStar = 'all'" :class="selectedStar === 'all'
                                ? 'bg-neutral-900 text-white border-neutral-900'
                                : 'bg-white text-neutral-700 border-gray-300'"
                                class="px-4 py-1.5 rounded-md border text-[11px] font-bold">
                                All
                            </button>

                            <button v-for="star in [5, 4, 3, 2, 1]" :key="star" @click="selectedStar = star" :class="selectedStar === star
                                ? 'bg-neutral-900 text-white border-neutral-900'
                                : 'bg-white text-neutral-700 border-gray-300'"
                                class="px-4 py-1.5 rounded-md border text-[11px] font-bold hover:border-neutral-900">
                                {{ star }} Star
                            </button>
                        </div>
                    </div> -->
                </div>

                <div v-if="filteredReviews.length" class="mt-4 divide-y divide-gray-300">
                    <div class="space-y-6 p-2">
                        <div v-for="review in filteredReviews" :key="review.id"
                            class="flex gap-4 pb-6 border-b border-gray-300 last:border-0">
                            <Avatar :name="review.user.name.charAt(0)" class="h-8 w-8" />
                            <div class="space-y-1">

                                <div class="flex gap-6 items-start">
                                    <p class="text-xs font-bold text-neutral-900">
                                        {{ review.user.name }}
                                        <span v-if="review.user_id === props.auth.user.id">(You)</span>
                                    </p>

                                    <button v-if="review.user_id === props.auth.user.id" @click="editReview"
                                        class="text-xs font-bold text-blue-700 hover:underline">
                                        Edit
                                    </button>
                                </div>
                                <div class="flex gap-0.5">
                                    <Star v-for="i in 5" :key="i"
                                        :class="['w-3 h-3', i <= review.rating ? 'fill-yellow-400 text-yellow-400' : 'text-gray-200']" />
                                </div>
                                <p class="text-xs text-neutral-500">{{ dayjs(review.created_at).format('MMM, DD YYYY')
                                    }}</p>
                                <p class="text-sm text-neutral-700 mt-2">{{ review.comment }}</p>

                                <div v-if="review.attachments && review.attachments.length"
                                    class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-3">

                                    <img v-for="(image, index) in review.attachments" :key="index"
                                        :src="`/storage/${image}`"
                                        class="w-full h-24 object-cover rounded-md border border-gray-200 cursor-pointer hover:opacity-90 transition"
                                        @click="openPreview(review.attachments, index)"
                                    />
                                </div>
                                <div v-if="review.edit_at" class="flex gap-1">
                                    <p class="text-sm text-neutral-500">Edited at: </p>
                                    <p class="text-sm text-neutral-900">{{ dayjs(review.edit_at).format('MMM, DD YYYY')
                                        }}</p>
                                </div>
                                <div v-if="review.staff_reply"
                                    class="mt-3 p-3 bg-gray-50 border border-gray-300 rounded-md">
                                    <p class="text-xs font-bold text-neutral-900">Seller Reply</p>
                                    <p class="text-sm text-gray-700">{{ review.staff_reply }}</p>
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
        <BaseModal :show="rateModal" @close="rateModal = false" title="Rate Product"
            subtitle="Tell us what you experience with your product and warranty">
            <div class="space-y-3">
                <div class="p-6 space-y-8 border border-gray-300 rounded-md">
                    <div>
                        <InputLabel for="rate" value="Rating" />
                        <div class="flex gap-2 my-2">
                            <button id="rate" v-for="i in 5" :key="i" @mouseenter="hoverRating = i"
                                @mouseleave="hoverRating = 0" @click="form.rating = i" class="flex gap-1 my-2">
                                <Star :class="[
                                    'w-6 h-6 transition-colors duration-150 cursor-pointer',
                                    i <= (hoverRating || form.rating)
                                        ? 'fill-yellow-400 text-yellow-400'
                                        : 'text-gray-300'
                                ]" />
                            </button>
                        </div>
                        <InputError :message="form.errors.rating" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel for="comment" class="mb-3">
                            Describe your experince with you purchase product
                        </InputLabel>
                        <textarea id="comment" v-model="form.comment" rows="6"
                            class="block w-full rounded-md border-neutral-900 input-border"
                            placeholder="Please detail the problem you are experiencing..."></textarea>
                        <InputError :message="form.errors.comment" class="mt-2" />
                    </div>
                    <!-- ATTACHMENTS -->
                    <div>
                        <label class="block text-md font-semibold text-neutral-900 mb-1">
                            Attachments
                            <span class="text-neutral-500 text-sm font-normal">(Images)</span>
                        </label>
                        <div class="relative group border-2 border-dashed border-gray-300 rounded-md p-8 transition-all hover:border-neutral-900 hover:bg-neutral-50 cursor-pointer"
                            @click="fileInput?.click()">
                            <div class="text-center space-y-2">
                                <p class="text-sm text-neutral-600 font-medium">
                                    Click to select files or drag and drop
                                </p>
                                <p class="text-xs text-neutral-400">
                                    PNG, JPG, JPEG (Max 10 images)
                                </p>
                            </div>
                            <input ref="fileInput" type="file" class="sr-only" multiple accept="image/*,.pdf"
                                @change="handleFiles" />
                            <InputError :message="form.errors.attachments" class="mt-2" />
                        </div>
                        <!-- PREVIEW -->
                        <div v-if="images.length" class="mt-4 grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3">
                            <div v-for="(file, index) in images" :key="index" class="relative group">
                                <img v-if="file.type.startsWith('image/')" :src="file.src"
                                    class="h-24 w-full object-cover rounded-md border border-gray-200" />
                                <button type="button" @click="removeImage(index)"
                                    class="absolute -top-1.5 -right-1.5 text-black rounded-full p-0.5 hover:bg-gray-300 shadow">
                                    <CircleX />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="submitReview" :disabled="form.processing"
                        class="px-4 py-2 bg-black text-white rounded-md">
                        {{ form.processing ? 'Submitting' : 'Submit Review' }}
                    </button>
                </div>
            </div>
        </BaseModal>
    </CustomerLayout>
    <vue-easy-lightbox
        :visible="isLightboxOpen"
        :imgs="activeImagesArray"
        :index="activeImageIndex"
        @hide="handleHide"
    />
</template>