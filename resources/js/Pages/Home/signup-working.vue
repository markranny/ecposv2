<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

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

/* const submitForm = () => {
    form.post("/signup", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        data: {
            ...form.data,
            selectStore: form.storeid, // Add the storeid value as selectStore
        },
    });
}; */

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
};

const navigate = () => {
  window.location.href = '/dashboard';
};

</script>

<template>
    <div class="bgDaksMode">
        <Head title="Register"/>

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
            <img id="background" class="absolute -left-20 top-0 max-w-[877px]" src="https://laravel.com/assets/img/welcome/background.svg" />
        </template>

        <form @submit.prevent="submitForm"  >
            <div>
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

            <div class="mt-4">
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
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

                <div class="mt-4">
                    <InputLabel for="StoreName" value="StoreName" />
                    <select id="storeid" v-model="form.storeid" class="select select-bordered w-full" @change="selectStore(form.storeid)">
                        <option v-for="store in rbostoretables" :key="store.storeid" :value="store.NAME">
                            {{ store.NAME }}
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.storeid" />
                </div>


            <div class="mt-4">
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

            <div class="mt-4">
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

            <div class="mt-4">
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

            <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-4">
                <InputLabel for="terms">
                    <div class="flex items-center">
                        <Checkbox id="terms" v-model:checked="form.terms" name="terms" required />

                        <div class="ms-2">
                            I agree to the <a target="_blank" :href="route('terms.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Terms of Service</a> and <a target="_blank" :href="route('policy.show')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Privacy Policy</a>
                        </div>
                    </div>
                    <InputError class="mt-2" :message="form.errors.terms" />
                </InputLabel>
            </div>

            <div class="flex items-center justify-center mt-4 ">
                <div class="flex items-center justify-end mt-4">
                    <PrimaryButton @click="navigate()" class="mr-14">
                    BACK
                    </PrimaryButton>

                    <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                    SUBMIT
                    </PrimaryButton>
                </div>
            </div>
            

            <!-- <div class="flex justify-start mt-4">
                <PrimaryButton @click="navigate()">BACK</PrimaryButton>
                <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">SUBMIT</PrimaryButton>
            </div> -->
            
        </form>
    </AuthenticationCard>
    </div>
    
</template>
