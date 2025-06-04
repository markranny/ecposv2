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
    TRANSACTIONID: '',
    LINENUM: '',
    DISCLINENUM: '',
    STORE: '',
    DISCOUNTTYPE: '',
    DISCOUNTPCT: '',
    DISCOUNTAMT: '',
    DISCOUNTAMTWITHTAX: '',
    PERIODICDISCTYPE: '',
    DISCOFFERID: '',
    DISCOFFERNAME: '',
    QTYDISCOUNTED: '',
});

const submitForm = () => {
    form.post("/rbotransactiondiscounttrans", {
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
    <Modal title="LEDGER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="TRANSACTIONID" value="TRANSACTIONID" />
                    <TextInput
                        id="TRANSACTIONID"
                        v-model="form.TRANSACTIONID"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.TRANSACTIONID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.LINENUM" class="mt-2" />
                    </div>  
                    <div class="col-span-2">
                    <InputLabel for="LINENUM" value="LINENUM" />
                    <TextInput
                        id="LINENUM"
                        v-model="form.LINENUM"
                        :is-error="form.errors.LINENUM ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.LINENUM" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="DISCLINENUM" value="DISCLINENUM" />
                        <TextInput
                        id="DISCLINENUM"
                        v-model="form.DISCLINENUM"
                        :is-error="form.errors.DISCLINENUM ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.DISCLINENUM" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="STORE" value="STORE" />
                    <TextInput
                        id="STORE"
                        v-model="form.STORE"
                        :is-error="form.errors.STORE ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.STORE" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="DISCOUNTTYPE" value="DISCOUNTTYPE" />
                    <TextInput
                        id="DISCOUNTTYPE"
                        v-model="form.DISCOUNTTYPE"
                        :is-error="form.errors.DISCOUNTTYPE ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOUNTTYPE" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="DISCOUNTPCT" value="DISCOUNTPCT" />
                    <TextInput
                        id="DISCOUNTPCT"
                        v-model="form.DISCOUNTPCT"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.DISCOUNTPCT ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.DISCOUNTPCT" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="DISCOUNTAMT" value="DISCOUNTAMT" />
                    <TextInput
                        id="DISCOUNTAMT"
                        v-model="form.DISCOUNTAMT"
                        :is-error="form.errors.DISCOUNTAMT ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOUNTAMT" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="DISCOUNTAMTWITHTAX" value="DISCOUNTAMTWITHTAX" />
                    <TextInput
                        id="DISCOUNTAMTWITHTAX"
                        v-model="form.DISCOUNTAMTWITHTAX"
                        :is-error="form.errors.DISCOUNTAMTWITHTAX ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOUNTAMTWITHTAX" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="PERIODICDISCTYPE" value="PERIODICDISCTYPE" />
                    <TextInput
                        id="PERIODICDISCTYPE"
                        v-model="form.PERIODICDISCTYPE"
                        :is-error="form.errors.PERIODICDISCTYPE ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.PERIODICDISCTYPE" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="DISCOFFERID" value="DISCOFFERID" />
                    <TextInput
                        id="DISCOFFERID"
                        v-model="form.DISCOFFERID"
                        :is-error="form.errors.DISCOFFERID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOFFERID" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                        <InputLabel for="DISCOFFERNAME" value="DISCOFFERNAME" />
                    <TextInput
                        id="DISCOFFERNAME"
                        v-model="form.DISCOFFERNAME"
                        :is-error="form.errors.DISCOFFERNAME ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCOFFERNAME" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="QTYDISCOUNTED" value="QTYDISCOUNTED" />
                    <TextInput
                        id="QTYDISCOUNTED"
                        v-model="form.QTYDISCOUNTED"
                        :is-error="form.errors.QTYDISCOUNTED ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.QTYDISCOUNTED" class="mt-2" />
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
