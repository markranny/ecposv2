//resources/js/Components/Items/Links/Update.vue
<template>
    <TransitionRoot appear :show="showModal" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all">
                            <!-- Modal Header -->
                            <DialogTitle class="text-lg font-medium text-gray-900">
                                Edit Item Link
                            </DialogTitle>

                            <!-- Error Display -->
                            <div v-if="form.errors.general" class="mt-2 p-2 bg-red-100 text-red-600 rounded-md">
                                {{ form.errors.general }}
                            </div>

                            <form @submit.prevent="submit" class="mt-4">
                                <!-- Linked Item Display -->
                                <div class="mb-4">
                                    <InputLabel>Linked Item</InputLabel>
                                    <div class="mt-1 p-2 bg-gray-50 rounded-md">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ getLinkedItemName() }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ getLinkedItemId() }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Link Type Selection -->
                                <div class="mb-4">
                                    <InputLabel for="link_type">Link Type</InputLabel>
                                    <select
                                        id="link_type"
                                        v-model="form.link_type"
                                        class="mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.link_type }"
                                    >
                                        <option value="bundle">Bundle</option>
                                        <option value="recipe">Recipe</option>
                                    </select>
                                    <div v-if="form.errors.link_type" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.link_type }}
                                    </div>
                                </div>

                                <!-- Quantity Input -->
                                <div class="mb-4">
                                    <InputLabel for="quantity">Quantity</InputLabel>
                                    <input
                                        type="number"
                                        id="quantity"
                                        v-model="form.quantity"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.quantity }"
                                        min="0.01"
                                        step="0.01"
                                        required
                                    />
                                    <div v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity }}
                                    </div>
                                </div>

                                <!-- Active Status -->
                                <div class="mb-4">
                                    <label class="flex items-center">
                                        <input
                                            type="checkbox"
                                            v-model="form.active"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <span class="ml-2 text-sm text-gray-600">Active</span>
                                    </label>
                                </div>

                                <!-- Form Actions -->
                                <div class="mt-6 flex justify-end space-x-3">
                                    <SecondaryButton
                                        type="button"
                                        @click="closeModal"
                                        :disabled="form.processing"
                                    >
                                        Cancel
                                    </SecondaryButton>
                                    <PrimaryButton
                                        type="submit"
                                        :disabled="form.processing"
                                        :class="{ 'opacity-50': form.processing }"
                                    >
                                        {{ form.processing ? 'Updating...' : 'Update Link' }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild,
} from '@headlessui/vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    showModal: Boolean,
    link: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['close', 'updated']);

// Form
const form = useForm({
    link_type: props.link.link_type,
    quantity: props.link.quantity,
    active: props.link.active
});

// Methods
const getLinkedItemName = () => {
    return props.link.is_parent 
        ? props.link.child_item?.itemname 
        : props.link.parent_item?.itemname;
};

const getLinkedItemId = () => {
    return props.link.is_parent 
        ? props.link.child_item?.itemid 
        : props.link.parent_item?.itemid;
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

const submit = () => {
    form.put(route('item-links.update', props.link.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('updated');
            closeModal();
        }
    });
};
</script>