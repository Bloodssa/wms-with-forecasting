<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';

const props = defineProps({
    products: {
        type: Array,
        default: []
    }
});

// Form inputs init
const form = useForm({
    email: '',
    product_id: '',
    purchase_date: '',
    serial_number: ''
});

const submit = () => {
    form.post(route('register-warranty-details'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            console.log('Warranty Registered Successfully!');
        },
        onError: () => {
            console.error('There was an error submitting the form.');
        },
    });
};

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
            <div v-if="form.wasSuccessful" class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg">
                Warranty has been successfully registered and invitation sent!
            </div>
            <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="px-3 sm:px-6 py-4 border-b border-gray-300">
                    <h1 class="text-neutral-900 text-lg font-semibold">Customer Information</h1>
                </div>
                <div class="px-4 sm:px-6 py-5">
                    <div class="grid grid-cols-1 max-w-120">
                        <div class="flex-1 space-y-2">
                            <InputLabel for="email" value="Customer Email Address" />
                            <TextInput id="email" v-model="form.email" type="email" placeholder="customer@email.com" />
                            <InputError :message="form.errors.email" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-300">
                    <h1 class="text-neutral-900 text-lg font-semibold">Product Specification</h1>
                </div>
                <div class="px-4 sm:px-6 py-3 sm:py-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
                        <div class="space-y-1">
                            <InputLabel for="product_id" value="Product Purchased" />
                            <select v-model="form.product_id" id="product_id"
                                class="w-full border-gray-300 focus:border-neutral-800 focus:ring-neutral-800 rounded-md py-3 px-3 text-sm">
                                <option value="">Select Product</option>
                                <option v-for="product in props.products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.product_id" />
                        </div>
                        <div class="space-y-1">
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
                                    class="block w-full ps-10 pe-3 py-3 bg-white border border-gray-300 text-sm rounded-md focus:border-neutral-800 focus:ring-neutral-800" />
                            </div>
                            <InputError :message="form.errors.purchase_date" />
                        </div>
                        <div class="space-y-1">
                            <InputLabel for="serial_number" value="Serial Number" />
                            <TextInput id="serial_number" v-model="form.serial_number"
                                placeholder="(e.g., DUWQKV8320001MC)" />
                            <InputError :message="form.errors.serial_number" />
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