<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    STOREID:{
        type: [String, Number],
        required: true,
    },
    NAME: {
        type: [String, Number],
        required: true,
    },
    ADDRESS: {
        type: [String, Number],
        required: true,
    },
    STREET: {
        type: [String, Number],
        required: true,
    },
    ZIPCODE: {
        type: [String, Number],
        required: true,
    },
    CITY: {
        type: [String, Number],
        required: true,
    },
    COUNTY: {
        type: [String, Number],
        required: true,
    },
    STATE: {
        type: [String, Number],
        required: true,
    },
    COUNTRY: {
        type: [String, Number],
        required: true,
    },
    PHONE: {
        type: [String, Number],
        required: true,
    },
    CURRENCY: {
        type: [String, Number],
        required: true,
    },
    SQLSERVERNAME: {
        type: [String, Number],
        required: true,
    },
    DATABASENAME: {
        type: [String, Number],
        default: false,
    },
    USERNAME: {
        type: [String, Number],
        default: false,
    },
    PASSWORD: {
        type: [String, Number],
        default: false,
    },
    WINDOWSAUTHENTICATION: {
        type: [String, Number],
        default: false,
    },
    LAYOUTNAME: {
        type: [String, Number],
        default: false,
    },
    RECEIPTLOGO: {
        type: [String, Number],
        default: false,
    },
    RECEIPTPROFILEID: {
        type: [String, Number],
        default: false,
    },
    RECEIPTLOGOWIDTH: {
        type: [String, Number],
        default: false,
    },
    FORMINFOFIELD1: {
        type: [String, Number],
        default: false,
    },
    FORMINFOFIELD2: {
        type: [String, Number],
        default: false,
    },
    FORMINFOFIELD3: {
        type: [String, Number],
        default: false,
    },
    FORMINFOFIELD4: {
        type: [String, Number],
        default: false,
    }
});

const form = useForm({
    STOREID: '',
    NAME: '',
    ADDRESS: '',
    STREET: '',
    ZIPCODE: '',
    CITY: '',
    COUNTY: '',
    STATE: '',
    COUNTRY: '',
    PHONE: '',
    CURRENCY: '',
    SQLSERVERNAME: '',
    DATABASENAME: '',
    USERNAME: '',
    PASSWORD: '',
    WINDOWSAUTHENTICATION: '',
    LAYOUTNAME: '',
    RECEIPTPROFILEID: '',
    RECEIPTLOGO: '',
    RECEIPTLOGOWIDTH: '',
    FORMINFOFIELD1: '',
    FORMINFOFIELD2: '',
    FORMINFOFIELD3: '',
    FORMINFOFIELD4: '',
});

const submitForm = () => {
    form.patch("/rbostoretables/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.STOREID = props.STOREID;
    form.NAME = props.NAME;
    form.ADDRESS = props.ADDRESS;
    form.STREET = props.STREET;
    form.ZIPCODE = props.ZIPCODE;
    form.CITY = props.CITY;
    form.COUNTY = props.COUNTY;
    form.STATE = props.STATE;
    form.COUNTRY = props.COUNTRY;
    form.PHONE = props.PHONE;
    form.CURRENCY = props.CURRENCY;
    form.SQLSERVERNAME = props.SQLSERVERNAME;
    form.DATABASENAME = props.DATABASENAME;
    form.USERNAME = props.USERNAME;
    form.PASSWORD = props.PASSWORD;
    form.WINDOWSAUTHENTICATION = props.WINDOWSAUTHENTICATION;
    form.LAYOUTNAME = props.LAYOUTNAME;
    form.RECEIPTPROFILEID = props.RECEIPTPROFILEID;
    form.RECEIPTLOGO = props.RECEIPTLOGO;
    form.RECEIPTLOGOWIDTH = props.RECEIPTLOGOWIDTH;
    form.FORMINFOFIELD1 = props.FORMINFOFIELD1;
    form.FORMINFOFIELD2 = props.FORMINFOFIELD2;
    form.FORMINFOFIELD3 = props.FORMINFOFIELD3;
    form.FORMINFOFIELD4 = props.FORMINFOFIELD4;

    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });
    watch(() => props.NAME, (newValue) => {
        form.NAME = newValue;
    });
    watch(() => props.customer, (newValue) => {
        form.customer = newValue;
    });
    watch(() => props.ADDRESS, (newValue) => {
        form.ADDRESS = newValue;
    });
    watch(() => props.STREET, (newValue) => {
        form.STREET = newValue;
    });
    watch(() => props.ZIPCODE, (newValue) => {
        form.ZIPCODE = newValue;
    });
    watch(() => props.CITY, (newValue) => {
        form.CITY = newValue;
    });
    watch(() => props.COUNTY, (newValue) => {
        form.COUNTY = newValue;
    });
    watch(() => props.STATE, (newValue) => {
        form.STATE = newValue;
    });
    watch(() => props.COUNTRY, (newValue) => {
        form.COUNTRY = newValue;
    });
    watch(() => props.PHONE, (newValue) => {
        form.PHONE = newValue;
    });
    watch(() => props.CURRENCY, (newValue) => {
        form.CURRENCY = newValue;
    });
    watch(() => props.SQLSERVERNAME, (newValue) => {
        form.SQLSERVERNAME = newValue;
    });
    watch(() => props.DATABASENAME, (newValue) => {
        form.DATABASENAME = newValue;
    });
    watch(() => props.USERNAME, (newValue) => {
        form.USERNAME = newValue;
    });
    watch(() => props.PASSWORD, (newValue) => {
        form.PASSWORD = newValue;
    });
    watch(() => props.WINDOWSAUTHENTICATION, (newValue) => {
        form.WINDOWSAUTHENTICATION = newValue;
    });
    watch(() => props.LAYOUTNAME, (newValue) => {
        form.LAYOUTNAME = newValue;
    });
    watch(() => props.RECEIPTPROFILEID, (newValue) => {
        form.RECEIPTPROFILEID = newValue;
    });
    watch(() => props.RECEIPTLOGO, (newValue) => {
        form.RECEIPTLOGO = newValue;
    });
    watch(() => props.RECEIPTLOGOWIDTH, (newValue) => {
        form.RECEIPTLOGOWIDTH = newValue;
    });
    watch(() => props.FORMINFOFIELD1, (newValue) => {
        form.FORMINFOFIELD1 = newValue;
    });
    watch(() => props.FORMINFOFIELD2, (newValue) => {
        form.FORMINFOFIELD2 = newValue;
    });
    watch(() => props.FORMINFOFIELD3, (newValue) => {
        form.FORMINFOFIELD3 = newValue;
    });
    watch(() => props.FORMINFOFIELD4, (newValue) => {
        form.FORMINFOFIELD4 = newValue;
    });

});
</script>

<template>
    <Modal title="storetable" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="STOREID" value="STOREID" />
                    <TextInput
                        id="STOREID"
                        v-model="form.STOREID"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.STOREID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.STOREID" class="mt-2" />
                    </div>  
                    <div class="col-span-2">
                    <InputLabel for="NAME" value="NAME" />
                    <TextInput
                        id="NAME"
                        v-model="form.NAME"
                        :is-error="form.errors.NAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.NAME" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="ADDRESS" value="ADDRESS" />
                        <TextInput
                        id="ADDRESS"
                        v-model="form.ADDRESS"
                        :is-error="form.errors.ADDRESS ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.ADDRESS" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="STREET" value="STREET" />
                    <TextInput
                        id="STREET"
                        v-model="form.STREET"
                        :is-error="form.errors.STREET ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.STREET" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="ZIPCODE" value="ZIPCODE" />
                    <TextInput
                        id="ZIPCODE"
                        v-model="form.ZIPCODE"
                        :is-error="form.errors.ZIPCODE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ZIPCODE" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="CITY" value="CITY" />
                    <TextInput
                        id="CITY"
                        v-model="form.CITY"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.CITY ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.CITY" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="COUNTY" value="COUNTY" />
                    <TextInput
                        id="COUNTY"
                        v-model="form.COUNTY"
                        :is-error="form.errors.COUNTY ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.COUNTY" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="STATE" value="STATE" />
                    <TextInput
                        id="STATE"
                        v-model="form.STATE"
                        :is-error="form.errors.STATE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.STATE" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="COUNTRY" value="COUNTRY" />
                    <TextInput
                        id="COUNTRY"
                        v-model="form.COUNTRY"
                        :is-error="form.errors.COUNTRY ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.COUNTRY" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="PHONE" value="PHONE" />
                    <TextInput
                        id="PHONE"
                        v-model="form.PHONE"
                        :is-error="form.errors.PHONE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.PHONE" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="CURRENCY" value="CURRENCY" />
                    <TextInput
                        id="CURRENCY"
                        v-model="form.CURRENCY"
                        :is-error="form.errors.CURRENCY ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.CURRENCY" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="SQLSERVERNAME" value="SQLSERVERNAME" />
                    <TextInput
                        id="SQLSERVERNAME"
                        v-model="form.SQLSERVERNAME"
                        :is-error="form.errors.SQLSERVERNAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.SQLSERVERNAME" class="mt-2" />
                    </div>

                    

                    <div class="col-span-1 ml-4">
                        <InputLabel for="DATABASENAME" value="DATABASENAME" />
                    <TextInput
                        id="DATABASENAME"
                        v-model="form.DATABASENAME"
                        :is-error="form.errors.DATABASENAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DATABASENAME" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="USERNAME" value="USERNAME" />
                    <TextInput
                        id="USERNAME"
                        v-model="form.USERNAME"
                        :is-error="form.errors.USERNAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.USERNAME" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="PASSWORD" value="PASSWORD" />
                    <TextInput
                        id="PASSWORD"
                        v-model="form.PASSWORD"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.PASSWORD ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.PASSWORD" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="WINDOWSAUTHENTICATION" value="WINDOWSAUTHENTICATION" />
                    <TextInput
                        id="WINDOWSAUTHENTICATION"
                        v-model="form.WINDOWSAUTHENTICATION"
                        :is-error="form.errors.WINDOWSAUTHENTICATION ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.WINDOWSAUTHENTICATION" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="LAYOUTNAME" value="LAYOUTNAME" />
                    <TextInput
                        id="LAYOUTNAME"
                        v-model="form.LAYOUTNAME"
                        :is-error="form.errors.LAYOUTNAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.LAYOUTNAME" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="RECEIPTPROFILEID" value="RECEIPTPROFILEID" />
                    <TextInput
                        id="RECEIPTPROFILEID"
                        v-model="form.RECEIPTPROFILEID"
                        :is-error="form.errors.RECEIPTPROFILEID ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.RECEIPTPROFILEID" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="RECEIPTLOGO" value="RECEIPTLOGO" />
                    <TextInput
                        id="RECEIPTLOGO"
                        v-model="form.RECEIPTLOGO"
                        :is-error="form.errors.RECEIPTLOGO ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.RECEIPTLOGO" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="RECEIPTLOGOWIDTH" value="RECEIPTLOGOWIDTH" />
                    <TextInput
                        id="RECEIPTLOGOWIDTH"
                        v-model="form.RECEIPTLOGOWIDTH"
                        :is-error="form.errors.RECEIPTLOGOWIDTH ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.RECEIPTLOGOWIDTH" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="FORMINFOFIELD1" value="FORMINFOFIELD1" />
                    <TextInput
                        id="FORMINFOFIELD1"
                        v-model="form.FORMINFOFIELD1"
                        :is-error="form.errors.FORMINFOFIELD1 ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.FORMINFOFIELD1" class="mt-2" />
                    </div>
                    
                    <div class="col-span-3">
                        <InputLabel for="FORMINFOFIELD2" value="FORMINFOFIELD2" />
                    <TextInput
                        id="FORMINFOFIELD2"
                        v-model="form.FORMINFOFIELD2"
                        :is-error="form.errors.FORMINFOFIELD2 ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.FORMINFOFIELD2" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="FORMINFOFIELD3" value="FORMINFOFIELD3" />
                    <TextInput
                        id="FORMINFOFIELD3"
                        v-model="form.FORMINFOFIELD3"
                        :is-error="form.errors.FORMINFOFIELD3 ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.FORMINFOFIELD3" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="FORMINFOFIELD4" value="FORMINFOFIELD4" />
                    <TextInput
                        id="FORMINFOFIELD4"
                        v-model="form.FORMINFOFIELD4"
                        :is-error="form.errors.FORMINFOFIELD4 ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.FORMINFOFIELD4" class="mt-2" />
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
