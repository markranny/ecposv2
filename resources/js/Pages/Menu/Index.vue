<script setup>
import { ref, onMounted, computed, onUnmounted, watch } from 'vue';
import CarouselSlides from '@/Components/Carousel/MenuCarouselSlides.vue';
import 'vue3-carousel/dist/carousel.css'
import MixAndMatchModal from '@/Pages/Menu/MixAndMatch.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import Card from '@/Components/Menu/Card.vue';
import SwipeLeft from '@/Components/Svgs/SwipeLeft.vue';
import SwipeRight from '@/Components/Svgs/SwipeRight.vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import Main from "@/Layouts/Main.vue";
import Title from "@/Components/Titles/Title.vue";
import Cart from "@/Components/Svgs/Cart.vue";
import Emoji from "@/Components/Svgs/emoji.vue";
import MenuDot from "@/Components/Svgs/MenuDot.vue";
import ClearCart from "@/Components/Svgs/ClearCart.vue";
import ModalDaisy from "@/Components/DaisyUI/Modal.vue";
import CashInputModal from '@/Components/DaisyUI/submitpayment.vue';
import Swal from 'sweetalert2';

const selectedCategory = ref('');
const searchQuery = ref('');
const selectedAR = ref('CASH');
const selectedCustomer = ref('WALK-IN');
const cartMessage = ref('');
const selectedItem = ref(null);

const cartItems = ref([]);
const total = ref(0);
const selectedItems = ref([]);
const selectAll = ref(false);
const selectedItemsForModal = ref([]);
const discountData = ref([]);
const selectedDiscount = ref(null);
let pollingInterval;
const note = ref('');

const cashAmount = ref('');
const isLoading = ref(false);
const storeId = ref(''); 
const staffId = ref(''); 

const modalRef = ref(null);

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
        // Ensure windowId is properly passed and converted to a number if needed
        const windowId = Number(props.windowId);
        
        if (!windowId) {
            cartMessage.value = 'Invalid window ID';
            return false;
        }

        const response = await fetch(`/windows/menu/addtocart/${itemId}/${windowId}/${selectedAR.value}/${selectedCustomer.value}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            cartMessage.value = data.message;
            await fetchCartData();
            return true;
        } else {
            cartMessage.value = data.message || 'Failed to add item to cart';
            return false;
        }
    } catch (error) {
        console.error('Error adding item to cart:', error);
        cartMessage.value = 'Failed to add item to cart. Please try again.';
        return false;
    }
}

const fetchCartData = async () => {
  try {
    const windowId = Number(props.windowId);

    if (!windowId) {
      cartMessage.value = 'Invalid window ID';
      return false;
    }

    const response = await fetch(`/api/cart/${windowId}`, {
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      },
    });

    if (!response.ok) {
      const errorMessage = await response.text();
      throw new Error(`HTTP error ${response.status}: ${errorMessage}`);
    }

    const data = await response.json();
    cartItems.value = data.items.map((item) => ({
      ...item,
      total_price: parseFloat(item.total_price),
      total_qty: parseFloat(item.total_qty),
    }));
    calculateTotal();
  } catch (error) {
    console.error('Error fetching cart data:', error);
    cartMessage.value = error.message;
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

      location.reload();
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

const openModal = () => {
  if (modalRef.value) {
    const selectedItemsData = cartItems.value.filter(item => 
      selectedItems.value.includes(item.itemname)
    );

    const hasDiscountedItem = selectedItemsData.some(item => item.discamount > 0);
    const itemselected = selectedItems.value.length === 0;
      selectedItemsForModal.value = selectedItemsData.map(item => ({
        ...item,
        itemid: item.itemid || item.itemId || 'No ID'
      }));
      console.log('Items being sent to modal:', selectedItemsForModal.value);
      modalRef.value.showModal(selectedItemsForModal.value);
  }
};

const VAT_RATE = 0.12; 

const computeVAT = computed(() => {
  const netTotal = calculateNetsTotal();
  const VATABLECOMPUTE = netTotal / 1.12;
  return VATABLECOMPUTE * VAT_RATE;
});

const computeNetTotalWithVAT = computed(() => {
  return calculateNetTotal() + computeVAT.value;
});

const calculateGrossTotal = () => {
  return cartItems.value.reduce((sum, item) => sum + item.total_price, 0);
};

const calculateNetsTotal = () => {
  return cartItems.value.reduce((sum, item) => sum + item.netamount, 0);
};

const calculatePartialTotal = () => {
  return cartItems.value.reduce((sum, item) => sum + item.partial_payment, 0);
};

const calculateTotalDiscount = () => {
  return cartItems.value.reduce((sum, item) => sum + item.discamount, 0);
};

const calculateTotalPartial = () => {
  return cartItems.value.reduce((sum, item) => sum + item.partial_payment, 0);
};


const calculateNetTotal = () => {
  return calculateGrossTotal() - calculateTotalDiscount();
};

let searchTimeout = null;

const handleBarcodeInput = (event) => {
  const barcode = event.target.value;
  searchQuery.value = barcode;

  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }

  // Set a new timeout
  searchTimeout = setTimeout(() => {
    processBarcode(barcode);
  }, 300); 
};

const processBarcode = async (barcode) => {
  const item = props.items.find(item => item.barcode === barcode);

  if (item) {
    const success = await addToCart(item.itemid);
    if (success) {
      console.log('Item added to cart:', item.itemname);
      searchQuery.value = ''; 
    } else {
      console.log('Failed to add item to cart');
    }
  } else {
    console.log('Item not found for barcode:', barcode);
  }
};

const showCashModal = ref(false);

const openCashModal = () => {
  showCashModal.value = true;
};

const handleEnter = () => {
    this.handleCashSubmit(cashAmount);
};

const handleCashSubmit = async (cashAmount) => {
  console.log('Cash Amount Input:', cashAmount);

  const cashAmountParsed = parseFloat(cashAmount);
  
  if (isNaN(cashAmountParsed) || cashAmountParsed < 0) {
    await Swal.fire({
      icon: 'error',
      title: 'Invalid Input',
      text: 'Please enter a valid cash amount.',
      confirmButtonText: 'OK',
      position: 'center'
    });
    return;
  }

  const netTotal = calculateNetTotal();
  const totalAmount = netTotal - calculateTotalPartial();

  // Check if customer is not WALK-IN
  if (selectedCustomer.value !== 'WALK-IN') {
    // For non-WALK-IN customers, validate if it's an exact amount payment
    if (cashAmountParsed !== totalAmount) {
      await Swal.fire({
        icon: 'error',
        title: 'Invalid Input',
        text: 'This is Account Receivable, Please input Exact Amount',
        confirmButtonText: 'OK',
        position: 'center'
      });
      return;
    }
  } else {
    // For WALK-IN customers, check if the amount is sufficient
    if (cashAmountParsed < totalAmount) {
      await Swal.fire({
        icon: 'warning',
        title: 'Insufficient Cash',
        text: `Cash amount must be equal to or greater than the total amount (${totalAmount.toFixed(2)}).`,
        confirmButtonText: 'OK',
        position: 'center'
      });
      return;
    }
  }

  
  if (cashAmountParsed < totalAmount) {
    await Swal.fire({
      icon: 'warning',
      title: 'Insufficient Cash',
      text: `Cash amount must be equal to or greater than the total amount (${totalAmount.toFixed(2)}).`,
      confirmButtonText: 'OK',
      position: 'center'
    });
    return;
  }

  try {
    isLoading.value = true;

    const orderData = {
      cashAmount: cashAmountParsed,
      cartItems: cartItems.value.map(item => ({
        note: note.value,
        itemid: item.itemid,
        itemname: item.itemname,
        itemgroup: item.itemgroup,
        discofferid: item.discofferid,
        price: parseFloat(item.price),
        total_qty: parseFloat(item.total_qty),
        total_price: parseFloat(item.total_price) || 0,
        total_netprice: parseFloat(item.total_netprice) || 0,
        discamount: parseFloat(item.discamount) || 0,
        costamount: parseFloat(item.costamount) || 0,
        unit: item.unit || 'PCS',
        wintransid: item.wintransid || 'PCS',
        taxinclinprice: parseFloat(item.taxinclinprice) || 0,
        netamountnotincltax: parseFloat(item.netamountnotincltax) || 0,
        partialpayment: parseFloat(item.partialpayment) || 0 
      })),
      totalAmount: totalAmount,
      selectedAR: selectedAR.value, 
      selectedCustomer: selectedCustomer.value,
      store: 'ANCHETA',
      staff: 'ANCHETA',
      partialpayment: calculatePartialPayment(), 
      note: note.value,
    };

    console.log('Order Data:', orderData);

    const response = await axios.post('/submit-order', orderData);

    if (response.data.success) {
      showCashModal.value = false;
      note.value = '';
      
      // Calculate cash change
      const cashChange = cashAmountParsed - totalAmount;
      await Swal.fire({
        icon: 'success',
        title: `Cash Change: <br>${cashChange.toFixed(2)}`,
        confirmButtonText: 'OK',
        position: 'center'
      });
      
    } else {
      throw new Error(response.data.message || 'Failed to submit order');
    }
  } catch (error) {
    console.error('Error submitting order:', error);
    if (error.response && error.response.data && error.response.data.errors) {
      const errorMessages = Object.values(error.response.data.errors).flat().join('\n');
      await Swal.fire({
        icon: 'error',
        title: 'Submission Failed',
        text: `Please correct the following errors:\n\n${errorMessages}`,
        confirmButtonText: 'OK',
        position: 'center'
      });
    } else {
      await Swal.fire({
        icon: 'error',
        title: 'Submission Failed',
        text: `Error: ${error.message}`,
        confirmButtonText: 'OK',
        position: 'center'
      });
    }
  } finally {
    isLoading.value = false;
  }
};



const calculatePartialPayment = () => {
  return cartItems.value.reduce((total, item) => total + (parseFloat(item.partial_payment) || 0), 0);
};

watch(selectedAR, (newValue) => {
  console.log('Selected payment method changed:', newValue);
});

onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
});

onMounted(() => {
  fetchCartData();
  pollingInterval = setInterval(fetchCartData, 5000);
});

onUnmounted(() => {
  clearInterval(pollingInterval);
});
</script>

<template>
    <Main active-tab="POS">
        <template v-slot:main>
            <div class="relative w-full h-full">
                <div class="absolute top-0 bottom-0 left-0 w-4/6 lg:w-4/6 p-2">
                    <div class="flex justify-between items-center gap-2">
                        <div class="flex items-center">
                          <!-- <Title class="text-sm font-bold lg:text-md hidden lg:block">{{ windowDesc }}</Title> -->
                          <!-- <span class="mx-2 hidden lg:block">|</span>  -->
                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0 sm:mr-1 md:mr-1">
                            <select v-model="selectedAR" id="payment-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                <option value="" disabled>Payment Method</option>
                                <!-- <option value="CASH">CASH</option> -->
                                <option v-for="data in ar" :key="data.ID" :value="data.ar">{{ data.ar }}</option>
                            </select>
                            </div>

                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0 lg:ml-2">
                                <select v-model="selectedCustomer" id="customer-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                    <option value="" disabled>CUSTOMER</option>
                                    <!-- <option value="WALK-IN">WALK-IN</option> -->
                                    <option v-for="result in customers" :key="result.ID" :value="result.name">{{ result.name }}</option>
                                </select>
                            </div>
                        </div>

                        <label class="input input-bordered flex items-center gap-2 w-3/5 lg:w-3/5">
                            <input 
                            v-model="searchQuery"
                            @input="handleBarcodeInput"
                            type="text" 
                            class="grow text-sm" 
                            placeholder="Barcode / Item" 
                            />
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70">
                            <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" />
                            </svg>
                        </label>
                    </div>

                    <div class="relative px-10 mt-4">
                        <div class="flex overflow-x-auto">
                            <div class="flex flex-nowrap text-xs lg:text-sm lg:ml-10 lg:w-[80%] md:h-[13vh] lg:h-[8vh]">

                                <div class="flex items-center justify-center rounded-lg p-2 font-bold italic cursor-pointer transition-colors duration-300 h-10 mt-2">
                                    <MixAndMatchModal />
                                </div> 

                                <div class="flex items-center bg-red-900 justify-center text-white rounded-lg p-5 font-bold italic cursor-pointer transition-colors duration-300 h-10 mt-2">
                                    <a href="/partycakes">DETAILS</a>
                                </div>

                                <div v-for="cat in category" :key="cat.name">
                                    <div class="flex-none p-2 w-48">
                                        <div 
                                            @click="selectCategory(cat.name)"
                                            :class="{'bg-blue-500 text-white': selectedCategory === cat.name, 'bg-gray-200': selectedCategory !== cat.name}"
                                            class="flex items-center justify-center rounded-lg p-2 font-bold italic cursor-pointer transition-colors duration-300 h-full "
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

                <div class="absolute top-[20vh] bottom-0 left-0 w-9/12 ml-[-50px] md:w-[72%] lg:w-[72%] lg:top-35 md:mt-10 lg:mt-0">
                  <div class="relative w-full h-full">

                      <div class="left-0 top-5 overflow-y-auto h-[70vh] w-full ml-10 justify-center gap-x-5 gap-y-4 p-2 lg:gap-x-20 lg:ml-20">
                          <div class="flex flex-wrap">
                              <div 
                                  v-for="item in filteredItems" 
                                  :key="item.itemid" 
                                  class="relative flex flex-col justify-center w-full sm:w-1/4 lg:w-1/6 min-h-20 bg-white shadow-md shadow-gray-200 py-4 px-2 rounded-lg cursor-pointer m-2 hover:shadow-lg transition-shadow duration-300"
                              >
                                  <span class="font-bold text-base text-nowrap overflow-hidden">
                                      <figure @click="addToCart(item.itemid)"><img src="../../../../public/images/Product.webp" alt="Product" /></figure>
                                  </span>
                                  <span class="text-xs text-gray-400 font-thin">{{ item.barcode }}</span>
                                  <span class="text-md text-gray-600 font-bold">{{ item.itemname }}</span>
                                  <span class="text-end text-sm text-gray-600 font-bold">Php {{ item.price }}.00</span>
                                  <span class="absolute top-0 right-0 h-9 w-9 bg-blue-400 rounded-full flex items-center justify-center">
                                      <div class="text-sm text-white">{{ item.quantity }}</div>
                                  </span>
                              </div>
                          </div>
                      </div>

                  </div>
              </div>


                <div class="absolute top-0 bottom-0 right-0 w-[42%] lg:w-2/6">

                    <!-- Cart Content -->
                    <div class="relative bg-white border-s border-gray-400 shadow-md shadow-gray-600 w-full h-full lg:w-full lg:h-full max-w-sm lg:max-w-full">
                        
                        <div class="absolute left-0 right-0 top-0 h-10 bg-white text-black flex justify-center items-center text-xl font-bold p-3">
                            <p class="tracking-widest">ORDER | CART</p>
                            <div class="ml-auto flex items-center">
                                <button @click="deleteSelectedItems" class="mr-2 px-2 py-1 bg-white text-white rounded text-xs" :disabled="selectedItems.length === 0">
                                    <ClearCart class="w-7 h-7"></ClearCart>
                                </button>
                            </div>
                        </div>

                        <div class="absolute top-7 bottom-12 left-0 right-0 my-2 px-2 overflow-y-auto">
                            <div 
                                class="absolute inset-4 bg-cover bg-center bg-no-repeat duration-300"
                                style="background-image: url('/images/ec2.webp'); z-index: 0;"
                            >
                            </div>

                            <section class="h-5/6  relative z-10">
                              <div class="h-full w-full overflow-y-auto bg-transparent p-3 top-0 right-0 left-0 m-0">
                                  <!-- Sticky Header -->
                                  <ul class="grid grid-cols-4 text-md font-bold lg:grid-cols-4 bg-violet-900 text-white rounded sticky top-0 z-20">
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
                                      <li class="p-2 flex justify-center">Item</li>
                                      <li class="p-2 flex justify-center">Price</li>
                                      <li class="p-2 flex justify-center">Qty</li>
                                  </ul>
                                  <ul v-for="item in cartItems" :key="item.itemid" class="grid grid-cols-4 text-sm lg:grid-cols-4 items-center mb-4 border-b pb-2 bg-white-200">
                                      <li class="p-2 flex justify-center">
                                          <label class="inline-flex items-center">
                                              <input type="checkbox" 
                                                  :value="item.itemname" 
                                                  v-model="selectedItems" 
                                                  class="form-checkbox h-4 w-4 text-blue-600 rounded-full"
                                              />
                                          </label>
                                      </li>
                                      <li class="p-2 overflow-hidden flex flex-col">
                                          <span class="whitespace-no-wrap">{{ item.itemname }}</span>
                                          <span v-if="item.discamount > 0" class="text-xs text-green-600 mt-1">
                                              Discount: -{{ formattedPrice(item.discamount) }}
                                          </span>
                                      </li>
                                      <li class="p-2 flex flex-col items-center">
                                          <span class="font-bold text-xs">{{ formattedPrice(item.total_price) }}</span>
                                          <span v-if="item.discamount > 0" class="text-xs text-green-600 mt-1">
                                              {{ item.discofferid }}
                                          </span>
                                      </li>
                                      <li class="p-2 flex justify-center">
                                          <span class="font-bold text-xs mx-2">{{ item.total_qty }} PCS</span>
                                      </li>
                                  </ul>
                              </div>
                          </section>

                          <section class="p-2 bg-gray-100 text-gray-600 relative z-10">
                              <div class="flex text-sm font-bold justify-between items-center px-1">
                                  <p>GROSS</p>
                                  <p>{{ formattedPrice(calculateGrossTotal()) }}</p>
                              </div>
                              <div class="flex text-sm font-bold justify-between items-center px-1">
                                  <p>DISCOUNT</p>
                                  <p>{{ formattedPrice(calculateTotalDiscount()) }}</p>
                              </div>
                              <div class="flex text-sm font-bold justify-between items-center px-1">
                                  <p>VAT (12%)</p>
                                  <p>{{ formattedPrice(computeVAT) }}</p>
                              </div>
                              <div class="flex text-sm font-bold justify-between items-center px-1">
                                  <p>PARTIAL PAYMENT</p>
                                  <p>{{ formattedPrice(calculateTotalPartial()) }}</p>
                              </div>
                              <div class="flex text-sm font-bold justify-between items-center px-1">
                                  <p class="pr-5">NOTE: </p>
                                  <input 
                                      v-model="note"
                                      id="note" 
                                      name="note" 
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                  >
                              </div>
                              <p class="border-b border-b-gray-300 pt-3"></p>
                          </section>

                        </div>
        
                        <div class="absolute left-0 right-0 bottom-0 h-14 text-white bg-navy flex justify-between items-center border-t border-gray-900 shadow-sm shadow-gray-900">
                            <div class="flex flex-col justify-start ps-1 tracking-wider w-[100%]">
                              <!-- <p class="font-thin text-xs text-white ml-2">NET TOTAL</p> -->
                              <p class="font-extrabold text-xl md:text-lg ml-2"><b>PHP {{ formattedPrice(calculateNetsTotal()) }}</b></p>
                            </div>
                            <div class="skeleton ml-48 w-1/6 h-10 bg-blue-900 flex justify-center items-center cursor-pointer font-bold text-white text-xs md:text-sm lg:text-base">
                            <button class="" @click="openModal(selectedItem)">
                                <Emoji class="w-10 p-2 lg:p-1"></Emoji>
                            </button>
                            <ModalDaisy ref="modalRef" />
                            </div>
                            <div class="skeleton w-1/4 h-10 bg-violet-600 m-2 flex justify-center items-center cursor-pointer font-bold text-white tracking-wider text-xs md:text-sm lg:text-base" @click="openCashModal">
                                <Cart class="w-10 p-2 lg:p-1"></Cart>
                            </div>

                            <!-- <CashInputModal 
                            :is-open="showCashModal" 
                            :total-amount="calculateNetTotal()"
                            :total-gross="calculateGrossTotal()"
                            :total-discount="calculateTotalDiscount()"
                            :total-net="calculateNetsTotal()"
                            :total-partial="calculateTotalPartial()"
                            :selected-ar="props.selectedAR"
                            :selected-customer="selectedCustomer"
                            @close="showCashModal = false"
                            @submit="handleCashSubmit"
                            @keyup.enter="handleEnter" 
                            /> -->

                            <CashInputModal 
                              :is-open="showCashModal" 
                              :total-amount="calculateNetTotal()"
                              :total-partial="calculateTotalPartial()"
                              :selected-ar="selectedAR"
                              :selected-customer="selectedCustomer"
                              @close="showCashModal = false"
                              @submit="handleCashSubmit"
                            />
                        </div>
                    </div>
                </div>
            </div>

        </template>
    </Main>
</template>

<style scoped>
.vue-slide {
  transition: transform 0.3s ease;
}
</style>