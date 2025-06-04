<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import SelectOption from '@/Components/SelectOption/SelectOption.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';


const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const selectStore = (selectStore) => {
    form.storeid = selectStore;
};

/* const initialStoreId = computed(() => {
  return $page.props.auth.user.storeid;
}); */

const form = useForm({
    storeid: '',
    customername: '',
    address: '',
    telno: '',
    datepickedup: '',
    timepickedup: '',
    delivered: '',
    timedelivered: '',
    dedication: '',
    bdaycodeno: '',
    flavor: '',
    motif: '',
    icing: '',
    others: '',
    srp: '',
    discount: '',
    partialpayment: '',
    netamount: '',
    balanceamount: '',
    file_path: null,
});

const handleFileUpload = (event) => {
  form.file_path = event.target.files[0];
};

/* const submitForm = () => {
    form.post("/partycakes", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}; */

const submitForm = () => {
    let formData = new FormData();
    for (let key in form) {
        formData.append(key, form[key]);
    }
    
    form.post("/partycakes", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        data: formData,
    });

    /* location.reload(); */
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

</script>

<template>
    <Modal title="ORDER PARTYCAKE" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm" enctype="multipart/form-data">
                
                <div class="grid grid-cols-1">
                    <div class="col-span-5">
                        <div class="grid gap-4">

                            <div class="col-span-12">
                                <InputLabel for="file" value="Attachment" />
                                <input
                                    type="file"
                                    id="file"
                                    @change="handleFileUpload"
                                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                                />
                                <InputError :message="form.errors.file" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Del. Address" value="Del. Address" />
                                <select id="storeid" v-model="form.storeid" class="select !bg-white select-bordered w-full" @change="selectStore(form.storeid)">
                                    <option v-for="store in rbostoretables" :key="store.storeid" :value="store.NAME">
                                        {{ store.NAME }}
                                    </option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.storeid" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Customer Name" value="Customer Name" />
                                <TextInput
                                    id="customername"
                                    v-model="form.customername"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :is-error="form.errors.customername ? true : false"
                                    autofocus
                                />
                                <InputError :message="form.errors.customername" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <div>
                                <InputLabel for="Tel No." value="Tel No." />
                                <TextInput
                                    id="telno"
                                    v-model="form.telno"
                                    :is-error="form.errors.telno ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.telno" class="mt-2" />
                            </div>
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Address" value="Address" />
                                <TextInput
                                    id="address"
                                    v-model="form.address"
                                    :is-error="form.errors.address ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.address" class="mt-2" />
                            </div>

                            <div class="col-span-11">
                                <InputLabel for="Datepickup" value="Date Pick Up" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="date"
                                        id="datepickedup" 
                                        v-model="form.datepickedup"
                                        :is-error="form.errors.datepickedup ? true : false"
                                        class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                        required />
                                    <InputError :message="form.errors.datepickedup" class="mt-2" />
                                </div> 
                            </div>

                            <div class="col-span-1">
                                <InputLabel for="Timepickup" value="Time Pick Up" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="time"
                                        id="timepickedup" 
                                        v-model="form.timepickedup"
                                        :is-error="form.errors.timepickedup ? true : false"
                                        class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                        required />
                                    <InputError :message="form.errors.timepickedup" class="mt-2" />
                                </div> 
                            </div>

                            <!-- <div class="col-span-11">
                                <InputLabel for="Delivered" value="Delivered" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="date"
                                        id="delivered" 
                                        v-model="form.delivered"
                                        :is-error="form.errors.delivered ? true : false"
                                        class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                        required />
                                    <InputError :message="form.errors.delivered" class="mt-2" />
                                </div> 
                            </div> -->

                            <!-- <div class="col-span-1">
                                <InputLabel for="Timedelivered" value="Time Delivered" />
                                <div class="relative">
                                    <div class="absolute inset-y-0 end-0 top-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                        <path fill-rule="evenodd" d="M2 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10S2 17.523 2 12Zm11-4a1 1 0 1 0-2 0v4a1 1 0 0 0 .293.707l3 3a1 1 0 0 0 1.414-1.414L13 11.586V8Z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <input 
                                        type="time"
                                        id="timedelivered" 
                                        v-model="form.timedelivered"
                                        :is-error="form.errors.timedelivered ? true : false"
                                        class="bg-gray-50 border leading-none border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                                        required />
                                    <InputError :message="form.errors.timedelivered" class="mt-2" />
                                </div> 
                            </div> -->

                            <div class="col-span-12">
                                <InputLabel for="Dedication" value="Dedication" />
                                <TextInput
                                    id="dedication"
                                    v-model="form.dedication"
                                    :is-error="form.errors.dedication ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.dedication" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="B-day Code No." value="B-day Code No." />
                                <TextInput
                                    id="bdaycodeno"
                                    v-model="form.bdaycodeno"
                                    :is-error="form.errors.bdaycodeno ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.bdaycodeno" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Flavor" value="Flavor" />
                                <TextInput
                                    id="flavor"
                                    v-model="form.flavor"
                                    :is-error="form.errors.flavor ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.flavor" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Motif" value="Motif" />
                                <TextInput
                                    id="motif"
                                    v-model="form.motif"
                                    :is-error="form.errors.motif ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.motif" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Icing" value="Icing" />
                                <TextInput
                                    id="icing"
                                    v-model="form.icing"
                                    :is-error="form.errors.icing ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.icing" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="Others" value="Others" />
                                <textarea
                                    id="others"
                                    v-model="form.others"
                                    :is-error="form.errors.others ? true : false"
                                    type="text"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                ></textarea>
                                <InputError :message="form.errors.others" class="mt-2" />
                            </div>

                            <div class="col-span-6">
                                <InputLabel for="SRP" value="SRP" />
                                <TextInput
                                    id="srp"
                                    v-model="form.srp"
                                    :is-error="form.errors.srp ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.srp" class="mt-2" />
                            </div>

                            <div class="col-span-6">
                                <InputLabel for="Discount" value="Discount" />
                                <TextInput
                                    id="discount"
                                    v-model="form.discount"
                                    :is-error="form.errors.discount ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.discount" class="mt-2" />
                            </div>

                            <div class="col-span-6">
                                <InputLabel for="PartialPayment" value="PartialPayment" />
                                <TextInput
                                    id="partialpayment"
                                    v-model="form.partialpayment"
                                    :is-error="form.errors.partialpayment ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.partialpayment" class="mt-2" />
                            </div>

                            <div class="col-span-6">
                                <InputLabel for="NetAmount" value="NetAmount" />
                                <TextInput
                                    id="netamount"
                                    v-model="form.netamount"
                                    :is-error="form.errors.netamount ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.netamount" class="mt-2" />
                            </div>

                            <div class="col-span-12">
                                <InputLabel for="BalAmount" value="Balance Amount" />
                                <TextInput
                                    id="balanceamount"
                                    v-model="form.balanceamount"
                                    :is-error="form.errors.balanceamount ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.balanceamount" class="mt-2" />
                            </div>
                            

                        </div>
                    </div>
                </div>
                
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                SUBMIT
            </PrimaryButton>
        </template>
    </Modal>
</template>

<!-- <script>
export default {
  data() {
    return {
      selectedCategory: '',
    };
  },
};
</script> -->
