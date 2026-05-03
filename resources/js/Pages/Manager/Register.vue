<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Plus, X, Handbag } from 'lucide-vue-next';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    products: {
        type: Array,
        default: []
    },
    categories: {
        type: Array,
        default: () => ([])
    }
});

const searchQuery = ref('');
const selectCategory = ref('');

// calculate total price
const totalPrice = computed(() => {
    return form.multiple_products.reduce((sum, item) => {
        const price = parseFloat(item.price || 0);

        return sum + price;
    }, 0);
});

// count the duplicate
const getDuplicateIndex = (productId, currentIndex) => {
    let count = 0;

    for (let i = 0; i <= currentIndex; i++) {
        if (form.multiple_products[i].product_id === productId) {
            count++;
        }
    }
    return count;
};

// count specific product
const getProductCount = (productId) => {
    return form.multiple_products.filter(
        item => item.product_id === productId
    ).length;
};

// Form inputs init
const form = useForm({
    claim_email: '',
    purchase_date: '',
    multiple_products: []
});

const addProduct = (product) => {
    form.multiple_products.push({ product_id: product.id, serial_number: '', price: product.price });
};

const findProductName = (id) => {
    const product = props.products.find(p => p.id === id);

    return product ? product.name : 'Unknown Product';
};

const submit = () => {
    form.post(route('register-warranty-details'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

// seeach filter by category or name or brand based on all product
const filteredProducts = computed(() => {
    return props.products.filter(product => {

        const search = product.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            product.brand.toLowerCase().includes(searchQuery.value.toLowerCase());

        const category = selectCategory.value === '' || product.category.name === selectCategory.value;

        return search && category;
    });
});

</script>

<template>

    <Head title="Register" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Register New Warranty</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Assign purchase product to the customer and send
                    email invitation</p>
            </article>
        </section>

        <form @submit.prevent="submit" class="mt-6 space-y-6">
            <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="px-3 sm:px-6 py-4 border-b border-gray-300">
                    <h1 class="text-neutral-900 text-lg font-semibold">Customer Information</h1>
                </div>
                <div class="px-4 sm:px-6 py-5">
                    <div class="flex flex-col md:grid md:grid-cols-2 items-center gap-2">
                        <div class="w-full">
                            <InputLabel for="claim_email" value="Customer Email Address" />
                            <TextInput id="claim_email" v-model="form.claim_email" type="email"
                                placeholder="customer@email.com" />
                            <InputError class="mt-1" :message="form.errors.claim_email" />
                        </div>
                        <div class="w-full">
                            <InputLabel for="purchase_date" value="Purchase Date" />
                            <div class="relative w-full">
                                <div class="absolute inset-y-0 inset-s-0 flex items-center ps-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-neutral-500" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input type="date" v-model="form.purchase_date"
                                    class="input-border border-gray-300 focus:border-neutral-900 block w-full ps-10 pe-3 py-2.25 bg-white border rounded-md" />
                            </div>
                            <InputError class="mt-1" :message="form.errors.purchase_date" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-300">
                    <h1 class="text-neutral-900 text-lg font-semibold">Purchased Product</h1>
                </div>
                <div class="px-4 sm:px-6 py-3 sm:py-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:h-162.5 items-stretch">
                        <div class="lg:col-span-5 flex flex-col min-h-0">
                            <div class="space-y-4 mb-4 shrink-0">
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <TextInput v-model="searchQuery" placeholder="Search product name or brand" />
                                    <select v-model="selectCategory" class="text-sm border-gray-300 rounded-md">
                                        <option value="">All Categories</option>
                                        <option v-for="category in props.categories" :key="category.id"
                                            :value="category">{{ category }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 overflow-y-auto pr-2">
                                    <template v-if="filteredProducts.length">
                                        <button type="button" v-for="product in filteredProducts" :key="product.id"
                                            @click="addProduct(product)"
                                            class="group flex items-center gap-3 p-2.5 rounded-md hover:bg-gray-50 border border-gray-300 bg-white text-left w-full">
                                            <div
                                                class="shrink-0 h-12 w-12 overflow-hidden rounded-md border border-gray-300">
                                                <img :src="product.image_url" class="w-full h-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-[13px] font-semibold text-neutral-900 truncate">
                                                    {{ product.name }}
                                                </p>
                                                <p class="text-[11px] text-neutral-500">{{ product.brand }}</p>
                                            </div>
                                            <Plus />
                                        </button>
                                    </template>
                                    <template v-else>
                                        <div class="col-span-full flex justify-center">
                                            <EmptyState :border="false" class="min-h-0! py-4"
                                                message="No products match your search." />
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div
                            class="lg:col-span-7 flex flex-col min-h-0 border border-gray-300 rounded-md bg-gray-50/50 p-4">
                            <div class="flex justify-between items-center mb-4 shrink-0">
                                <h3 class="text-md text-neutral-900 font-semibold flex justify-between mb-3">
                                    Items to Register
                                </h3>
                                <span class="text-neutral-900">{{ form.multiple_products.length }}</span>
                            </div>
                            <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar space-y-3">
                                <div v-for="(product, index) in form.multiple_products" :key="product.id"
                                    class="bg-white p-3 border border-gray-300 rounded-md animate-in slide-in-from-right-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <div>
                                            <p class="text-sm font-semibold text-neutral-900">
                                                {{ findProductName(product.product_id) }}
                                                <span v-if="getProductCount(product.product_id) > 1"
                                                    class="text-xs text-blue-600 ml-1">
                                                    #{{ getDuplicateIndex(product.product_id, index) }}
                                                </span>
                                            </p>
                                        </div>
                                        <button @click="form.multiple_products.splice(index, 1)" type="button"
                                            class="text-neutral-900 hover:text-red-800 transition duration-200">
                                            <X class="h-5" />
                                        </button>
                                    </div>
                                    <div class="flex flex-col space-y-1 sm:flex-row items-start space-x-2">
                                        <div class="w-full md:flex-1">
                                            <TextInput v-model="product.serial_number" placeholder="Enter Serial Number"
                                                class="py-3! text-base! w-full bg-gray-50 focus:bg-white" />
                                            <InputError
                                                :message="form.errors[`multiple_products.${index}.serial_number`]" />
                                        </div>
                                        <div class="w-full sm:w-32">
                                            <TextInput type="text" inputmode="decimal" v-model="product.price"
                                                @input="product.price = $event.target.value.replace(/[^0-9.]/g, '')"
                                                placeholder="Price"
                                                class="py-3! text-base! w-full bg-gray-50 focus:bg-white" />
                                            <InputError :message="form.errors[`multiple_products.${index}.price`]" />
                                        </div>
                                    </div>
                                </div>
                                <div v-if="form.multiple_products.length === 0"
                                    class="h-full flex flex-col items-center justify-center py-20 text-neutral-400 border-2 border-dashed border-gray-300 rounded-md bg-white">
                                    <Handbag />
                                    <p class="text-sm">Click products to add to registration</p>
                                </div>
                                <div v-if="form.multiple_products.length !== 0" class="flex justify-end">
                                    <span class="text-md fomt-semibold text-neutral-900">
                                        Total: {{ totalPrice }}
                                    </span>
                                </div>
                            </div>
                            <div v-if="form.multiple_products.length === 0">
                                <InputError :message="form.errors.multiple_products" class="mt-2 text-center" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-6">
                <PrimaryButton class="w-full sm:w-auto justify-center" :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing">
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Register and Send Invitation</span>
                </PrimaryButton>
            </div>
        </form>
    </ManagerLayout>
</template>