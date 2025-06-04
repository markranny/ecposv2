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
    name:{
        type: [String, Number],
        required: true,
    },
    
    email:{
            type: [String, Number],
            required: true,
        },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    name: (''),
    email: (''),
    password: (''),
    password_confirmation: (''),
});

const submitForm = () => {
    form.patch("/signup/patch", {
        preserveScroll: true,
    });
    location.reload();
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.name = props.name;
    form.email = props.email;

    watch(() => props.name, (newValue) => {
        form.name = newValue;
    });

    watch(() => props.email, (newValue) => {
        form.email = newValue;
    });

});
</script>

<template>
    <Modal title="Update Form"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="name" value="name"/>
                    <TextInput
                        id="name"
                        v-model="form.name"
                        type="text"
                        class="mt-1 block w-full bg-blue-50 "
                        :is-error="form.errors.name ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="email" value="email" class="pt-4"/>
                        <TextInput
                        id="email"
                        v-model="form.email"
                        :is-error="form.errors.email ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.email" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4 ">
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="col-span-6 sm:col-span-4">
                <InputLabel for="password_confirmation" value="Confirm Password" />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
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
