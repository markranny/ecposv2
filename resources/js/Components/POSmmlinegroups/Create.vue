<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    OFFERID: {
        type: String,
        default: '',
    }
});

const form = useForm({
    OFFERID: '',
    LINEGROUP: '',
    NOOFITEMSNEEDED: '',
    DESCRIPTION: '',

});

const submitForm = () => {
    form.post("/posmmlinegroups", {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.OFFERID = props.OFFERID;
    
    watch(() => props.OFFERID, (newValue) => {
        form.OFFERID = newValue;
    });
});

</script>

<template>
    <Modal title="LINE GROUP ENTRIES" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 ">

                    <div class="col-span-3 ">
                    <InputLabel for="OFFERID" value="OFFERID" />
                    <TextInput
                        id="OFFERID"
                        v-model="form.OFFERID"
                        type="text"
                        class="mt-1 block w-full input"
                        :is-error="form.errors.OFFERID ? true : false"
                        autofocus
                        disabled
                    />
                    <InputError :message="form.errors.OFFERID" class="mt-2" />
                    </div>  

                    <div class="col-span-3 mt-4">
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

                    <div class="col-span-3 mt-3">
                        <InputLabel for="No of Items" value="No of Items" />
                        <TextInput
                        id="NOOFITEMSNEEDED"
                        v-model="form.NOOFITEMSNEEDED"
                        :is-error="form.errors.NOOFITEMSNEEDED ? true : false"
                        type="number"
                        class="mt-1 block w-full"
                        />
                        <InputError :message="form.errors.NOOFITEMSNEEDED" class="mt-2" />
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
