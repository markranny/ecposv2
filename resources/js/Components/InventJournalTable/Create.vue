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
    JOURNALID: '',
    DESCRIPTION: '',
    POSTED: '',
    POSTEDDATETIME: '',
    JOURNALTYPE: '',
    DELETEPOSTEDLINES: '',
});

const submitForm = () => {
    form.post("/inventjournaltables", {
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
    <Modal title="Tables" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">
                    <div class="col-span-2 ">
                    <InputLabel for="JOURNALID" value="JOURNAL ID" />
                    <TextInput
                        id="JOURNALID"
                        v-model="form.JOURNALID"
                        type="text"
                        class="mt-1 block w-full"
                        :is-error="form.errors.JOURNALID ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.ITEMID" class="mt-2" />
                    </div>  

                    <div class="col-span-2">
                    <InputLabel for="DESCRIPTION" value="DESCRIPTION" />
                    <TextInput
                        id="DESCRIPTION"
                        v-model="form.DESCRIPTION"
                        :is-error="form.errors.DESCRIPTION ? true : false"
                        type="text"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.DESCRIPTION" class="mt-2" />
                    </div>

                    <div class="col-span-2">
                        <InputLabel for="POSTED" value="POSTED" />
                        <TextInput
                        id="POSTED"
                        v-model="form.POSTED"
                        :is-error="form.errors.STOREID ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.POSTED" class="mt-2" />
                    </div>

                    <div class="col-span-1 ml-4">
                    <InputLabel for="POSTEDDATETIME" value="POSTED DATE TIME" />
                    <TextInput
                        id="POSTEDDATETIME"
                        v-model="form.POSTEDDATETIME"
                        :is-error="form.errors.POSTEDDATETIME ? true : false"
                        type="date"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.POSTEDDATETIME" class="mt-2" />
                    </div>

                    <div class="col-span-1">
                        <InputLabel for="JOURNALTYPE" value="JOURNAL TYPE" />
                    <TextInput
                        id="JOURNALTYPE"
                        v-model="form.JOURNALTYPE"
                        :is-error="form.errors.JOURNALTYPE ? true : false"
                        type="NUMBER"
                        class="mt-1 block w-full"
                    />
                    <InputError :message="form.errors.JOURNALTYPE" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                    <InputLabel for="DELETEPOSTEDLINES" value="DELETE POSTED LINES" />
                    <TextInput
                        id="COSTPRICEPERITEM"
                        v-model="form.DELETEPOSTEDLINES"
                        type="number"
                        class="mt-1 block w-full"
                        :is-error="form.errors.DELETEPOSTEDLINES ? true : false"
                        autofocus
                    />
                    <InputError :message="form.errors.DELETEPOSTEDLINES" class="mt-2" />
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
