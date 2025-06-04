<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const modalRef = ref(null);
const selectedItems = ref([]);
const discountData = ref([]);

const showModal = (items) => {
  selectedItems.value = items;
  const modal = document.getElementById('my_modal_1');
  modal.showModal();
};

const closeModal = () => {
  const modal = document.getElementById('my_modal_1');
  modal.close();
};

const handleDiscountClick = async () => {
  try {
    const response = await axios.get('/api/discounts');
    discountData.value = response.data;
  } catch (error) {
    console.error('Error fetching discount data:', error);
  }
};

const safeToFixed = (value, decimals = 2) => {
  if (typeof value === 'number') {
    return value.toFixed(decimals);
  }
  return '0.00';
};

onMounted(() => {
  modalRef.value.addEventListener('click', (e) => {
    const dialogDimensions = modalRef.value.getBoundingClientRect();
    if (
      e.clientX < dialogDimensions.left ||
      e.clientX > dialogDimensions.right ||
      e.clientY < dialogDimensions.top ||
      e.clientY > dialogDimensions.bottom
    ) {
      e.preventDefault();
      e.stopPropagation();
    }
  });
});

defineExpose({ showModal, closeModal });
</script>

<template>
  <div>
    <dialog id="my_modal_1" ref="modalRef" class="modal w-full h-full p-0 max-w-none max-h-none">
      <div class="w-full h-full flex flex-col bg-white">
        <div class="p-4 border-b">
          <h2 class="text-2xl font-bold">EC-POS</h2>
        </div>
        
        <div class="flex-grow flex">
          <div class="w-1/2 p-6 border-r">
            <h3 class="font-bold text-lg mb-4">MENU</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 p-6 h-[70vh]">
              <div v-for="(item, index) in ['DISCOUNT', 'PARTIAL PAYMENT', 'REMOVE PARTIAL PAYMENT', 'DAILY JOURNAL', 'TENDER DECLARATION', 'PULLOUT CASHFUND', 'X-READ', 'Z-READ']" :key="index" 
                   class="bg-navy text-white rounded-lg shadow-lg overflow-hidden flex items-center justify-center h-full cursor-pointer"
                   @click="item === 'DISCOUNT' ? handleDiscountClick() : null">
                <div class="p-6 text-center flex-grow">
                  <h2 class="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-semibold text-white">{{ item }}</h2>
                </div>
              </div>
            </div>
          </div>

          <div class="w-2/5 p-6">
            <h3 class="font-bold text-lg mb-4">INPUT</h3>
            <div v-if="discountData.length > 0" class="max-h-[70vh] overflow-y-auto">
              <div v-for="discount in discountData" :key="discount.id" class="mb-4 p-4 border rounded">
                <h4 class="font-semibold">{{ discount.DISCOFFERNAME }}</h4>
                <p><strong>Parameter:</strong> {{ discount.PARAMETER }}</p>
                <p><strong>Discount Type:</strong> {{ discount.DISCOUNTTYPE }}</p>
              </div>
            </div>
            <p v-else>No discount data available</p>
          </div>

          <div class="w-1/2 p-6">
            <h3 class="font-bold text-lg mb-4">Selected Items</h3>
            <div v-if="selectedItems.length > 0" class="max-h-[70vh] overflow-y-auto">
              <div v-for="item in selectedItems" :key="item.itemname" class="mb-4 p-4 border rounded">
                <h4 class="font-semibold">{{ item.itemname || 'Unnamed Item' }}</h4>
                <p><strong>Quantity:</strong> 
                  <input type="number" 
                         v-model="item.total_qty" 
                         class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" 
                         min="0" 
                         required>
                </p>
              </div>
            </div>
            <p v-else>No items selected</p>
          </div>
        </div>
        
        <div class="p-4 border-t flex justify-end">
          <form method="dialog">
            <!-- <button class="btn bg-navy hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              SAVE
            </button> -->
            <button class="btn bg-navy hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              Close
            </button>
          </form>
        </div>
      </div>
    </dialog>
  </div>
</template>