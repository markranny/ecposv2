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
    storeid: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

</script>

<template>
    <div class="bgDaksMode">
        <Head title="Register"/>

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
            <!-- <img id="background" class="absolute -left-20 top-0 max-w-[877px]" src="https://laravel.com/assets/img/welcome/background.svg" /> -->
        </template>

        <form @submit.prevent="submit">
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

            <!-- <div class="mt-4">
                <InputLabel for="role" value="Role" />
                <TextInput
                    id="role"
                    v-model="form.role"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="role"
                />
                <InputError class="mt-2" :message="form.errors.role" />
            </div> -->

            <div class="mt-4">
                    <InputLabel for="Role" value="Role" />
                    <select
                        id="role"
                        v-model="form.role"
                        className="select select-bordered w-full"
                    >
                        <option value="SUPERADMIN">
                        SUPERADMIN
                        </option>
                        <option value="ADMIN">
                        ADMIN
                        </option>
                        <option value="STORE">
                        STORE
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.role" />
                </div>

            <!-- <div class="mt-4">
                <InputLabel for="Store" value="Store eg. (ANCHETA BRANCH)" />
                <TextInput
                    id="storeid"
                    v-model="form.storeid"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="storeid"
                    @input="form.storeid = $event.target.value.toUpperCase()"
                />
                <InputError class="mt-2" :message="form.storeid.role" />
            </div> -->

            <!-- <div class="mt-4">
                    <InputLabel for="Store" value="Store" />
                    <select
                        id="Store"
                        v-model="Store"
                        class="mt-1 block w-full"
                    >
                        <option value="">-- Select an Store --</option>
                        <option
                        v-for="item in props.items"
                        :key="item.itemid"
                        :value="item.itemid"
                        >
                        {{ item.itemname }}
                        </option>
                    </select>
                </div> -->

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

            <div class="flex items-center justify-end mt-4">
                <Link :href="route('login')" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </AuthenticationCard>
    </div>
    
</template>
