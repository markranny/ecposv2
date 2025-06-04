<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    offerid: {
        type: [String, Number],
        required: true,
    },
    description: {
        type: [String, Number],
        required: true,
    },
    status: {
        type: [String, Number],
        required: true,
    },
    discounttype: {
        type: [String, Number],
        required: true,
    },
    dealpricevalue: {
        type: [String, Number],
        required: true,
    },
    discountpctvalue: {
        type: [String, Number],
        required: true,
    },
    discountamountvalue: {
        type: [String, Number],
        required: true,
    },
    discvalidperiodid: {
        type: Array,
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    offerid: '',
    description: '',
    status: '',
    discvalidperiodid: '',
    discounttype: '',
    dealpricevalue: '',
    discountpctvalue: '',
    discountamountvalue: '',
});

const submitForm = () => {
    form.patch("/posperiodicdiscounts/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    // Initialize form with props
    form.offerid = props.offerid;
    form.description = props.description;
    form.status = props.status;
    form.discvalidperiodid = props.discvalidperiodid;
    form.discounttype = props.discounttype;
    form.dealpricevalue = props.dealpricevalue;
    form.discountpctvalue = props.discountpctvalue;
    form.discountamountvalue = props.discountamountvalue;
});

// Watch for props changes
watch(() => props.offerid, (newValue) => {
    form.offerid = newValue;
});

watch(() => props.description, (newValue) => {
    form.description = newValue;
});

watch(() => props.status, (newValue) => {
    form.status = newValue;
});

watch(() => props.discounttype, (newValue) => {
    form.discounttype = newValue;
});

watch(() => props.dealpricevalue, (newValue) => {
    form.dealpricevalue = newValue;
});

watch(() => props.discountpctvalue, (newValue) => {
    form.discountpctvalue = newValue;
});

watch(() => props.discountamountvalue, (newValue) => {
    form.discountamountvalue = newValue;
});

watch(() => props.discvalidperiodid, (newValue) => {
    form.discvalidperiodid = newValue;
});
</script>

<template>
    <Modal title="Update Discount" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-6">
                    <div class="col-span-6">
                        <InputLabel for="offerid" value="Offer ID" />
                        <TextInput
                            id="offerid"
                            v-model="form.offerid"
                            type="text"
                            class="mt-1 block input input-bordered w-full"
                            :is-error="form.errors.offerid ? true : false"
                            autofocus
                            disabled
                        />
                        <InputError :message="form.errors.offerid" class="mt-2" />
                    </div>

                    <div class="col-span-6 mt-4">
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            v-model="form.description"
                            :is-error="form.errors.description ? true : false"
                            type="text"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4">
                        <InputLabel for="status" value="Status" />
                        <select 
                            class="select select-bordered w-full max-w-xs"
                            id="status"
                            name="status"
                            v-model="form.status"
                            :is-error="form.errors.status ? true : false"
                            autofocus
                        >
                            <option disabled selected>Status</option>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <InputError :message="form.errors.status" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4 mt-4">
                        <InputLabel for="discounttype" value="Discount Type" />
                        <select 
                            class="select select-bordered w-full max-w-xs"
                            id="discounttype"
                            name="discounttype"
                            v-model="form.discounttype"
                            :is-error="form.errors.discounttype ? true : false"
                            autofocus
                        >
                            <option disabled selected>Discount Type</option>
                            <option value="0">Deal Price</option>
                            <option value="1">Discount Percent</option>
                            <option value="2">Discount Amount</option>
                            <option value="3">Line Specific</option>
                        </select>
                        <InputError :message="form.errors.discounttype" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4 ml-4">
                        <InputLabel for="discvalidperiodid" value="Valid Period" />
                        <select
                            class="w-full p-2 border rounded"
                            id="discvalidperiodid"
                            name="discvalidperiodid"
                            v-model="form.discvalidperiodid"
                            :class="{ 'border-red-500': form.errors.discvalidperiodid }"
                        >
                            <option disabled value="">Select Period</option>
                            <option value="">None</option>
                            <option 
                                v-for="period in discvalidperiodid" 
                                :key="period.id" 
                                :value="period.id"
                            >
                                {{ period.description }}
                            </option>
                        </select>
                        <InputError :message="form.errors.discvalidperiodid" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4">
                        <InputLabel for="dealpricevalue" value="Deal Price Value" />
                        <TextInput
                            id="dealpricevalue"
                            v-model="form.dealpricevalue"
                            :is-error="form.errors.dealpricevalue ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.dealpricevalue" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4 ml-4">
                        <InputLabel for="discountpctvalue" value="Discount Percent Value" />
                        <TextInput
                            id="discountpctvalue"
                            v-model="form.discountpctvalue"
                            :is-error="form.errors.discountpctvalue ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.discountpctvalue" class="mt-2" />
                    </div>

                    <div class="col-span-2 mt-4 ml-4">
                        <InputLabel for="discountamountvalue" value="Discount Amount Value" />
                        <TextInput
                            id="discountamountvalue"
                            v-model="form.discountamountvalue"
                            :is-error="form.errors.discountamountvalue ? true : false"
                            type="number"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.discountamountvalue" class="mt-2" />
                    </div>
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                Submit
            </PrimaryButton>
        </template>
    </Modal>
</template>
