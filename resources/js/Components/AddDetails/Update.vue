<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    JOURNALID:{
        type: [String, Number],
        required: true,
    },
    FGENCODER:{
        type: [String, Number],
        required: true,
    },
    PLENCODER:{
        type: [String, Number],
        required: true,
    },
    DISPATCHER: {
        type: [String, Number],
        required: true,
    },
    LOGISTICS: {
        type: [String, Number],
        required: true,
    },
    ROUTES: {
        type: [String, Number],
        required: true,
    },
    CREATEDDATE: {
        type: [String, Number],
        required: true,
    },
    DELIVERYDATE: {
        type: [String, Number],
        required: true,
    },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    FGENCODER: '',
    PLENCODER: '',
    DISPATCHER: '',
    LOGISTICS: '',
    ROUTES: '',
    CREATEDDATE: '',
    DELIVERYDATE: '',

});
const submitForm = () => {
    form.patch("/updatedetails/patch", {
        preserveScroll: true,
    });
    /* location.reload(); */
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.FGENCODER = props.FGENCODER;
    form.PLENCODER = props.PLENCODER;
    form.DISPATCHER = props.DISPATCHER;
    form.LOGISTICS = props.LOGISTICS;
    form.ROUTES = props.ROUTES;
    form.CREATEDDATE = props.CREATEDDATE;
    form.DELIVERYDATE = props.DELIVERYDATE;

    watch(() => props.FGENCODER, (newValue) => {
        form.FGENCODER = newValue;
    });
    watch(() => props.PLENCODER, (newValue) => {
        form.PLENCODER = newValue;
    });
    watch(() => props.DISPATCHER, (newValue) => {
        form.DISPATCHER = newValue;
    });
    watch(() => props.LOGISTICS, (newValue) => {
        form.LOGISTICS = newValue;
    });
    watch(() => props.ROUTES, (newValue) => {
        form.ROUTES = newValue;
    });
    watch(() => props.CREATEDDATE, (newValue) => {
        form.CREATEDDATE = newValue;
    });
    watch(() => props.DELIVERYDATE, (newValue) => {
        form.DELIVERYDATE = newValue;
    });

});
</script>

<template>
    <Modal title="UPDATE DETAILS" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-3">
                    <InputLabel for="FGENCODER" value="FINISH GOODS ENCODER" />
                    <TextInput
                        id="FGENCODER"
                        v-model="form.FGENCODER"
                                    type="text"
                                    class="mt-1 block w-full"
                        :is-error="form.errors.FGENCODER ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.PLENCODER" class="mt-2" />
                    </div> 

                    <div class="col-span-3 mt-4">
                    <InputLabel for="PACKINGLIST ENCODER" value="PACKINGLIST ENCODER" />
                    <TextInput
                        id="PLENCODER"
                        v-model="form.PLENCODER"
                                    type="text"
                                    class="mt-1 block w-full"
                        :is-error="form.errors.PLENCODER ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.PLENCODER" class="mt-2" />
                    </div> 

                    <div class="col-span-3 mt-4">
                    <InputLabel for="DISPATCHER" value="DISPATCHER" />
                    <TextInput
                        id="DISPATCHER"
                        v-model="form.DISPATCHER"
                        :is-error="form.errors.DISPATCHER ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISPATCHER" class="mt-2" />
                    </div>

                    <div class="col-span-3 mt-4">
                    <InputLabel for="LOGISTICS" value="LOGISTICS" />
                    <TextInput
                        id="LOGISTICS"
                        v-model="form.LOGISTICS"
                        :is-error="form.errors.LOGISTICS ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.LOGISTICS" class="mt-2" />
                    </div>

                    <div class="col-span-3 mt-4">
                    <InputLabel for="ROUTES" value="ROUTES" />
                    <TextInput
                        id="ROUTES"
                        v-model="form.ROUTES"
                        :is-error="form.errors.ROUTES ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        disabled
                    />
                    <InputError :message="form.errors.ROUTES" class="mt-2" />
                    </div>

                    <div class="col-span-3 mt-4">
                    <InputLabel for="CREATEDDATE" value="CREATEDDATE" />
                    <TextInput
                        id="CREATEDDATE"
                        v-model="form.CREATEDDATE"
                        :is-error="form.errors.CREATEDDATE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        disabled
                    />
                    <InputError :message="form.errors.CREATEDDATE" class="mt-2" />
                    </div>

                    <div class="col-span-3 mt-4">
                    <InputLabel for="DELIVERYDATE" value="DELIVERYDATE" />
                    <TextInput
                        id="DELIVERYDATE"
                        v-model="form.DELIVERYDATE"
                        :is-error="form.errors.DELIVERYDATE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        disabled
                    />
                    <InputError :message="form.errors.DELIVERYDATE" class="mt-2" />
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