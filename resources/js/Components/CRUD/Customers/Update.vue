<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    accountnum: {
        type: [String, Number],
        required: true,
    },
    name:{
        type: [String, Number],
        required: true,
    },
    address: {
        type: [String, Number],
        required: true,
    },

    currency: {
        type: [String, Number],
        required: true,
    },

    phone: {
        type: [String, Number],
        required: true,
    },
    
    blocked: {
        type: [String, Number],
        required: true,
    },
    
    creditmax: {
        type: [String, Number],
        required: true,
    },
    
    
    country: {
        type: [String, Number],
        required: true,
    },
    
    zipcode: {
        type: [String, Number],
        required: true,
    },
    
    state: {
        type: [String, Number],
        required: true,
    },
    
    email: {
        type: [String, Number],
        required: true,
    },
    
    cellularphone: {
        type: [String, Number],
        required: true,
    },
    
    
    gender: {
        type: [String, Number],
        required: true,
    },
    
    
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
    email: (''),
    cellularphone: (''),
    gender: (''),
});


const submitForm = () => {
    form.patch("/customers/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};


onMounted(() => {
    form.accountnum = props.accountnum;
    form.name = props.name;
    form.address = props.address;
    form.phone = props.phone;
    form.currency = props.currency;
    form.blocked = props.blocked;
    form.creditmax = props.creditmax;
    form.country = props.country;
    form.zipcode = props.zipcode;
    form.state = props.state;
    form.email = props.email;
    form.cellularphone = props.cellularphone;
    form.gender = props.gender;

    watch(() => props.accountnum, (newValue) => {
        form.accountnum = newValue;
    });
    watch(() => props.name, (newValue) => {
        form.name = newValue;
    });
    watch(() => props.address, (newValue) => {
        form.address = newValue;
    });
    watch(() => props.phone, (newValue) => {
        form.phone = newValue;
    });
    watch(() => props.currency, (newValue) => {
        form.currency = newValue;
    });
    watch(() => props.blocked, (newValue) => {
        form.blocked = newValue;
    });
    watch(() => props.creditmax, (newValue) => {
        form.creditmax = newValue;
    });
    watch(() => props.country, (newValue) => {
        form.country = newValue;
    });
    watch(() => props.zipcode, (newValue) => {
        form.zipcode = newValue;
    });
    watch(() => props.state, (newValue) => {
        form.state = newValue;
    });
    watch(() => props.email, (newValue) => {
        form.email = newValue;
    });
    watch(() => props.cellularphone, (newValue) => {
        form.cellularphone = newValue;
    });
    watch(() => props.gender, (newValue) => {
        form.gender = newValue;
    });
});
</script>

<template>
    <Modal title="Update / View Form"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto" >
                <div class="grid grid-cols-3 mt-4">
                    <div class="col-span-3 ">
                    <InputLabel for="accountnum" value="Accountnum" />
                    <TextInput
                        id="accountnum"
                        v-model="form.accountnum"
                        type="number"
                        class="mt-1 block w-full input input-bordered w-full"
                        :is-error="form.errors.number ? true : false"
                        autofocus
                        disabled
                    />
                    <InputError :message="form.errors.number" class="mt-2" />
                    </div>  

                    <div class="col-span-3 mt-2">
                    <InputLabel for="name" value="Name" />
                    <TextInput
                        id="name"
                        v-model="form.name"
                        :is-error="form.errors.name ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.name" class="mt-2" />
                    </div>

                    <div class="col-span-3 mt-2">
                        <InputLabel for="address" value="Address" />
                        <TextInput
                        id="address"
                        v-model="form.address"
                        :is-error="form.errors.address ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.address" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-2">
                        <InputLabel for="email" value="Email" />
                        <TextInput
                        id="email"
                        v-model="form.email"
                        :is-error="form.errors.email ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.email" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
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

                    <div class="col-span-1 mt-2">
                        <InputLabel for="currency" value="Currency" />
                        <TextInput
                        id="currency"
                        v-model="form.currency"
                        :is-error="form.errors.currency ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        value="PHP"
                        readonly
                    />
                    <InputError :message="form.errors.currency" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
                    <InputLabel for="phone" value="Phone" />
                    <TextInput
                        id="phone"
                        v-model="form.phone"
                        :is-error="form.errors.phone ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.phone" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
                        <InputLabel for="cellularphone" value="CELLULARPHONE" />
                    <TextInput
                        id="cellularphone"
                        v-model="form.cellularphone"
                        :is-error="form.errors.cellularphone ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.cellularphone" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2">
                        <InputLabel for="country" value="Country" />
                    <TextInput
                        id="country"
                        v-model="form.country"
                        :is-error="form.errors.country ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.country" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
                        <InputLabel for="zipcode" value="Zipcode" />
                    <TextInput
                        id="zipcode"
                        v-model="form.zipcode"
                        :is-error="form.errors.zipcode ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.zipcode" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
                        <InputLabel for="state" value="STATE" />
                    <TextInput
                        id="state"
                        v-model="form.state"
                        :is-error="form.errors.state ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.state" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-2">
                        <InputLabel for="creditmax" value="creditmax" />
                    <TextInput
                        id="creditmax"
                        v-model="form.creditmax"
                        :is-error="form.errors.creditmax ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.creditmax" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-2 ml-4">
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

                    
                    </div>
                    
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                UPDATE
            </PrimaryButton>
        </template>
    </Modal>
</template>
