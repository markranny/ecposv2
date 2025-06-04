<template>
  <div v-if="isOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
      <h2 class="text-xl font-bold mb-4 text-black">Enter Cash Amount</h2>

      <!-- Debug section - can be removed after confirming values -->
      <!-- <div class="mb-2 p-2 bg-gray-100 rounded text-xs">
        <pre>Selected Method: "{{ selectedAR }}"</pre>
        <pre>Is Cash: {{ selectedAR === 'CASH' }}</pre>
      </div> -->

      <div class="mb-4 space-y-2">
        <!-- <div class="flex justify-between">
          <span class="text-gray-700">Payment Method:</span>
          <span class="font-bold text-black">{{ selectedAR || 'Not Selected' }}</span>
        </div> -->

        <div class="flex justify-between">
          <span class="text-gray-700">Customer:</span>
          <span class="font-bold text-black">{{ selectedCustomer || 'Not Selected' }}</span>
        </div>

        <div class="flex justify-between">
          <span class="text-gray-700">Total Amount: </span>
          <span class="font-bold text-black">₱ {{ totalAmount?.toFixed(2) || '0.00' }}</span>
        </div>
        
        <div v-if="currentAmount && isCashPayment" class="flex justify-between">
          <span class="text-gray-700">Cash Received: </span>
          <span class="font-bold text-green-600">₱ {{ parseFloat(currentAmount).toFixed(2) }}</span>
        </div>

        <div v-if="currentAmount && isCashPayment && showChange" class="flex justify-between">
          <span class="text-gray-700">Change Due: </span>
          <span class="font-bold text-blue-600">₱ {{ changeDue.toFixed(2) }}</span>
        </div>
      </div>
      
      <input
        v-model="displayAmount"
        type="number"
        placeholder="Enter cash amount"
        class="w-full p-2 border rounded mb-4 text-black"
        @input="handleInput"
        @keyup.enter="handleEnter" 
        :class="{ 'bg-gray-100': !isCashPayment }"
        :readonly="!isCashPayment"
      />

      <div v-if="errorMessage" class="text-red-500 mb-2">{{ errorMessage }}</div>
      
      <div class="flex justify-end">
        <button @click="cancel" class="px-4 py-2 bg-gray-300 rounded mr-2">Cancel</button>
        <button 
          @click="submit" 
          class="px-4 py-2 bg-blue-500 text-white rounded" 
        >
          Submit
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
  isOpen: Boolean,
  totalAmount: {
    type: Number,
    default: 0
  },
  
  selectedAR: {
    type: String,
    required: true,
    default: 'CASH'
  },
  selectedCustomer: {
    type: String,
    required: true,
    default: ''
  }
});

const emit = defineEmits(['close', 'submit']);

// Reactive references
const currentAmount = ref('');
const errorMessage = ref('');
const isValidAmount = ref(false);

const handleEnter = () => {
  if (isValidAmount.value) {
    submit();
  }
};


// Computed properties
const isCashPayment = computed(() => props.selectedAR === 'CASH');

const showChange = computed(() => {
  const parsedAmount = parseFloat(currentAmount.value);
  return isCashPayment.value && !isNaN(parsedAmount) && parsedAmount >= props.totalAmount;
});

const changeDue = computed(() => {
  const parsedAmount = parseFloat(currentAmount.value);
  return isNaN(parsedAmount) ? 0 : parsedAmount - props.totalAmount;
});

const displayAmount = computed({
  get: () => {
    if (!isCashPayment.value) {
      return props.totalAmount?.toFixed(2) || '';
    }
    /* if (props.selectedCustomer !== 'WALK-IN') {
      return props.totalAmount?.toFixed(2);
    } */
    return currentAmount.value;
  },
  set: (value) => {
    if (isCashPayment.value) {
      currentAmount.value = value;
      console.log('Amount entered:', value);
    }
  }
});

// Methods
const handleInput = () => {
  console.log('Input changed:', {
    currentAmount: currentAmount.value,
    totalAmount: props.totalAmount,
    paymentMethod: props.selectedAR
  });

  if (isCashPayment.value) {
    validateAmount();
  } else {
    isValidAmount.value = true;
  }
};

const validateAmount = () => {
  const parsedAmount = parseFloat(currentAmount.value);
  
  if (isNaN(parsedAmount) || parsedAmount < 0) {
    errorMessage.value = 'Please enter a valid amount.';
    isValidAmount.value = false;
  } else if (parsedAmount < props.totalAmount) {
    errorMessage.value = 'Amount must be greater than or equal to total amount.';
    isValidAmount.value = false;
  } else {
    errorMessage.value = '';
    isValidAmount.value = true;
  }
};

const submit = () => {
  const amount = isCashPayment.value ? 
    parseFloat(currentAmount.value) : 
    props.totalAmount;

  emit('submit', amount);
  reset();
};

const cancel = () => {
  emit('close');
  reset();
};

const reset = () => {
  currentAmount.value = '';
  errorMessage.value = '';
  isValidAmount.value = false;
};

// Watchers
watch(() => props.selectedAR, (newValue) => {
  console.log('Payment method changed:', newValue);
  if (!isCashPayment.value) {
    reset();
    isValidAmount.value = true;
  }
});

watch(() => props.isOpen, (newValue) => {
  console.log('Modal opened:', newValue);
  if (newValue) {
    reset();
    if (!isCashPayment.value) {
      isValidAmount.value = true;
    }
  }
});
</script>