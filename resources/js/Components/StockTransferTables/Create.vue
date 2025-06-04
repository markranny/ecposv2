<script setup>
import { ref, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3'; // Add usePage import
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

const page = usePage(); // Initialize usePage

const stores = computed(() => {
    return page.props.stores || [];
});

const currentStoreId = computed(() => {
    return page.props.auth.user.storeid;
});

const form = useForm({
    from_storeid: '',
    to_storeid: '',
    description: ''
});

// Set the from_storeid to the current user's store
form.from_storeid = currentStoreId.value;

const submitForm = () => {
    form.post("/StockTransfer", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('toggleActive');
        },
    });
};

const emit = defineEmits(['toggleActive']);

const toggleActive = () => {
    emit('toggleActive');
};
</script>

<template>
    <Modal title="CREATE STOCK TRANSFER" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <FormComponent @submit.prevent="submitForm">
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <InputLabel for="from_storeid" value="From Store" />
                        <TextInput
                            id="from_storeid"
                            v-model="form.from_storeid"
                            type="text"
                            class="mt-1 block w-full"
                            disabled
                            :value="currentStoreId"
                        />
                        <InputError :message="form.errors.from_storeid" class="mt-2" />
                    </div>

                    <div class="bg-white text-black p-4 rounded">
                        <InputLabel for="to_storeid" value="To Store" />
                        <SelectOption
                            id="to_storeid"
                            v-model="form.to_storeid"
                            class="mt-1 block w-full bg-white text-black border-gray-300 rounded"
                            :is-error="form.errors.to_storeid ? true : false"
                        >
                            <option value="">Select Store</option>
                            <option 
                                v-for="store in stores" 
                                :key="store.STOREID" 
                                :value="store.NAME"
                                :disabled="store.STOREID === currentStoreId"
                            >
                                {{ store.NAME }}
                            </option>
                        </SelectOption>
                        <InputError :message="form.errors.to_storeid" class="mt-2" />
                    </div>


                    <div>
                        <InputLabel for="description" value="Description" />
                        <TextInput
                            id="description"
                            v-model="form.description"
                            type="text"
                            class="mt-1 block w-full"
                            :is-error="form.errors.description ? true : false"
                            required
                        />
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                </div>
            </FormComponent>
        </template>
        <template #buttons>
            <PrimaryButton 
                type="submit" 
                @click="submitForm" 
                :disabled="form.processing" 
                :class="{ 'opacity-25': form.processing }"
            >
                Create
            </PrimaryButton>
        </template>
    </Modal>
</template>