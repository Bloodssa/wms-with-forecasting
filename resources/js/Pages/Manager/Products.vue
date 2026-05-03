<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import Table from '@/Components/Table/Table.vue';
import { EllipsisVertical, Plus, Eye, SquarePen, Trash2 } from 'lucide-vue-next';
import { Head, Link, router, usePage, useForm } from '@inertiajs/vue3';
import EmptyState from '@/Components/EmptyState.vue';
import BaseModal from '@/Components/Modals/BaseModal.vue';
import ProductForm from '@/Components/Modals/Products/ProductForm.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import InputError from '@/Components/Forms/InputError.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import TextInput from '@/Components/Forms/TextInput.vue';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({})
    },
    categories: {
        type: Object,
        default: () => ({})
    },
    categoriesForFilter: {
        type: Object,
        default: () => ({})
    },
    categoriesForForm: {
        type: Object,
        default: () => ({})
    },
    tab: String
});

const headers = ['Product', 'Price', 'Category', 'Brand', 'Warranty Duration'];

// note updated this with composable if there is atime
// products
const showModal = ref(false); // add
const showEditModal = ref(false); // edit  
const showDeleteModal = ref(false); // delete
const selectedProduct = ref(null);
const activeActionId = ref(null);

// categories
const addCategory = ref(false);
const showEditCategoryModal = ref(false);
const showDeleteCategoryModal = ref(false);
const selectedCategory = ref(null);

const categoryForm = useForm({
    name: '',
});

const openEditCategory = (category) => {
    selectedCategory.value = category;
    categoryForm.name = category.name;
    showEditCategoryModal.value = true;
    activeActionId.value = null;
};

const openDeleteCategoryModal = (category) => {
    selectedCategory.value = category;
    showDeleteCategoryModal.value = true;
    activeActionId.value = null;
};

// post and patch
const submitCategory = () => {
    if (selectedCategory.value) {
        categoryForm.put(route('edit-category', selectedCategory.value.id), {
            onSuccess: () => closeModal(),
        });
    } else {
        categoryForm.post(route('store-category'), {
            onSuccess: () => closeModal(),
        });
    }
};

// delete
const confirmDeleteCategory = () => {
    router.delete(route('delete-category', selectedCategory.value.id), {
        onSuccess: () => {
            showDeleteCategoryModal.value = false;
            selectedCategory.value = null;
        },
    });
};

// close the modal
const closeModal = () => {
    addCategory.value = false;
    showEditCategoryModal.value = false;
    showDeleteCategoryModal.value = false;
    categoryForm.reset();
    categoryForm.clearErrors();
    selectedCategory.value = null;
};

const page = usePage();
const can = computed(() => page.props.can ?? {});

// query string for tabs instead of localStorage
const activeTab = computed({
    get: () => page.props.tab ?? 'products',
    set: (value) => {
        router.get(route('products'), {
            tab: value,
        }, {
            preserveState: true,
            replace: true,
        });
    }
});

watch(activeTab, (value) => {
    router.get(route('products'), {
        tab: value,
    }, {
        preserveState: true,
        replace: true,
    });
});

const openEditModal = (product) => {
    selectedProduct.value = product;
    showEditModal.value = true;
    activeActionId.value = null; // close the dropdown menu
};

const openDeleteModal = (product) => {
    selectedProduct.value = product;
    showDeleteModal.value = true;
    activeActionId.value = null;
};

const confirmDelete = () => {
    router.delete(route('delete-product', selectedProduct.value.id), {
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedProduct.value = null;
        }
    });
};

const toggleActions = (id) => {
    activeActionId.value = activeActionId.value === id ? null : id;
};

const handleClickOutside = () => {
    activeActionId.value = null;
}

onMounted(() => {
    window.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutside);
});

</script>

<template>

    <Head title="Products" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Product Management</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage your warranty data and configurations.</p>
            </article>

            <div class="inline-flex items-center p-1 space-x-1 bg-white border border-gray-300 rounded-md">
                <button @click="activeTab = 'products'"
                    :class="activeTab === 'products' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                    class="flex-1 font-semibold md:flex-none text-center px-4 py-2 rounded-md">
                    <span>Product List</span>
                </button>
                <button @click="activeTab = 'categories'"
                    :class="activeTab === 'categories' ? 'bg-neutral-900 text-white' : 'bg-white hover:text-neutral-700'"
                    class="flex-1 font-semibold md:flex-none text-center px-4 py-2 rounded-md">
                    <span>Category List</span>
                </button>
            </div>
        </section>

        <template v-if="activeTab === 'products'">
            <section>
                <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                    <TableSearch name="category" :select="categoriesForFilter" :route="route('products')">
                        <button v-if="can.canAdd" @click="showModal = true"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-neutral-900 text-white font-semibold rounded-md hover:bg-neutral-800 transition whitespace-nowrap">
                            <Plus class="h-5 w-5" />
                            <span>Add Product</span>
                        </button>
                    </TableSearch>
                    <Table v-if="props.products.data?.length > 0" :headers="headers" :datas="props.products"
                        :action="true">
                        <tr v-for="product in props.products.data" :key="product.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-2">
                                    <div class="h-10 w-10 shrink-0">
                                        <img class="h-10 w-10 rounded-md object-cover border border-gray-200"
                                            :src="product?.image_url" alt="Product">
                                    </div>
                                    <div class="text-sm font-semibold text-gray-900">{{ product.name }}</div>
                                </div>
                            </td>
                            <td class="table-text font-semibold">
                                ₱{{ product.price }}
                            </td>
                            <td class="table-text">
                                {{ product.category?.name }}
                            </td>
                            <td class="table-text">
                                {{ product.brand }}
                            </td>
                            <td class="table-text">
                                {{ product.warranty_duration }} months
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-neutral-900">
                                <div class="flex justify-end">
                                    <div class="flex justify-center items-center gap-2">
                                        <Link :href="route('show.product', product.id)"
                                            class="p-2 border border-gray-300 rounded-md bg-white hover:bg-gray-200 transition duration-150">
                                            <Eye class="hover:text-neutral-700" />
                                        </Link>
                                        <button v-if="$page.props.auth.user.role === 'admin'" @click.stop="toggleActions(product.id)"
                                            class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                            <EllipsisVertical />
                                        </button>
                                    </div>
                                    <div v-if="activeActionId === product.id"
                                        class="bg-white h-auto py-1 w-auto absolute right-8 rounded-md border border-gray-300 flex flex-col all-border items-start z-1000">
                                        <button @click="openEditModal(product)"
                                            class="w-full group flex gap-2 items-center px-4 py-2 text-sm text-neutral-900 hover:bg-gray-100">
                                            <SquarePen class="h-4 w-4" />
                                            Edit
                                        </button>
                                        <div class="py-1 border-t border-gray-300">
                                            <button @click="openDeleteModal(product)"
                                                class="group flex gap-2 w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                <Trash2 class="h-4 w-4" />
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </Table>
                    <EmptyState v-else
                        :message="props.products.data?.length ? 'No products found' : 'There is no product at the moment'" />
                </div>
            </section>
            <BaseModal :show="showEditModal" @close="showEditModal = false" title="Edit Product"
                subtitle="Modify the existing product details.">
                <ProductForm :product="selectedProduct" :categories="categoriesForForm"
                    @close="showEditModal = false" />
            </BaseModal>
            <BaseModal :show="showModal" @close="showModal = false" title="Add Product"
                subtitle="Create a new product entry in the system.">
                <ProductForm @close="showModal = false" :categories="categoriesForForm" />
            </BaseModal>
            <BaseModal size="sm" height="md" :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="flex flex-col gap-6">
                    <div class="flex items-center justify-center">
                        <div
                            class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-500">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex flex-col items-center px-4">
                        <h4 class="mb-2 text-2xl font-bold text-gray-800">
                            Delete Product
                        </h4>
                        <p class="mb-8 text-sm text-gray-500 text-center max-w-sm">
                            Are you sure you want to delete:
                            <span class="text-neutral-900 font-semibold">
                                {{ selectedProduct.name }}
                            </span>
                        </p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <button @click="showDeleteModal = false"
                            class="px-6 py-2 border border-gray-300 text-sm rounded-md text-neutral-900 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="confirmDelete"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition">
                            Yes, Delete Product
                        </button>
                    </div>
                </div>
            </BaseModal>
        </template>
        <template v-if="activeTab === 'categories'">
            <section>
                <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                    <div v-if="can.canAdd" class="flex justify-end px-4 py-3">
                        <button @click="addCategory = true"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 bg-neutral-900 text-white font-semibold rounded-md hover:bg-neutral-800 transition whitespace-nowrap">
                            <Plus class="h-5 w-5" />
                            <span>Add Category</span>
                        </button>
                    </div>

                    <Table v-if="props.categories?.length > 0" :borderTop="$page.props.auth.user.role !== 'technician'" :headers="['Category Name', 'Available Products']"
                        :action="$page.props.auth.user.role === 'admin'">
                        <tr v-for="category in props.categories" :key="category.id">
                            <td class="table-text">
                                {{ category.name }}
                            </td>
                            <td class="table-text"> 
                                {{ category.product_count }}
                            </td>
                            <td v-if="$page.props.auth.user.role === 'admin'" class="relative px-6 py-4 whitespace-nowrap text-sm text-right text-neutral-900">
                                <button @click.stop="toggleActions(category.id)"
                                    class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                    <EllipsisVertical />
                                </button>
                                <div v-if="activeActionId === category.id"
                                    class="bg-white py-1 w-32 absolute right-12 top-4 rounded-md border border-gray-300 shadow-lg flex flex-col z-50">
                                    <button @click="openEditCategory(category)"
                                        class="group flex gap-2 items-center px-4 py-2 text-sm text-neutral-900 hover:bg-gray-100">
                                        <SquarePen class="h-4 w-4" />
                                        Edit
                                    </button>
                                    <button @click="openDeleteCategoryModal(category)"
                                        class="group flex gap-2 items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 border-t border-gray-100">
                                        <Trash2 class="h-4 w-4" />
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </Table>
                    <EmptyState v-else
                        :message="props.categories?.length ? 'No products found' : 'There is no product at the moment'" />
                </div>
            </section>
            <BaseModal :show="addCategory || showEditCategoryModal" @close="closeModal"
                :title="selectedCategory ? 'Edit Category' : 'Add Category'"
                :subtitle="selectedCategory ? 'Update the category name.' : 'Create a new category for products.'">

                <form @submit.prevent="submitCategory" class="space-y-4">
                    <div>
                        <InputLabel for="name" value="Category Name" />
                        <TextInput placeholder="Graphics Card" id="name" v-model="categoryForm.name" type="text"
                            class="mt-1 block w-full" />
                        <InputError :message="categoryForm.errors.name" class="mt-2" />
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" @click="closeModal"
                            class="px-6 py-2 border border-gray-300 text-sm rounded-md text-neutral-900 hover:bg-gray-50">Cancel</button>
                        <PrimaryButton type="submit" :disabled="categoryForm.processing" >
                            {{ selectedCategory ? 'Update Category' : 'Save Category' }}
                        </PrimaryButton>
                    </div>
                </form>
            </BaseModal>

            <BaseModal size="sm" :show="showDeleteCategoryModal" @close="closeModal">
                <div class="p-6 flex flex-col items-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-50 text-red-500">
                        <Trash2 class="w-8 h-8" />
                    </div>
                    <h4 class="text-xl font-bold text-gray-800">Confirm Delete</h4>
                    <p class="mt-2 text-sm text-gray-500 text-center">
                        Are you sure you want to delete <span class="font-bold text-neutral-900">{{ selectedCategory?.name }}</span>?
                        This action cannot be undone.
                    </p>

                    <div class="mt-6 flex justify-center gap-3 w-full">
                        <button @click="closeModal" class="px-6 py-2 border border-gray-300 text-sm rounded-md text-neutral-900 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="confirmDeleteCategory"
                            class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition">
                            Yes, Delete Product
                        </button>
                    </div>
                </div>
            </BaseModal>
        </template>
    </ManagerLayout>
</template>