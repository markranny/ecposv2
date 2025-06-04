<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
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
    form.delete("/rbostoretables/destroy", {
        preserveScroll: true,
        onSuccess: () => {
            toggleActive();
        },
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.STOREID = props.STOREID;

    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an ID of {{ STOREID }}?
                </div>
                <InputError :message="form.errors.STOREID" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
