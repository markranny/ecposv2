<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    NUMBERSEQUENCE: {
        type: [String, Number],
        required: true,
    },

    NEXTREC: {
        type: [String, Number],
        required: true,
    },

    STOREID:{
        type: [String, Number],
        required: true,
    },
    
    
    
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    NUMBERSEQUENCE: (''),
    NEXTREC: (''),
    STOREID: (''),
    
});


const submitForm = () => {
    form.patch("/nubersequencevalues/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.NUMBERSEQUENCE = props.NUMBERSEQUENCE;
    form.NEXTREC = props.NEXTREC;
    form.STOREID = props.STOREID;
    

    watch(() => props.NUMBERSEQUENCE, (newValue) => {
        form.NUMBERSEQUENCE = newValue;
    });
    watch(() => props.NEXTREC, (newValue) => {
        form.NEXTREC = newValue;
    });
    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });
});
</script>

<template>
    <Modal title="nubersequencevalues" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-2 ">
                    <InputLabel for="NUMBERSEQUENCE" value="NUMBERSEQUENCE" />
                    <TextInput
                        id="NUMBERSEQUENCE"
                        v-model="form.NUMBERSEQUENCE"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.NUMBERSEQUENCE ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.NUMBERSEQUENCE" class="mt-2" />
                    </div>
                    
                    <div class="col-span-2 ">
                    <InputLabel for="UNITID" value="UNITID" />
                    <TextInput
                        id="UNITID"
                        v-model="form.UNITID"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.UNITID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.UNITID" class="mt-2" />
                    </div>
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="STOREID" value="STOREID" />
                    <TextInput
                        id="STOREID"
                        v-model="form.STOREID"
                        :is-error="form.errors.STOREID ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.STOREID" class="mt-2" />
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
