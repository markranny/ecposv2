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
    itemid:{
        type: [String, Number],
        required: true,
    },
    showModal: {
        type: Boolean,
        default: false,
    }
});

/* const form = useForm({
    itemid: 0,
}); */

const form = useForm({
  itemid: props.itemid,
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
    form.itemid = props.itemid;

    watch(() => props.itemid, (newValue) => {
        form.itemid = newValue;
    });
});
</script>

<template>
    <Modal title="OPTIONS"  @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm2"  >
                
                <div class="col-span-6 sm:col-span-4">
                    <!-- <InputLabel for="itemid" value="itemid" /> -->
                    <TextInput
                        id="itemid"
                        v-model="form.itemid"
                        type="text"
                        class="mt-1 block w-full bg-blue-500 text-white"
                        :is-error="form.errors.itemid ? true : false"
                        disabled
                    />
                    <InputError :message="form.errors.itemid" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4 pt-5">
                    <TransparentButton>
                        <div class="bg-blue-50 p-4 rounded-md w-full">
                            <Price></Price>
                            <div class="flex items-center justify-center font-bold ">RETAIL PRICE</div>
                        </div>
                    </TransparentButton>

                    <!-- <TransparentButton url="/terminal">
                        <div class="bg-blue-50 p-4 rounded-md w-full">
                            <POSSETTINGS></POSSETTINGS>
                            <div class="flex items-center justify-center font-bold ">POS TERMINAL</div>
                        </div>
                    </TransparentButton> -->

                    <!-- <TransparentButton :url="route('retail.terminal', { itemid: form.itemid })">
                        <div class="bg-blue-50 p-4 rounded-md w-full">
                            <POSSETTINGS></POSSETTINGS>
                            <div class="flex items-center justify-center font-bold">POS TERMINAL</div>
                        </div>
                    </TransparentButton> -->

                    <TransparentButton :url="route('retail.terminal', { itemid: form.itemid })">
                        <div class="bg-blue-50 p-4 rounded-md w-full">
                        <POSSETTINGS /> 
                        <div class="flex items-center justify-center font-bold">POS TERMINAL</div>
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
