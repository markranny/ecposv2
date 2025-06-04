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
    ID: '',
    DESCRIPTION: '',
    STARTINGDATE: '',
    ENDINGDATE: '',

});

const submitForm = () => {
    form.post("/posdiscvalidationperiods", {
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
    <Modal title="DISCOUNT VALIDATION ENTRIES" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-4 py-6 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <!-- Hidden ID field -->
                <div class="hidden">
                    <InputLabel for="ID" value="ID" />
                    <TextInput
                        id="ID"
                        v-model="form.ID"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.ID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.ID" class="mt-2" />
                </div>

                <!-- Description field -->
                <div class="mb-6">
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

                <!-- Date fields container -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Starting Date field -->
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

                    <!-- Ending Date field -->
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
                Submit
            </PrimaryButton>
        </template>
    </Modal>
</template>
