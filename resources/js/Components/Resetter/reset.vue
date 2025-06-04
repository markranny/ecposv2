<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/DaisyModal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import SelectOption from '@/Components/SelectOption/SelectOption.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import FormComponent from '@/Components/Form/FormComponent.vue';


const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    }
});

const initialStoreId = computed(() => {
  return $page.props.auth.user.storeid;
});

const form = useForm({
    passcode: '',
    tr: '',
});

const submitForm = () => {
    form.post("/reset-order", {
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
    <Modal title="RESET ORDER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content >
            <FormComponent @submit.prevent="submitForm"  >
                
                <div class="grid grid-cols-1">
                    <div class="col-span-1">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <InputLabel for="STORE" value="Please double-check before proceeding with the reset of the Transfer Request." />
                            </div>

                            <div>
                                <InputLabel for="Passcode" value="Passcode" />
                                <TextInput
                                    id="passcode"
                                    v-model="form.passcode"
                                    :is-error="form.errors.passcode ? true : false"
                                    type="password"
                                    placeholder="**********"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.passcode" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="TR#" value="TR#" />
                                <TextInput
                                    id="tr"
                                    v-model="form.tr"
                                    :is-error="form.errors.tr ? true : false"
                                    type="number"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.tr" class="mt-2" />
                            </div>
                            

                        </div>
                    </div>
                </div>
                
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                RESET
            </PrimaryButton>
        </template>
    </Modal>
</template>

<!-- <script>
export default {
  data() {
    return {
      selectedCategory: '',
    };
  },
};
</script> -->
