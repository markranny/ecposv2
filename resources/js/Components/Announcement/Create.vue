<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import SelectOption from '@/Components/SelectOption/SelectOption.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';


const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    }
});

/* const initialStoreId = computed(() => {
  return $page.props.auth.user.storeid;
}); */

const form = useForm({
    subject: '',
    description: '',
    file_path: null,
});

const handleFileUpload = (event) => {
  form.file_path = event.target.files[0];
};

const submitForm = () => {
    form.post("/announcement", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

</script>

<template>
    <Modal title="CREATE NEW ANNOUNCEMENT" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm" >
                
                <div class="grid grid-cols-1">
                    <div class="col-span-1">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <InputLabel for="name" value="SUBJECT" />
                                <TextInput
                                    id="subject"
                                    v-model="form.subject"
                                    :is-error="form.errors.subject ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.subject" class="mt-2" />
                            </div>

                            <div>
                                <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DESCRIPTION</label>
                                <textarea
                                    id="description"
                                    v-model="form.description"
                                    :is-error="form.errors.description ? true : false"
                                    type="text"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                >
                            </textarea>
                                <InputError :message="form.errors.description" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="file" value="Attachment" />
                                <input
                                type="file"
                                id="file"
                                @change="handleFileUpload"
                                class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>

                        </div>
                    </div>
                </div>
                
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                SUBMIT
            </PrimaryButton>
        </template>
    </Modal>
</template>

<!-- <script>
export default {
  data() {
    return {
      selectedCategory: '',
    };
  },
};
</script> -->
