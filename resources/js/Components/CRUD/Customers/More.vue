<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted } from 'vue';
import Modal from "@/Components/Modals/DaisyModal.vue";
import Price from "@/Components/Svgs/price.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import POSSETTINGS from "@/Components/Svgs/possettings.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';

const props = defineProps({
    accountnum:{
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

/* const form = useForm({
    accountnum: 0,
}); */

const form = useForm({
    accountnum: props.accountnum,
});

const submitForm = () => {
    form.patch("/items/patch", {
        preserveScroll: true,
    });
};

const submitForm2 = () => {
  form.patch(route('retail.terminal'), {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success
    },
    onError: () => {
      // Handle error
    },
  });
};

const emit = defineEmits();

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.accountnum = props.accountnum;

    watch(() => props.accountnum, (newValue) => {
        form.accountnum = newValue;
    });
});
</script>

<template>
    <Modal title="OPTIONS"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm2"  >
                
                <div class="col-span-6 sm:col-span-4">
                    <TextInput
                        id="accountnum"
                        v-model="form.accountnum"
                        type="text"
                        class="mt-1 block w-full bg-blue-500 text-white"
                        :is-error="form.errors.accountnum ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.accountnum" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 gap-4 pt-5">
                    <TransparentButton :url="route('retail.terminal', { accountnum: form.accountnum })">
                        <div class="bg-blue-50 p-4 rounded-md w-full">
                        <POSSETTINGS /> 
                        <div class="flex items-center justify-center font-bold">LEDGER</div>
                        </div>
                    </TransparentButton>
                </div>
                
            </FormComponent>
        </template>
        

        <<!-- template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                UPDATE
            </PrimaryButton>
        </template> -->

    </Modal>
</template>
