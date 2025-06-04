<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const modalRef = ref(null);
const selectedItemsForModal = ref([]);
const discountData = ref([]);
const selectedDiscount = ref(null);
const cartItems = ref([]);
const selectedItems = ref([]); 
const isLoading = ref(false);

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
    selectedDiscount.value = null; // Reset selected discount when fetching new data
  } catch (error) {
    console.error('Error fetching discount data:', error);
  }
};

/* const selectDiscount = async (discount) => {
  selectedDiscount.value = discount;
  
  try {
    const itemsToUpdate = selectedItems.value.map(item => ({
      itemid: item.itemid,
      discamount: parseFloat(discount.PARAMETER),
    }));

    console.log('Sending request with data:', { items: itemsToUpdate, discount });

    const response = await axios.post('/api/update-cart-discount', {
      items: itemsToUpdate,
      discount: discount
    });

    console.log('Server response:', response.data);

    cartItems.value = cartItems.value.map(item => {
      const updatedItem = response.data.find(i => i.itemid === item.itemid);
      return updatedItem ? { ...item, ...updatedItem } : item;
    });

    console.log('Discount applied successfully!');

    location.reload();
  } catch (error) {
    console.error('Error applying discount:', error);
    if (error.response) {
      console.error('Server responded with:', error.response.data);
    }
    alert('Failed to apply discount. Please check the console for more details.');
  }
}; */

const selectDiscount = async (discount) => {
  selectedDiscount.value = discount;
  
  try {
    isLoading.value = true; // Set loading to true before starting the operation
    const itemsToUpdate = selectedItems.value.map(item => ({
      itemid: item.itemid,
      discamount: parseFloat(discount.PARAMETER),
    }));

    console.log('Sending request with data:', { items: itemsToUpdate, discount });

    const response = await axios.post('/api/update-cart-discount', {
      items: itemsToUpdate,
      discount: discount
    });

    console.log('Server response:', response.data);

    cartItems.value = cartItems.value.map(item => {
      const updatedItem = response.data.find(i => i.itemid === item.itemid);
      return updatedItem ? { ...item, ...updatedItem } : item;
    });

    console.log('Discount applied successfully!');

    // Wait for a short time to show the success message
    await new Promise(resolve => setTimeout(resolve, 1000));

    location.reload();
  } catch (error) {
    console.error('Error applying discount:', error);
    if (error.response) {
      console.error('Server responded with:', error.response.data);
    }
    alert('Failed to apply discount. Please check the console for more details.');
  } finally {
    isLoading.value = false; // Set loading to false after the operation (success or failure)
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
        <div v-if="isLoading" class="absolute inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-white"></div>
            <p class="mt-4 text-white text-xl font-semibold">Applying Discount...</p>
          </div>
        </div>
        <div class="p-4 border-b">
          <h2 class="text-2xl font-bold text-black">EC-POS</h2>
        </div>
        
        <div class="flex-grow flex">
          <div class="w-1/3 p-6 border-r">
            <h3 class="font-bold text-lg mb-4 text-black">MENU</h3>

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
            <h3 class="font-bold text-lg mb-4 text-black">INPUT</h3>
            <div v-if="discountData.length > 0" class="max-h-[70vh] overflow-y-auto">
              <div v-for="discount in discountData" 
                   :key="discount.id" 
                   class="mb-4 p-4 border rounded cursor-pointer hover:bg-gray-100"
                   @click="selectDiscount(discount)"
                   :class="{ 'bg-blue-100': selectedDiscount && selectedDiscount.id === discount.id }">
                <h4 class="font-semibold text-black">{{ discount.DISCOFFERNAME }}</h4>
                <p class="text-black"><strong>Parameter:</strong> {{ discount.PARAMETER }}</p>
                <p class="text-black"><strong>Discount Type:</strong> {{ discount.DISCOUNTTYPE }}</p>
              </div>
            </div>
            <p v-else class="text-black">No discount data available</p>
          </div>

          <div class="w-1/2 p-6">
            <h3 class="font-bold text-lg mb-4 text-black">Selected Items</h3>
            <div v-if="selectedItems.length > 0" class="max-h-[70vh] overflow-y-auto">
              <div v-for="item in selectedItems" :key="item.itemid" class="mb-4 p-4 border rounded">
                <h4 class="font-semibold text-black">{{ item.itemname || 'Unnamed Item' }}</h4>
                <p class="text-black"><strong>Item ID:</strong> {{ item.itemid }}</p>
                <p class="text-black"><strong>PRICE:</strong> 
                  <input type="number" 
                         v-model="item.total_price" 
                         class="bg-gray-50 border border-gray-300 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                         min="0" 
                         required>
                </p>
                <p class="text-black"><strong>Quantity:</strong> 
                  <input type="number" 
                         v-model="item.total_qty" 
                         class="bg-gray-50 border border-gray-300 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                         min="0" 
                         required>
                </p>
              </div>
            </div>
            <p v-else class="text-black">No items selected</p>
            
            <div v-if="selectedDiscount" class="mt-4 p-4 border rounded bg-green-100">
              <h4 class="font-semibold text-black">Selected Discount:</h4>
              <p class="text-black">{{ selectedDiscount.DISCOFFERNAME }}</p>
              <p class="text-black"><strong>ID:</strong> {{ selectedDiscount.id }}</p>
              <p class="text-black"><strong>Parameter:</strong> {{ selectedDiscount.PARAMETER }}</p>
              <p class="text-black"><strong>Discount Type:</strong> {{ selectedDiscount.DISCOUNTTYPE }}</p>
            </div>
          </div>
        </div>
        
        <div class="p-4 border-t flex justify-end">
          <form method="dialog">
            <button class="btn bg-navy hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              SAVE PRICE & QTY
            </button>
            <button class="btn bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
              Close
            </button>
          </form>
        </div>
      </div>
    </dialog>
  </div>
</template>