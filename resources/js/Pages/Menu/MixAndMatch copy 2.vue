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
  loading.value = true;
  error.value = null;
  
  try {
    const orderData = {
      discountId: selectedDiscount.value.id,
      items: Object.entries(selectedItemsByGroup.value).map(([groupId, items]) => ({
        groupId,
        items: items.map(item => ({
          id: item.id,
          itemid: item.itemid,
          linegroup: item.linegroup,
          dealpriceordiscpct: item.dealpriceordiscpct.toString()
        }))
      }))
    };

    const response = await axios.post('/mix-match/submit-order', orderData);
    
    if (response.data.status === 'success') {
      // Reset the modal state
      isOpen.value = false;
      resetSelection();
      
      // Show success message
      const toast = document.createElement('div');
      toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2';
      toast.innerHTML = `
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>Order completed successfully!</span>
      `;
      document.body.appendChild(toast);
      
      // Remove toast after 3 seconds
      setTimeout(() => {
        toast.remove();
      }, 3000);
    } else {
      throw new Error(response.data.message || 'Failed to submit order');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to submit order. Please try again.';
    // Show error in UI
    const errorMessage = document.createElement('div');
    errorMessage.className = 'fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2';
    errorMessage.innerHTML = `
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
      </svg>
      <span>${error.value}</span>
    `;
    document.body.appendChild(errorMessage);
    
    // Remove error message after 3 seconds
    setTimeout(() => {
      errorMessage.remove();
    }, 3000);
  } finally {
    loading.value = false;
  }
};



onMounted(fetchDiscounts);
</script>

<template>
  <div class="p-4">
    <!-- Trigger Button -->
    <button 
      @click="isOpen = true"
      class="group relative inline-flex items-center justify-center px-6 py-3 overflow-hidden font-bold text-white rounded-lg shadow-2xl bg-gradient-to-br from-purple-600 to-blue-500 hover:from-purple-500 hover:to-blue-400 transition-all duration-300 ease-out hover:scale-105"
    >
      <span class="absolute right-0 w-8 h-32 -mt-12 transition-all duration-1000 transform translate-x-12 bg-white opacity-10 rotate-12 group-hover:-translate-x-40 ease"></span>
      <span class="relative">Mix&Match</span>
    </button>

    <!-- Modal Overlay -->
    <div v-if="isOpen" class="fixed inset-0 z-50">
      <!-- Backdrop -->
      <div class="absolute inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-sm"></div>

      <!-- Modal Content -->
      <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden">
          <!-- Header -->
          <div class="bg-gradient-to-r from-purple-600 to-blue-500 p-6">
            <div class="flex justify-between items-center">
              <h2 class="text-2xl font-bold text-white">Mix & Match Menu</h2>
              <button 
                @click="isOpen = false; resetSelection();"
                class="text-white hover:text-gray-200 transition-colors"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <!-- Progress Steps -->
            <div class="flex items-center justify-center space-x-4 mt-6">
              <div 
                v-for="i in 3" 
                :key="i"
                class="flex items-center"
              >
                <div 
                  :class="[
                    'w-8 h-8 rounded-full flex items-center justify-center border-2 font-bold',
                    step >= i 
                      ? 'bg-white text-purple-600 border-white' 
                      : 'border-white/50 text-white/50'
                  ]"
                >
                  {{ i }}
                </div>
                <div 
                  v-if="i < 3"
                  :class="[
                    'w-16 h-0.5 mx-2',
                    step > i ? 'bg-white' : 'bg-white/30'
                  ]"
                ></div>
              </div>
            </div>
          </div>

          <!-- Loading & Error States -->
          <div v-if="loading" class="p-12 text-center">
            <div class="w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin mx-auto"></div>
            <p class="mt-4 text-gray-600">Loading available offers...</p>
          </div>

          <div v-else-if="error" class="p-8 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 mb-4">
              <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <p class="text-red-600 font-medium">{{ error }}</p>
          </div>

          <!-- Main Content -->
          <div v-else class="p-6">
            <!-- Step 1: Select Discount -->
            <div v-if="step === 1" class="space-y-6">
              <h3 class="text-xl font-bold text-gray-800">Select an Offer</h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="discount in discounts"
                  :key="discount.id"
                  @click="selectDiscount(discount)"
                  class="group relative overflow-hidden p-6 rounded-xl border-2 border-gray-100 cursor-pointer transition-all duration-300 hover:border-purple-500 hover:shadow-lg"
                >
                  <div class="absolute inset-0 bg-gradient-to-r from-purple-500/0 to-blue-500/0 group-hover:from-purple-500/5 group-hover:to-blue-500/5 transition-colors duration-300"></div>
                  <h4 class="font-bold text-gray-800 group-hover:text-purple-600 transition-colors">
                    {{ discount.description }}
                  </h4>
                </div>
              </div>
            </div>

            <!-- Step 2: Select Items -->
            <div v-if="step === 2 && selectedDiscount" class="space-y-8">
              <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-800">Select Your Items</h3>
                <div class="px-4 py-2 bg-purple-100 rounded-full">
                  <span class="text-purple-600 font-medium">
                    {{ totalSelectedItems }} of {{ totalItemsNeeded }} selected
                  </span>
                </div>
              </div>

              <div v-for="group in groupedItems" :key="group.linegroup" class="space-y-4">
                <div class="flex items-center space-x-2">
                  <h4 class="text-lg font-semibold text-gray-800">
                    {{ group.description || `Group ${group.linegroup}` }}
                  </h4>
                  <span class="px-3 py-1 bg-gray-100 rounded-full text-sm text-gray-600">
                    Select {{ group.noofitemsneeded }}
                  </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                  <div
                    v-for="item in group.items"
                    :key="item.id"
                    @click="selectItem(item, group)"
                    :class="[
                      'relative p-4 rounded-xl transition-all duration-300',
                      isItemSelected(item, group.linegroup)
                        ? 'bg-gradient-to-r from-purple-500/10 to-blue-500/10 border-2 border-purple-500'
                        : isGroupComplete(group)
                          ? 'opacity-50 cursor-not-allowed bg-gray-50 border-2 border-gray-100'
                          : 'hover:shadow-md border-2 border-gray-100 cursor-pointer'
                    ]"
                  >
                    <div class="font-medium text-gray-800">Item: {{ item.itemid }}</div>
                    <div class="mt-2 inline-flex items-center space-x-1">
                      <span class="text-2xl font-bold text-purple-600">
                        {{ item.dealpriceordiscpct }}{{ item.disctype === 1 ? '%' : '' }}
                      </span>
                      <span class="text-sm text-gray-500">off</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: Review -->
            <div v-if="step === 3" class="space-y-6">
              <h3 class="text-xl font-bold text-gray-800">Review Your Selection</h3>
              <div class="space-y-6">
                <div 
                  v-for="group in groupedItems" 
                  :key="group.linegroup"
                  class="p-6 rounded-xl bg-gray-50 space-y-4"
                >
                  <h4 class="font-semibold text-gray-800">{{ group.description }}</h4>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                      v-for="item in selectedItemsByGroup[group.linegroup]"
                      :key="item.id"
                      class="p-4 bg-white rounded-lg shadow-sm border border-gray-100"
                    >
                      <div class="font-medium text-gray-800">{{ item.itemid }}</div>
                      <div class="mt-2 text-purple-600 font-bold">
                        {{ item.dealpriceordiscpct }}{{ item.disctype === 1 ? '%' : '' }} off
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="border-t bg-gray-50 p-6">
            <div class="flex justify-between items-center">
              <button 
                @click="step > 1 ? step-- : resetSelection()"
                class="px-6 py-2 border-2 border-gray-300 rounded-lg font-medium text-gray-600 hover:bg-gray-100 transition-colors"
              >
                {{ step > 1 ? 'Back' : 'Cancel' }}
              </button>
              
              <div class="flex space-x-4">
                <button
                  v-if="step < 3"
                  @click="step++"
                  :disabled="!selectedDiscount || (step === 2 && !isSelectionComplete)"
                  :class="[
                    'px-6 py-2 rounded-lg font-medium transition-all duration-300',
                    (!selectedDiscount || (step === 2 && !isSelectionComplete))
                      ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                      : 'bg-gradient-to-r from-purple-600 to-blue-500 text-white hover:from-purple-500 hover:to-blue-400'
                  ]"
                >
                  Continue
                </button>
                <button
                  v-else
                  @click="submitOrder"
                  class="px-6 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg font-medium hover:from-green-400 hover:to-emerald-500 transition-all duration-300">
                  <span class="relative inline-flex items-center">
                    <span>Complete Order</span>
                    <svg 
                      class="w-5 h-5 ml-2" 
                      fill="none" 
                      stroke="currentColor" 
                      viewBox="0 0 24 24"
                    >
                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M5 13l4 4L19 7"
                      />
                    </svg>
                  </span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Success Toast -->
    <div
      v-show="isOpen === false && step === 1"
      class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 ease-in-out"
      :class="isOpen ? 'translate-y-20 opacity-0' : 'translate-y-0 opacity-100'"
    >
      <div class="flex items-center space-x-2">
        <svg 
          class="w-6 h-6" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M5 13l4 4L19 7"
          />
        </svg>
        <span>Order completed successfully!</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Custom transitions */
.modal-enter-active,
.modal-leave-active {
  transition: all 0.3s ease-out;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
  transform: scale(0.95);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>