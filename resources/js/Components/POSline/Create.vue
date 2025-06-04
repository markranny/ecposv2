<script setup>
import { ref, defineProps, defineEmits, watch, onMounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';
import { Search, Check } from 'lucide-react';

const props = defineProps({
    showModal: {
        type: Boolean,
        default: false,
    },
    items: {
        type: Array,
        required: true,
    },
    offerid: {
        type: [String, Number],
        required: true,
    },
    discounttype: {
        type: [String, Number],
        required: true,
    },
});

const form = useForm({
    offerid: '',
    itemid: '',
    dealpriceordiscpct: '0',
    linegroup: '',
    disctype: '0',
});

const selectedItemId = ref('');
const selectedItem = ref(null);
const isLineSpecific = ref(false);
const searchQuery = ref('');
const showDropdown = ref(false);
const searchInputRef = ref(null);

const filteredItems = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return [];
    return props.items.filter(item => 
        item.itemname.toLowerCase().includes(query) ||
        item.itemid.toString().toLowerCase().includes(query)
    ).slice(0, 8); // Limit to 8 results for better performance
});

const selectItem = (item) => {
    selectedItemId.value = item.itemid;
    selectedItem.value = item;
    searchQuery.value = `${item.itemname} (${item.itemid})`;
    showDropdown.value = false;
};

const clearSelection = () => {
    selectedItemId.value = '';
    selectedItem.value = null;
    searchQuery.value = '';
    showDropdown.value = false;
};

const submitForm = () => {
    if (!selectedItemId.value) {
        form.setError('itemid', 'Please select an item');
        return;
    }

    form.itemid = selectedItemId.value;
    
    form.post("/posperiodicdiscountlines", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            clearSelection();
            emit('toggleActive');
        },
    });
};

const emit = defineEmits(['toggleActive']);

const toggleActive = () => {
    emit('toggleActive');
};

const handleClickOutside = (event) => {
    const searchContainer = document.getElementById('search-container');
    if (searchContainer && !searchContainer.contains(event.target)) {
        showDropdown.value = false;
    }
};

onMounted(() => {
    form.offerid = props.offerid;
    isLineSpecific.value = props.discounttype === 'LineSpecific';
    if (!isLineSpecific.value) {
        form.dealpriceordiscpct = 0;
    }
    document.addEventListener('click', handleClickOutside);
});

watch(() => props.offerid, (newValue) => {
    form.offerid = newValue;
});
</script>

<template>
    <Modal title="CREATE DISCOUNT LINE" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="space-y-6">
                    <!-- Offer ID -->
                    <div>
                        <InputLabel for="offerid" value="Offer ID" hidden/>
                        <TextInput
                            id="offerid"
                            v-model="form.offerid"
                            type="text"
                            class="mt-1 block w-full bg-gray-50"
                            :is-error="form.errors.offerid ? true : false"
                            disabled
                            hidden
                            
                        />
                        <InputError :message="form.errors.offerid" class="mt-2" />
                    </div>

                    <!-- Item Search -->
                    <div class="relative" id="search-container">
                        <InputLabel for="itemid" value="Search Item" />
                        <div class="relative mt-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <Search class="h-5 w-5 text-gray-400" />
                            </div>
                            <TextInput
                                id="itemid"
                                ref="searchInputRef"
                                v-model="searchQuery"
                                type="text"
                                class="block w-full pl-10 pr-12"
                                :class="{'ring-2 ring-indigo-500': showDropdown}"
                                placeholder="Search by item name or ID..."
                                @focus="showDropdown = true"
                                :is-error="form.errors.itemid ? true : false"
                            />
                            <button
                                v-if="selectedItem"
                                type="button"
                                @click="clearSelection"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <span class="sr-only">Clear selection</span>
                                <svg class="h-5 w-5 text-gray-400 hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        <InputError :message="form.errors.itemid" class="mt-2" />
                        
                        <!-- Dropdown Results -->
                        <div v-if="showDropdown && searchQuery" 
                             class="absolute z-10 w-full mt-1 bg-white rounded-md shadow-lg border border-gray-200 max-h-60 z-50 overflow-y-auto">
                            <div v-if="filteredItems.length === 0" class="px-4 py-3 text-sm text-gray-500">
                                No items found matching "{{ searchQuery }}"
                            </div>
                            <div v-else>
                                <div v-for="item in filteredItems"
                                     :key="item.itemid"
                                     @click="selectItem(item)"
                                     class="px-4 py-2 hover:bg-gray-50 cursor-pointer group z-50">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ item.itemname }}</div>
                                            <div class="text-sm text-gray-500">ID: {{ item.itemid }}</div>
                                        </div>
                                        <Check 
                                            v-if="item.itemid === selectedItemId"
                                            class="h-5 w-5 text-indigo-600"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected Item Details -->
                    <div v-if="selectedItem" 
                         class="rounded-lg bg-gray-50 p-4 border border-gray-200 shadow-sm z-index">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-500">Price</div>
                                <div class="mt-1 text-sm text-gray-900">{{ selectedItem.price }}</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-500">Group</div>
                                <div class="mt-1 text-sm text-gray-900 ">{{ selectedItem.itemgroup }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Discount Type -->
                    <div v-if="isLineSpecific">
                        <InputLabel for="disctype" value="Discount Type" />
                        <select 
                            id="disctype"
                            v-model="form.disctype"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            :class="{ 'border-red-500': form.errors.disctype }"
                        >
                            <option value="0">None</option>
                            <option value="1">Deal Price</option>
                            <option value="2">Discount Percent</option>
                        </select>
                        <InputError :message="form.errors.disctype" class="mt-2" />
                    </div>

                    <!-- Deal Price / Discount Percentage -->
                    <div v-if="isLineSpecific && form.disctype !== '0'">
                        <InputLabel 
                            for="dealpriceordiscpct" 
                            :value="form.disctype === '3' ? 'Deal Price' : 'Discount Percentage'" 
                        />
                        <TextInput
                            id="dealpriceordiscpct"
                            v-model="form.dealpriceordiscpct"
                            type="number"
                            step="0.01"
                            class="mt-1 block w-full"
                            :is-error="form.errors.dealpriceordiscpct ? true : false"
                        />
                        <InputError :message="form.errors.dealpriceordiscpct" class="mt-2" />
                    </div>

                    <!-- Line Group -->
                    <div>
                        <InputLabel for="linegroup" value="Line Group" />
                        <TextInput
                            id="linegroup"
                            v-model="form.linegroup"
                            type="text"
                            class="mt-1 block w-full"
                            :is-error="form.errors.linegroup ? true : false"
                        />
                        <InputError :message="form.errors.linegroup" class="mt-2" />
                    </div>
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton 
                type="button"
                @click="submitForm"
                :disabled="form.processing"
                :class="{ 'opacity-25': form.processing }"
            >
                Create
            </PrimaryButton>
        </template>
    </Modal>
</template>