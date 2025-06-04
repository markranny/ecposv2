<script setup>
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
    rboinventitemretailgroups:{
        type: Array,
        required: true,
    },
});

const selectitemgroup = () => {
  const selectedCategory = props.rboinventitemretailgroups.find(cat => cat.GROUPID === form.itemgroup);
  if (selectedCategory) {
    emit('select-item', selectedCategory);
  }
};

const form = useForm({
    itemid: '',
    itemname: '',
    itemdepartment: '',
    itemgroup: '',
    barcode: '',
    cost: '',
    price: '',
});

const submitForm = () => {
    form.post("/items", {
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
    <Modal title="CREATE NEW ITEM" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >
                
                <div class="grid grid-cols-1">
                    <div class="col-span-1">
                        <div class="grid gap-4">

                            <div class="col-span-1">
                                <InputLabel for="PRODUCTCODE" value="PRODUCT CODE" />
                                <TextInput
                                    id="itemid"
                                    v-model="form.itemid"
                                    type="text"
                                    class="mt-1 block w-full"
                                    :is-error="form.errors.itemid ? true : false"
                                    autofocus
                                />
                                <InputError :message="form.errors.itemid" class="mt-2" />
                            </div>

                            <div class="col-span-11">
                                <InputLabel for="ITEMNAME" value="ITEM NAME" />
                                <TextInput
                                    id="itemname"
                                    v-model="form.itemname"
                                    :is-error="form.errors.itemname ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.itemname" class="mt-2" />
                            </div>

                            <!-- <div>
                                <InputLabel for="CATEGORY" value="CATEGORY" />
                                <SelectOption 
                                    id="itemgroup"
                                    v-model="form.itemgroup" 
                                    :is-error="form.errors.itemgroup ? true : false"
                                    class="mt-1 block w-full"
                                    >
                                    <option disabled value="">Select an option</option>
                                    <option>Option 1</option>
                                    <option>Option 2</option>
                                </SelectOption>
                                <InputError :message="form.errors.itemgroup" class="mt-2" />
                            </div> -->

                            <div class="col-span-1">
                                <InputLabel for="RETAILGROUP" value="RETAILGROUP" />
                                <SelectOption 
                                    id="itemdepartment"
                                    v-model="form.itemdepartment" 
                                    :is-error="form.errors.itemdepartment ? true : false"
                                    class="mt-1 block w-full !bg-white"
                                    >
                                    <option disabled value="">Select an option</option>
                                    <option>REGULAR PRODUCT</option>
                                    <option>NON PRODUCT</option>
                                </SelectOption>
                                <InputError :message="form.errors.itemdepartment" class="mt-2" />
                            </div>

                            <div class="col-span-11">
                                <InputLabel for="Category" value="Category" />
                                <select
                                    id="itemgroup"
                                    v-model="form.itemgroup" 
                                    class="input input-bordered w-full !bg-white"
                                    @change="selectitemgroup" 
                                >
                                    <option v-for="cat in rboinventitemretailgroups" :key="cat.GROUPID" :value="cat.NAME">
                                        {{ cat.NAME }}
                                    </option>
                                </select>
                            </div>

                            <div class="col-span-12">
                                <div>
                                <InputLabel for="BARCODE" value="BARCODE" />
                                <TextInput
                                    id="barcode"
                                    v-model="form.barcode"
                                    :is-error="form.errors.barcode ? true : false"
                                    type="text"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.barcode" class="mt-2" />
                            </div>
                            </div>
                            

                            <div class="col-span-1">
                                <InputLabel for="COSTPRICE" value="COSTPRICE" />
                                <TextInput
                                    id="cost"
                                    v-model="form.cost"
                                    :is-error="form.errors.cost ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.cost" class="mt-2" />
                            </div>

                            <div class="col-span-11">
                                <InputLabel for="PRICE" value="PRICE" />
                                <TextInput
                                    id="price"
                                    v-model="form.price"
                                    :is-error="form.errors.price ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.price" class="mt-2" />
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
