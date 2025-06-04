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
    form.post("/rbostoretables", {
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
