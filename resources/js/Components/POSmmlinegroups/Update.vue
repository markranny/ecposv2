<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    ID:{
        type: [String, Number],
        required: true,
    },
    DESCRIPTION: {
        type: [String, Number],
        required: true,
    },
    STARTINGDATE: {
        type: [String, Number],
        required: true,
    },
    ENDINGDATE: {
        type: [String, Number],
        required: true,
    },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    ID: '',
    DESCRIPTION: '',
    STARTINGDATE: '',
    ENDINGDATE: '',

});
const submitForm = () => {
    form.patch("/posdiscvalidationperiods/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.ID = props.ID;
    form.DESCRIPTION = props.DESCRIPTION;
    form.STARTINGDATE = props.STARTINGDATE;
    form.ENDINGDATE = props.ENDINGDATE;


    watch(() => props.ID, (newValue) => {
        form.ID = newValue;
    });
    watch(() => props.DESCRIPTION, (newValue) => {
        form.DESCRIPTION = newValue;
    });
    watch(() => props.STARTINGDATE, (newValue) => {
        form.STARTINGDATE = newValue;
    });
    watch(() => props.ENDINGDATE, (newValue) => {
        form.ENDINGDATE = newValue;
    });

});
</script>

<template>
    <Modal title="Posvalidation" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-3 ">
                    <InputLabel for="ID" value="ID" />
                    <TextInput
                        id="ID"
                        v-model="form.ID"
                        type="text"
                        class="mt-1 block w-full input"
                        :is-error="form.errors.ID ? true : false"
                        autofocus
                        disabled
                    />
                    <InputError :message="form.errors.ID" class="mt-2" />
                    </div>  

                    <div class="col-span-3 mt-4">
                    <InputLabel for="DESCRIPTION" value="DESCRIPTION" />
                    <TextInput
                        id="DESCRIPTION"
                        v-model="form.DESCRIPTION"
                        :is-error="form.errors.DESCRIPTION ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DESCRIPTION" class="mt-2" />
                    </div>

                    <div class="col-span-1 mt-4">
                        <InputLabel for="STARTINGDATE" value="STARTINGDATE" />
                        <TextInput
                        id="STARTINGDATE"
                        v-model="form.STARTINGDATE"
                        :is-error="form.errors.STARTINGDATE ? true : false"
                        type="date"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.STARTINGDATE" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4 ml-40">
                        <InputLabel for="ENDINGDATE" value="ENDINGDATE" />
                    <TextInput
                        id="ENDINGDATE"
                        v-model="form.ENDINGDATE"
                        :is-error="form.errors.ENDINGDATE ? true : false"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ENDINGDATE" class="mt-2" />
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