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
    // Added default fields
    default1: {
        type: [String, Number, Boolean],
        default: 0,
    },
    default2: {
        type: [String, Number, Boolean],
        default: 0,
    },
    default3: {
        type: [String, Number, Boolean],
        default: 0,
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
    // Added default fields to form
    default1: false,
    default2: false,
    default3: false,
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
    // Initialize default fields
    form.default1 = Boolean(Number(props.default1));
    form.default2 = Boolean(Number(props.default2));
    form.default3 = Boolean(Number(props.default3));

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

    // Watch for default fields changes
    watch(() => props.default1, (newValue) => {
        form.default1 = Boolean(Number(newValue));
    });

    watch(() => props.default2, (newValue) => {
        form.default2 = Boolean(Number(newValue));
    });

    watch(() => props.default3, (newValue) => {
        form.default3 = Boolean(Number(newValue));
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

                <!-- Added Default Fields Section -->
                <div class="col-span-6 sm:col-span-4 mt-6">
                    <InputLabel value="DEFAULT SETTINGS" class="text-lg font-semibold mb-4" />
                    
                    <div class="space-y-3">
                        <!-- Default 1 -->
                        <div class="flex items-center">
                            <input
                                id="default1"
                                type="checkbox"
                                v-model="form.default1"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            />
                            <label for="default1" class="ml-2 text-sm font-medium text-gray-900">
                                Default 1
                            </label>
                        </div>
                        <InputError :message="form.errors.default1" class="mt-1" />

                        <!-- Default 2 -->
                        <div class="flex items-center">
                            <input
                                id="default2"
                                type="checkbox"
                                v-model="form.default2"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            />
                            <label for="default2" class="ml-2 text-sm font-medium text-gray-900">
                                Default 2
                            </label>
                        </div>
                        <InputError :message="form.errors.default2" class="mt-1" />

                        <!-- Default 3 -->
                        <div class="flex items-center">
                            <input
                                id="default3"
                                type="checkbox"
                                v-model="form.default3"
                                class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                            />
                            <label for="default3" class="ml-2 text-sm font-medium text-gray-900">
                                Default 3
                            </label>
                        </div>
                        <InputError :message="form.errors.default3" class="mt-1" />
                    </div>
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