<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import Cart from "@/Components/Svgs/Cart.vue";
import MenuDot from "@/Components/Svgs/MenuDot.vue";
import ClearCart from "@/Components/Svgs/ClearCart.vue";
import ModalDaisy from "@/Components/DaisyUI/Modal.vue";

const cartItems = ref([]);
const total = ref(0);
const selectedItems = ref([]);
const selectAll = ref(false);
let pollingInterval;

const fetchCartData = async () => {
  try {
    const response = await fetch('/api/cart');
    const data = await response.json();
    cartItems.value = data.items.map(item => ({
      ...item,
      total_price: parseFloat(item.total_price),
      total_qty: parseFloat(item.total_qty)
    }));
    calculateTotal();
  } catch (error) {
    console.error('Error fetching cart data:', error);
  }
};

const calculateTotal = () => {
  total.value = cartItems.value.reduce((sum, item) => sum + item.total_price, 0);
};

const clearCart = async () => {
  try {
    await fetch('/api/cart/clear', { method: 'POST' });
    cartItems.value = [];
    selectedItems.value = [];
    selectAll.value = false;
    calculateTotal();
  } catch (error) {
    console.error('Error clearing cart:', error);
  }
};

const updateQuantity = async (itemName, newQuantity) => {
  try {
    const response = await fetch(`/api/cart/update/${encodeURIComponent(itemName)}`, {
      method: 'POST',
      body: JSON.stringify({ quantity: newQuantity }),
      headers: { 'Content-Type': 'application/json' }
    });
    const data = await response.json();
    const itemIndex = cartItems.value.findIndex(item => item.itemname === itemName);
    if (itemIndex !== -1) {
      cartItems.value[itemIndex] = {
        ...data.item,
        total_price: parseFloat(data.item.total_price),
        total_qty: parseFloat(data.item.total_qty)
      };
      calculateTotal();
    }
  } catch (error) {
    console.error('Error updating quantity:', error);
  }
};

const deleteSelectedItems = async () => {
  if (selectedItems.value.length === 0) return;

  try {
    const response = await fetch('/api/cart/delete-multiple', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: selectedItems.value })
    });
    
    if (response.ok) {
      cartItems.value = cartItems.value.filter(item => !selectedItems.value.includes(item.itemname));
      selectedItems.value = [];
      selectAll.value = false;
      calculateTotal();
    } else {
      console.error('Failed to delete items');
    }
  } catch (error) {
    console.error('Error deleting selected items:', error);
  }
};

const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedItems.value = cartItems.value.map(item => item.itemname);
  } else {
    selectedItems.value = [];
  }
};

const formattedPrice = (price) => {
  return typeof price === 'number' ? price.toFixed(2) : '0.00';
};

const formattedTotal = computed(() => formattedPrice(total.value));

onMounted(() => {
  fetchCartData();
  // Start polling for updates every 5 seconds
  pollingInterval = setInterval(fetchCartData, 5000);
});

onUnmounted(() => {
  // Clear the polling interval when the component is unmounted
  clearInterval(pollingInterval);
});
</script>

<template>
  <div class="relative bg-white border-s border-gray-400 shadow-md shadow-gray-600 w-full h-full lg:w-full lg:h-full max-w-sm lg:max-w-full">
    <div class="absolute left-0 right-0 top-0 h-10 bg-white text-black flex justify-center items-center text-xl font-bold p-3">
      <p class="tracking-widest">ORDER | CART</p>
      <div class="ml-auto flex items-center">
        <button @click="deleteSelectedItems" class="mr-2 px-2 py-1 bg-red-500 text-white rounded text-xs" :disabled="selectedItems.length === 0">
          Delete Selected
        </button>
        <a class="text-xs cursor-pointer" @click="clearCart"><ClearCart class="w-7 h-7"></ClearCart></a>
      </div>
    </div>
    <div class="absolute top-7 bottom-12 left-0 right-0 my-2 px-2 overflow-y-auto">
      <section class="h-5/6 px-1 py-3">
        <div class="h-full w-full overflow-y-auto bg-white p-3 top-0 right-0 left-0 m-0">
          <ul class="grid grid-cols-4 text-md font-bold lg:grid-cols-4">
            <li class="p-2 flex justify-center">
              <label class="inline-flex items-center">
                <input type="checkbox" 
                       v-model="selectAll" 
                       @change="toggleSelectAll"
                       class="form-checkbox h-4 w-4 text-blue-600 rounded-full"
                />
                <span class="ml-2">All</span>
              </label>
            </li>
            <li class="p-2 flex justify-center"><a>Item</a></li>
            <li class="p-2 flex justify-center"><a>Price</a></li>
            <li class="p-2 flex justify-center"><a>Qty</a></li>
            <li class="w-full col-span-6"><hr></li>
          </ul>
          <ul v-for="item in cartItems" :key="item.itemname" class="grid grid-cols-4 text-sm lg:grid-cols-4">
            <li class="p-2 flex justify-center">
              <label class="inline-flex items-center">
                <input type="checkbox" 
                       :value="item.itemname" 
                       v-model="selectedItems" 
                       class="form-checkbox h-4 w-4 text-blue-600 rounded-full"
                />
              </label>
            </li>
            <li class="p-2 overflow-hidden">
              <a class="whitespace-no-wrap">{{ item.itemname }}</a>
            </li>
            <li class="p-2 flex justify-center"><a><span class="font-bold text-xs">{{ formattedPrice(item.total_price) }}</span></a></li>
            <li class="p-2 flex justify-center">
              <a>
                <span class="font-bold text-xs mx-2">{{ item.total_qty }} PCS</span>
              </a>
            </li>
          </ul>
          <hr class="h-px-10 bg-gray-100">
        </div>
      </section>
    </div>
    <div class="absolute left-0 right-0 bottom-0 h-14 text-white bg-navy flex justify-between items-center border-t border-gray-900 shadow-sm shadow-gray-900">
      <div class="flex flex-col justify-start ps-1 tracking-wider">
        <p class="font-thin text-xs text-white ml-2">TOTAL</p>
        <p class="font-extrabold text-lg ml-2">PHP {{ formattedTotal }}</p>
      </div>
      <div class="skeleton m-5 w-1/3 h-10 bg-blue-900 flex justify-center items-center cursor-pointer font-bold text-white tracking-wider text-xs md:text-sm lg:text-base">
        <Cart class="w-10 p-2 lg:p-1"></Cart>
      </div>
    </div>
  </div>
</template>