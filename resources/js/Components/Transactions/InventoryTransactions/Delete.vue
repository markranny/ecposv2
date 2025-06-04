<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    POSTINGDATE:{
        type: [String, Number],
        required: true,
    },
    ITEMID: {
        type: [String, Number],
        required: true,
    },
    STOREID: {
        type: [String, Number],
        required: true,
    },
    ADJUSTMENT: {
        type: [String, Number],
        required: true,
    },
    TYPE: {
        type: [String, Number],
        required: true,
    },
    COSTPRICEPERITEM: {
        type: [String, Number],
        required: true,
    },
    SALESPRICEWITHOUTTAXPERITEM: {
        type: [String, Number],
        required: true,
    },
    SALESPRICEWITHTAXPERITEM: {
        type: [String, Number],
        required: true,
    },
    REASONCODE: {
        type: [String, Number],
        required: true,
    },
    DISCOUNTAMOUNTPERITEM: {
        type: [String, Number],
        required: true,
    },
    UNITID: {
        type: [String, Number],
        required: true,
    },
    ADJUSTMENTININVENTORYUNIT: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    POSTINGDATE: '',
    ITEMID: '',
    STOREID: '',
    ADJUSTMENT: '',
    TYPE: '',
    COSTPRICEPERITEM: '',
    SALESPRICEWITHOUTTAXPERITEM: '',
    SALESPRICEWITHTAXPERITEM: '',
    REASONCODE: '',
    DISCOUNTAMOUNTPERITEM: '',
    UNITID: '',
    ADJUSTMENTININVENTORYUNIT: '',
});

const submitForm = () => {
    form.delete("/inventtrans/destroy", {
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
    form.POSTINGDATE = props.POSTINGDATE;

    watch(() => props.POSTINGDATE, (newValue) => {
        form.POSTINGDATE = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an ID of {{ POSTINGDATE }}?
                </div>
                <InputError :message="form.errors.POSTINGDATE" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
