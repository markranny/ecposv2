<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const discounts = ref([]);
const selectedDiscount = ref(null);
const selectedItemsByGroup = ref({});
const step = ref(1);
const isOpen = ref(false);
const loading = ref(false);
const error = ref(null);

const selectDiscount = (discount) => {
  selectedDiscount.value = discount;
  selectedItemsByGroup.value = {};
  step.value = 2;
};

const groupedItems = computed(() => {
  if (!selectedDiscount.value) return [];
  
  return selectedDiscount.value.line_groups.map(group => ({
    ...group,
    items: group.discount_lines || [],
    selected: selectedItemsByGroup.value[group.linegroup] || []
  }));
});

const fetchDiscounts = async () => {
  loading.value = true;
  error.value = null;
  try {
    const response = await axios.get('/mix-match/discounts');
    discounts.value = response.data;
  } catch (err) {
    error.value = 'Failed to load discounts. Please try again.';
  } finally {
    loading.value = false;
  }
};

const selectItem = (item, group) => {
  if (!selectedItemsByGroup.value[group.linegroup]) {
    selectedItemsByGroup.value[group.linegroup] = [];
  }
  
  const groupItems = selectedItemsByGroup.value[group.linegroup];
  const itemIndex = groupItems.findIndex(i => i.id === item.id);
  
  if (itemIndex > -1) {
    groupItems.splice(itemIndex, 1);
  } else if (groupItems.length < group.noofitemsneeded) {
    groupItems.push(item);
  }
  
  if (isSelectionComplete.value) {
    step.value = 3;
  }
};

const isItemSelected = (item, groupId) => {
  return selectedItemsByGroup.value[groupId]?.some(i => i.id === item.id) || false;
};

const isGroupComplete = (group) => {
  const selectedCount = selectedItemsByGroup.value[group.linegroup]?.length || 0;
  return selectedCount === group.noofitemsneeded;
};

const isSelectionComplete = computed(() => {
  if (!selectedDiscount.value) return false;
  return selectedDiscount.value.line_groups.every(group => isGroupComplete(group));
});

const totalSelectedItems = computed(() => {
  return Object.values(selectedItemsByGroup.value)
    .reduce((sum, items) => sum + items.length, 0);
});

const totalItemsNeeded = computed(() => {
  if (!selectedDiscount.value) return 0;
  return selectedDiscount.value.line_groups
    .reduce((sum, group) => sum + group.noofitemsneeded, 0);
});

const resetSelection = () => {
  selectedDiscount.value = null;
  selectedItemsByGroup.value = {};
  step.value = 1;
};

const submitOrder = async () => {
  try {
    const orderData = {
      discountId: selectedDiscount.value.id,
      items: Object.entries(selectedItemsByGroup.value).map(([groupId, items]) => ({
        groupId,
        items: items.map(item => ({
          id: item.id,
          itemid: item.itemid,
          linegroup: item.linegroup,
          dealpriceordiscpct: item.dealpriceordiscpct
        }))
      }))
    };
    
    await axios.post('/api/mix-match/submit-order', orderData);
    isOpen.value = false;
    resetSelection();
  } catch (err) {
    error.value = 'Failed to submit order. Please try again.';
  }
};


onMounted(fetchDiscounts);
</script>

<template>
  <div class="p-4">
    <button 
      @click="isOpen = true"
      class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md"
    >
      Mix & Match Menu
    </button>

    <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white w-11/12 max-w-4xl max-h-[90vh] overflow-y-auto rounded-lg shadow-xl">
        <!-- Header -->
        <div class="flex justify-between items-center p-4 border-b">
          <h2 class="text-2xl font-bold">Mix & Match Menu</h2>
          <button @click="isOpen = false; resetSelection();" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Loading & Error States -->
        <div v-if="loading" class="p-6 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
          <p class="mt-4">Loading discounts...</p>
        </div>

        <div v-else-if="error" class="p-6 text-center text-red-600">
          {{ error }}
        </div>

        <!-- Main Content -->
        <div v-else class="p-6">
          <!-- Step 1: Select Discount -->
          <div v-if="step === 1">
            <h3 class="text-xl font-bold mb-4">Select an Offer</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div
                v-for="discount in discounts"
                :key="discount.id"
                @click="selectDiscount(discount)"
                class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
              >
                <h4 class="font-bold">{{ discount.description }}</h4>
              </div>
            </div>
          </div>

          <!-- Step 2: Select Items -->
          <div v-if="step === 2 && selectedDiscount" class="space-y-6">
            <h3 class="text-xl font-bold mb-4">
              Selected {{ totalSelectedItems }} of {{ totalItemsNeeded }} items
            </h3>

            <div v-for="group in groupedItems" :key="group.linegroup" class="mb-8">
              <h4 class="text-lg font-semibold mb-2">
                {{ group.description || `Group ${group.linegroup}` }}
                (Select {{ group.noofitemsneeded }})
              </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                    v-for="item in group.items"
                    :key="item.id"
                    @click="selectItem(item, group)"
                    :class="[
                        'border rounded-lg p-4 cursor-pointer',
                        isItemSelected(item, group.linegroup)
                        ? 'bg-green-100 border-green-500'
                        : isGroupComplete(group)
                            ? 'opacity-50 cursor-not-allowed'
                            : 'hover:border-blue-500'
                    ]"
                    >
                    <div class="font-medium">Item: {{ item.itemid }}</div>
                    <div class="text-sm text-gray-600">
                        {{ item.dealpriceordiscpct }}
                        {{ item.disctype === 1 ? '%' : '$' }}
                    </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- Step 3: Review -->
          <div v-if="step === 3" class="space-y-6">
            <h3 class="text-xl font-bold mb-4">Review Selection</h3>
            <div v-for="group in groupedItems" :key="group.linegroup" class="mb-4">
              <h4 class="font-semibold mb-2">{{ group.description }}</h4>
              <div class="space-y-2">
                <div
                  v-for="item in selectedItemsByGroup[group.linegroup]"
                  :key="item.id"
                  class="p-2 bg-gray-50 rounded"
                >
                  {{ item.itemid }} - {{item.dealpriceordiscpct }}
                  {{ item.disctype === 1 ? '%' : 'P' }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="border-t p-4 flex justify-between items-center">
          <button 
            @click="step > 1 ? step-- : resetSelection()"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md"
          >
            {{ step > 1 ? 'Back' : 'Cancel' }}
          </button>
          
          <div class="flex space-x-4">
            <button
              v-if="step < 3"
              @click="step++"
              :disabled="!selectedDiscount || (step === 2 && !isSelectionComplete)"
              :class="[
                'px-4 py-2 rounded-md',
                (!selectedDiscount || (step === 2 && !isSelectionComplete))
                  ? 'bg-gray-300 cursor-not-allowed'
                  : 'bg-blue-500 text-white hover:bg-blue-600'
              ]"
            >
              Next
            </button>
            <button
              v-else
              @click="submitOrder"
              class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600"
            >
              Complete Order
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>