<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    JOURNALID: (''),
    LINENUM: (''),
    TRANSDATE: (''),
    ITEMID: (''),
    ADJUSTMENT: (''),
    COSTPRICE: (''),
    PRICEUNIT: (''),
    SALESAMOUNT: (''),
    INVENTONHAND: (''),
    COUNTED:(''),
    REASONREFRECID: (''),
    VARIANTID: (''),
    POSTED: (''),
    POSTEDDATETIME:(''),
    UNITID: (''),
});

const submitForm = () => {
    form.post("/inventjournaltrans", {
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
    <Modal title="ADD CUSTOMER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="JOURNALID" value="JOURNALID" />
                    <TextInput
                        id="JOURNALID"
                        v-model="form.JOURNALID"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.JOURNALID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.JOURNALID" class="mt-2" />
                    </div>  
                    <div class="col-span-2">
                    <InputLabel for="LINENUM" value="LINENUM" />
                    <TextInput
                        id="LINENUM"
                        v-model="form.LINENUM"
                        :is-error="form.errors.LINENUM ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.LINENUM" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="TRANSDATE" value="TRANSDATE" />
                        <TextInput
                        id="TRANSDATE"
                        v-model="form.TRANSDATE"
                        :is-error="form.errors.TRANSDATE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.TRANSDATE" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="ITEMID" value="ITEMID" />
                    <TextInput
                        id="ITEMID"
                        v-model="form.ITEMID"
                        :is-error="form.errors.ITEMID ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ITEMID" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="ADJUSTMENT" value="ADJUSTMENT" />
                    <TextInput
                        id="ADJUSTMENT"
                        v-model="form.ADJUSTMENT"
                        :is-error="form.errors.ADJUSTMENT ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ADJUSTMENT" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="COSTPRICE" value="COSTPRICE" />
                    <TextInput
                        id="COSTPRICE"
                        v-model="form.COSTPRICE"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.COSTPRICE ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.COSTPRICE" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="PRICEUNIT" value="PRICEUNIT" />
                    <TextInput
                        id="PRICEUNIT"
                        v-model="form.PRICEUNIT"
                        :is-error="form.errors.PRICEUNIT ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.PRICEUNIT" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="SALESAMOUNT" value="SALESAMOUNT" />
                    <TextInput
                        id="SALESAMOUNT"
                        v-model="form.SALESAMOUNT"
                        :is-error="form.errors.SALESAMOUNT ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.SALESAMOUNT" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="INVENTONHAND" value="INVENTONHAND" />
                    <TextInput
                        id="INVENTONHAND"
                        v-model="form.INVENTONHAND"
                        :is-error="form.errors.INVENTONHAND ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.INVENTONHAND" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="COUNTED" value="COUNTED" />
                    <TextInput
                        id="COUNTED"
                        v-model="form.COUNTED"
                        :is-error="form.errors.COUNTED ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.COUNTED" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="REASONREFRECID" value="REASONREFRECID" />
                    <TextInput
                        id="REASONREFRECID"
                        v-model="form.REASONREFRECID"
                        :is-error="form.errors.REASONREFRECID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.REASONREFRECID" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="VARIANTID" value="VARIANTID" />
                    <TextInput
                        id="VARIANTID"
                        v-model="form.VARIANTID"
                        :is-error="form.errors.VARIANTID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.VARIANTID" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="POSTED" value="POSTED" />
                    <TextInput
                        id="POSTED"
                        v-model="form.POSTED"
                        :is-error="form.errors.POSTED ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.POSTED" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="POSTEDDATETIME" value="POSTEDDATETIME" />
                    <TextInput
                        id="POSTEDDATETIME"
                        v-model="form.POSTEDDATETIME"
                        :is-error="form.errors.POSTEDDATETIME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.POSTEDDATETIME" class="mt-2" />
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
