<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';

const props = defineProps({
    journalid:{
        type: [String, Number],
        required: true,
    },

    description:{
            type: [String, Number],
            required: true,
        },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    journalid: (''),
    description: (''),
});

const submitForm = () => {
    form.patch("/order/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.journalid = props.journalid;
    form.description = props.description;

    watch(() => props.journalid, (newValue) => {
        form.journalid = newValue;
    });

    watch(() => props.description, (newValue) => {
        form.description = newValue;
    });

});
</script>

<template>
    <Modal title="Update Form"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="journalid" value="journalid"/>
                    <TextInput
                        id="journalid"
                        v-model="form.journalid"
                        type="text"
                        class="mt-1 block w-full bg-blue-50 "
                        :is-error="form.errors.journalid ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.journalid" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="description" value="DESCRIPTION" class="pt-4"/>
                        <TextInput
                        id="description"
                        v-model="form.description"
                        :is-error="form.errors.description ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
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
