<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    POSTINGDATE:{
        type: [String, Number],
        required: true,
    },
    ITEMID: {
        type: [String, Number],
        required: true,
    },
    STOREID: {
        type: [String, Number],
        required: true,
    },
    ADJUSTMENT: {
        type: [String, Number],
        required: true,
    },
    TYPE: {
        type: [String, Number],
        required: true,
    },
    COSTPRICEPERITEM: {
        type: [String, Number],
        required: true,
    },
    SALESPRICEWITHOUTTAXPERITEM: {
        type: [String, Number],
        required: true,
    },
    SALESPRICEWITHTAXPERITEM: {
        type: [String, Number],
        required: true,
    },
    REASONCODE: {
        type: [String, Number],
        required: true,
    },
    DISCOUNTAMOUNTPERITEM: {
        type: [String, Number],
        required: true,
    },
    UNITID: {
        type: [String, Number],
        required: true,
    },
    ADJUSTMENTININVENTORYUNIT: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    POSTINGDATE: '',
    ITEMID: '',
    STOREID: '',
    ADJUSTMENT: '',
    TYPE: '',
    COSTPRICEPERITEM: '',
    SALESPRICEWITHOUTTAXPERITEM: '',
    SALESPRICEWITHTAXPERITEM: '',
    REASONCODE: '',
    DISCOUNTAMOUNTPERITEM: '',
    UNITID: '',
    ADJUSTMENTININVENTORYUNIT: '',
});

const submitForm = () => {
    form.patch("/inventtrans/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.POSTINGDATE = props.POSTINGDATE;
    form.ITEMID = props.ITEMID;
    form.STOREID = props.STOREID;
    form.ADJUSTMENT = props.ADJUSTMENT;
    form.TYPE = props.TYPE;
    form.COSTPRICEPERITEM = props.COSTPRICEPERITEM;
    form.SALESPRICEWITHOUTTAXPERITEM = props.SALESPRICEWITHOUTTAXPERITEM;
    form.SALESPRICEWITHTAXPERITEM = props.SALESPRICEWITHTAXPERITEM;
    form.REASONCODE = props.REASONCODE;
    form.DISCOUNTAMOUNTPERITEM = props.DISCOUNTAMOUNTPERITEM;
    form.UNITID = props.UNITID;
    form.ADJUSTMENTININVENTORYUNIT = props.ADJUSTMENTININVENTORYUNIT;

    watch(() => props.POSTINGDATE, (newValue) => {
        form.POSTINGDATE = newValue;
    });
    watch(() => props.ITEMID, (newValue) => {
        form.ITEMID = newValue;
    });
    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });
    watch(() => props.ADJUSTMENT, (newValue) => {
        form.ADJUSTMENT = newValue;
    });
    watch(() => props.TYPE, (newValue) => {
        form.TYPE = newValue;
    });
    watch(() => props.COSTPRICEPERITEM, (newValue) => {
        form.COSTPRICEPERITEM = newValue;
    });
    watch(() => props.SALESPRICEWITHOUTTAXPERITEM, (newValue) => {
        form.SALESPRICEWITHOUTTAXPERITEM = newValue;
    });
    watch(() => props.SALESPRICEWITHTAXPERITEM, (newValue) => {
        form.SALESPRICEWITHTAXPERITEM = newValue;
    });
    watch(() => props.REASONCODE, (newValue) => {
        form.REASONCODE = newValue;
    });
    watch(() => props.DISCOUNTAMOUNTPERITEM, (newValue) => {
        form.DISCOUNTAMOUNTPERITEM = newValue;
    });
    watch(() => props.UNITID, (newValue) => {
        form.UNITID = newValue;
    });
    watch(() => props.ADJUSTMENTININVENTORYUNIT, (newValue) => {
        form.ADJUSTMENTININVENTORYUNIT = newValue;
    });
});
</script>

<template>
    <Modal title="Transactions" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="POSTINGDATE" value="POSTINGDATE" />
                    <TextInput
                        id="POSTINGDATE"
                        v-model="form.POSTINGDATE"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.POSTINGDATE ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.ITEMID" class="mt-2" />
                    </div>  
                    <div class="col-span-2">
                    <InputLabel for="ITEMID" value="ITEMID" />
                    <TextInput
                        id="ITEMID"
                        v-model="form.ITEMID"
                        :is-error="form.errors.ITEMID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.STOREID" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="STOREID" value="STOREID" />
                        <TextInput
                        id="STOREID"
                        v-model="form.STOREID"
                        :is-error="form.errors.STOREID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.STOREID" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="ADJUSTMENT" value="ADJUSTMENT" />
                    <TextInput
                        id="ADJUSTMENT"
                        v-model="form.ADJUSTMENT"
                        :is-error="form.errors.ADJUSTMENT ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ADJUSTMENT" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="TYPE" value="TYPE" />
                    <TextInput
                        id="TYPE"
                        v-model="form.TYPE"
                        :is-error="form.errors.TYPE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.TYPE" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="COSTPRICEPERITEM" value="COSTPRICEPERITEM" />
                    <TextInput
                        id="COSTPRICEPERITEM"
                        v-model="form.COSTPRICEPERITEM"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.COSTPRICEPERITEM ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.COSTPRICEPERITEM" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="SALESPRICEWITHOUTTAXPERITEM" value="SALESPRICEWITHOUTTAXPERITEM" />
                    <TextInput
                        id="SALESPRICEWITHOUTTAXPERITEM"
                        v-model="form.SALESPRICEWITHOUTTAXPERITEM"
                        :is-error="form.errors.SALESPRICEWITHOUTTAXPERITEM ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.SALESPRICEWITHOUTTAXPERITEM" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="SALESPRICEWITHTAXPERITEM" value="SALESPRICEWITHTAXPERITEM" />
                    <TextInput
                        id="SALESPRICEWITHTAXPERITEM"
                        v-model="form.SALESPRICEWITHTAXPERITEM"
                        :is-error="form.errors.SALESPRICEWITHTAXPERITEM ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.SALESPRICEWITHTAXPERITEM" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="REASONCODE" value="REASONCODE" />
                    <TextInput
                        id="REASONCODE"
                        v-model="form.REASONCODE"
                        :is-error="form.errors.REASONCODE ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.REASONCODE" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="DISCOUNTAMOUNTPERITEM" value="DISCOUNTAMOUNTPERITEM" />
                    <TextInput
                        id="DISCOUNTAMOUNTPERITEM"
                        v-model="form.DISCOUNTAMOUNTPERITEM"
                        :is-error="form.errors.DISCOUNTAMOUNTPERITEM ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOUNTAMOUNTPERITEM" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="UNITID" value="UNITID" />
                    <TextInput
                        id="UNITID"
                        v-model="form.UNITID"
                        :is-error="form.errors.UNITID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.UNITID" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="ADJUSTMENTININVENTORYUNIT" value="ADJUSTMENTININVENTORYUNIT" />
                    <TextInput
                        id="ADJUSTMENTININVENTORYUNIT"
                        v-model="form.ADJUSTMENTININVENTORYUNIT"
                        :is-error="form.errors.ADJUSTMENTININVENTORYUNIT ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ADJUSTMENTININVENTORYUNIT" class="mt-2" />
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
