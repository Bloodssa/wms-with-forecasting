<script setup>
import { ref } from 'vue';
import { router, useForm, Link } from '@inertiajs/vue3';
import { CircleX } from 'lucide-vue-next';
import InputLabel from '../Forms/InputLabel.vue';
import InputError from '../Forms/InputError.vue';

const props = defineProps({
    warranty: Object
});

const message = ref('');
const files = ref([]);
const images = ref([]);

const handleFiles = (event) => {
    const selectedFiles = Array.from(event.target.files);

    selectedFiles.forEach(file => {
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

const removeImage = (index) => {
    images.value.splice(index, 1);
    files.value.splice(index, 1);
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
    warranty_id: props.warranty.id,
    message: '',
    attachments: []
});
const submit = () => {
    form.post(route('inquire-warranty'), {
        forceFormData: true,
        onSuccess: () => {
            message.value = '';
            files.value = [];
            images.value = [];
        }
    });
};
</script>

<template>
    <div class="mx-auto mt-8">
        <form @submit.prevent="submit" enctype="multipart/form-data">
            <input type="hidden" :value="warranty.id" />
            <div class="bg-white border border-gray-300 rounded-md overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-300 bg-white">
                    <h2 class="text-xl font-semibold text-neutral-900">
                        New Warranty Inquiry
                    </h2>
                </div>
                <div class="p-6 space-y-8">
                    <div>
                        <InputLabel for="messages" class="mb-3">
                            Describe the issue of your purchased product
                        </InputLabel>
                        <textarea id="messages" v-model="form.message" rows="6"
                            class="input-border w-full rounded-md border-gray-300 focus:border-neutral-900"
                            placeholder="Please detail the problem you are experiencing..."></textarea>
                        <InputError :message="form.errors.message" class="mt-2" />
                    </div>  
                    <!-- ATTACHMENTS -->
                    <div>
                        <label class="block text-md font-semibold text-neutral-900 mb-2">
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
                            <InputError :message="form.errors.file" class="mt-2" />
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
                        <div v-if="Object.keys(form.errors).some(key => key.startsWith('attachments'))" class="mt-2">
                            <ul class="text-sm text-red-600 list-disc list-inside">
                                <li v-for="(error, key) in form.errors" :key="key">
                                    <span v-if="key.startsWith('attachments')">{{ error }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 flex items-center justify-end gap-3">
                <Link :href="route('inquiries')" class="text-black px-6 py-2.5 border border-gray-300 rounded-md hover:bg-gray-50">
                    Cancel
                </Link>
                <button type="submit"
                    class="inline-flex items-center px-6 py-2.5 bg-neutral-900 text-white text-sm font-bold rounded-md hover:bg-black transition-all">
                    Submit Inquiry
                </button>
            </div>
        </form>
    </div>
</template>