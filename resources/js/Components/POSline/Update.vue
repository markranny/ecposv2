<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    offerid: {
        type: [String, Number],
        required: true,
    },
    lineid: {
        type: [String, Number],
        required: true,
    },
    producttype: {
        type: [String, Number],
        required: true,
    },
    id: {
        type: [String, Number],
        required: true,
    },
    dealpriceordiscpct: {
        type: [String, Number],
        required: true,
    },
    linegroup: {
        type: [String, Number],
        required: true,
    },
    disctype: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    offerid: '',
    lineid: '',
    producttype: '',
    id: '',
    dealpriceordiscpct: '',
    linegroup: '',
    disctype: '',
});

const submitForm = () => {
    form.patch("/posperiodicdiscountlines/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.offerid = props.offerid;
    form.lineid = props.lineid;
    form.producttype = props.producttype;
    form.id = props.id;
    form.dealpriceordiscpct = props.dealpriceordiscpct;
    form.linegroup = props.linegroup;
    form.disctype = props.disctype;
    
    watch(() => props.offerid, (newValue) => {
        form.offerid = newValue;
    });
    watch(() => props.lineid, (newValue) => {
        form.lineid = newValue;
    });
    watch(() => props.producttype, (newValue) => {
        form.producttype = newValue;
    });
    watch(() => props.id, (newValue) => {
        form.id = newValue;
    });
    watch(() => props.dealpriceordiscpct, (newValue) => {
        form.dealpriceordiscpct = newValue;
    });
    watch(() => props.linegroup, (newValue) => {
        form.linegroup = newValue;
    });
    watch(() => props.disctype, (newValue) => {
        form.disctype = newValue;
    });
});
</script>

<template>
    <Modal title="Posdiscount" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                        <InputLabel for="offerid" value="offerid" />
                        <TextInput
                            id="offerid"
                            v-model="form.offerid"
                            type="text"
                            class="mt-1 block w-full"
                            :is-error="form.errors.offerid ? true : false"
                            autofocus
                        />
                        <InputError :message="form.errors.offerid" class="mt-2" />
                    </div>  

                    <div class="col-span-2">
                        <InputLabel for="lineid" value="lineid" />
                        <TextInput
                            id="lineid"
                            v-model="form.lineid"
                            :is-error="form.errors.lineid ? true : false"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.lineid" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="producttype" value="producttype" />
                        <TextInput
                            id="producttype"
                            v-model="form.producttype"
                            :is-error="form.errors.producttype ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.producttype" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="id" value="id" />
                        <TextInput
                            id="id"
                            v-model="form.id"
                            :is-error="form.errors.id ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.id" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="dealpriceordiscpct" value="dealpriceordiscpct" />
                        <TextInput
                            id="dealpriceordiscpct"
                            v-model="form.dealpriceordiscpct"
                            :is-error="form.errors.dealpriceordiscpct ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.dealpriceordiscpct" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                        <InputLabel for="linegroup" value="linegroup" />
                        <TextInput
                            id="linegroup"
                            v-model="form.linegroup"
                            type="text"
                            class="mt-1 block w-full"
                            :is-error="form.errors.linegroup ? true : false"
                            autofocus
                        />
                        <InputError :message="form.errors.linegroup" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="disctype" value="disctype" />
                        <TextInput
                            id="disctype"
                            v-model="form.disctype"
                            :is-error="form.errors.disctype ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.disctype" class="mt-2" />
                    </div>
                    
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                submit
            </PrimaryButton>
        </template>
    </Modal>
</template>
