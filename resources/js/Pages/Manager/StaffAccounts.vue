<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router, Head } from '@inertiajs/vue3';
import ManagerLayout from '@/Layouts/ManagerLayout.vue';
import Pagination from '@/Components/Table/Pagination.vue';
import { Search, EllipsisVertical } from 'lucide-vue-next';
import TextInput from '@/Components/Forms/TextInput.vue';
import Table from '@/Components/Table/Table.vue';
import Avatar from '@/Components/Icons/Avatar.vue';
import InputError from '@/Components/Forms/InputError.vue';
import InputLabel from '@/Components/Forms/InputLabel.vue';
import PrimaryButton from '@/Components/Forms/PrimaryButton.vue';
import EmptyState from '@/Components/EmptyState.vue';

const props = defineProps({
    users: Object,
    filters: Object,
});

// store in loacal storage if the web reload still in that tab
const tab = ref(localStorage.getItem('team-tab') || 'list');

watch(tab, (newTab) => {
    localStorage.setItem('team-tab', newTab);
});

const search = ref(props.filters.search || '');
watch(search, (value) => { // watch search
    router.get(route('staff-accounts'), { search: value }, {
        preserveState: true,
        replace: true
    });
});

// filter based on tab selected on the roles of the team
const filteredUsers = computed(() => {
    const list = props.users.data;
    if (tab.value === 'list') return list;
    if (tab.value === 'staff') return list.filter(u => u.role === 'staff');
    if (tab.value === 'tech') return list.filter(u => u.role === 'technician');
    return [];
});

const form = useForm({
    name: '',
    email: '',
    role: '',
    password: '',
});

const submit = () => {
    form.post(route('create-employee'), {
        onSuccess: () => form.reset(),
    });
};

// name to first lett and next word letters
const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const headers = ['Name', 'Email', 'Role'];
</script>

<template>
    <Head title="Team Management" />
    <ManagerLayout>
        <section class="lg:mt-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <article class="space-y-1">
                <h1 class="text-neutral-900 text-[22px] lg:text-2xl capitalize font-bold">Team Management</h1>
                <p class="text-neutral-500 text-[13px] lg:text-sm">Manage staff and technicians</p>
            </article>
        </section>
        <div class="space-y-6">
            <div
                class="flex flex-col-reverse md:flex-row items-center justify-between gap-4 mb-6 border-b border-gray-300">
                <div class="flex gap-2">
                    <button v-for="t in ['list', 'staff', 'tech', 'create']" :key="t" @click="tab = t"
                        :class="tab === t ? 'border-b-2 border-neutral-900 text-neutral-900' : 'text-neutral-500'"
                        class="px-4 py-3 text-sm font-semibold capitalize transition-all">
                        {{ t === 'tech' ? 'Technicians' : (t === 'list' ? 'Team List' : t) }}
                    </button>
                </div>

                <div v-show="tab !== 'create'" class="w-full md:w-auto flex items-center">
                    <div class="relative w-full">
                        <div class="relative w-full md:w-96">
                            <div
                                class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-neutral-500 z-10">
                                <Search class="w-4 h-5 " />
                            </div>
                            <TextInput id="search" v-model="search" placeholder="Search employee name or email"
                                class="pl-10" />
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="tab === 'list'" class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="overflow-x-auto">
                    <Table v-if="filteredUsers.length > 0" :borderTop="false" :headers="headers" :route="route('staff-accounts')" :data="users"
                        :action="true">
                        <tr v-for="user in filteredUsers" :key="user.id">
                            <td class="table-text">
                                <div class="flex items-center space-x-2">
                                    <Avatar :name="user.name" class="h-8 w-8" />
                                    <span class="font-semibold">{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="table-text">{{ user.email }}</td>
                            <td class="table-text capitalize">{{ user.role }}</td>
                            <td class="px-6 text-right py-4 whitespace-nowrap text-sm text-neutral-900">
                                <button
                                    class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                    <EllipsisVertical />
                                </button>
                            </td>
                        </tr>
                    </Table>
                    <EmptyState v-else message="No team member found" />
                </div>
            </div>

            <div v-if="tab === 'staff' || tab === 'tech'" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div v-for="user in filteredUsers" :key="user.id"
                        class="p-4 bg-white border border-gray-300 rounded-md flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <div
                                class="h-10 w-10 rounded-full bg-neutral-900 text-white flex items-center justify-center font-semibold">
                                {{ getInitials(user.name) }}
                            </div>
                            <div>
                                <p class="font-semibold">{{ user.name }}</p>
                                <p class="font-normal text-neutral-500">{{ user.email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-1">
                            <span class="text-sm px-2 py-1 rounded-md capitalize font-semibold bg-gray-200">{{ user.role
                                }}</span>
                            <button
                                class="text-neutral-500 hover:text-neutral-900 transition p-1 rounded-md hover:bg-gray-100">
                                <EllipsisVertical />
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="filteredUsers.length === 0"
                    class="bg-white border border-gray-300 rounded-md p-12 text-center">
                    <p class="text-gray-500 font-semibold">No employee found</p>
                </div>

                <div v-if="users.links.length > 3" class="mt-6">
                    <Pagination :links="users.links" />
                </div>
            </div>

            <div v-if="tab === 'create'" class="bg-white border border-gray-300 rounded-md p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-6">Create New Team Member</h2>
                <form @submit.prevent="submit" class="grid md:grid-cols-2 gap-6">
                    <div class="space-y-1">
                        <InputLabel for="name" value="Name" />
                        <TextInput id="name" v-model="form.name" autocomplete="name" placeholder="John Doe" />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div class="space-y-1">
                        <InputLabel for="email" value="Email" />
                        <TextInput id="email" v-model="form.email" autocomplete="email" placeholder="jdoe@gmail.com" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role"
                            class="w-full border-gray-300 rounded-md focus:ring-black focus:border-black py-2">
                            <option value="">Select Role</option>
                            <option value="staff">Staff</option>
                            <option value="technician">Technician</option>
                        </select>
                        <div v-if="form.errors.role" class="text-red-500 text-xs mt-1">{{ form.errors.role }}</div>
                    </div>
                    <div class="space-y-1">
                        <InputLabel for="password" value="Password" />
                        <TextInput id="password" v-model="form.password" autocomplete="password" placeholder="Password" type="password" />
                        <InputError :message="form.errors.password" />
                    </div>
                    <div class="md:col-span-2 flex justify-end pt-4">
                        <PrimaryButton :disabled="form.processing">Create Employee</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </ManagerLayout>
</template>