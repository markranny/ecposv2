<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    entryno:{
        type: [String, Number],
        required: true,
    },
    postingdate: {
        type: [String, Number],
        required: true,
    },
    customer: {
        type: [String, Number],
        required: true,
    },
    type: {
        type: [String, Number],
        required: true,
    },
    documentno: {
        type: [String, Number],
        required: true,
    },
    description: {
        type: [String, Number],
        required: true,
    },
    reasoncode: {
        type: [String, Number],
        required: true,
    },
    currency: {
        type: [String, Number],
        required: true,
    },
    currencyamount: {
        type: [String, Number],
        required: true,
    },
    amount: {
        type: [String, Number],
        required: true,
    },
    remainingamount: {
        type: [String, Number],
        required: true,
    },
    userid: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    entryno: '',
    postingdate: '',
    customer: '',
    type: '',
    documentno: '',
    description: '',
    reasoncode: '',
    currency: '',
    currencyamount: '',
    amount: '',
    remainingamount: '',
    userid: '',
});

const submitForm = () => {
    form.patch("/customerledgerentries/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.entryno = props.entryno;
    form.postingdate = props.postingdate;
    form.customer = props.customer;
    form.type = props.type;
    form.documentno = props.documentno;
    form.description = props.description;
    form.reasoncode = props.reasoncode;
    form.currency = props.currency;
    form.currencyamount = props.currencyamount;
    form.amount = props.amount;
    form.remainingamount = props.remainingamount;
    form.userid = props.userid;

    watch(() => props.entryno, (newValue) => {
        form.entryno = newValue;
    });
    watch(() => props.postingdate, (newValue) => {
        form.postingdate = newValue;
    });
    watch(() => props.customer, (newValue) => {
        form.customer = newValue;
    });
    watch(() => props.type, (newValue) => {
        form.type = newValue;
    });
    watch(() => props.documentno, (newValue) => {
        form.documentno = newValue;
    });
    watch(() => props.description, (newValue) => {
        form.description = newValue;
    });
    watch(() => props.reasoncode, (newValue) => {
        form.reasoncode = newValue;
    });
    watch(() => props.currency, (newValue) => {
        form.currency = newValue;
    });
    watch(() => props.currencyamount, (newValue) => {
        form.currencyamount = newValue;
    });
    watch(() => props.amount, (newValue) => {
        form.amount = newValue;
    });
    watch(() => props.remainingamount, (newValue) => {
        form.remainingamount = newValue;
    });
    watch(() => props.userid, (newValue) => {
        form.userid = newValue;
    });
});
</script>

<template>
    <Modal title="LEDGER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="entryno" value="entryno" />
                    <TextInput
                        id="entryno"
                        v-model="form.entryno"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.number ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.number" class="mt-2" />
                    </div>  
                    <div class="col-span-2">
                    <InputLabel for="postingdate" value="postingdate" />
                    <TextInput
                        id="postingdate"
                        v-model="form.postingdate"
                        :is-error="form.errors.postingdate ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.postingdate" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="customer" value="customer" />
                        <TextInput
                        id="customer"
                        v-model="form.customer"
                        :is-error="form.errors.customer ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.customer" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="type" value="type" />
                    <TextInput
                        id="type"
                        v-model="form.type"
                        :is-error="form.errors.type ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.type" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="documentno" value="documentno" />
                    <TextInput
                        id="documentno"
                        v-model="form.documentno"
                        :is-error="form.errors.documentno ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.documentno" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="description" value="description" />
                    <TextInput
                        id="description"
                        v-model="form.description"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.description ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="reasoncode" value="reasoncode" />
                    <TextInput
                        id="reasoncode"
                        v-model="form.reasoncode"
                        :is-error="form.errors.reasoncode ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.reasoncode" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="currency" value="currency" />
                    <TextInput
                        id="currency"
                        v-model="form.currency"
                        :is-error="form.errors.currency ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.currency" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="currencyamount" value="currencyamount" />
                    <TextInput
                        id="currencyamount"
                        v-model="form.currencyamount"
                        :is-error="form.errors.currencyamount ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.currencyamount" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="amount" value="amount" />
                    <TextInput
                        id="amount"
                        v-model="form.amount"
                        :is-error="form.errors.amount ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.amount" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="remainingamount" value="remainingamount" />
                    <TextInput
                        id="remainingamount"
                        v-model="form.remainingamount"
                        :is-error="form.errors.remainingamount ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.remainingamount" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="userid" value="userid" />
                    <TextInput
                        id="userid"
                        v-model="form.userid"
                        :is-error="form.errors.userid ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.userid" class="mt-2" />
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