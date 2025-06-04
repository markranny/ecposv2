<!-- resources/js/components/StockTransfer/Create.vue -->
<template>
    <div class="container mx-auto p-4">
      <h1 class="text-2xl font-bold mb-4">Create Stock Transfer</h1>
      
      <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <!-- Store Selection -->
        <div class="grid grid-cols-2 gap-4 mb-6">
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">From Store</label>
            <select 
              v-model="fromStoreId"
              class="w-full px-3 py-2 border rounded"
              :class="{ 'border-red-500': errors.fromStore }"
            >
              <option value="">Select Store</option>
              <option v-for="store in stores" :key="store.STOREID" :value="store.STOREID">
                {{ store.NAME }}
              </option>
            </select>
            <p v-if="errors.fromStore" class="text-red-500 text-xs mt-1">{{ errors.fromStore }}</p>
          </div>
          
          <div>
            <label class="block text-gray-700 text-sm font-bold mb-2">To Store</label>
            <select 
              v-model="toStoreId"
              class="w-full px-3 py-2 border rounded"
              :class="{ 'border-red-500': errors.toStore }"
            >
              <option value="">Select Store</option>
              <option 
                v-for="store in availableToStores" 
                :key="store.STOREID" 
                :value="store.STOREID"
              >
                {{ store.NAME }}
              </option>
            </select>
            <p v-if="errors.toStore" class="text-red-500 text-xs mt-1">{{ errors.toStore }}</p>
          </div>
        </div>
  
        <!-- Items Selection -->
        <div class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Transfer Items</h2>
            <button 
              @click="addItem"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
            >
              Add Item
            </button>
          </div>
  
          <div v-if="transferItems.length === 0" class="text-gray-500 text-center py-4">
            No items added to transfer
          </div>
  
          <div v-for="(item, index) in transferItems" :key="index" class="mb-4">
            <div class="grid grid-cols-12 gap-4 items-center bg-gray-50 p-4 rounded">
              <div class="col-span-4">
                <label class="block text-sm font-medium mb-1">Item</label>
                <select 
                  v-model="item.itemid"
                  class="w-full px-3 py-2 border rounded"
                  @change="updateItemDetails(index)"
                >
                  <option value="">Select Item</option>
                  <option 
                    v-for="availableItem in availableItems" 
                    :key="availableItem.itemid" 
                    :value="availableItem.itemid"
                  >
                    {{ availableItem.itemname }}
                  </option>
                </select>
              </div>
  
              <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Available Qty</label>
                <input 
                  type="text" 
                  :value="getAvailableQuantity(item.itemid)" 
                  class="w-full px-3 py-2 border rounded bg-gray-100" 
                  readonly
                >
              </div>
  
              <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Transfer Qty</label>
                <input 
                  type="number" 
                  v-model="item.quantity"
                  class="w-full px-3 py-2 border rounded"
                  min="1"
                  :max="getAvailableQuantity(item.itemid)"
                >
              </div>
  
              <div class="col-span-2">
                <label class="block text-sm font-medium mb-1">Unit Price</label>
                <input 
                  type="number" 
                  v-model="item.unit_price"
                  class="w-full px-3 py-2 border rounded bg-gray-100"
                  readonly
                >
              </div>
  
              <div class="col-span-2 flex items-end">
                <button 
                  @click="removeItem(index)"
                  class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
  
        <!-- Notes -->
        <div class="mb-6">
          <label class="block text-gray-700 text-sm font-bold mb-2">Notes</label>
          <textarea 
            v-model="notes"
            class="w-full px-3 py-2 border rounded"
            rows="3"
          ></textarea>
        </div>
  
        <!-- Actions -->
        <div class="flex justify-end gap-4">
          <button 
            @click="cancel"
            class="bg-gray-500 text-white px-6 py-2 rounded hover:bg-gray-600"
          >
            Cancel
          </button>
          <button 
            @click="saveTransfer"
            class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Saving...' : 'Save Transfer' }}
          </button>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, computed } from 'vue'
  import axios from 'axios'
  
  export default {
    name: 'CreateStockTransfer',
    
    setup() {
      const stores = ref([])
      const items = ref([])
      const fromStoreId = ref('')
      const toStoreId = ref('')
      const transferItems = ref([])
      const notes = ref('')
      const errors = ref({})
      const isLoading = ref(false)
  
      // Fetch initial data
      const fetchStoresAndItems = async () => {
        try {
          const [storesResponse, itemsResponse] = await Promise.all([
            axios.get('/api/stock-transfers/stores'),
            axios.get('/api/stock-transfers/items')
          ])
          stores.value = storesResponse.data
          items.value = itemsResponse.data
        } catch (error) {
          console.error('Error fetching initial data:', error)
        }
      }
  
      // Computed properties
      const availableToStores = computed(() => {
        return stores.value.filter(store => store.STOREID !== fromStoreId.value)
      })
  
      const availableItems = computed(() => {
        return items.value.filter(item => {
          const module = item.inventtablemodules?.[0]
          return module && !module.blocked
        })
      })
  
      // Methods
      const getAvailableQuantity = (itemId) => {
        const item = items.value.find(i => i.itemid === itemId)
        return item?.inventtablemodules?.[0]?.quantity || 0
      }
  
      const addItem = () => {
        transferItems.value.push({
          itemid: '',
          quantity: 1,
          unit_price: 0
        })
      }
  
      const removeItem = (index) => {
        transferItems.value.splice(index, 1)
      }
  
      const updateItemDetails = (index) => {
        const item = transferItems.value[index]
        const inventItem = items.value.find(i => i.itemid === item.itemid)
        if (inventItem?.inventtablemodules?.[0]) {
          item.unit_price = inventItem.inventtablemodules[0].price || 0
        }
      }
  
      const validateForm = () => {
        errors.value = {}
        
        if (!fromStoreId.value) {
          errors.value.fromStore = 'Please select a source store'
        }
        
        if (!toStoreId.value) {
          errors.value.toStore = 'Please select a destination store'
        }
        
        if (fromStoreId.value === toStoreId.value) {
          errors.value.toStore = 'Source and destination stores must be different'
        }
        
        if (transferItems.value.length === 0) {
          errors.value.items = 'Please add at least one item to transfer'
        }
        
        for (let i = 0; i < transferItems.value.length; i++) {
          const item = transferItems.value[i]
          if (!item.itemid) {
            errors.value[`item_${i}`] = 'Please select an item'
          }
          if (!item.quantity || item.quantity <= 0) {
            errors.value[`quantity_${i}`] = 'Quantity must be greater than 0'
          }
          if (item.quantity > getAvailableQuantity(item.itemid)) {
            errors.value[`quantity_${i}`] = 'Transfer quantity cannot exceed available quantity'
          }
        }
        
        return Object.keys(errors.value).length === 0
      }
  
      const saveTransfer = async () => {
        if (!validateForm()) {
          return
        }
        
        isLoading.value = true
        
        try {
          const response = await axios.post('/api/stock-transfers', {
            from_store_id: fromStoreId.value,
            to_store_id: toStoreId.value,
            notes: notes.value,
            items: transferItems.value
          })
          
          // Show success message
          alert('Stock transfer created successfully!')
          
          // Reset form
          fromStoreId.value = ''
          toStoreId.value = ''
          transferItems.value = []
          notes.value = ''
          
          // Optionally redirect to transfer list or detail page
          // router.push('/stock-transfers')
        } catch (error) {
          console.error('Error saving stock transfer:', error)
          alert('Error creating stock transfer. Please try again.')
        } finally {
          isLoading.value = false
        }
      }
  
      const cancel = () => {
        // Optionally add confirmation dialog
        if (confirm('Are you sure you want to cancel? All changes will be lost.')) {
          // Reset form or redirect
          fromStoreId.value = ''
          toStoreId.value = ''
          transferItems.value = []
          notes.value = ''
          // router.push('/stock-transfers')
        }
      }
  
      // Initialize data on component mount
      fetchStoresAndItems()
  
      return {
        stores,
        availableToStores,
        availableItems,
        fromStoreId,
        toStoreId,
        transferItems,
        notes,
        errors,
        isLoading,
        addItem,
        removeItem,
        updateItemDetails,
        getAvailableQuantity,
        saveTransfer,
        cancel
      }
    }
  }