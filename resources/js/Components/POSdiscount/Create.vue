<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    discvalidperiodid: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    offerId: '',
    description: '',
    status: '',
    pdType: '',
    priority: '',
    discvalidperiodid: '',
    discountType: '',
    noOfLinesToTrigger: '',
    dealPriceValue: '',
    discountPctValue: '',
    discountAmountValue: '',
    priceGroup: '',
});

const submitForm = () => {
    form.post("/posperiodicdiscounts", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};
</script>

<template>
    <Modal title="Mix and Match Entries" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="p-4 max-h-[90vh] md:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Hidden Offer ID -->
                    <div class="hidden">
                        <InputLabel for="offerId" value="Offer ID" />
                        <TextInput
                            id="offerId"
                            v-model="form.offerId"
                            type="text"
                            class="mt-1 block w-full"
                            :is-error="form.errors.offerId ? true : false"
                        />
                        <InputError :message="form.errors.offerId" class="mt-2" />
                    </div>

                    <!-- Description - Full Width -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
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

                    <!-- Status -->
                    <div class="col-span-1">
                        <InputLabel for="status" value="Status" />
                        <select 
                            class="w-full p-2 border rounded"
                            id="status"
                            name="status"
                            v-model="form.status"
                            :class="{ 'border-red-500': form.errors.status }"
                        >
                            <option disabled value="">Select Status</option>
                            <option value="1">Enabled</option>
                            <option value="0">Disabled</option>
                        </select>
                        <InputError :message="form.errors.status" class="mt-2" />
                    </div>

                    <!-- Discount Type -->
                    <div class="col-span-1">
                        <InputLabel for="discountType" value="Discount Type" />
                        <select 
                            class="w-full p-2 border rounded"
                            id="discountType"
                            name="discountType"
                            v-model="form.discountType"
                            :class="{ 'border-red-500': form.errors.discountType }"
                        >
                            <option disabled value="">Select Type</option>
                            <option value="0">Deal Price</option>
                            <option value="1">Discount Percent</option>
                            <option value="2">Discount Amount</option>
                            <option value="3">Line Specific</option>
                        </select>
                        <InputError :message="form.errors.discountType" class="mt-2" />
                    </div>

                    <!-- Discount Valid Period -->
                    <div class="col-span-1">
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

                    <!-- Value Fields -->
                    <div class="col-span-1">
                        <InputLabel for="dealPriceValue" value="Deal Price" />
                        <TextInput
                            id="dealPriceValue"
                            v-model="form.dealPriceValue"
                            :is-error="form.errors.dealPriceValue ? true : false"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.dealPriceValue" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="discountPctValue" value="Discount %" />
                        <TextInput
                            id="discountPctValue"
                            v-model="form.discountPctValue"
                            :is-error="form.errors.discountPctValue ? true : false"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.discountPctValue" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="discountAmountValue" value="Discount Amount" />
                        <TextInput
                            id="discountAmountValue"
                            v-model="form.discountAmountValue"
                            :is-error="form.errors.discountAmountValue ? true : false"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.discountAmountValue" class="mt-2" />
                    </div>
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton 
                type="submit"   
                @click="submitForm" 
                :disabled="form.processing" 
                class="w-full sm:w-auto"
                :class="{ 'opacity-25': form.processing }"
            >
                Submit
            </PrimaryButton>
        </template>
    </Modal>
</template>