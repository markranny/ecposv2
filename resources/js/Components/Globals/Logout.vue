<script setup>
import { router } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    }
});

const logout = () => {
    router.post(route('logout'));
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};
</script>

<template>
    <Modal title="Confirm Logout" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <div class="col-span-6 sm:col-span-4 font-thin text-sm px-1 mt-5">
                Are you sure you want to logout?
            </div>
        </template>
        <template #buttons>
            <PrimaryButton type="button" @click="logout" >
                LOGOUT
            </PrimaryButton>
        </template>
    </Modal>
</template>
