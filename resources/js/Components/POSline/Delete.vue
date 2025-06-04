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
    LINEID: {
        type: [String, Number],
        required: true,
    },
    PRODUCTTYPE: {
        type: [String, Number],
        required: true,
    },
    ID: {
        type: [String, Number],
        required: true,
    },
    DEALPRICEORDISCPCT: {
        type: [String, Number],
        required: true,
    },
    LINEGROUP: {
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
    LINEID: '',
    PRODUCTTYPE: '',
    ID: '',
    DEALPRICEORDISCPCT: '',
    LINEGROUP: '',
    DISCOUNTTYPE: '',
    NOOFLINESTOTRIGGER: '',
    DEALPRICEVALUE: '',
    DISCOUNTPCTVALUE: '',
    DISCOUNTAMOUNTVALUE: '',
    PRICEGROUP: '',
});

const submitForm = () => {
    form.delete("/posperiodicdiscountlines/destroy", {
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
    form.LINEID = props.LINEID;

    watch(() => props.LINEID, (newValue) => {
        form.LINEID = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an ID of {{ LINEID }}?
                </div>
                <InputError :message="form.errors.LINEID" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
