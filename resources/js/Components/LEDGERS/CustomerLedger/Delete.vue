<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
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
    form.delete("/customerledgerentries/destroy", {
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
    form.entryno = props.entryno;

    watch(() => props.entryno, (newValue) => {
        form.entryno = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an ID of {{ entryno }}?
                </div>
                <InputError :message="form.errors.entryno" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
