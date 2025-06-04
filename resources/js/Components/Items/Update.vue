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
    itemid: {
        type: [String, Number],
        required: true,
    },
    itemname: {
        type: [String, Number],
        required: true,
    },
    itemgroup: {
        type: [String, Number],
        required: true,
    },
    price: {
        type: [String, Number],
        required: true,
    },
    manilaprice: {
        type: [String, Number],
        required: true,
    },
    foodpandaprice: {
        type: [String, Number],
        required: true,
    },
    grabfoodprice: {
        type: [String, Number],
        required: true,
    },
    mallprice: {
        type: [String, Number],
        required: true,
    },
    production: {
        type: [String, Number],
        required: true,
    },
    moq: {
        type: [String, Number],
        required: true,
    },
    cost: {
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
    itemname: '',
    itemgroup: '',
    price: '',
    manilaprice: '',
    foodpandaprice: '',
    grabfoodprice: '',
    mallprice: '',
    cost: '',
    production: '',
    moq: '',
});

const submitForm = () => {
    form.patch("/items/patch", {
        preserveScroll: true,
    });
    location.reload();
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    // Initialize all form fields
    form.itemid = props.itemid;
    form.itemname = props.itemname;
    form.itemgroup = props.itemgroup;
    form.price = props.price;
    form.manilaprice = props.manilaprice;
    form.foodpandaprice = props.foodpandaprice;
    form.grabfoodprice = props.grabfoodprice;
    form.mallprice = props.mallprice;
    form.cost = props.cost;
    form.production = props.production;
    form.moq = props.moq;

    // Watch for changes in all props
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

    watch(() => props.manilaprice, (newValue) => {
        form.manilaprice = newValue;
    });

    watch(() => props.foodpandaprice, (newValue) => {
        form.foodpandaprice = newValue;
    });

    watch(() => props.grabfoodprice, (newValue) => {
        form.grabfoodprice = newValue;
    });

    watch(() => props.mallprice, (newValue) => {
        form.mallprice = newValue;
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
    <Modal title="Update Form" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <FormComponent @submit.prevent="submitForm">
                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="itemid" value="PRODUCTCODE" />
                    <TextInput
                        id="itemid"
                        v-model="form.itemid"
                        type="text"
                        class="mt-1 block w-full bg-blue-50"
                        :is-error="form.errors.itemid ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.itemid" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="itemname" value="DESCRIPTION" class="pt-4" />
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
                    <InputLabel for="cost" value="COST" class="pt-4" />
                    <TextInput
                        id="cost"
                        v-model="form.cost"
                        :is-error="form.errors.cost ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.cost" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="price" value="SRP" class="pt-4" />
                    <TextInput
                        id="price"
                        v-model="form.price"
                        :is-error="form.errors.price ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.price" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="manilaprice" value="MANILA PRICE" class="pt-4" />
                    <TextInput
                        id="manilaprice"
                        v-model="form.manilaprice"
                        :is-error="form.errors.manilaprice ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.manilaprice" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="foodpandaprice" value="FOODPANDA PRICE" class="pt-4" />
                    <TextInput
                        id="foodpandaprice"
                        v-model="form.foodpandaprice"
                        :is-error="form.errors.foodpandaprice ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.foodpandaprice" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="grabfoodprice" value="GRABFOOD PRICE" class="pt-4" />
                    <TextInput
                        id="grabfoodprice"
                        v-model="form.grabfoodprice"
                        :is-error="form.errors.grabfoodprice ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.grabfoodprice" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4">
                    <InputLabel for="mallprice" value="MALL PRICE" class="pt-4" />
                    <TextInput
                        id="mallprice"
                        v-model="form.mallprice"
                        :is-error="form.errors.mallprice ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.mallprice" class="mt-2" />
                </div>

                <div class="col-span-6 sm:col-span-4 mt-2">
                    <InputLabel for="DESIGNATE" value="DESIGNATE" />
                    <SelectOption 
                        id="production"
                        v-model="form.production" 
                        :is-error="form.errors.production ? true : false"
                        class="mt-1 block w-full !bg-white"
                    >
                        <option disabled value="">Select an option</option>
                        <option>BREADS&PASTRIES</option>
                        <option>CAKELAB</option>
                    </SelectOption>
                    <InputError :message="form.errors.production" class="mt-2" />
                </div>
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton 
                type="submit" 
                @click="submitForm" 
                :disabled="form.processing" 
                :class="{ 'opacity-25': form.processing }"
            >
                UPDATE
            </PrimaryButton>
        </template>
    </Modal>
</template>