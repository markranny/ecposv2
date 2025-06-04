<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    REASONID: {
        type: [String, Number],
        required: true,
    },
    REASONTEXT:{
        type: [String, Number],
        required: true,
    },
    
    
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    REASONID: (''),
    REASONTEXT: (''),
    
});


const submitForm = () => {
    form.patch("/inventtransreasons/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.REASONID = props.REASONID;
    form.REASONTEXT = props.REASONTEXT;
    

    watch(() => props.REASONID, (newValue) => {
        form.REASONID = newValue;
    });
    watch(() => props.REASONTEXT, (newValue) => {
        form.REASONTEXT = newValue;
    });
});
</script>

<template>
    <Modal title="RBO" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-2 ">
                    <InputLabel for="REASONID" value="REASONID" />
                    <TextInput
                        id="REASONID"
                        v-model="form.REASONID"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.REASONID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.REASONID" class="mt-2" />
                    </div>  
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="REASONTEXT" value="REASONTEXT" />
                    <TextInput
                        id="REASONTEXT"
                        v-model="form.REASONTEXT"
                        :is-error="form.errors.REASONTEXT ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.REASONTEXT" class="mt-2" />
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
