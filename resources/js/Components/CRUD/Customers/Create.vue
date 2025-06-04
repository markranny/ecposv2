<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    accountnum: (''),
    name: (''),
    address: (''),
    phone: (''),
    currency: (''),
    blocked: (''),
    creditmax: (''),
    country: (''),
    zipcode: (''),
    state:(''),
    /* county: (''), */
    email: (''),
    cellularphone: (''),
    /* dataareaid:(''), */
    gender: (''),
});

const submitForm = () => {
    form.post("/customers", {
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
    <Modal title="ADD CUSTOMER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-3 ">
                    <InputLabel for="accountnum" value="Accountnum" />
                    <TextInput
                        id="accountnum"
                        v-model="form.accountnum"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.number ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.number" class="mt-2" />
                    </div>  
                    
                    <div class="col-span-3">
                    <InputLabel for="name" value="Name" class="mt-2 block w-full"/>
                    <TextInput
                        id="name"
                        v-model="form.name"
                        :is-error="form.errors.name ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="address" value="Address" class="mt-2 block w-full"/>
                        <TextInput
                        id="address"
                        v-model="form.address"
                        :is-error="form.errors.address ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.address" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="country" value="Country" class="mt-2"/>
                    <TextInput
                        id="country"
                        v-model="form.country"
                        :is-error="form.errors.country ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.country" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="zipcode" value="Zipcode" class="mt-2"/>
                    <TextInput
                        id="zipcode"
                        v-model="form.zipcode"
                        :is-error="form.errors.zipcode ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.zipcode" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="state" value="State" class="mt-2"/>
                    <TextInput
                        id="state"
                        v-model="form.state"
                        :is-error="form.errors.state ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.state" class="mt-2" />
                    </div>

                    
                    <div class="col-span-1 ml-4">
                    <InputLabel for="phone" value="Phone" class="mt-2 block w-full"/>
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        :is-error="form.errors.phone ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.phone" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="currency" value="Currency" class="mt-2"/>
                        <TextInput
                        id="currency"
                        v-model="form.currency"
                        :is-error="form.errors.currency ? true : false"
                        type="text"
                        value="PHP"
                        class="mt-1 block w-full"
                        readonly
                    />
                    <InputError :message="form.errors.currency" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="cellularphone" value="Telephone" class="mt-2"/>
                    <TextInput
                        id="cellularphone"
                        v-model="form.cellularphone"
                        :is-error="form.errors.cellularphone ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.cellularphone" class="mt-2" />
                    </div>

                    <div class="col-span- ml-4">
                    <!-- <InputLabel for="Blocked" value="Blocked" class="mt-2"/> -->
                    <!-- <TextInput
                        id="blocked"
                        v-model="form.blocked"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.blocked ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.blocked" class="mt-2" /> -->
                    <select 
                        className="select select-bordered w-full max-w-xs mt-5"
                        id="blocked"
                        v-model="form.blocked"
                        :is-error="form.errors.blocked ? true : false"
                        autofocus
                    >
                        <option disabled selected>Blocked?</option>
                        <option value="1">YES</option>
                        <option value="0">NO</option>
                    </select>
                        <InputError :message="form.errors.blocked" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="email" value="Email" class="mt-2"/>
                    <TextInput
                        id="email"
                        v-model="form.email"
                        :is-error="form.errors.email ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <!-- <InputLabel for="gender" value="Gender" class="mt-2"/>
                    <TextInput
                        id="gender"
                        v-model="form.gender"
                        :is-error="form.errors.gender ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.gender" class="mt-2" /> -->

                    <select 
                        className="select select-bordered w-full max-w-xs mt-5"
                        id="gender"
                        v-model="form.gender"
                        :is-error="form.errors.gender ? true : false"
                        autofocus
                    >
                        <option disabled selected>Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                        <InputError :message="form.errors.gender" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="creditmax" value="Creditmax" class="mt-2"/>
                    <TextInput
                        id="creditmax"
                        v-model="form.creditmax"
                        :is-error="form.errors.creditmax ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.creditmax" class="mt-2" />
                    </div>
                    </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                SUBMIT
            </PrimaryButton>
        </template>
    </Modal>
</template>
