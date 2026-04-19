<script setup>
import { ref, nextTick } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
    inquiryId: {
        type: Number,
        required: true
    },
    buttonText: {
        type: String,
        default: 'Response'
    },
    placeholder: {
        type: String,
        default: 'Response to the inquiry of the user'
    }
});

const page = usePage();
const authUser = page.props.auth.user;

// Inertia Form Helper
const form = useForm({
    message: '',
    attachments: [],
    warranty_inquiries_id: props.inquiryId,
});

// UI State for previews
const previews = ref([]);

const handleFiles = (event) => {
    const selectedFiles = Array.from(event.target.files);
    
    selectedFiles.forEach(file => {
        // Add to form data
        form.attachments.push(file);
        
        // Create preview URL
        const reader = new FileReader();
        reader.onload = (e) => {
            previews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    form.attachments.splice(index, 1);
    previews.value.splice(index, 1);
};

const submit = () => {
    form.post(route('inquiry-response'), {
        forceFormData: true, // Necessary for file uploads
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message', 'attachments');
            previews.value = [];
            nextTick(() => {
                window.scrollTo({
                    top: document.body.scrollHeight,
                    behavior: 'smooth'
                })
            })
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4 border-t border-gray-300 p-5 bg-white">
        <div class="flex items-start space-x-3">
            <div class="shrink-0 mt-1">
                <div class="w-8 h-8 rounded-full bg-neutral-900 text-white flex items-center justify-center text-xs font-bold uppercase">
                    {{ authUser.name.charAt(0) }}
                </div>
            </div>

            <div class="flex-1">
                <div 
                    :class="[
                        'border rounded-md overflow-hidden bg-white transition-colors duration-200 focus-within:border-neutral-900',
                        previews.length > 0 ? 'border-neutral-900' : 'border-gray-300'
                    ]"
                >
                    <div v-if="previews.length > 0" class="flex gap-3 p-3 overflow-x-auto bg-neutral-50/50 border-b border-gray-200">
                        <div v-for="(img, index) in previews" :key="index" class="relative shrink-0">
                            <img :src="img" class="h-20 w-20 object-cover rounded-md border border-gray-300">
                            <button 
                                type="button" 
                                @click="removeImage(index)"
                                class="absolute -top-1.5 -right-1.5 bg-neutral-900 text-white rounded-full p-0.5 hover:bg-neutral-800"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <textarea 
                        v-model="form.message"
                        rows="3" 
                        :placeholder="placeholder"
                        class="w-full p-4 text-sm text-neutral-900 placeholder-neutral-500 border-none focus:ring-0 resize-none"
                    ></textarea>

                    <div class="flex items-center justify-between px-4 py-2.5 bg-neutral-50 border-t border-gray-200">
                        <div class="flex items-center gap-2">
                            <input 
                                type="file" 
                                ref="fileInput" 
                                class="hidden" 
                                accept="image/*" 
                                multiple 
                                @change="handleFiles"
                            >
                            <button 
                                type="button" 
                                @click="$refs.fileInput.click()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-neutral-500 hover:text-neutral-900 hover:bg-gray-200 rounded-md transition font-medium"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="text-xs">Add Photos</span>
                            </button>
                        </div>

                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="hidden sm:inline-flex bg-neutral-900 text-white px-6 py-1.5 rounded-md text-sm font-semibold hover:bg-neutral-800 transition disabled:opacity-50"
                        >
                            <span v-if="form.processing">Sending...</span>
                            <span v-else>{{ buttonText }}</span>
                        </button>
                    </div>
                </div>

                <div class="sm:hidden mt-3">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-neutral-900 text-white py-3 rounded-md text-sm font-semibold hover:bg-neutral-800 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Sending...' : buttonText }}
                    </button>
                </div>

                <div v-if="form.errors.message" class="text-red-500 text-xs mt-2">
                    {{ form.errors.message }}
                </div>
            </div>
        </div>
    </form>
</template>