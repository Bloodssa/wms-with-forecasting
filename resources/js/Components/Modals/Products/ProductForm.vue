<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';

const props = defineProps({
    categories: Object,
    product: Object,
    idSuffix: { type: String, default: 'create' },

});

const emit = defineEmits(['close']);

const isEdit = !!props.product;

const form = useForm({
    name: props.product?.name ?? '',
    category_id: props.product?.category_id ?? '',
    description: props.product?.description ?? '',
    price: props.product?.price ?? '',
    brand: props.product?.brand ?? '',
    warranty_duration: props.product?.warranty_duration ?? '',
    service_center_name: props.product?.service_center_name ?? '',
    service_center_address: props.product?.service_center_address ?? '',
    product_image_url: null,
})

const submit = () => {
    const options = {
        forceFormData: true,
        errorBag: 'product',
        onSuccess: () => {
            emit('close');
        },
    };

    if (isEdit) {
        form.transform((data) => ({
            ...data,
            _method: 'put',
        }))
            .post(route('update-product', props.product.id), options);
    } else {
        form.post(route('store-product'), {
            ...options,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};

const imageUrl = ref(props.product?.image_url ?? null);

const handleImageUpdate = (event) => {
    const file = event.target.files[0];
    if (!file) return;

    form.product_image_url = file;

    const reader = new FileReader();
    reader.onload = (e) => {
        imageUrl.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

// keys allowed in input of price and duration
const onlyNumbers = (event) => {
    const keysAllowed = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete'];

    if (!keysAllowed.includes(event.key)) {
        event.preventDefault();
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-300">
                <h1 class="text-neutral-900 text-lg font-semibold">Product Specification</h1>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="space-y-1">
                        <InputLabel for="product_name" :value="'Product Name'" />
                        <TextInput id="product_name" v-model="form.name" type="text" class="w-full"
                            :class="{ 'border-red-500': form.errors.name }" placeholder="e.g. ROG Zephyrus M16" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel for="category_id" :value="'Category'" />

                        <select id="category_id" v-model="form.category_id"
                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            :class="{ 'border-red-500': form.errors.category_id }">
                            <option value="">Select Category</option>

                            <option v-for="(name, id) in props.categories" :key="id" :value="id">
                                {{ name }}
                            </option>
                        </select>

                        <InputError :message="form.errors.category_id" class="mt-2" />
                    </div>

                    <div class="space-y-1">
                        <InputLabel for="price" :value="'Price'" />
                        <TextInput id="price" v-model.number="form.price" class="w-full" type="number"
                            @keydown="onlyNumbers" :class="{ 'border-red-500': form.errors.price }"
                            placeholder="Enter product brand" />
                        <InputError :message="form.errors.price" class="mt-2" />
                    </div>

                    <div class="space-y-1">
                        <InputLabel for="brand" :value="'Brand'" />
                        <TextInput id="brand" v-model="form.brand" class="w-full"
                            :class="{ 'border-red-500': form.errors.brand }" placeholder="Enter product brand" />
                        <InputError :message="form.errors.brand" class="mt-2" />
                    </div>

                    <div class="space-y-1">
                        <InputLabel for="warranty_duration" :value="'Warranty Duration'" />
                        <TextInput id="warranty_duration" type="number" v-model.number="form.warranty_duration"
                            class="w-full" @keydown="onlyNumbers"
                            :class="{ 'border-red-500': form.errors.warranty_duration }"
                            placeholder="Enter number of months" />
                        <InputError :message="form.errors.warranty_duration" class="mt-2" />
                    </div>
                </div>
                <div>
                    <InputLabel for="messages" class="mb-3">
                        Describe the description or specification of the product
                    </InputLabel>
                    <textarea id="messages" v-model="form.description" rows="6"
                        :class="{ 'border-red-500': form.errors.description }"
                        class="input-border w-full rounded-md border-gray-300 focus:border-neutral-900"
                        placeholder="Please detail the problem you are experiencing..."></textarea>
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-300">
                <h1 class="text-neutral-900 text-lg font-semibold">Product Image</h1>
            </div>
            <div class="p-4 sm:p-6">
                <div v-if="imageUrl" class="mb-6">
                    <div
                        class="relative group border border-gray-300 rounded-md overflow-hidden max-w-lg mx-auto bg-gray-50">
                        <img :src="imageUrl" class="w-full h-64 object-contain">
                        <div
                            class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            <label :for="'product_image_input_' + idSuffix"
                                class="cursor-pointer py-3 px-6 bg-neutral-900 hover:bg-neutral-800 text-white font-semibold rounded-md">
                                Update Image
                            </label>
                        </div>
                    </div>
                </div>

                <div v-else
                    class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg py-12 bg-gray-50 hover:bg-gray-100 transition-colors">
                    <label :for="'product_image_input_' + idSuffix" class="cursor-pointer flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-700">Click to upload product image</span>
                        <span class="text-xs text-gray-500 mt-1">PNG, JPG, SVG or GIF</span>
                    </label>
                </div>

                <input type="file" :id="'product_image_input_' + idSuffix" accept="image/*" @change="handleImageUpdate"
                    class="hidden" />
                <InputError :message="form.errors.product_image_url" class="mt-4" />
            </div>
        </div>

        <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-300">
                <h1 class="text-neutral-900 text-lg font-semibold">Service Center</h1>
            </div>
            <div class="px-4 sm:px-6 py-3 sm:py-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                    <div class="space-y-1">
                        <InputLabel for="service_center_name" :value="'Service Center Name'" />
                        <TextInput id="service_center_name" v-model="form.service_center_name" class="w-full"
                            :class="{ 'border-red-500': form.errors.service_center_name }"
                            placeholder="Service Center Name" />
                        <InputError :message="form.errors.service_center_name" class="mt-2" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel for="service_center_address" :value="'Service Center Address'" />
                        <TextInput id="service_center_address" v-model="form.service_center_address" class="w-full"
                            :class="{ 'border-red-500': form.errors.service_center_address }"
                            placeholder="Service Center Address" />
                        <InputError :message="form.errors.service_center_address" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
            <button type="button" @click="emit('close')"
                class="px-6 py-2 border border-gray-300 rounded-md text-neutral-900 hover:bg-gray-50">
                Cancel
            </button>
            <PrimaryButton type="submit" class="w-full sm:w-auto justify-center"
                :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : 'Add Product' }}
            </PrimaryButton>
        </div>
    </form>
</template>