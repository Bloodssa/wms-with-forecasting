<script setup>
import { ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import Table from '@/Components/Table/Table.vue';
import TableSearch from '@/Components/Table/TableSearch.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import Badge from '@/Components/Badge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import useCountdown from '@/composables/useCountDown';
import { EllipsisVertical, Eye, SearchAlert, SquarePen, Trash2, Archive } from 'lucide-vue-next';
import BaseModal from '@/Components/Modals/BaseModal.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import TextInput from '@/Components/Forms/TextInput.vue';
import InputError from '@/Components/Forms/InputError.vue';

defineProps({
    warranties: {
        type: Object,
        default: () => ({})
    },
    select: {
        type: Object,
        default: () => ({})
    }
});

// table headers
const headers = ['Products', 'Customer', 'Serial Number', 'Status', 'Purchase Date', 'Warranty Duration'];

// format date for purchased date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: '2-digit'
    })
};

const { timeLeft, isExpired } = useCountdown();

const activeActionId = ref(null);
const showEditModal = ref(false);
const showDeleteModal = ref(false);
const selectedWarranty = ref(null);

const toggleActions = (id) => {
    if (activeActionId.value === id) {
        activeActionId.value = null;
    } else {
        activeActionId.value = id;
    }
};

const form = useForm({
    serial_number: '',
    purchase_date: '',
    expiry_date: '',
    status: '',
});

const openEditModal = (warranty) => {
    activeActionId.value = null;
    selectedWarranty.value = warranty;
    form.serial_number = warranty.serial_number;
    form.purchase_date = warranty.purchase_date ? warranty.purchase_date.split('T')[0] : '';
    form.expiry_date = warranty.expiry_date ? warranty.expiry_date.split('T')[0] : '';
    form.status = warranty.status;
    showEditModal.value = true;
};

const openDeleteModal = (warranty) => {
    activeActionId.value = null;
    selectedWarranty.value = warranty;
    showDeleteModal.value = true;
};

const closeModal = () => {
    showEditModal.value = false;
    showDeleteModal.value = false;
    selectedWarranty.value = null;
    form.reset();
};

const submitEdit = () => {
    form.put(route('warranties.update', selectedWarranty.value.id), {
        onSuccess: () => closeModal(),
    });
};

const forceDelete = () => {
    router.delete(route('warranties.destroy', selectedWarranty.value.id), {
        onSuccess: () => closeModal(),
    });
};

const archiveWarranty = () => {
    router.put(route('warranties.archive', selectedWarranty.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const unarchiveWarranty = () => {
    router.put(route('warranties.unarchive', selectedWarranty.value.id), {}, {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};
</script>

<template>

    <Head title="Warranties" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Customer Warranties</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">View all active warranties, their customers, and
                    status details.</p>
            </article>
        </section>

        <section>
            <div class="mt-6 bg-white border border-gray-300 rounded-md overflow-hidden">
                <TableSearch :select="select" :route="route('warranties')" />
                <Table v-if="warranties.data?.length > 0" :headers="headers" :datas="warranties" :action="true">
                    <tr v-for="warranty in warranties.data" :key="warranty.id">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center space-x-2">
                                <div class="h-10 w-10 shrink-0">
                                    <img class="h-10 w-10 rounded-md object-cover border border-gray-200"
                                        :src="warranty.product?.image_url" />
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    {{ warranty.product.name }}</div>
                            </div>
                        </td>
                        <td class="table-text">
                            <div class="flex items-center space-x-2">
                                <div v-if="warranty.is_claimed">
                                    <Avatar class="h-8 w-8" :name="warranty.user.name" />
                                </div>
                                <div v-else
                                    class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 bg-neutral-50 shrink-0">
                                    <SearchAlert class="w-4 h-4 text-neutral-900" />
                                </div>
                                <span>{{ warranty.user?.name ?? warranty.claim_email }}</span>
                            </div>
                        </td>
                        <td class="table-text">
                            {{ warranty.serial_number }}
                        </td>
                        <td class="table-text">
                            <Badge :type="warranty.status">
                                {{ warranty.status }}
                            </Badge>
                        </td>
                        <td class="table-text">
                            {{ formatDate(warranty.purchase_date) }}
                        </td>
                        <td class="table-text">
                            <div v-if="isExpired(warranty.expiry_date)">
                                <p class="text-[12px] tracking-wide text-fg-danger-strong font-medium">Expired</p>
                                <p class="text-sm font-semibold text-fg-danger-strong whitespace-nowrap">
                                    {{ formatDate(warranty.expiry_date) }}
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-[12px] tracking-wide text-neutral-500 font-medium">Expires In</p>
                                <p class="text-sm font-semibold text-neutral-900 whitespace-nowrap">
                                    {{ timeLeft(warranty.expiry_date) }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 relative py-4 whitespace-nowrap text-sm text-neutral-900">
                            <div class="flex justify-between">
                                <Link :href="route('view-warranty', warranty.id)"
                                    class="p-2 border border-gray-300 rounded-md bg-white hover:bg-gray-200 transition duration-150">
                                    <Eye class="hover:text-neutral-700" />
                                </Link>
                                <button v-if="$page.props.auth.user.role === 'admin'"
                                    @click.stop="toggleActions(warranty.id)"
                                    class="ml-1 text-neutral-500 hover:text-neutral-900 transition p-2 rounded-md border border-gray-300 bg-white hover:bg-gray-100">
                                    <EllipsisVertical class="h-4 w-4" />
                                </button>
                            </div>
                            <div v-if="activeActionId === warranty.id"
                                class="bg-white py-1 w-32 absolute right-6 top-12 rounded-md border border-gray-300 shadow-lg flex flex-col z-50">
                                <button @click="openEditModal(warranty)"
                                    class="group flex gap-2 items-center px-4 py-2 text-sm text-neutral-900 hover:bg-gray-100">
                                    <SquarePen class="h-4 w-4" />
                                    Edit
                                </button>
                                <button @click="openDeleteModal(warranty)"
                                    class="group flex gap-2 items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 border-t border-gray-100">
                                    <Trash2 class="h-4 w-4" />
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </Table>
                <EmptyState v-else :message="warranties.data?.length
                    ? 'No Customer warranties found.'
                    : 'There are no customer warranties at the moment.'" />
            </div>
        </section>
    </ManagerLayout>
    <BaseModal :show="showEditModal" title="Edit Warranty" subtitle="Update warranty details" @close="closeModal">
        <form @submit.prevent="submitEdit" class="space-y-4">
            <!-- Serial Number -->
            <div>
                <InputLabel for="serial_number" value="Serial Number" />
                <TextInput id="serial_number" v-model="form.serial_number" type="text" class="mt-1 block w-full"
                    placeholder="Enter Serial Number" />
                <InputError :message="form.errors.serial_number" class="mt-2" />
            </div>

            <!-- Dates Grid -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <InputLabel for="purchase_date" value="Purchase Date" />
                    <TextInput id="purchase_date" v-model="form.purchase_date" type="date" class="mt-1 block w-full" />
                    <InputError :message="form.errors.purchase_date" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="expiry_date" value="Expiry Date" />
                    <TextInput id="expiry_date" v-model="form.expiry_date" type="date" class="mt-1 block w-full" />
                    <InputError :message="form.errors.expiry_date" class="mt-2" />
                </div>
            </div>

            <!-- Status Selection -->
            <div>
                <InputLabel for="status" value="Status" />
                <select id="status" v-model="form.status"
                    class="mt-1 block w-full border-gray-300 focus:border-neutral-500 focus:ring-neutral-500 rounded-md shadow-sm text-sm">
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="near-expiry">Near Expiry</option>
                    <option value="expired">Expired</option>
                </select>
                <InputError :message="form.errors.status" class="mt-2" />
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" @click="closeModal"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="submit" :disabled="form.processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-neutral-900 rounded-md hover:bg-neutral-800 disabled:opacity-50 transition">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </BaseModal>

    <!-- DELETE MODAL -->
    <BaseModal size="sm" :show="showDeleteModal" @close="closeModal">
        <div class="p-6 flex flex-col items-center text-center">
            <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-orange-50">
                <Trash2 v-if="selectedWarranty?.status === 'archived'" class="w-8 h-8 text-red-500" />
                <Archive v-else class="w-8 h-8 text-orange-500" />
            </div>
            <h4 class="text-xl font-bold text-gray-800">
                {{
                    selectedWarranty?.status === 'archived'
                        ? 'Permanently Delete Warranty?'
                        : 'Delete Warranty?'
                }}
            </h4>
            <p class="mt-2 text-sm text-gray-500">
                <template v-if="selectedWarranty?.status === 'archived'">
                    This warranty is archived.
                    <span class="font-bold text-neutral-900">
                        {{ selectedWarranty?.days_left ?? 0 }} days left
                    </span>
                    before permanent deletion.
                </template>

                <template v-else>
                    Are you sure you want to delete warranty
                    <span class="font-bold text-neutral-900">
                        {{ selectedWarranty?.serial_number }}
                    </span>?
                    This will move it to archive first.
                </template>

            </p>
            <div class="mt-6 flex justify-center gap-3 w-full">

                <button @click="closeModal"
                    class="flex-1 px-4 py-2 border border-gray-300 text-sm rounded-md text-neutral-900 hover:bg-gray-50">
                    Cancel
                </button>
                <button v-if="selectedWarranty?.status !== 'archived'" @click="archiveWarranty"
                    class="flex-1 px-4 py-2 bg-yellow-600 text-white text-sm font-semibold rounded-md hover:bg-yellow-700">
                    Archive
                </button>

                <button v-if="selectedWarranty?.status === 'archived'" @click="unarchiveWarranty"
                    class="flex-1 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-md hover:bg-blue-700">
                    Cancel Archive, Restore
                </button>

                <button v-if="selectedWarranty?.status === 'archived'" @click="forceDelete"
                    class="flex-1 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700">
                    Delete Now
                </button>

            </div>
        </div>
    </BaseModal>
</template>