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
    ID: (''),
    DISCTYPE: (''),
    ISPRECENTAGE: (''),
    
});

const submitForm = () => {
    form.post("/isdiscblankoperations", {
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
    <Modal title="ADD Disc" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-2 ">
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
                    
                    <div class="col-span-1 ml-4">
                        <InputLabel for="DISCTYPE" value="DISCTYPE" />
                    <TextInput
                        id="DISCTYPE"
                        v-model="form.DISCTYPE"
                        :is-error="form.errors.DISCTYPE ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DISCTYPE" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="ISPRECENTAGE" value="ISPRECENTAGE" />
                    <TextInput
                        id="ISPRECENTAGE"
                        v-model="form.ISPRECENTAGE"
                        :is-error="form.errors.ISPRECENTAGE ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.ISPRECENTAGE" class="mt-2" />
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
