<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    NUMBERSEQUENCE:{
        type: [String, Number],
        required: true,
    },
    TXT: {
        type: [String, Number],
        required: true,
    },
    LOWEST: {
        type: [String, Number],
        required: true,
    },
    HIGHEST: {
        type: [String, Number],
        required: true,
    },
    BLOCKED: {
        type: [String, Number],
        required: true,
    },
    STOREID: {
        type: [String, Number],
        required: true,
    },
    CANBEDELETED: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    NUMBERSEQUENCE: '',
    TXT: '',
    LOWEST: '',
    HIGHEST: '',
    BLOCKED: '',
    STOREID: '',
    CANBEDELETED: '',

});
const submitForm = () => {
    form.patch("/nubersequencetables/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.NUMBERSEQUENCE = props.NUMBERSEQUENCE;
    form.TXT = props.TXT;
    form.LOWEST = props.LOWEST;
    form.HIGHEST = props.HIGHEST;
    form.BLOCKED = props.BLOCKED;
    form.STOREID = props.STOREID;
    form.CANBEDELETED = props.CANBEDELETED;
    
    watch(() => props.NUMBERSEQUENCE, (newValue) => {
        form.NUMBERSEQUENCE = newValue;
    });
    watch(() => props.TXT, (newValue) => {
        form.TXT = newValue;
    });
    watch(() => props.LOWEST, (newValue) => {
        form.LOWEST = newValue;
    });
    watch(() => props.HIGHEST, (newValue) => {
        form.HIGHEST = newValue;
    });
    watch(() => props.BLOCKED, (newValue) => {
        form.BLOCKED = newValue;
    });
    watch(() => props.STOREID, (newValue) => {
        form.STOREID = newValue;
    });
    watch(() => props.CANBEDELETED, (newValue) => {
        form.CANBEDELETED = newValue;
    });
});
</script>

<template>
    <Modal title="Posdiscount" @toggle-active="toggleActive" :show-modal="showModal">
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

                    <div class="col-span-2">
                    <InputLabel for="TXT" value="TXT" />
                    <TextInput
                        id="TXT"
                        v-model="form.TXT"
                        :is-error="form.errors.TXT ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.TXT" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="LOWEST" value="LOWEST" />
                        <TextInput
                        id="LOWEST"
                        v-model="form.LOWEST"
                        :is-error="form.errors.LOWEST ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.LOWEST" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="HIGHEST" value="HIGHEST" />
                    <TextInput
                        id="HIGHEST"
                        v-model="form.HIGHEST"
                        :is-error="form.errors.HIGHEST ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.HIGHEST" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="BLOCKED" value="BLOCKED" />
                    <TextInput
                        id="BLOCKED"
                        v-model="form.BLOCKED"
                        :is-error="form.errors.BLOCKED ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.BLOCKED" class="mt-2" />
                    </div>

                    <div class="col-span-2 ml-4">
                    <InputLabel for="STOREID" value="STOREID" />
                    <TextInput
                        id="STOREID"
                        v-model="form.STOREID"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.STOREID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.STOREID" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="CANBEDELETED" value="CANBEDELETED" />
                    <TextInput
                        id="CANBEDELETED"
                        v-model="form.CANBEDELETED"
                        :is-error="form.errors.CANBEDELETED ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.CANBEDELETED" class="mt-2" />
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