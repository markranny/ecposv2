<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
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
    phone: {
        type: [String, Number],
        required: true,
    },

    currency: {
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
    
    county: {
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
    

    
    dataareaid: {
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
    accountnum: '',
});

const submitForm = () => {
    form.delete("/customers/destroy", {
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
    form.accountnum = props.accountnum;

    watch(() => props.accountnum, (newValue) => {
        form.accountnum = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ itemName }} with an Accountnum of {{ accountnum }}?
                </div>
                <InputError :message="form.errors.accountnum" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
