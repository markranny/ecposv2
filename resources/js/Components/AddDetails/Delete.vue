<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    JOURNALID:{
        type: [String, Number],
        required: true,
    },
    LINENUM:{
        type: [String, Number],
        required: true,
    }
});

const form = useForm({
    JOURNALID: '',
    LINENUM: '',
});

const submitForm = () => {
    form.delete("/ItemOrders/destroy", {
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
    form.LINENUM = props.LINENUM;

    watch(() => props.JOURNALID, (newValue) => {
        form.JOURNALID = newValue;
    });

    watch(() => props.LINENUM, (newValue) => {
        form.LINENUM = newValue;
    });
});
</script>

<template>
    <Modal title="Confirm Deletion"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="mt-3" method="DELETE">
                <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1">
                    Are you sure you want to delete this {{ LINENUM }} with an ID of {{ JOURNALID }}?
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
