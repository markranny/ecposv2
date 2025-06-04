<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    JOURNALID:{
        type: [String, Number],
        required: true,
    },
    LINENUM:{
        type: [String, Number],
        required: true,
    },
    COUNTED: {
        type: [String, Number],
        required: true,
    },

    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    JOURNALID: '',
    LINENUM: '',
    COUNTED: '',

});
const submitForm = () => {
    form.patch("/ItemOrders/patch", {
        preserveScroll: true,
    });
    location.reload();
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.JOURNALID = props.JOURNALID;
    form.LINENUM = props.LINENUM;
    /* form.COUNTED = props.COUNTED; */

    watch(() => props.JOURNALID, (newValue) => {
        form.JOURNALID = newValue;
    });
    watch(() => props.LINENUM, (newValue) => {
        form.LINENUM = newValue;
    });
    /* watch(() => props.COUNTED, (newValue) => {
        form.COUNTED = newValue;
    }); */

});
</script>

<template>
    <Modal title="UPDATE QTY" @toggle-active="toggleActive" :show-modal="showModal">
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

                    <div class="col-span-3 ">
                    <InputLabel for="LINENUM" value="LINENUM" />
                    <TextInput
                        id="LINENUM"
                        v-model="form.LINENUM"
                                    type="number"
                                    class="mt-1 block w-full"
                        :is-error="form.errors.LINENUM ? true : false"
                        autofocus
                        disabled
                    />
                    <InputError :message="form.errors.LINENUM" class="mt-2" />
                    </div> 

                    <div class="col-span-3 mt-4">
                    <InputLabel for="COUNTED" value="QTY" />
                    <TextInput
                        id="COUNTED"
                        v-model="form.COUNTED"
                        :is-error="form.errors.COUNTED ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.COUNTED" class="mt-2" />
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