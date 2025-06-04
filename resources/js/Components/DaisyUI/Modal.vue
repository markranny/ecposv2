<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

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
/* const selectedItemsForReturn = ref(new Map()); */
const selectedItemsForReturn = ref([]);
const showDailyJournal = ref(false);
const transactionTables = ref([]);
const selectedTransaction = ref(null);
const transactionSalesTrans = ref([]);
const isLoadingTransactions = ref(false);
const selectedDate = ref(new Date().toISOString().split('T')[0]);

const billDenominations = [1000, 500, 200, 100, 50, 20];
const coinDenominations = [10, 5, 1, 0.25, 0.10, 0.05];
const ARDenominations = ['GCASH', 'FOODPANDA', 'GRABFOOD', 'CHARGE', 'LOYALTYCARD', 'PAYMAYA', 'CARD'];

const showTenderDeclaration = ref(false);
const tenderDeclaration = ref({ bills: {}, coins: {}, ar: {} }); 
const totalBills = ref(0);
const totalCoins = ref(0);
const totalAR = ref(0);
const grandTotal = ref(0);
const isSubmitting = ref(false);

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
    showTenderDeclaration.value = false;
  } catch (error) {
    console.error('Error fetching discount data:', error);
  }
};

const calculateDiscountPercentage = (item) => {
  if (!item.price || !item.discamount) return 0;
  return ((item.discamount / (item.price * item.qty)) * 100).toFixed(1);
};

const calculateItemTotal = (item) => {
  return (item.price * item.qty) - (item.discamount || 0);
};



const handlePartialPaymentClick = () => {
  showPartialPayment.value = true;
  showRemovePartialPayment.value = false;
  discountData.value = [];
  selectedDiscount.value = null;
};

const handleRemovePartialPaymentClick = () => {
  showRemovePartialPayment.value = true;
  showTenderDeclaration.value = false;
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

const zReadData = ref(null);
const isGeneratingZRead = ref(false);
const showZRead = ref(false);

const handleZReadClick = async () => {
  // Get today's date in YYYYMMDD format
  const today = new Date();
  const formattedToday = today.toISOString().slice(0, 10).replace(/-/g, '');

  closeModal();

  // Prompt for confirmation using SweetAlert
  const { value: inputDate } = await Swal.fire({
    title: "Enter Today's Date",
    input: 'text',
    inputLabel: 'Please enter today\'s date in YYYYMMDD format (e.g., 20241026):',
    inputPlaceholder: 'YYYYMMDD',
    showCancelButton: true,
    confirmButtonText: 'Submit',
    cancelButtonText: 'Cancel',
    inputValidator: (value) => {
      const dateRegex = /^\d{8}$/; // Matches YYYYMMDD
      if (!value) {
        return 'You need to enter a date!';
      } else if (!dateRegex.test(value)) {
        return 'Invalid date format. Please enter the date in YYYYMMDD format.';
      } else if (value !== formattedToday) {
        return "The date entered does not match today's date. Please try again.";
      }
    }
  });

  // Check if the user cancelled the input
  if (!inputDate) {
    return;
  }

  try {
    isGeneratingZRead.value = true;
    error.value = null;
    showZRead.value = true;
    

    // Generate Z-READ report
    const response = await axios.get('/generate-zread');
    zReadData.value = response.data;

    // Print Z-READ report
    const printResponse = await axios.post('/print-zread', {
      template: 'default',
      reportData: response.data
    });
    

    if (printResponse.data.success) {
      Swal.fire('Success!', 'Z-READ report generated and printed successfully!', 'success');
    } else {
      throw new Error('Failed to print Z-READ report');
    }

  } catch (error) {
    console.error('Error generating/printing Z-READ:', error);
    Swal.fire('Error!', 'Error generating/printing Z-READ report. Please try again.', 'error');
    error.value = 'Error generating/printing Z-READ report. Please try again.';
  } finally {
    isGeneratingZRead.value = false;
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
    showTenderDeclaration.value = false;
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
      window.print();
      console.log('Receipt reprinted successfully!');
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
  const index = selectedItemsForReturn.value.indexOf(linenum);
  if (index > -1) {
    selectedItemsForReturn.value.splice(index, 1);
  } else {
    selectedItemsForReturn.value.push(linenum);
  }
};

// Update the handleReturnTransaction function
const handleReturnTransaction = async () => {
  try {
    if (selectedItemsForReturn.value.length === 0 || !transactionSalesTrans.value) {
      throw new Error('Selected items or transaction data is not available');
    }

    const itemsToReturn = selectedItemsForReturn.value
      .map(linenum => transactionSalesTrans.value.find(item => item.linenum === linenum))
      .filter(item => item !== undefined);

    if (itemsToReturn.length === 0) {
      alert('Please select at least one item to return');
      return;
    }

    const returnItems = itemsToReturn.map(item => ({
      linenum: item.linenum,
      itemid: item.itemid,
      returnQty: item.qty,
      returnDiscAmount: item.discamount, 
      price: item.price,
      description: item.description || item.itemname
    }));

    // Additional checks for returnItems
    if (returnItems.some(item => !item.linenum || !item.itemid || !item.returnQty || !item.price)) {
      alert('One or more return items have invalid properties');
      return;
    }

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
      /* alert(`Transaction returned successfully! Return Receipt ID: ${response.data.returnReceiptId}`); */

      // Clear selections and refresh data
      selectedTransaction.value = null;
      transactionSalesTrans.value = [];
      selectedItemsForReturn.value = []; // Clear the array
      location.reload();
      // Rest of the success handling...

    } else {
      throw new Error(response.data.message || 'Failed to process return');
    }
  } catch (error) {
    // Error handling...
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

const handleTenderClick = () => {
    showTenderDeclaration.value = true;
    showDailyJournal.value = false;
    showPartialPayment.value = false;
    showRemovePartialPayment.value = false;
  resetTenderDeclaration();
};

const resetTenderDeclaration = () => {
  tenderDeclaration.value = {
    bills: {},
    coins: {},
    ar: {}
  };
  billDenominations.forEach(amount => {
    tenderDeclaration.value.bills[amount] = 0;
  });
  coinDenominations.forEach(amount => {
    tenderDeclaration.value.coins[amount] = 0;
  });
  ARDenominations.forEach(method => {
    tenderDeclaration.value.ar[method] = 0;
  });
  calculateTotal();
};

const calculateTotal = () => {
  totalBills.value = Object.entries(tenderDeclaration.value.bills).reduce((sum, [amount, count]) => {
    return sum + (Number(amount) * (Number(count) || 0));
  }, 0);

  totalCoins.value = Object.entries(tenderDeclaration.value.coins).reduce((sum, [amount, count]) => {
    return sum + (Number(amount) * (Number(count) || 0));
  }, 0);

  totalAR.value = Object.entries(tenderDeclaration.value.ar).reduce((sum, [method, amount]) => {
  return sum + (Number(amount) || 0);
}, 0);


  grandTotal.value = totalBills.value + totalCoins.value + totalAR.value;
};

const submitTenderDeclaration = async () => {
  isSubmitting.value = true;

  try {
    const tenderData = {
      total: grandTotal.value,
      bills: Object.fromEntries(
        Object.entries(tenderDeclaration.value.bills)
          .map(([amount, count]) => [amount, Number(count)])
      ),
      coins: Object.fromEntries(
        Object.entries(tenderDeclaration.value.coins)
          .map(([amount, count]) => [amount, Number(count)])
      ),
      ar: Object.fromEntries(
        Object.entries(tenderDeclaration.value.ar)
          .map(([method, count]) => [method, Number(count)])
      )
    };

    const response = await axios.post('/tender-declaration', tenderData);

    if (response.data.success) {
      alert('Tender declaration submitted successfully!');
      resetTenderDeclaration();
      showTenderDeclaration.value = false;
    } else {
      throw new Error(response.data.message || 'Failed to submit tender declaration.');
    }
  } catch (error) {
    console.error('Error submitting tender declaration:', error);
    alert('Failed to submit tender declaration. Please try again.');
  } finally {
    isSubmitting.value = false;
  }
};

const closeDialog = () => {
  emit('close');
};


watch([totalBills, totalCoins, totalAR, grandTotal], ([newBills, newCoins, newAR, newTotal]) => {
  console.log('Total Bills:', newBills);
  console.log('Total Coins:', newCoins);
  console.log('Total AR:', newAR);
  console.log('Grand Total:', newTotal.toFixed(2));
});


const savePriceAndQuantity = async () => {
  if (isLoading.value) return;

  try {
    isLoading.value = true;

    if (!selectedItems.value?.length) {
      throw new Error('Please select items to update');
    }

    const invalidItems = selectedItems.value.filter(item => {
      const price = parseFloat(item.total_price);
      const qty = parseFloat(item.total_qty);
      return (
        isNaN(price) || 
        isNaN(qty) || 
        price < 0 || 
        qty < 0 || 
        !Number.isInteger(qty) ||
        item.total_price === '' || 
        item.total_qty === ''
      );
    });

    if (invalidItems.length > 0) {
      throw new Error(
        'Please check all items have valid prices (non-negative) and quantities (positive whole numbers)'
      );
    }

    const itemsData = selectedItems.value.map(item => ({
      itemid: item.itemid,
      price: parseFloat(item.total_price),
      qty: parseInt(item.total_qty),
      description: item.itemname || 'Unnamed Item',
      linenum: item.linenum || 1
    }));

    const response = await axios.post('/update-price-quantity', { items: itemsData })
      .catch(error => {
        console.error('API Error:', error.response?.data || error.message);
        throw new Error(
          error.response?.data?.message || 
          error.response?.data?.errors?.[0] || 
          'Failed to communicate with server'
        );
      });

    if (response.data.status === 'success') {
      selectedItems.value = selectedItems.value.map(item => {
        const updated = response.data.updatedItems.find(
          u => u.itemid === item.itemid
        );
        return updated ? { ...item, ...updated } : item;
      });

      closeModal();

      await Swal.fire({
        title: 'Success!',
        text: 'Prices and quantities updated successfully',
        icon: 'success',
        confirmButtonText: 'OK'
      });

      location.reload();
    } else {
      throw new Error(response.data.message || 'Update failed');
    }

  } catch (error) {
    console.error('Price/Quantity Update Error:', error);
    
    await Swal.fire({
      title: 'Error',
      text: error.message || 'An error occurred while updating items',
      icon: 'error',
      confirmButtonText: 'OK'
    });
  } finally {
    isLoading.value = false;
  }
};

/* const storeOriginalValues = (items) => {
  items.forEach(item => {
    if (!item.original_price) {
      item.original_price = item.price || 0;
      item.original_qty = item.qty || 0;
      item.original_total = (item.original_price * item.original_qty) || 0;
    }
  });
};

const updateTotals = (item) => {
  const price = Number(item.price) || 0;
  const qty = Number(item.qty) || 0;
  item.current_total = price * qty;
  
  item.price_difference = price - (item.original_price || 0);
  item.total_difference = item.current_total - (item.original_total || 0);
};

watch(selectedItems, (newItems) => {
  if (newItems) {
    storeOriginalValues(newItems);
    newItems.forEach(item => updateTotals(item));
  }
}, { immediate: true, deep: true });

const priceComparison = computed(() => {
  return selectedItems.value.map(item => ({
    itemid: item.itemid,
    itemname: item.itemname,
    original_price: formatCurrency(item.original_price),
    current_price: formatCurrency(item.price),
    price_difference: formatCurrency(item.price_difference),
    original_total: formatCurrency(item.original_total),
    current_total: formatCurrency(item.current_total),
    total_difference: formatCurrency(item.total_difference)
  }));
}); */

const storeOriginalValues = (items) => {
  items.forEach(item => {
    if (!item.original_price) {
      item.original_price = item.price || 0;
      item.original_qty = item.qty || 0;
      item.original_total = (item.original_price * item.original_qty) || 0;
    }
  });
};

const updateTotals = (item) => {
  const price = Number(item.original_price) || 0
  const qty = Number(item.qty) || 0
  
  item.total_price = price * qty
  item.total_qty = qty
}


watch(selectedItems, (newItems) => {
  if (newItems) {
    storeOriginalValues(newItems);
    newItems.forEach(item => updateTotals(item));
  }
}, { immediate: true, deep: true });

const priceComparison = computed(() => {
  return selectedItems.value.map(item => ({
    itemid: item.itemid,
    itemname: item.itemname,
    original_price: formatCurrency(item.original_price),
    current_price: formatCurrency(item.price),
    price_difference: formatCurrency(item.price_difference),
    original_total: formatCurrency(item.original_total),
    current_total: formatCurrency(item.current_total),
    total_difference: formatCurrency(item.total_difference)
  }));
});

</script>

<template>
  <div>
    <dialog id="my_modal_1" ref="modalRef" class="modal w-full h-full p-0 max-w-none max-h-none">
      <div class="w-full h-full flex flex-col bg-gradient-to-br from-slate-50 to-slate-100">
        <!-- Loading Overlay -->
        <div v-if="isLoading" class="absolute inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center">
          <div class="text-center">
            <div class="inline-block animate-spin rounded-full h-20 w-20 border-t-4 border-b-4 border-blue-500"></div>
            <p class="mt-4 text-white text-xl font-semibold">Processing...</p>
          </div>
        </div>

        <!-- Header -->
        <div class="p-4 border-b bg-white shadow-sm">
          <div class="flex items-center space-x-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-800">EC-POS</h2>
          </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-grow flex">
          <!-- Left Sidebar - Menu -->
          <div class="w-1/3 bg-white shadow-lg">
            <div class="p-6">
              <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                MENU
              </h3>

              <div class="grid grid-cols-2 gap-4 p-4 h-[70vh] overflow-y-auto">
                <div v-for="(item, index) in ['DISCOUNT', 'PARTIAL PAYMENT', 'REMOVE PARTIAL PAYMENT', 'DAILY JOURNAL', 'TENDER DECLARATION', 'PULLOUT CASHFUND', 'X-READ', 'Z-READ']" 
                     :key="index" 
                     class="relative group"
                     :class="{ 'opacity-50': item === 'PARTIAL PAYMENT' && totalExistingPartialPayment >= 1 }">
                  <button 
                    class="w-full h-32 rounded-xl shadow-sm transition-all duration-300 group-hover:shadow-lg overflow-hidden bg-gradient-to-br from-blue-600 to-blue-700 group-hover:from-blue-700 group-hover:to-blue-800"
                    :class="{ 'cursor-not-allowed': item === 'PARTIAL PAYMENT' && totalExistingPartialPayment >= 1 }"
                    @click="
                      item === 'DISCOUNT' ? (handleDiscountClick(), handleOtherButtonClick()) : 
                      item === 'PARTIAL PAYMENT' && totalExistingPartialPayment < 1 ? (handlePartialPaymentClick(), handleOtherButtonClick()) : 
                      item === 'REMOVE PARTIAL PAYMENT' ? (handleRemovePartialPaymentClick(), handleOtherButtonClick()) :
                      item === 'X-READ' ? (handleXReadClick(), handleOtherButtonClick()) :
                      item === 'Z-READ' ? (handleZReadClick(), handleOtherButtonClick()) :
                      item === 'TENDER DECLARATION' ? (handleTenderClick(), handleOtherButtonClick()) :
                      item === 'DAILY JOURNAL' ? handleDailyJournalClick() : handleOtherButtonClick() 
                    "
                  >
                  <div class="h-full flex flex-col items-center justify-center p-4 text-white">
                    <div class="mb-2">
                      <!-- Dynamic icons based on menu item -->
                      <svg v-if="item === 'DISCOUNT'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      <svg v-else-if="item === 'PARTIAL PAYMENT'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      <svg v-else-if="item === 'REMOVE PARTIAL PAYMENT'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 6l3 12h10l3-12M4 6l2.5-4h11L20 6M16 6l2 12" />
                      </svg>
                      <svg v-else-if="item === 'DAILY JOURNAL'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a2 2 0 00-2 2v14a2 2 0 002 2h18a2 2 0 002-2V6a2 2 0 00-2-2H3zm0 0h18M9 2v4m6-4v4M3 10h18M3 14h18M3 18h18" />
                      </svg>
                      <svg v-else-if="item === 'TENDER DECLARATION'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 10h10M7 14h10M7 18h10M12 6h0M12 6h0M12 18h0M12 6h0M12 6h0M12 18h0M12 6h0" />
                        <circle cx="12" cy="12" r="8" stroke="currentColor" stroke-width="2" fill="none" />
                        <text x="12" y="15" text-anchor="middle" fill="currentColor" font-size="8" font-weight="bold">₱</text>
                      </svg>
                              <svg v-else-if="item === 'PULLOUT CASHFUND'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m0 0l4-4m-4 4l-4-4" />
                              </svg>
                              <svg v-else-if="item === 'X-READ'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2h12a2 2 0 012 2v16a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm0 0v16m12-16v16M6 6h12M6 10h12M6 14h12M6 18h12" />
                      </svg>
                      <svg v-else-if="item === 'Z-READ'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 2h12a2 2 0 012 2v16a2 2 0 01-2 2H6a2 2 0 01-2-2V4a2 2 0 012-2zm3 6h8m-8 4h8m-8 4h8M8 2v2m8-2v2" />
                      </svg>
                    </div>
                    <span class="text-sm font-medium text-center">{{ item }}</span>
                  </div>
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle Section - Input -->
          <div class="w-2/5 p-6 bg-white border-x">
            <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
              INPUT
            </h3>

            <!-- Input Section Content -->
            <div class="bg-white rounded-xl shadow-sm p-6 max-h-[70vh] overflow-y-auto">
              <!-- Existing input content remains the same -->
              <!-- Just adding styling to match the new design -->
              <div v-if="showPartialPayment" class="p-6 bg-white shadow-lg rounded-lg space-y-6">
                <h4 class="text-2xl font-semibold text-gray-800">Enter Partial Payments</h4>
                <form @submit.prevent="submitPartialPayments" class="space-y-4">
                    <div v-for="item in selectedItems" :key="item.itemid" class="p-4 border rounded-md bg-gray-50 shadow-sm">
                        <label :for="'partialPayment_' + item.itemid" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ item.itemname }} (Total: {{ item.total_price - item.discamount | currency }})
                        </label>
                        <input 
                            type="number" 
                            :id="'partialPayment_' + item.itemid" 
                            v-model="item.partialPayment" 
                            class="border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3" 
                            :placeholder="'Enter amount for ' + item.itemname" 
                            required 
                            min="0" 
                            :max="item.total_price"
                            step="0.01"
                        >
                    </div>
                    <div class="font-bold text-lg text-gray-800">
                        Total Partial Payment: {{ totalPartialPayment | currency }}
                    </div>
                    <button 
                        type="submit" 
                        class="w-full py-3 px-5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow transition duration-150 ease-in-out"
                        :disabled="!isValidPartialPayment"
                    >
                        Submit Partial Payments
                    </button>
                </form>
            </div>

              
              <div v-if="showRemovePartialPayment" class="p-6 bg-white shadow-lg rounded-lg space-y-6">
                <h4 class="text-2xl font-semibold text-gray-800">Remove Partial Payments</h4>
                <form @submit.prevent="submitRemovePartialPayments" class="space-y-4">
                    <div v-for="item in selectedItems" :key="item.itemid" class="p-4 border rounded-md bg-gray-50 shadow-sm">
                        <label :for="'removePartialPayment_' + item.itemid" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ item.itemname }} (Total: {{ item.total_price | currency }}, Current Partial Payment: {{ safeToFixed(item.partial_payment) | currency }})
                        </label>
                        <input 
                            type="number" 
                            :id="'removePartialPayment_' + item.itemid" 
                            v-model="item.removePartialPayment" 
                            class="border border-gray-300 text-gray-900 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3" 
                            :placeholder="'Enter amount to remove for ' + item.itemname" 
                            required 
                            min="0" 
                            :max="item.partial_payment || 0"
                            step="0.01"
                            disabled
                        >
                        <!-- Uncomment if you want to show the remaining partial payment
                        <p class="mt-2 text-sm text-gray-600">
                            Remaining Partial Payment: {{ safeToFixed(item.partial_payment - (item.removePartialPayment || 0)) | currency }}
                        </p>
                        -->
                    </div>
                    <div class="font-bold text-lg text-gray-800">
                        Total Amount to Remove: {{ safeToFixed(totalRemovePartialPayment) | currency }}
                    </div>
                    <button 
                        type="submit" 
                        class="w-full py-3 px-5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg shadow transition duration-150 ease-in-out"
                        :disabled="!isValidRemovePartialPayment"
                    >
                        Remove Partial Payments
                    </button>
                </form>
            </div>


            <div v-if="discountData.length > 0" class="p-6 bg-white shadow-lg rounded-lg space-y-6">
                <!-- Enhanced search input -->
                <div class="relative">
                    <input
                        type="text"
                        v-model="searchDiscount"
                        placeholder="Search discounts..."
                        class="w-full pl-10 pr-4 py-3 text-gray-700 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
                    />
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                
                <div class="max-h-[70vh] overflow-y-auto">
                    <div 
                        v-for="discount in filteredDiscounts" 
                        :key="discount.id" 
                        class="mb-4 p-4 border rounded-lg cursor-pointer transition-colors duration-200 ease-in-out hover:bg-gray-100"
                        @click="selectDiscount(discount)"
                        :class="{ 'bg-blue-100': selectedDiscount && selectedDiscount.id === discount.id }"
                    >
                        <h4 class="text-lg font-semibold text-gray-800">{{ discount.DISCOFFERNAME }}</h4>
                        <p class="text-gray-600"><strong>Parameter:</strong> {{ discount.PARAMETER }}</p>
                        <p class="text-gray-600"><strong>Discount Type:</strong> {{ discount.DISCOUNTTYPE }}</p>
                    </div>
                </div>
            </div>


            <div v-if="showDailyJournal" class="p-6 bg-white shadow-lg rounded-lg space-y-6">
                <div class="relative">
                    <input
                        type="text"
                        v-model="searchTransaction"
                        placeholder="Search transactions..."
                        class="text-black w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200 ease-in-out"
                    />
                    <span class="absolute right-3 top-2.5 text-gray-400 text-xl">
                        🔍
                    </span>
                </div>
                
                <div class="max-h-[70vh] overflow-y-auto">
                    <div 
                        v-for="transaction in filteredTransactions" 
                        :key="transaction.transactionid" 
                        class="mb-4 p-4 border rounded-lg cursor-pointer hover:bg-gray-100 transition duration-200 ease-in-out"
                        @click="selectTransaction(transaction)"
                    >
                        <h4 class="font-semibold text-gray-800">Receipt ID: {{ transaction.receiptid }}</h4>
                        <p class="text-gray-700">Total Gross: {{ formatCurrency(transaction.grossamount) }}</p>
                        <p class="text-gray-700">Date: {{ formatDate(transaction.createddate) }}</p>
                    </div>
                </div>
            </div>


              <template v-if="showTenderDeclaration" class="space-y-4 p-6 bg-white rounded-lg shadow-md">
                <h4 class="font-semibold text-black mb-4 text-lg">Tender Declaration</h4>
                <form @submit.prevent="submitTenderDeclaration" class="space-y-6">
                  
                  <!-- Bills Section -->
                  <div class="space-y-4">
                    <h5 class="font-medium text-gray-700">Bills</h5>
                    <div class="grid grid-cols-1 gap-4">
                      <div v-for="amount in billDenominations" :key="'bill-' + amount" class="space-y-2">
                        <label :for="'bill-' + amount" class="block text-sm font-medium text-gray-700">
                          ₱{{ amount }}
                        </label>
                        <div class="flex items-center space-x-2">
                          <input 
                            type="number" 
                            :id="'bill-' + amount" 
                            v-model="tenderDeclaration.bills[amount]" 
                            class="w-full px-3 py-2 border border-gray-300 text-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            min="0"
                            @input="calculateTotal"
                          />
                          <span class="text-sm text-gray-600 w-24">
                            = ₱{{ (amount * (tenderDeclaration.bills[amount] || 0)).toFixed(2) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Coins Section -->
                  <div class="space-y-4">
                    <h5 class="font-medium text-gray-700">Coins</h5>
                    <div class="grid grid-cols-1 gap-4">
                      <div v-for="amount in coinDenominations" :key="'coin-' + amount" class="space-y-2">
                        <label :for="'coin-' + amount" class="block text-sm font-medium text-gray-700">
                          ₱{{ amount }}
                        </label>
                        <div class="flex items-center space-x-2">
                          <input 
                            type="number" 
                            :id="'coin-' + amount" 
                            v-model="tenderDeclaration.coins[amount]" 
                            class="w-full px-3 py-2 border border-gray-300 text-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                            min="0"
                            @input="calculateTotal"
                          />
                          <span class="text-sm text-gray-600 w-24">
                            = ₱{{ (amount * (tenderDeclaration.coins[amount] || 0)).toFixed(2) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- AR Denominations Section -->
                    <div class="space-y-4">
                      <h5 class="font-medium text-gray-700">Alternative Methods</h5>
                      <div class="grid grid-cols-1 gap-4">
                        <div v-for="method in ARDenominations" :key="'ar-' + method" class="space-y-2">
                          <label :for="'ar-' + method" class="block text-sm font-medium text-gray-700">
                            {{ method }}
                          </label>
                          <div class="flex items-center space-x-2">
                            <input 
                              type="number" 
                              :id="'ar-' + method" 
                              v-model="tenderDeclaration.ar[method]" 
                              class="w-full px-3 py-2 border border-gray-300 text-gray-700 rounded-md focus:ring-blue-500 focus:border-blue-500"
                              min="0"
                              @input="calculateTotal"
                            />
                            <span class="text-sm text-gray-600 w-24">
                              = ₱{{ (tenderDeclaration.ar[method] || 0).toFixed(2) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>


                  <!-- Summary Section -->
                  <div class="p-4 bg-gray-50 rounded-lg space-y-2">
                    <div class="flex justify-between text-sm">
                      <span class="font-medium">Total Bills:</span>
                      <span class="text-gray-700 text-gray-700">₱{{ totalBills.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="font-medium text-gray-700">Total Coins:</span>
                      <span class="text-gray-700">₱{{ totalCoins.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                      <span class="font-medium text-gray-700">Total Coins:</span>
                      <span class="text-gray-700">₱{{ totalAR.toFixed(2) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-lg pt-2 border-t">
                      <span class="text-gray-700">Grand Total:</span>
                      <span class="text-gray-700">₱{{ grandTotal.toFixed(2) }}</span>
                    </div>
                  </div>

                  <button 
                    type="submit" 
                    class="w-full py-2 px-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 transition duration-200"
                    :disabled="isSubmitting"
                  >
                    {{ isSubmitting ? 'Submitting...' : 'Submit Declaration' }}
                  </button>
                </form>
              </template>
       
            </div>
          </div>

          <!-- Right Section - Selected Items -->
          <div class="w-1/2 p-6">
            <h3 class="font-bold text-lg mb-4 text-gray-800 flex items-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
              Selected Items
            </h3>

            <!-- Selected Items Content -->
            <div class="bg-white rounded-xl shadow-sm p-6 max-h-[70vh] overflow-y-auto">
              <!-- <div v-if="selectedItems.length > 0 && !selectedTransaction">
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
              </div> -->

              <div v-for="item in selectedItems" :key="item.itemid" class="mb-4 p-4 border rounded">
                <h4 class="font-semibold text-black">{{ item.itemname || 'Unnamed Item' }}</h4>
                <p class="text-black"><strong>Item ID:</strong> {{ item.itemid }}</p>
                
                <!-- Price Input with Original Price -->
                <div class="text-black mb-2">
                  <strong>Price per unit:</strong>
                  <span class="text-gray-600 text-sm">
                      (Original: P {{ item.total_price?.toFixed(2) }})
                  </span>
                  <div class="flex items-center gap-1">
                    <!-- <input type="number" 
                          v-model.number="item.price_per_unit" 
                          @input="updateTotals(item)" 
                          class="bg-gray-50 border border-gray-300 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                          min="0" 
                          step="0.01"
                          required> -->

                          <input type="number" 
                            v-model.number="item.price" 
                            @input="updateTotals(item)" 
                            class="bg-gray-50 border border-gray-300 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                            min="0" 
                            step="0.01"
                            required>

                    
                  </div>
                </div>
                
                <!-- Quantity Input with Original Quantity -->
                <div class="text-black mb-2">
                  <strong>Quantity:</strong>
                  <span class="text-gray-600 text-sm">
                      (Original: {{ item.total_qty }})
                  </span>
                  <div class="flex items-center gap-2">
                    <input type="number" 
                          v-model.number="item.qty" 
                          @input="updateTotals(item)" 
                          class="bg-gray-50 border border-gray-300 text-black text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" 
                          min="0" 
                          required>
                  </div>
                </div>
                
                <!-- Total Price with Original Total -->
                <p class="text-black mt-2">
                  <strong>Total Price:</strong> 
                  <span>P {{ item.total_price?.toFixed(2) }}</span>
                  <span class="text-gray-600 text-sm ml-2">
                    (Original Total: P {{ item.original_total?.toFixed(2) }})
                  </span>
                </p>
              </div>

              <div v-if="selectedTransaction" class="bg-white w-full rounded-lg shadow-md p-6 w-full max-w-md">
              <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900">ORDER | CART</h2>
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
                      <th class="py-2 px-4 text-right text-sm font-medium text-gray-900">Discount</th>
                      <th class="py-2 px-4 text-right text-sm font-medium text-gray-900">Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="item in transactionSalesTrans" :key="`${item.transactionid}-${item.linenum}`" class="border-b">
                      <td class="py-2 px-4 text-sm text-gray-900">
                        <input type="checkbox" 
                          :checked="selectedItemsForReturn.includes(item.linenum)"
                          @change="handleItemSelect(item.linenum)">
                      </td>
                      <td class="py-2 px-4 text-sm text-gray-900">{{ item.itemname }}</td>
                      <td class="py-2 px-4 text-right text-sm text-gray-900">{{ formatCurrency(item.price) }}</td>
                      <td class="py-2 px-4 text-center text-sm text-gray-900">{{ item.qty }}</td>
                      <td class="py-2 px-4 text-right text-sm text-gray-900">
                        <template v-if="item.discamount > 0">
                          <span class="text-red-600">-{{ formatCurrency(item.discamount) }}</span>
                          <br>
                          <span class="text-xs text-gray-500">({{ calculateDiscountPercentage(item) }}%)</span>
                        </template>
                        <span v-else>-</span>
                      </td>
                      <td class="py-2 px-4 text-right text-sm text-gray-900 font-medium">
                        {{ formatCurrency(calculateItemTotal(item)) }}
                      </td>
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
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.discamount) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">VAT (12%)</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency((selectedTransaction.netamount / 1.12) * .12) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Partial Payment</span>
                  <span class="font-medium text-gray-900">{{ formatCurrency(selectedTransaction.partialpayment || 0) }}</span>
                </div>
                <div class="flex justify-between text-sm font-bold pt-2 border-t">
                  <span class="text-gray-600">Net Amount</span>
                  <span class="text-gray-900">{{ formatCurrency(selectedTransaction.netamount) }}</span>
                </div>

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
        </div>
        
        <!-- <div class="p-4 border-t bg-white shadow-lg flex justify-end space-x-4">
          <form method="dialog" class="flex space-x-4">
            <button class="btn flex items-center px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
              </svg>
              SAVE PRICE & QTY
            </button>
            <button class="btn flex items-center px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
              Close
            </button>
          </form>
        </div> -->

        

        <div class="p-4 border-t bg-white shadow-lg flex justify-end space-x-4">
    <form @submit.prevent="savePriceAndQuantity" class="flex space-x-4">
      <button
        type="submit"
        :disabled="isLoading"
        class="btn flex items-center px-6 py-2 text-white rounded-lg transition-colors shadow-sm"
        :class="[
          isLoading 
            ? 'bg-blue-400 cursor-not-allowed'
            : 'bg-blue-600 hover:bg-blue-700'
        ]"
      >
        <template v-if="isLoading">
          <svg 
            class="animate-spin h-5 w-5 mr-2" 
            xmlns="http://www.w3.org/2000/svg" 
            fill="none" 
            viewBox="0 0 24 24"
          >
            <circle 
              class="opacity-25" 
              cx="12" 
              cy="12" 
              r="10" 
              stroke="currentColor" 
              stroke-width="4"
            />
            <path 
              class="opacity-75" 
              fill="currentColor" 
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            />
          </svg>
          <span>SAVING...</span>
        </template>
        <template v-else>
          <svg 
            xmlns="http://www.w3.org/2000/svg" 
            class="h-5 w-5 mr-2" 
            fill="none" 
            viewBox="0 0 24 24" 
            stroke="currentColor"
          >
            <path 
              stroke-linecap="round" 
              stroke-linejoin="round" 
              stroke-width="2" 
              d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" 
            />
          </svg>
          <span>SAVE PRICE & QTY</span>
        </template>
      </button>

      <button
        type="button"
        @click="closeModal"
        :disabled="isLoading"
        class="btn flex items-center px-6 py-2 text-white rounded-lg transition-colors shadow-sm"
        :class="[
          isLoading 
            ? 'bg-gray-400 cursor-not-allowed'
            : 'bg-gray-600 hover:bg-gray-700'
        ]"
      >
        <svg 
          xmlns="http://www.w3.org/2000/svg" 
          class="h-5 w-5 mr-2" 
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path 
            stroke-linecap="round" 
            stroke-linejoin="round" 
            stroke-width="2" 
            d="M6 18L18 6M6 6l12 12" 
          />
        </svg>
        <span>Close</span>
      </button>
    </form>
  </div>





      </div>
    </dialog>
  </div>
</template>