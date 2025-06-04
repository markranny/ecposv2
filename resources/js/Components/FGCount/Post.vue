<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import axios from 'axios';

const props = defineProps({
    journalid:{
        type: [String, Number],
        required: true,
    }
});

const form = useForm({
    journalid: ''
});

const submitForm = () => {
    console.log('Submitting form...');
    form.patch("/ItemOrders/post", {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Form submission successful.');
            toggleActive();
        },
        onError: (error) => {
            console.error('Form submission error:', error);
        }
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.journalid = props.journalid;

    watch(() => props.journalid, (newValue) => {
        form.journalid = newValue;
    });
});
</script>

<template>
    <Modal title="Post all lines"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to post all entries associated with data ID {{ journalid }}?
                </div>
                <InputError :message="form.errors.ID" class="mt-2" />
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                CONFIRM
            </PrimaryButton>
        </template>
    </Modal>
</template>
