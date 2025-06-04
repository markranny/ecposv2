<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    GROUPID: {
        type: [String, Number],
        required: true,
    },
    NAME:{
        type: [String, Number],
        required: true,
    },
    
    
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    GROUPID: (''),
    NAME: (''),
    
});


const submitForm = () => {
    form.patch("/rbospecialgroups/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.GROUPID = props.GROUPID;
    form.NAME = props.NAME;
    

    watch(() => props.GROUPID, (newValue) => {
        form.GROUPID = newValue;
    });
    watch(() => props.NAME, (newValue) => {
        form.NAME = newValue;
    });
});
</script>

<template>
    <Modal title="Update Special Group" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <!-- <div class="col-span-1 ">
                    <InputLabel for="GROUPID" value="GROUPID" />
                    <TextInput
                        id="GROUPID"
                        v-model="form.GROUPID"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.GROUPID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.GROUPID" class="mt-2" />
                    </div>   -->
                    
                    <div class="col-span-3">
                        <InputLabel for="SPECIAL GROUP" value="SPECIAL GROUP" />
                    <TextInput
                        id="NAME"
                        v-model="form.NAME"
                        :is-error="form.errors.NAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.NAME" class="mt-2" />
                    </div>
                    </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                SUBMIT
            </PrimaryButton>
        </template>
    </Modal>
</template>
