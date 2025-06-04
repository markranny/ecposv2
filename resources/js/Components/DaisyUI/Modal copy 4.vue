<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';

const modalRef = ref(null);
const selectedItemsForModal = ref([]);
const discountData = ref([]);
const selectedDiscount = ref(null);
const cartItems = ref([]);
const selectedItems = ref([]); 
const isLoading = ref(false);
const showPartialPayment = ref(false);
const showRemovePartialPayment = ref(false);
const partialPaymentAmount = ref('');
const removePartialPaymentAmount = ref('');
const searchDiscount = ref('');
const searchTransaction = ref('');
/* const selectedItemsForReturn = ref({}); */
const selectedItemsForReturn = ref(new Map());

const showDailyJournal = ref(false);
const transactionTables = ref([]);
const selectedTransaction = ref(null);
const transactionSalesTrans = ref([]);
const isLoadingTransactions = ref(false);
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const receiptData = ref(null);
const isPrinting = ref(false);

// Add computed properties for filtered results
const filteredDiscounts = computed(() => {
  if (!searchDiscount.value) return discountData.value;
  const searchTerm = searchDiscount.value.toLowerCase();
  return discountData.value.filter(discount => 
    discount.DISCOFFERNAME.toLowerCase().includes(searchTerm) ||
    discount.DISCOUNTTYPE.toLowerCase().includes(searchTerm)
  );
});

const filteredTransactions = computed(() => {
  if (!searchTransaction.value) return transactionTables.value;
  const searchTerm = searchTransaction.value.toLowerCase();
  return transactionTables.value.filter(transaction => 
    transaction.receiptid.toLowerCase().includes(searchTerm) ||
    transaction.grossamount.toString().includes(searchTerm) ||
    formatDate(transaction.createddate).toLowerCase().includes(searchTerm)
  );
});

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
    selectedDiscount.value = null;
    showPartialPayment.value = false;
    showRemovePartialPayment.value = false;
  } catch (error) {
    console.error('Error fetching discount data:', error);
  }
};


const handlePartialPaymentClick = () => {
  showPartialPayment.value = true;
  showRemovePartialPayment.value = false;
  discountData.value = [];
  selectedDiscount.value = null;
};

const handleRemovePartialPaymentClick = () => {
  showRemovePartialPayment.value = true;
  showPartialPayment.value = false;
  discountData.value = [];
  selectedDiscount.value = null;
  
  // Initialize removePartialPayment with the current partial_payment
  selectedItems.value.forEach(item => {
    item.removePartialPayment = item.partial_payment || 0;
  });
};

// Add a watch effect to update partial_payment when removePartialPayment changes
selectedItems.value.forEach(item => {
  watch(() => item.removePartialPayment, (newValue) => {
    item.partial_payment = parseFloat(item.partial_payment) - parseFloat(newValue);
    if (item.partial_payment < 0) item.partial_payment = 0;
  });
});

const totalPartialPayment = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + (parseFloat(item.partialPayment) || 0), 0);
});


const isValidPartialPayment = computed(() => {
  return selectedItems.value.every(item => {
    const partialPayment = parseFloat(item.partialPayment) || 0;
    return partialPayment >= 0 && partialPayment <= parseFloat(item.total_price - item.discamount) ||
    partialPayment == parseFloat(item.total_price - item.discamount)
    ;
  }) && totalPartialPayment.value > 0;
});

const totalExistingPartialPayment = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + (parseFloat(item.partial_payment) || 0), 0);
});

const submitPartialPayments = async () => {
  if (selectedItems.length !== cartItems.length) {
      alert("Partial payment can only be executed when all items in the cart are selected.");
      return;
    }

    if (!isValidPartialPayment.value) {
      console.log('Invalid partial payment amounts detected');
      alert('Invalid partial payment amounts. Please check your inputs.');
      return;
    }

  try {
    isLoading.value = true;
    console.log('Preparing partial payments submission');
    
    const partialPayments = selectedItems.value.map(item => ({
      itemid: item.itemid,
      amount: parseFloat(item.partialPayment) || 0
    }));
    
    console.log('Partial payments to be submitted:', partialPayments);

    const response = await axios.post('/api/submit-partial-payments', { partialPayments });
    console.log('Server response:', response.data);

    if (response.data.success) {
      console.log('Partial payments submitted successfully');
      /* alert(`Partial payments submitted and receipt printed successfully! Total partial payment: P ${response.data.totalPartialPayment.toFixed(2)}`); */
      
      // Update cart items with partial payment information
      selectedItems.value.forEach(item => {
        const updatedItem = response.data.updatedItems.find(i => i.itemid === item.itemid);
        if (updatedItem) {
          item.partialPayment = updatedItem.partialPayment;
          item.remainingBalance = item.total_price - updatedItem.partialPayment;
        }
        console.log(`Updated item ${item.itemid}: Partial Payment = ${item.partialPayment}, Remaining Balance = ${item.remainingBalance}`);
      });
      
      showPartialPayment.value = false;
      // Optionally, you can update the cart data here instead of reloading the page
      // updateCartData();
      location.reload();
    } else {
      throw new Error('Failed to submit partial payments');
    }
  } catch (error) {
    console.error('Error submitting partial payments:', error);
    if (error.response) {
      console.error('Server error response:', error.response.data);
    }
    alert('Failed to submit partial payments. Please try again.');
  } finally {
    isLoading.value = false;
    console.log('Partial payment submission process completed');
  }
};

const totalRemovePartialPayment = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + (parseFloat(item.removePartialPayment) || 0), 0);
});

const isValidRemovePartialPayment = computed(() => {
  return selectedItems.value.every(item => {
    const removeAmount = parseFloat(item.removePartialPayment) || 0;
    const currentPartialPayment = parseFloat(item.partial_payment) || 0;
    return removeAmount >= 0 && removeAmount <= currentPartialPayment;
  }) && totalRemovePartialPayment.value > 0;
});

const submitRemovePartialPayments = async () => {
  if (!isValidRemovePartialPayment.value) {
    console.log('Invalid remove partial payment amounts detected');
    alert('Invalid remove partial payment amounts. Please check your inputs.');
    return;
  }

  try {
    isLoading.value = true;
    console.log('Preparing remove partial payments submission');
    
    const removePartialPayments = selectedItems.value.map(item => ({
      itemid: item.itemid,
      amount: parseFloat(item.removePartialPayment) || 0
    }));
    
    console.log('Remove Partial payments to be submitted:', removePartialPayments);

    const response = await axios.post('/api/submit-remove-partial-payments', { removePartialPayments });
    console.log('Server response:', response.data);

    if (response.data.success) {
      console.log('Partial payments removed successfully');
      /* alert(`Partial payments removed and receipt printed successfully! Total removed amount: P ${response.data.totalRemovedAmount.toFixed(2)}`); */
      
      selectedItems.value.forEach(item => {
        const updatedItem = response.data.updatedItems.find(i => i.itemid === item.itemid);
        if (updatedItem) {
          item.partial_payment = updatedItem.partialPayment;
          item.remainingBalance = updatedItem.remainingBalance;
        }
        console.log(`Updated item ${item.itemid}: Partial Payment = ${item.partial_payment}, Remaining Balance = ${item.remainingBalance}`);
      });
      
      showRemovePartialPayment.value = false;
      location.reload();
    } else {
      throw new Error('Failed to remove partial payments');
    }
  } catch (error) {
    console.error('Error removing partial payments:', error);
    if (error.response) {
      console.error('Server error response:', error.response.data);
    }
    alert('Failed to remove partial payments. Please try again.');
  } finally {
    isLoading.value = false;
    console.log('Remove Partial payment process completed');
  }
};


const selectDiscount = async (discount) => {
  selectedDiscount.value = discount;
  
  try {
    isLoading.value = true; 
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

    await new Promise(resolve => setTimeout(resolve, 1000));

    location.reload();
  } catch (error) {
    console.error('Error applying discount:', error);
    if (error.response) {
      console.error('Server responded with:', error.response.data);
    }
    alert('Failed to apply discount. Please check the console for more details.');
  } finally {
    isLoading.value = false; 
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

// X-READ related refs
const xReadData = ref(null);
const isGeneratingXRead = ref(false);
const error = ref(null);
const showXRead = ref(false);

const handleXReadClick = async () => {
  try {
    isGeneratingXRead.value = true;
    error.value = null;
    showXRead.value = true;
    showPartialPayment.value = false;
    showRemovePartialPayment.value = false;
    discountData.value = [];

    // Generate X-READ report
    const response = await axios.get('/generate-xread');
    xReadData.value = response.data;
    
    // Print X-READ report
    const printResponse = await axios.post('/print-xread', {
      template: 'default',
      reportData: response.data
    });

    if (printResponse.data.success) {
      console.log('X-READ report generated and printed successfully!');
    } else {
      throw new Error('Failed to print X-READ report');
    }

  } catch (error) {
    console.error('Error generating/printing X-READ:', error);
    error.value = 'Error generating/printing X-READ report. Please try again.';
  } finally {
    isGeneratingXRead.value = false;
  }
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(value);
};


const calculateTransactionTotals = computed(() => {
  if (!selectedTransaction.value) return null;
  
  const totals = {
    gross: selectedTransaction.value.grossamount || 0,
    discount: selectedTransaction.value.totaldiscamount || 0,
    vat: selectedTransaction.value.taxinclinprice || 0,
    partialPayment: selectedTransaction.value.partialpayment || 0,
    netAmount: selectedTransaction.value.netamount || 0,
    paymentDetails: {
      cash: selectedTransaction.value.cash || 0,
      gcash: selectedTransaction.value.gcash || 0,
      paymaya: selectedTransaction.value.paymaya || 0,
      card: selectedTransaction.value.card || 0,
      loyaltyCard: selectedTransaction.value.loyaltycard || 0
    }
  };
  
  return totals;
});

// Add this method to handle other button clicks
const handleOtherButtonClick = () => {
  showDailyJournal.value = false;
  clearSelectedTransaction();
};

// Modified handleDailyJournalClick
const handleDailyJournalClick = async () => {
  try {
    isLoadingTransactions.value = true;
    showDailyJournal.value = true;
    showPartialPayment.value = false;
    showRemovePartialPayment.value = false;
    showXRead.value = false;
    discountData.value = [];

    const response = await axios.get(`/daily-journal?date=${selectedDate.value}`);
    transactionTables.value = response.data;
  } catch (error) {
    console.error('Error fetching daily journal data:', error);
    alert('Failed to fetch daily journal data. Please try again.');
  } finally {
    isLoadingTransactions.value = false;
  }
};

const selectTransaction = async (transaction) => {
  try {
    isLoadingTransactions.value = true;
    selectedTransaction.value = transaction;
    const response = await axios.get(`/transaction-sales/${transaction.transactionid}`);
    transactionSalesTrans.value = response.data;
  } catch (error) {
    console.error('Error fetching transaction sales data:', error);
    alert('Failed to fetch transaction sales data. Please try again.');
  } finally {
    isLoadingTransactions.value = false;
  }
};

const clearSelectedTransaction = () => {
  selectedTransaction.value = null;
  transactionSalesTrans.value = [];
};

const handleReprintReceipt = async () => {
  try {
    isPrinting.value = true;
    const response = await axios.post('/reprint-receipt', { transactionId: selectedTransaction.value.transactionid });
    if (response.data.success) {
      console.log('Receipt reprinted successfully!');
      /* alert('Receipt has been reprinted successfully.'); */
    } else {
      throw new Error(response.data.message);
    }
  } catch (error) {
    console.error('Error reprinting receipt:', error);
    alert('Failed to reprint receipt. Please try again.');
  } finally {
    isPrinting.value = false;
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};


const fetchReceiptData = async () => {
  try {
    const response = await axios.get(`/api/print-receipt/${props.transactionId}`);
    receiptData.value = response.data;
  } catch (err) {
    console.error('Error fetching receipt data:', err);
    error.value = 'Failed to fetch receipt data. Please try again.';
  }
};

const handleItemSelect = (linenum) => {
  // Use linenum instead of itemid as the key since it's unique per transaction
  if (selectedItemsForReturn.value.has(linenum)) {
    selectedItemsForReturn.value.delete(linenum);
  } else {
    selectedItemsForReturn.value.set(linenum, true);
  }
};

const handleReturnTransaction = async () => {
  try {
    const itemsToReturn = transactionSalesTrans.value.filter(item => 
      selectedItemsForReturn.value.has(item.linenum)
    );
    
    if (itemsToReturn.length === 0) {
      alert('Please select at least one item to return');
      return;
    }

    const returnItems = itemsToReturn.map(item => ({
      linenum: item.linenum,
      itemid: item.itemid,
      returnQty: item.qty, // Default to returning all quantities
      price: item.price,
      description: item.description || item.itemname
    }));

    const totalReturnAmount = returnItems.reduce((total, item) => 
      total + (item.returnQty * item.price), 0
    );

    const confirmed = await new Promise(resolve => {
      const message = `Are you sure you want to return ${returnItems.length} item(s) for a total of ${formatCurrency(totalReturnAmount)}? This action cannot be undone.`;
      resolve(confirm(message));
    });

    if (!confirmed) return;

    isLoading.value = true;

    const response = await axios.post('/return-transaction', {
      transactionId: selectedTransaction.value.transactionid,
      returnItems: returnItems,
      totalReturnAmount: totalReturnAmount
    });

    if (response.data.success) {
      alert(`Transaction returned successfully! Return Receipt ID: ${response.data.returnReceiptId}`);

      // Clear and refresh
      selectedTransaction.value = null;
      transactionSalesTrans.value = [];
      selectedItemsForReturn.value.clear(); // Clear the Map
      
      // Emit an event to refresh the daily journal
      emit('refreshDailyJournal');

      if (response.data.returnReceiptId) {
        try {
          await handlePrintReturnReceipt(response.data.returnReceiptId);
        } catch (printError) {
          console.error('Print error:', printError);
          alert('Return was successful but printing the receipt failed. You can reprint it later.');
        }
      }

      // Refresh the transaction list
      handleDailyJournalClick();
    } else {
      throw new Error(response.data.message || 'Failed to process return');
    }
  } catch (error) {
    const errorMessage = error.response?.data?.message || 
      error.message || 
      'Failed to return transaction. Please try again.';
    
    alert(`Return Failed: ${errorMessage}`);
    console.error('Return transaction error:', error);
  } finally {
    isLoading.value = false;
  }
};

const handlePrintReturnReceipt = async (returnReceiptId) => {
  try {
    const printResponse = await axios.post('/print-return-receipt', { 
      returnReceiptId,
      userId: currentUser.value.id // Assuming you have a currentUser object
    });
    
    if (!printResponse.data.success) {
      throw new Error(printResponse.data.message || 'Failed to print return receipt');
    }
    
    console.log('Return receipt printed successfully');
  } catch (error) {
    console.error('Print receipt error:', error);
    alert('Failed to print return receipt. Please try printing manually.');
  }
};

</script>

<template>
  <div>
    <dialog id="my_modal_1" ref="modalRef" class="modal w-full h-full p-0 max-w-none max-h-none">
      <div class="w-full h-full flex flex-col bg-white">
        <div v-if="isLoading" class="absolute inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-white"></div>
            <p class="mt-4 text-white text-xl font-semibold">Processing...</p>
          </div>
        </div>
        <div class="p-4 border-b">
          <h2 class="text-2xl font-bold text-black">EC-POS</h2>
        </div>
        
        <div class="flex-grow flex">
          <div class="w-1/3 p-6 border-r">
            <h3 class="font-bold text-lg mb-4 text-black">MENU</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6 p-6 h-[70vh] overflow-y-auto">
              <div v-for="(item, index) in ['DISCOUNT', 'PARTIAL PAYMENT', 'REMOVE PARTIAL PAYMENT', 'DAILY JOURNAL', 'TENDER DECLARATION', 'PULLOUT CASHFUND', 'X-READ', 'Z-READ']" :key="index" 
                    class="bg-navy text-white rounded-lg shadow-lg overflow-hidden flex items-center justify-center h-full cursor-pointer"
                  :class="{ 'opacity-50 cursor-not-allowed': item === 'PARTIAL PAYMENT' && totalExistingPartialPayment >= 1 }"
                  @click="
                  item === 'DISCOUNT' ? (handleDiscountClick(), handleOtherButtonClick()) : 
                  item === 'PARTIAL PAYMENT' && totalExistingPartialPayment < 1 ? (handlePartialPaymentClick(), handleOtherButtonClick()) : 
                  item === 'REMOVE PARTIAL PAYMENT' ? (handleRemovePartialPaymentClick(), handleOtherButtonClick()) :
                  item === 'X-READ' ? (handleXReadClick(), handleOtherButtonClick()) :
                  item === 'DAILY JOURNAL' ? handleDailyJournalClick() : handleOtherButtonClick()
                  ">
                <div class="p-6 text-center flex-grow">
                  <h2 class="text-xs sm:text-sm md:text-base lg:text-lg xl:text-xl font-semibold text-white">{{ item }}</h2>
                </div>
              </div>
            </div>
          </div>

          <div class="w-2/5 p-6">
          <h3 class="font-bold text-lg mb-4 text-black">INPUT</h3>
          <div v-if="showPartialPayment" class="max-h-[70vh] overflow-y-auto">
            <h4 class="font-semibold text-black mb-4">Enter Partial Payments</h4>
            <form @submit.prevent="submitPartialPayments" class="space-y-4">
              <div v-for="item in selectedItems" :key="item.itemid">
                <label :for="'partialPayment_' + item.itemid" class="block mb-2 text-sm font-medium text-gray-900">
                  {{ item.itemname }} (Total: {{ item.total_price - item.discamount }})
                </label>
                <input 
                  type="number" 
                  :id="'partialPayment_' + item.itemid" 
                  v-model="item.partialPayment" 
                  class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                  :placeholder="'Enter amount for ' + item.itemname" 
                  required 
                  min="0" 
                  :max="item.total_price"
                  step="0.01"
                >
              </div>
              <div class="font-bold text-lg">
                Total Partial Payment: {{ totalPartialPayment }}
              </div>
              <button 
                type="submit" 
                class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center"
                :disabled="!isValidPartialPayment"
              >
                Submit Partial Payments
              </button>
            </form>
          </div>

          <div v-if="showRemovePartialPayment" class="max-h-[70vh] overflow-y-auto">
              <h4 class="font-semibold text-black mb-4">Remove Partial Payments</h4>
              <form @submit.prevent="submitRemovePartialPayments" class="space-y-4">
                <div v-for="item in selectedItems" :key="item.itemid">
                  <label :for="'removePartialPayment_' + item.itemid" class="block mb-2 text-sm font-medium text-gray-900">
                    {{ item.itemname }} (Total: {{ item.total_price }}, Current Partial Payment: {{ safeToFixed(item.partial_payment) }})
                  </label>
                  <input 
                    type="number" 
                    :id="'removePartialPayment_' + item.itemid" 
                    v-model="item.removePartialPayment" 
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                    :placeholder="'Enter amount to remove for ' + item.itemname" 
                    required 
                    min="0" 
                    :max="item.partial_payment || 0"
                    step="0.01"
                    disabled
                  >
                  <!-- <p class="mt-2 text-sm text-gray-600">
                    Remaining Partial Payment: {{ safeToFixed(item.partial_payment - (item.removePartialPayment || 0)) }}
                  </p> -->
                </div>
                <div class="font-bold text-lg">
                  Total Amount to Remove: {{ safeToFixed(totalRemovePartialPayment) }}
                </div>
                <button 
                  type="submit" 
                  class="text-white bg-red-700 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center"
                  :disabled="!isValidRemovePartialPayment"
                >
                  Remove Partial Payments
                </button>
              </form>
            </div>

            <div v-if="discountData.length > 0" class="space-y-4">
                <div class="relative">
                  <input
                    type="text"
                    v-model="searchDiscount"
                    placeholder="Search discounts..."
                    class="w-full p-2 text-black border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                  <span class="absolute right-3 top-2.5 text-gray-400">
                    🔍
                  </span>
                </div>
                
                <div class="max-h-[70vh] overflow-y-auto">
                  <div v-for="discount in filteredDiscounts" 
                      :key="discount.id" 
                      class="mb-4 p-4 border rounded cursor-pointer hover:bg-gray-100"
                      @click="selectDiscount(discount)"
                      :class="{ 'bg-blue-100': selectedDiscount && selectedDiscount.id === discount.id }">
                    <h4 class="font-semibold text-black">{{ discount.DISCOFFERNAME }}</h4>
                    <p class="text-black"><strong>Parameter:</strong> {{ discount.PARAMETER }}</p>
                    <p class="text-black"><strong>Discount Type:</strong> {{ discount.DISCOUNTTYPE }}</p>
                  </div>
                </div>
              </div>

            <div v-else-if="discountData.length > 0" class="max-h-[70vh] overflow-y-auto">
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

            
            
            <div v-if="showDailyJournal" class="space-y-4">
            <div class="relative">
              <input
                type="text"
                v-model="searchTransaction"
                placeholder="Search transactions..."
                class="text-black w-full p-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <span class="absolute right-3 top-2.5 text-gray-400">
                🔍
              </span>
            </div>
            
            <div class="max-h-[70vh] overflow-y-auto">
              <div v-for="transaction in filteredTransactions" 
                   :key="transaction.transactionid" 
                   class="mb-4 p-4 border rounded cursor-pointer hover:bg-gray-100 border-rose-600 bg-white-100"
                   @click="selectTransaction(transaction)">
                <h4 class="font-semibold text-black">Receipt ID: {{ transaction.receiptid }}</h4>
                <p class="text-black">Total Gross: {{ formatCurrency(transaction.grossamount) }}</p>
                <p class="text-black">Date: {{ formatDate(transaction.createddate) }}</p>
              </div>
            </div>
          </div>
            
          </div>

          <div class="w-1/2 p-6">
            <h3 class="font-bold text-lg mb-4 text-black">Selected Items</h3>
            <div v-if="selectedItems.length > 0 && !selectedTransaction" class="max-h-[70vh] overflow-y-auto">
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

            <!-- <div v-if="selectedTransaction" class="mt-4 p-4 border rounded bg-green-100">
              <h4 class="font-semibold text-black">Selected Transaction Details:</h4>
              <div v-for="item in transactionSalesTrans" :key="`${item.transactionid}-${item.linenum}`" 
                  class="mb-4 p-4 border rounded">
                <h5 class="font-semibold text-black">{{ item.description || 'Unnamed Item' }}</h5>
                <p class="text-black"><strong>Item ID:</strong> {{ item.itemid }}</p>
                <p class="text-black"><strong>Price:</strong> {{ formatCurrency(item.price) }}</p>
                <p class="text-black"><strong>Quantity:</strong> {{ item.qty }}</p>
                <p class="text-black"><strong>Net Amount:</strong> {{ formatCurrency(item.netamount) }}</p>
              </div>
            </div> -->
    
            <div v-if="selectedDiscount" class="mt-4 p-4 border rounded bg-green-100">
              <h4 class="font-semibold text-black">Selected Discount:</h4>
              <p class="text-black">{{ selectedDiscount.DISCOFFERNAME }}</p>
              <p class="text-black"><strong>ID:</strong> {{ selectedDiscount.id }}</p>
              <p class="text-black"><strong>Parameter:</strong> {{ selectedDiscount.PARAMETER }}</p>
              <p class="text-black"><strong>Discount Type:</strong> {{ selectedDiscount.DISCOUNTTYPE }}</p>
            </div>


            <div v-if="selectedTransaction" class="bg-white rounded-lg shadow-md p-6 w-full max-w-md">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">ORDER | CART</h2>
                <!-- <i class="fas fa-shopping-cart h-6 w-6 text-blue-600"></i> -->
                <p class="text-black">{{ selectedTransaction.receiptid }}</p>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full">
                  <thead>
                    <tr class="border-b">
                      <th class="py-2 px-4 text-left text-sm font-medium text-gray-900">Select</th>
                      <th class="py-2 px-4 text-left text-sm font-medium text-gray-900">Item</th>
                      <th class="py-2 px-4 text-right text-sm font-medium text-gray-900">Price</th>
                      <th class="py-2 px-4 text-center text-sm font-medium text-gray-900">Qty</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in transactionSalesTrans" :key="`${item.transactionid}-${item.linenum}`" class="border-b">
                      <td class="py-2 px-4 text-sm text-gray-900">
                        <input type="checkbox" 
                              :checked="selectedItemsForReturn.has(item.linenum)"
                              @change="handleItemSelect(item.linenum)">
                      </td>
                      <td class="py-2 px-4 text-sm text-gray-900">{{ item.itemname }}</td>
                      <td class="py-2 px-4 text-right text-sm text-gray-900">{{ formatCurrency(item.price) }}</td>
                      <td class="py-2 px-4 text-center text-sm text-gray-900">{{ item.qty }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="mt-6 space-y-3 border-t pt-4">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Gross Amount</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.grossamount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Discount</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.totaldiscamount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">VAT (12%)</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.taxinclinprice) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Partial Payment</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.partialpayment || 0) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold pt-2 border-t">
                  <span>Net Amount</span>
                  <span class="text-gray-900">{{ formatCurrency(selectedTransaction.netamount) }}</span>
                </div>

                <!-- Payment Details Section -->
                <div class="mt-4 pt-2 border-t">
                  <h3 class="text-sm font-semibold text-gray-900 mb-2">Payment Details</h3>
                  <div class="space-y-2">
                    <div v-if="selectedTransaction.cash" class="flex justify-between text-sm">
                      <span class="text-gray-600">Cash</span>
                      <span class="text-gray-900">{{ formatCurrency(selectedTransaction.cash) }}</span>
                    </div>
                    <div v-if="selectedTransaction.gcash" class="flex justify-between text-sm">
                      <span class="text-gray-600">GCash</span>
                      <span class="text-gray-900">{{ formatCurrency(selectedTransaction.gcash) }}</span>
                    </div>
                    <div v-if="selectedTransaction.paymaya" class="flex justify-between text-sm">
                      <span class="text-gray-600">PayMaya</span>
                      <span class="text-gray-900">{{ formatCurrency(selectedTransaction.paymaya) }}</span>
                    </div>
                    <div v-if="selectedTransaction.card" class="flex justify-between text-sm">
                      <span class="text-gray-600">Card</span>
                      <span class="text-gray-900">{{ formatCurrency(selectedTransaction.card) }}</span>
                    </div>
                    <div v-if="selectedTransaction.loyaltycard" class="flex justify-between text-sm">
                      <span class="text-gray-600">Loyalty Card</span>
                      <span class="text-gray-900">{{ formatCurrency(selectedTransaction.loyaltycard) }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-6 grid grid-cols-2 gap-4">
                <button
                  @click="handleReprintReceipt"
                  class="flex items-center justify-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                >
                  <i class="fas fa-print h-4 w-4"></i>
                  <span class="text-sm font-medium">Reprint Receipt</span>
                </button>
                <button
                  @click="handleReturnTransaction"
                  class="flex items-center justify-center space-x-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                >
                  <i class="fas fa-undo h-4 w-4"></i>
                  <span class="text-sm font-medium">Return Transaction</span>
                </button>
              </div>
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