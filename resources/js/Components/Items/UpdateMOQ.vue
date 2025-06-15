<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';
import SelectOption from '@/Components/SelectOption/SelectOption.vue';

const props = defineProps({
    itemid:{
        type: [String, Number],
        required: true,
    },

    itemname:{
            type: [String, Number],
            required: true,
        },

    itemgroup:{
            type: [String, Number],
            required: true,
        },

    price:{
            type: [String, Number],
            required: true,
        },

    production:{
            type: [String, Number],
            required: true,
    },

    moq:{
            type: [String, Number],
            required: true,
    },

    cost:{
            type: [String, Number],
            required: true,
        },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    itemid: 0,
    itemname: (''),
    itemgroup: (''),
    price: (''),
    cost: (''),
    production: (''),
    moq: (''),
});


const submitForm = () => {
    // Fixed: Use the correct RESTful route with the itemid parameter
    form.patch(`/items/${props.itemid}`, {
        preserveScroll: true,
        onSuccess: () => {
            // Emit event to close modal instead of page reload
            toggleActive();
        },
        onError: (errors) => {
            console.error('Update failed:', errors);
        }
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.itemid = props.itemid;
    form.itemname = props.itemname;
    form.itemgroup = props.itemgroup;
    form.price = props.price;
    form.cost = props.cost;
    form.production = props.production;
    form.moq = props.moq;

    watch(() => props.itemid, (newValue) => {
        form.itemid = newValue;
    });

    watch(() => props.itemname, (newValue) => {
        form.itemname = newValue;
    });

    watch(() => props.itemgroup, (newValue) => {
        form.itemgroup = newValue;
    });

    watch(() => props.price, (newValue) => {
        form.price = newValue;
    });

    watch(() => props.cost, (newValue) => {
        form.cost = newValue;
    });

    watch(() => props.production, (newValue) => {
        form.production = newValue;
    });

    watch(() => props.moq, (newValue) => {
        form.moq = newValue;
    });
});
</script>

<template>
    <Modal title="Update Form"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="itemid" value="PRODUCTCODE"/>
                    <TextInput
                        id="itemid"
                        v-model="form.itemid"
                        type="text"
                        class="mt-1 block w-full bg-blue-50 "
                        :is-error="form.errors.itemid ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.itemid" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="itemname" value="DESCRIPTION" class="pt-4"/>
                        <TextInput
                        id="itemname"
                        v-model="form.itemname"
                        :is-error="form.errors.itemname ? true : false"
                        type="text"
                        class="mt-1 block w-full bg-blue-50"
                        disabled
                        />
                        <InputError :message="form.errors.itemname" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                        <InputLabel for="MOQ" value="MOQ" class="pt-4"/>
                        <TextInput
                        id="price"
                        v-model="form.moq"
                        :is-error="form.errors.moq ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.moq" class="mt-2" />
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
