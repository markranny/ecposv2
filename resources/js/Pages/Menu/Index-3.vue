<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import CarouselSlides from '@/Components/Carousel/MenuCarouselSlides.vue';
import 'vue3-carousel/dist/carousel.css'
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import Card from '@/Components/Menu/Card.vue';
import SwipeLeft from '@/Components/Svgs/SwipeLeft.vue';
import SwipeRight from '@/Components/Svgs/SwipeRight.vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import Main from "@/Layouts/Main.vue";
import Title from "@/Components/Titles/Title.vue";
import Cart from "@/Components/Svgs/Cart.vue";
import MenuDot from "@/Components/Svgs/MenuDot.vue";
import ClearCart from "@/Components/Svgs/ClearCart.vue";
import ModalDaisy from "@/Components/DaisyUI/Modal.vue";

// Menu-related state
const showThis = ref('Purchase');
const selectedCategory = ref('');
const searchQuery = ref('');
const selectedAR = ref('CASH');
const selectedCustomer = ref('000000');
const cartMessage = ref('');

// Cart-related state
const cartItems = ref([]);
const total = ref(0);
const selectedItems = ref([]);
const selectAll = ref(false);
let pollingInterval;

const props = defineProps({
    category: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    ar: {
        type: Array,
        required: true,
    },
    customers: {
        type: Array,
        required: true,
    },
    windowId: {
        type: String,
        required: true,
    },
    windowDesc: {
        type: String,
        required: true,
    }
});

// Menu-related methods
const filteredItems = computed(() => {
    let filtered = props.items;
    
    if (selectedCategory.value) {
        filtered = filtered.filter(item => item.itemgroup === selectedCategory.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item.itemname.toLowerCase().includes(query) || 
            item.barcode.toLowerCase().includes(query)
        );
    }
    
    return filtered.map(item => ({
        ...item,
        category: item.itemgroup || selectedCategory.value || ''
    }));
});

const selectCategory = (name) => {
    selectedCategory.value = name === selectedCategory.value ? null : name;
};

const addToCart = async (itemId) => {
    try {
        const response = await fetch(`/windows/menu/addtocart/${itemId}/${props.windowId}/${selectedAR.value}/${selectedCustomer.value}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        const data = await response.json();
        if (data.success) {
            cartMessage.value = data.message;
            fetchCartData(); // Refresh cart data after adding an item
        }
    } catch (error) {
        console.error('Error adding item to cart:', error);
        cartMessage.value = 'Failed to add item to cart. Please try again.';
    }
};

// Cart-related methods
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
    <Main active-tab="HOME">
        <template v-slot:main>
            <div class="relative w-full h-full">
                <div class="absolute top-0 bottom-0 left-0 w-4/6 lg:w-4/6 p-2">
                    <div class="flex justify-between items-center gap-2">
                        <div class="flex items-center">
                            <!-- <Title class="text-sm font-bold lg:text-md">{{ windowDesc }}</Title>
                            <span class="mx-2">|</span> --> 
                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0">
                                <select v-model="selectedAR" id="payment-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                    <option value="" disabled>Payment Method</option>
                                    <option value="CASH">CASH</option>
                                    <option v-for="data in ar" :key="data.ID" :value="data.ar">{{ data.ar }}</option>
                                </select>
                            </div>

                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0 lg:ml-2">
                                <select v-model="selectedCustomer" id="customer-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                    <option value="" disabled>CUSTOMER</option>
                                    <option value="000000">WALK-IN</option>
                                    <option v-for="result in customers" :key="result.ID" :value="result.accountnum">{{ result.name }}</option>
                                </select>
                            </div>
                        </div>

                        <label class="input input-bordered flex items-center gap-2 w-3/5 lg:w-2/5">
                            <input v-model="searchQuery" type="text" class="grow text-sm" placeholder="Barcode / Item" />
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg>
                        </label>
                    </div>

                    <div class="relative px-10 mt-4">
                        <div class="flex overflow-x-auto">
                            <div class="flex flex-nowrap ml-10 text-xs w-[80%] lg:text-sm">
                                <div v-for="cat in category" :key="cat.name">
                                    <div class="flex-none p-2 w-48">
                                        <div 
                                            @click="selectCategory(cat.name)"
                                            :class="{'bg-blue-500 text-white': selectedCategory === cat.name, 'bg-gray-200': selectedCategory !== cat.name}"
                                            class="flex items-center justify-center rounded-lg p-2 font-bold italic cursor-pointer transition-colors duration-300 h-full"
                                        >
                                            {{ cat.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <SwipeLeft class="w-6 h-6 absolute left-0 top-1/2 -translate-y-1/2 transform"></SwipeLeft>
                        <SwipeRight class="w-6 h-6 absolute right-0 top-1/2 transform -translate-y-1/2"></SwipeRight>
                    </div>
                </div>

                <div class="absolute top-[20vh] bottom-0 left-0 w-9/12 ml-[-50px] lg:w-4/6 lg:top-35 px-2">
                    <div class="relative w-full h-full">
                        <div class="left-0 top-5 overflow-y-auto h-[70vh] w-full ml-10 justify-center gap-x-5 gap-y-4 p-2 lg:gap-x-20 lg:ml-20 ">
                            <div class="flex flex-wrap">
                                <div v-for="item in filteredItems" :key="item.itemid" class="relative flex flex-col justify-center w-[20%] min-h-20 bg-white shadow-md shadow-gray-200 py-4 px-2 rounded-lg cursor-pointer lg:w-2/12 m-2 hover:shadow-lg transition-shadow duration-300">
                                    <span class="font-bold text-base text-nowrap overflow-hidden">
                                        <figure @click="addToCart(item.itemid)"><img src="../../../../public/images/Product.webp" alt="Product" /></figure>
                                    </span>
                                    <span class="text-xs text-gray-400 font-thin">{{ item.barcode }}</span>
                                    <span class="text-xs text-gray-600 font-bold">{{ item.itemname }}</span>
                                    <span class="text-end text-sm text-gray-600 font-bold">Php {{ item.price }}.00</span>
                                    <span class="absolute top-0 right-0 h-9 w-9 bg-blue-400 rounded-full flex items-center justify-center">
                                        <div class="text-sm text-white">{{ item.quantity }}</div>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="absolute top-0 bottom-0 right-0 w-2/6 lg:w-2/6">
                    <!-- Cart Content -->
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

                            <section class="p-2">
                                <div class="flex text-sm font-bold justify-between items-center px-1">
                                    <p>GROSS</p>
                                    <p>121.22</p>
                                </div>

                                <div class="flex text-sm font-bold justify-between items-center px-1">
                                    <p>DISCOUNT</p>
                                    <p>121.22</p>
                                </div>

                                <p class="border-b border-b-gray-300 pt-3"></p>
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
                </div>
            </div>

           <!--  <div v-if="cartMessage" class="fixed bottom-4 right-4 bg-green-500 text-white p-2 rounded">
                {{ cartMessage }}
            </div> -->
        </template>
    </Main>
</template>

<style scoped>
.vue-slide {
  transition: transform 0.3s ease;
}
</style>