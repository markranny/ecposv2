<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    JOURNALID: {
        type: [String, Number],
        required: true,
    },
    LINENUM:{
        type: [String, Number],
        required: true,
    },
    TRANSDATE: {
        type: [String, Number],
        required: true,
    },
    ITEMID: {
        type: [String, Number],
        required: true,
    },

    ADJUSTMENT: {
        type: [String, Number],
        required: true,
    },
    
    COSTPRICE: {
        type: [String, Number],
        required: true,
    },
    
    PRICEUNIT: {
        type: [String, Number],
        required: true,
    },
    
    
    SALESAMOUNT: {
        type: [String, Number],
        required: true,
    },
    
    INVENTONHAND: {
        type: [String, Number],
        required: true,
    },
    
    COUNTED: {
        type: [String, Number],
        required: true,
    },
    
    REASONREFRECID: {
        type: [String, Number],
        required: true,
    },

    
    VARIANTID: {
        type: [String, Number],
        required: true,
    },
    
    POSTED: {
        type: [String, Number],
        required: true,
    },
    

    
    POSTEDDATETIME: {
        type: [String, Number],
        required: true,
    },
    
    
    UNITID: {
        type: [String, Number],
        required: true,
    },
    
    
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    JOURNALID: (''),
    LINENUM: (''),
    TRANSDATE: (''),
    ITEMID: (''),
    ADJUSTMENT: (''),
    COSTPRICE: (''),
    PRICEUNIT: (''),
    SALESAMOUNT: (''),
    INVENTONHAND: (''),
    COUNTED:(''),
    REASONREFRECID: (''),
    VARIANTID: (''),
    POSTED: (''),
    POSTEDDATETIME:(''),
    UNITID: (''),
});

const submitForm = () => {
    form.delete("/inventjournaltrans/destroy", {
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
    form.JOURNALID = props.JOURNALID;

    watch(() => props.JOURNALID, (newValue) => {
        form.JOURNALID = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an ID of {{ JOURNALID }}?
                </div>
                <InputError :message="form.errors.JOURNALID" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
