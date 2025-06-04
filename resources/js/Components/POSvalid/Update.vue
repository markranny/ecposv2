<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    ID: {
        type: [String, Number],
        required: true,
    },
    DESCRIPTION: {
        type: [String, Number],
        required: true,
    },
    STARTINGDATE: {
        type: [String, Number],
        required: true,
    },
    ENDINGDATE: {
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

const form = useForm({
    ID: '',
    DESCRIPTION: '',
    STARTINGDATE: '',
    ENDINGDATE: '',
});

const submitForm = () => {
    form.patch("/posdiscvalidationperiods/patch", {
        preserveScroll: true,
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.ID = props.ID;
    form.DESCRIPTION = props.DESCRIPTION;
    form.STARTINGDATE = props.STARTINGDATE;
    form.ENDINGDATE = props.ENDINGDATE;

    watch(() => props.ID, (newValue) => {
        form.ID = newValue;
    });
    watch(() => props.DESCRIPTION, (newValue) => {
        form.DESCRIPTION = newValue;
    });
    watch(() => props.STARTINGDATE, (newValue) => {
        form.STARTINGDATE = newValue;
    });
    watch(() => props.ENDINGDATE, (newValue) => {
        form.ENDINGDATE = newValue;
    });
});
</script>

<template>
    <Modal title="POS Validation Period" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-4 py-6 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="space-y-6">
                    <!-- ID Field -->
                    <div>
                        <InputLabel for="ID" value="ID" class="text-sm font-medium" />
                        <TextInput
                            id="ID"
                            v-model="form.ID"
                            type="text"
                            class="mt-1 block w-full bg-gray-100"
                            :is-error="form.errors.ID ? true : false"
                            disabled
                        />
                        <InputError :message="form.errors.ID" class="mt-2" />
                    </div>

                    <!-- Description Field -->
                    <div>
                        <InputLabel for="DESCRIPTION" value="Description" class="text-sm font-medium" />
                        <TextInput
                            id="DESCRIPTION"
                            v-model="form.DESCRIPTION"
                            :is-error="form.errors.DESCRIPTION ? true : false"
                            type="text"
                            class="mt-1 block w-full"
                            placeholder="Enter description"
                        />
                        <InputError :message="form.errors.DESCRIPTION" class="mt-2" />
                    </div>

                    <!-- Date Fields Container -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Starting Date Field -->
                        <div>
                            <InputLabel for="STARTINGDATE" value="Starting Date" class="text-sm font-medium" />
                            <TextInput
                                id="STARTINGDATE"
                                v-model="form.STARTINGDATE"
                                :is-error="form.errors.STARTINGDATE ? true : false"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.STARTINGDATE" class="mt-2" />
                        </div>

                        <!-- Ending Date Field -->
                        <div>
                            <InputLabel for="ENDINGDATE" value="Ending Date" class="text-sm font-medium" />
                            <TextInput
                                id="ENDINGDATE"
                                v-model="form.ENDINGDATE"
                                :is-error="form.errors.ENDINGDATE ? true : false"
                                type="date"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.ENDINGDATE" class="mt-2" />
                        </div>
                    </div>
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton 
                type="submit" 
                @click="submitForm" 
                :disabled="form.processing" 
                class="px-6 py-2 transition-opacity duration-200"
                :class="{ 'opacity-25': form.processing }"
            >
                Update
            </PrimaryButton>
        </template>
    </Modal>
</template>