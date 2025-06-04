<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';

const props = defineProps({
    ID:{
        type: [String, Number],
        required: true,
    },
    
    SUBJECT:{
            type: [String, Number],
            required: true,
        },

    DESCRIPTION:{
            type: [String, Number],
            required: true,
        },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    ID: (''),
    SUBJECT: (''),
    DESCRIPTION: (''),
});

const submitForm = () => {
    form.patch("/announcement/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.ID = props.ID;
    form.SUBJECT = props.SUBJECT;
    form.DESCRIPTION = props.DESCRIPTION;

    watch(() => props.ID, (newValue) => {
        form.ID = newValue;
    });

    watch(() => props.SUBJECT, (newValue) => {
        form.SUBJECT = newValue;
    });

    watch(() => props.DESCRIPTION, (newValue) => {
        form.DESCRIPTION = newValue;
    });

});
</script>

<template>
    <Modal title="Update Announcement"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm">

                <div class="col-span-12 sm:col-span-4">
                    <InputLabel for="ID" value="ID"/>
                    <TextInput
                        id="ID"
                        v-model="form.ID"
                        type="text"
                        class="mt-1 block w-full bg-blue-50 "
                        :is-error="form.errors.ID ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.id" class="mt-2" />
                </div>

                <div class="col-span-12 sm:col-span-4">
                    <InputLabel for="SUBJECT" value="SUBJECT"/>
                    <TextInput
                        id="SUBJECT"
                        v-model="form.SUBJECT"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.SUBJECT ? true : false"
                    />
                    <InputError :message="form.errors.SUBJECT" class="mt-2" />
                </div>

                <!-- <div class="col-span-12 sm:col-span-4">
                        <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DESCRIPTION</label>
                        <textarea
                        id="DESCRIPTION"
                        v-model="form.DESCRIPTION"
                        :is-error="form.errors.DESCRIPTION ? true : false"
                        type="text"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                        ></textarea>
                        <InputError :message="form.errors.DESCRIPTION" class="mt-2" row="10"/>
                </div> -->

                <div class="col-span-12 sm:col-span-4">
                    <label for="DESCRIPTION" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DESCRIPTION</label>
                    <textarea
                        id="DESCRIPTION"
                        v-model="form.DESCRIPTION"
                        :is-error="form.errors.DESCRIPTION ? true : false"
                        rows="10"
                        class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    ></textarea>
                    <InputError :message="form.errors.DESCRIPTION" class="mt-2" :rows="10" />  <!-- Pass rows prop to InputError component -->
                </div>

                
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                UPDATE
            </PrimaryButton>
        </template>
    </Modal>
</template>
