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
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    role: '',
    email: '',
    storeid: '',
    password: '',
});

const selectStore = (selectStore) => {
    form.storeid = selectStore;
};

const submitForm = () => {
    form.post("/signup", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            // Perform any additional actions after successful form submission
        },
        onError: (errors) => {
            // Handle form submission errors
            console.error(errors);
        },
        data: {
            ...form.data,
            selectStore: form.storeid, // Add the storeid value as selectStore
        },
    });

    location.reload();
};

</script>

<template>
    <Modal title="CREATE NEW USERS" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >
                <div class="grid grid-cols-4 ">


                <div class="col-span-2 ">
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="col-span-2 ml-2">
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="col-span-2 ">
                <InputLabel for="Role" value="Role" />
                    <select
                        id="role"
                        v-model="form.role"
                        className="select select-bordered w-full"
                    >
                        <option value="ADMIN">
                        ADMIN
                        </option>
                        <option value="STORE">
                        STORE
                        </option>
                        <option value="OPIC">
                        OPIC
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div class="col-span-2 ml-2">
                    <InputLabel for="StoreName" value="StoreName" />
                    <select id="storeid" v-model="form.storeid" class="select select-bordered w-full" @change="selectStore(form.storeid)">
                        <option v-for="store in rbostoretables" :key="store.storeid" :value="store.NAME">
                            {{ store.NAME }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.storeid" />
                </div>


            <div class="col-span-2 ">
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

            <div class="col-span-2 ml-2">
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
