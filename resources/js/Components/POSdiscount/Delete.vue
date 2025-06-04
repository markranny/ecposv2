<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    OFFERID:{
        type: [String, Number],
        required: true,
    },
    DESCRIPTION: {
        type: [String, Number],
        required: true,
    },
    STATUS: {
        type: [String, Number],
        required: true,
    },
    PDTYPE: {
        type: [String, Number],
        required: true,
    },
    PRIORITY: {
        type: [String, Number],
        required: true,
    },
    DISCVALIDPERIODID: {
        type: [String, Number],
        required: true,
    },
    DISCOUNTTYPE: {
        type: [String, Number],
        required: true,
    },
    NOOFLINESTOTRIGGER: {
        type: [String, Number],
        required: true,
    },
    DEALPRICEVALUE: {
        type: [String, Number],
        required: true,
    },
    DISCOUNTPCTVALUE: {
        type: [String, Number],
        required: true,
    },
    DISCOUNTAMOUNTVALUE: {
        type: [String, Number],
        required: true,
    },
    PRICEGROUP: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    OFFERID: '',
    DESCRIPTION: '',
    STATUS: '',
    PDTYPE: '',
    PRIORITY: '',
    DISCVALIDPERIODID: '',
    DISCOUNTTYPE: '',
    NOOFLINESTOTRIGGER: '',
    DEALPRICEVALUE: '',
    DISCOUNTPCTVALUE: '',
    DISCOUNTAMOUNTVALUE: '',
    PRICEGROUP: '',
});

const submitForm = () => {
    form.delete("/posperiodicdiscounts/destroy", {
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
    form.OFFERID = props.OFFERID;

    watch(() => props.OFFERID, (newValue) => {
        form.OFFERID = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ OFFERID }} with an ID of {{ OFFERID }}?
                </div>
                <InputError :message="form.errors.OFFERID" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
