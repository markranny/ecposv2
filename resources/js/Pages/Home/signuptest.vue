<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch, defineProps, defineEmits } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const emit = defineEmits(); 

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const selectItem = (itemName) => {
  form.itemname = itemName; 
};

const form = useForm({
    JOURNALID: '',
    itemname: '',
    qty: '',
    unitid: '',

});

const submitForm = () => {
    form.post("/ItemOrders", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
    location.reload();
};

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.JOURNALID = props.JOURNALID;
    
    watch(() => props.JOURNALID, (newValue) => {
        form.JOURNALID = newValue;
    });
});

</script>

<template>
    <Modal title="ORDER ENTRIES" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-3 ">
                    <InputLabel for="JOURNALID" value="JOURNALID" />
                    <TextInput
                        id="JOURNALID"
                        v-model="form.JOURNALID"
                        type="text"
                        class="mt-1 block w-full input"
                        :is-error="form.errors.JOURNALID ? true : false"
                        autofocus
                        disabled
                    />
                    <InputError :message="form.errors.JOURNALID" class="mt-2" />
                    </div>  

                    <div class="col-span-3 mt-4">
                        <InputLabel for="itemname" value="ITEMNAME" />
                        <select
                        id="itemname"
                        v-model="form.itemname" 
                        class="input input-bordered w-full"
                        @change="selectItem(form.itemname)" 
                        >
                        <option v-for="item in items" :key="item.itemid" :value="item.itemid">
                            {{ item.itemname }}
                        </option>
                        </select>
                    </div>

                    <div class="col-span-3 mt-4">
                        <InputLabel for="qty" value="QTY" />
                        <TextInput
                            id="qty"
                            v-model="form.qty"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="qty"
                        />
                        <InputError class="mt-2" :message="form.errors.qty" />
                    </div>

                    <div class="col-span-3 mt-4">
                    <InputLabel for="UNIT" value="UNIT" />
                    <select
                        id="unitid"
                        v-model="form.unitid"
                        className="select select-bordered w-full"
                    >
                        <option value="PCS">
                            PCS
                        </option>
                        <option value="PACK">
                            PACK
                        </option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.unitid" />
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
