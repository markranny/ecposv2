<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';
import SelectOption from '@/Components/SelectOption/SelectOption.vue';

const props = defineProps({
    STOREID:{
        type: [String, Number],
        required: true,
    },
    
    NAME:{
            type: [String, Number],
            required: true,
        },

    ROUTES:{
        type: [String, Number],
        required: true,
    },
    TYPES:{
        type: [String, Number],
        required: true,
    },
    BLOCKED:{
        type: [String, Number],
        required: true,
    },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    STOREID: (''),
    NAME: (''),
    ROUTES: (''),
    TYPES: (''),
    BLOCKED: (''),
});

const submitForm = () => {
    form.patch("/store/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.STOREID = props.STOREID;
    form.NAME = props.NAME;
    form.ROUTES = props.ROUTES;

    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });

    watch(() => props.NAME, (newValue) => {
        form.NAME = newValue;
    });
    watch(() => props.ROUTES, (newValue) => {
        form.ROUTES = newValue;
    });
    watch(() => props.TYPES, (newValue) => {
        form.TYPES = newValue;
    });

});
</script>

<template>
    <Modal title="Update Form"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="STOREID" value="STOREID"/>
                    <TextInput
                        id="STOREID"
                        v-model="form.STOREID"
                        type="text"
                        class="mt-1 block w-full bg-blue-50 "
                        :is-error="form.errors.STOREID ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.STOREID" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="NAME" value="NAME" class="pt-4"/>
                        <TextInput
                        id="NAME"
                        v-model="form.NAME"
                        :is-error="form.errors.NAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.NAME" class="mt-2" />
                </div>

                <!-- <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="ROUTES" value="ROUTES" class="pt-4"/>
                        <TextInput
                        id="ROUTES"
                        v-model="form.ROUTES"
                        :is-error="form.errors.ROUTES ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.ROUTES" class="mt-2" />
                </div> -->

                <!-- <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="ROUTES" value="ROUTES" class="pt-4"/>
                        <Select
                            id="ROUTES"
                            v-model="form.ROUTES" 
                            :is-error="form.errors.ROUTES ? true : false"
                            class="select select-bordered w-full max-w-xs"
                            >
                            <option disabled value="">Select an option</option>
                            <option value="SOUTH 1">SOUTH 1</option>
                            <option value="SOUTH 2">SOUTH 2</option>
                            <option value="SOUTH3">SOUTH3</option>
                            <option value="NORTH 1">NORTH 1</option>
                            <option value="NORTH 2">NORTH 2</option>
                            <option value="CENTRAL">CENTRAL</option>
                            <option value="EAST">EAST</option>
                        </Select>
                    <InputError :message="form.errors.ROUTES" class="mt-2" />
                </div> -->

                <div class="col-span-6 mt-2 sm:col-span-4">
                                <InputLabel for="BLOCKED" value="BLOCKED" />
                                <SelectOption 
                                    id="BLOCKED"
                                    v-model="form.BLOCKED" 
                                    :is-error="form.errors.BLOCKED ? true : false"
                                    class="mt-1 block w-full !bg-gray-100"
                                    >
                                    <option disabled value="">Select an option</option>
                                    <option value="0">UNBLOCK</option>
                                    <option value="1">BLOCK</option>
                                </SelectOption>
                                <InputError :message="form.errors.BLOCKED" class="mt-2" />
                            </div>

                <div class="col-span-6 mt-2 sm:col-span-4">
                                <InputLabel for="ROUTES" value="ROUTES" />
                                <SelectOption 
                                    id="ROUTES"
                                    v-model="form.ROUTES" 
                                    :is-error="form.errors.ROUTES ? true : false"
                                    class="mt-1 block w-full !bg-gray-100"
                                    >
                                    <option disabled value="">Select an option</option>
                                    <option >SOUTH 1</option>
                                    <option >SOUTH 2</option>
                                    <option >SOUTH3</option>
                                    <option >NORTH 1</option>
                                    <option >NORTH 2</option>
                                    <option >CENTRAL</option>
                                    <option >EAST</option>
                                </SelectOption>
                                <InputError :message="form.errors.ROUTES" class="mt-2" />
                            </div>

                            <div class="col-span-6 mt-2 sm:col-span-4">
                                <InputLabel for="TYPES" value="TYPES" />
                                <SelectOption 
                                    id="TYPES"
                                    v-model="form.TYPES" 
                                    :is-error="form.errors.TYPES ? true : false"
                                    class="mt-1 block w-full !bg-gray-100"
                                    >
                                    <option disabled value="">Select an option</option>
                                    <option >NONE</option>
                                    <option >MOQ</option>
                                </SelectOption>
                                <InputError :message="form.errors.TYPES" class="mt-2" />
                            </div>
                
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                UPDATE
            </PrimaryButton>
        </template>
    </Modal>
</template>
