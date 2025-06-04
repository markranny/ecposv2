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
                                Create New Item Link
                            </DialogTitle>

                            <!-- Error Display -->
                            <div v-if="form.errors.general" class="mt-2 p-2 bg-red-100 text-red-600 rounded-md">
                                {{ form.errors.general }}
                            </div>

                            <form @submit.prevent="submit" class="mt-4">
                                <!-- Item Selection -->
                                <div class="mb-4 relative" ref="dropdownRef">
                                    <InputLabel for="child_itemid">Link To</InputLabel>
                                    
                                    <!-- Selected Item Display -->
                                    <div
                                        @click="toggleDropdown"
                                        class="mt-1 w-full cursor-pointer rounded-md border border-gray-300 bg-white py-2 px-3 text-left shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.child_itemid }"
                                    >
                                        <span v-if="selectedItem" class="block truncate">
                                            {{ selectedItem.itemname }}
                                            <span class="text-gray-500">({{ selectedItem.itemid }})</span>
                                        </span>
                                        <span v-else class="block text-gray-500">
                                            Select an item...
                                        </span>
                                    </div>

                                    <!-- Dropdown Menu -->
                                    <div v-if="isDropdownOpen"
                                         class="absolute z-50 mt-1 w-full rounded-md bg-white shadow-lg">
                                        <!-- Search Input -->
                                        <div class="border-b border-gray-200 px-3 py-2">
                                            <input
                                                type="text"
                                                class="w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600"
                                                v-model="searchQuery"
                                                placeholder="Search items..."
                                                @click.stop
                                                @keydown.esc="closeDropdown"
                                            />
                                        </div>

                                        <!-- Items List -->
                                        <ul class="max-h-60 overflow-auto py-1" role="listbox">
                                            <li v-if="filteredItems.length === 0" 
                                                class="px-3 py-2 text-sm text-gray-500">
                                                No items found
                                            </li>
                                            <li
                                                v-for="item in filteredItems"
                                                :key="item.itemid"
                                                @click="selectItem(item)"
                                                class="relative cursor-pointer select-none py-2 px-3 hover:bg-indigo-600 hover:text-white"
                                                :class="{
                                                    'bg-indigo-600 text-white': selectedItem?.itemid === item.itemid,
                                                    'text-gray-900': selectedItem?.itemid !== item.itemid
                                                }"
                                                role="option"
                                            >
                                                <div class="flex justify-between">
                                                    <span class="block truncate">{{ item.itemname }}</span>
                                                    <span class="ml-2 block truncate text-sm" 
                                                          :class="{ 'text-indigo-200': selectedItem?.itemid === item.itemid, 'text-gray-500': selectedItem?.itemid !== item.itemid }">
                                                        {{ item.itemid }}
                                                    </span>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>

                                    <div v-if="form.errors.child_itemid" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.child_itemid }}
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
                                        <option value="">Select a type</option>
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
                                        :disabled="form.processing || !isFormValid"
                                        :class="{ 'opacity-50': form.processing || !isFormValid }"
                                    >
                                        {{ form.processing ? 'Creating...' : 'Create Link' }}
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
    mainItem: {
        type: Object,
        required: true,
        validator: (value) => {
            return value && typeof value === 'object' && 'itemid' in value;
        }
    },
    availableItems: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'created']);

// Form
const form = useForm({
    parent_itemid: '',
    child_itemid: '',
    link_type: '',
    quantity: '1'
});

// Initialize parent_itemid with mainItem's itemid
form.parent_itemid = String(props.mainItem.itemid);

// Refs
const searchQuery = ref('');
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);
const selectedItem = ref(null);

// Computed
const filteredItems = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return props.availableItems;
    
    return props.availableItems.filter(item => {
        const nameMatch = item.itemname.toLowerCase().includes(query);
        const idMatch = String(item.itemid).toLowerCase().includes(query);
        return nameMatch || idMatch;
    });
});

const isFormValid = computed(() => {
    return form.child_itemid && 
           form.link_type && 
           parseFloat(form.quantity) > 0;
});

// Methods
const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
    if (isDropdownOpen.value) {
        searchQuery.value = '';
    }
};

const selectItem = (item) => {
    selectedItem.value = item;
    form.child_itemid = String(item.itemid);
    isDropdownOpen.value = false;
};

const closeModal = () => {
    form.reset();
    form.clearErrors();
    // Reset to initial parent_itemid after form reset
    form.parent_itemid = String(props.mainItem.itemid);
    searchQuery.value = '';
    isDropdownOpen.value = false;
    selectedItem.value = null;
    emit('close');
};

const submit = () => {
    if (!isFormValid.value) return;

    // Ensure parent_itemid is set before submission
    form.parent_itemid = String(props.mainItem.itemid);

    form.post(route('item-links.store'), {
        preserveScroll: true,
        onSuccess: () => {
            emit('created');
            closeModal();
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};

// Lifecycle
onMounted(() => {
    // Ensure parent_itemid is set on mount
    form.parent_itemid = String(props.mainItem.itemid);
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>