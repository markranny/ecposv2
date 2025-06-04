<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const amount = ref('')
const focused = ref(false)
const totalCashFunds = ref(0)

const formattedAmount = computed(() => {
  return parseFloat(amount.value || 0).toFixed(2)
})

const isValidAmount = computed(() => {
  const value = parseFloat(amount.value)
  return !isNaN(value) && value > 0
})

const progress = computed(() => {
  const value = parseFloat(amount.value || 0)
  return Math.min(value / 10, 100)
})

const fetchCashFundsCount = async () => {
  try {
    const response = await axios.get('/api/cash-funds-count')
    totalCashFunds.value = response.data.total || 0
  } catch (error) {
    console.error('Error fetching cash funds count:', error)
    // Add user notification here
  }
}

const submitCashFund = async () => {
  if (!isValidAmount.value) return

  try {
    await axios.post('/api/submit-cash-fund', {
      amount: parseFloat(amount.value)
    })
    amount.value = ''
    await fetchCashFundsCount()
    // Add success notification here

    location.reload();
  } catch (error) {
    console.error('Error submitting cash fund:', error)
    // Add error notification here
  }
}

onMounted(fetchCashFundsCount)
</script>


<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-cover bg-center backdrop-blur-md" style="background-image: url('/images/pos_wallpaper.png'); background-size: cover;">
    <div class="bg-white p-8 rounded-lg shadow-lg w-80">
      <h2 class="text-2xl font-bold mb-4 text-gray-800">Cash Input</h2>
      <div class="relative">
        <input
          v-model="amount"
          type="number"
          placeholder="Enter amount"
          class="w-full px-4 py-2 text-lg border-2 border-gray-300 rounded-md focus:outline-none focus:border-purple-500 transition-all duration-300"
          @focus="focused = true"
          @blur="focused = false"
        />
        <div
          class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none transition-all duration-300"
          :class="{ 'text-sm -translate-y-8': focused || amount }"
        >
          
        </div>
      </div>
      <div class="mt-4">
        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
          <div
            class="h-full bg-purple-500 transition-all duration-500 ease-out"
            :style="{ width: `${progress}%` }"
          ></div>
        </div>
      </div>
      <p class="mt-2 text-sm text-gray-600">Amount: ₱ {{ formattedAmount }}</p>
      <p class="mt-2 text-sm text-gray-600">Cash Funds Today: ₱ {{ totalCashFunds }}</p>
      <button
        @click="submitCashFund"
        :disabled="!isValidAmount"
        class="mt-4 w-full bg-navy text-white py-2 rounded-md hover:bg-purple-600 transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        Submit Cash Fund
      </button>
    </div>
  </div>
</template>

