<!-- CreateStockTransfer.vue -->
<template>
    <!-- Create Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <!-- Modal Content -->
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                    <!-- Modal Header -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-medium leading-6 text-gray-900">
                                Create New Stock Transfer
                            </h3>
                            <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                                <span class="sr-only">Close</span>
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Form Content -->
                        <div class="mt-5">
                            <!-- From Store (Read-only) -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    From Store
                                </label>
                                <div class="mt-1 block w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-gray-700 sm:text-sm">
                                    {{ fromStoreName }}
                                </div>
                            </div>

                            <!-- To Store (Destination) -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    To Store (Destination)
                                </label>
                                <select
                                    v-model="form.to_store_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    required
                                >
                                    <option value="">Select Destination Store</option>
                                    <option v-for="store in stores" :key="store.STOREID" :value="store.STOREID">
                                        {{ store.NAME }}
                                    </option>
                                </select>
                            </div>

                            <!-- Item Selection -->
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Select Items
                                    </label>
                                    <div class="flex items-center">
                                        <input
                                            type="text"
                                            v-model="searchQuery"
                                            placeholder="Search items..."
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </div>
                                </div>
                                <div class="mt-2 border rounded-md max-h-60 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Item ID</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Item Name</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Unit</th>
                                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="item in filteredItems" :key="item.itemid">
                                                <td class="px-4 py-2 text-sm">{{ item.itemid }}</td>
                                                <td class="px-4 py-2 text-sm">{{ item.itemname }}</td>
                                                <td class="px-4 py-2 text-sm">{{ item.unitid }}</td>
                                                <td class="px-4 py-2">
                                                    <input
                                                        type="number"
                                                        v-model.number="quantities[item.itemid]"
                                                        min="0"
                                                        class="w-20 rounded-md border-gray-300"
                                                        @change="validateQuantity($event, item.itemid)"
                                                    >
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes
                                </label>
                                <textarea
                                    v-model="form.notes"
                                    rows="3"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Add any notes..."
                                ></textarea>
                            </div>

                            <!-- Error Message -->
                            <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                                <p class="text-sm text-red-600">{{ error }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button
                            type="button"
                            @click="createTransfer"
                            :disabled="loading || !isValid"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                        >
                            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ loading ? 'Creating...' : 'Create Transfer' }}
                        </button>
                        <button
                            type="button"
                            @click="closeModal"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import axios from 'axios'

const props = defineProps({
    showModal: Boolean,
    stores: {
        type: Array,
        required: true
    },
    items: {
        type: Array,
        required: true
    },
    currentStore: {
        type: String,
        required: true
    },
    fromStoreName: {
        type: String,
        required: true
    }
})

const emit = defineEmits(['close'])

// Form state
const loading = ref(false)
const error = ref('')
const searchQuery = ref('')
const selectedItems = ref({})
const quantities = ref({})

const validateQuantity = (event, itemId) => {
    const value = event.target.value
    if (value < 0) {
        quantities.value[itemId] = 0
    }
}

const form = reactive({
    to_store_id: '',
    notes: ''
})

// Computed
const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    
    const query = searchQuery.value.toLowerCase()
    return props.items.filter(item => 
        item.itemname.toLowerCase().includes(query) ||
        item.itemid.toLowerCase().includes(query)
    )
})

const isValid = computed(() => {
    const hasDestination = !!form.to_store_id
    const hasItems = Object.values(selectedItems.value).some(selected => selected)
    const hasValidQuantities = Object.entries(selectedItems.value)
        .every(([itemId, selected]) => !selected || (quantities.value[itemId] && quantities.value[itemId] > 0))
    
    return hasDestination && hasItems && hasValidQuantities
})

// Methods
const closeModal = () => {
    emit('close')
    resetForm()
}

const resetForm = () => {
    form.to_store_id = ''
    form.notes = ''
    selectedItems.value = {}
    quantities.value = {}
    error.value = ''
    searchQuery.value = ''
}

const createTransfer = async () => {
    if (!props.currentStore) {
        error.value = 'Current store not found. Please refresh the page.'
        return
    }

    if (!form.destinationStore) {
        error.value = 'Please select a destination store'
        return
    }

    const transferItems = Object.entries(quantities.value)
        .map(([itemid, quantity]) => ({
            itemid,
            quantity: parseInt(quantity || 0)
        }))
        .filter(item => item.quantity > 0)

    if (transferItems.length === 0) {
        error.value = 'Please add quantity for at least one item'
        return
    }
}
</script>