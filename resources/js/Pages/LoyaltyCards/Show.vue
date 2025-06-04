# File: resources/js/Pages/LoyaltyCards/Show.vue
<template>
  <Head title="Loyalty Card Details">
    <meta name="description" content="Loyalty Card Details and Transactions" />
  </Head>

  <AdminPanel :active-tab="'LOYALTY'" :user="$page.props.auth.user">
    <template #main>
      <div class="container mx-auto px-4 mt-10 sm:px-6 lg:px-8">
        <!-- Debug panel - remove in production -->
        <!-- <div v-if="debug" class="mb-4 p-4 bg-gray-100 rounded">
          <p>Total Transactions: {{ props.transactions.length }}</p>
          <p>Filtered Transactions: {{ filteredTransactions.length }}</p>
          <p>Current Filters: {{ JSON.stringify(filters) }}</p>
        </div> -->

        <div class="mb-4 flex justify-between items-center">
          <h1 class="text-2xl font-bold">Loyalty Card Details</h1>
          <Link
            href="/loyalty-cards"
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          >
            Back to List
          </Link>
        </div>

        <!-- Card Details -->
        <div class="bg-white shadow rounded-lg p-6 mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <h3 class="text-gray-600">Card Number</h3>
              <p class="font-medium">{{ loyaltyCard.card_number }}</p>
            </div>
            <div>
              <h3 class="text-gray-600">Customer</h3>
              <p class="font-medium">{{ loyaltyCard.customer.name }}</p>
            </div>
            <div>
              <h3 class="text-gray-600">Points</h3>
              <p class="font-medium">{{ loyaltyCard.points_formatted }}</p>
            </div>
            <div>
              <h3 class="text-gray-600">Status</h3>
              <span :class="getStatusClass(loyaltyCard.status)">
                {{ capitalizeFirstLetter(loyaltyCard.status) }}
              </span>
            </div>
            <div>
              <h3 class="text-gray-600">Created At</h3>
              <p class="font-medium">{{ formatDate(loyaltyCard.created_at) }}</p>
            </div>
            <div>
              <h3 class="text-gray-600">Last Updated</h3>
              <p class="font-medium">{{ formatDate(loyaltyCard.updated_at) }}</p>
            </div>
          </div>
        </div>

        <!-- Points Management Section - Only visible for HEADOFFICE -->
        <!-- [Previous Points Management Code remains unchanged] -->

        <!-- Transaction History -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="p-4 border-b flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-xl font-bold">Transaction History</h2>
            <div class="flex gap-2">
              <select
                v-model="filters.type"
                class="rounded-md border-gray-300 shadow-sm text-sm"
              >
                <option value="">All Types</option>
                <option value="earn">Earned Points</option>
                <option value="redeem">Redeemed Points</option>
              </select>
              <select
                v-model="filters.period"
                class="rounded-md border-gray-300 shadow-sm text-sm"
              >
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
                <option value="year">This Year</option>
              </select>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in filteredTransactions" :key="transaction.id">
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatDateTime(transaction.created_at) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getTransactionTypeClass(transaction.type)">
                      {{ formatTransactionType(transaction.type) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap font-medium" :class="transaction.type === 'redeem' ? 'text-red-600' : 'text-green-600'">
                    {{ transaction.type === 'redeem' ? '-' : '+' }}{{ transaction.points_formatted }}
                  </td>
                  <td class="px-6 py-4">{{ transaction.description }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ transaction.balance_after_formatted }}</td>
                </tr>
                <tr v-if="filteredTransactions.length === 0">
                  <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No transactions found for the selected filters
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </template>
  </AdminPanel>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AdminPanel from '@/Layouts/Main.vue'
import { Head } from '@inertiajs/vue3'
import Swal from 'sweetalert2'

const debug = ref(process.env.NODE_ENV === 'development')

const props = defineProps({
  loyaltyCard: {
    type: Object,
    required: true
  },
  transactions: {
    type: Array,
    required: true
  },
  maxPointsPerTransaction: {
    type: Number,
    default: 10000
  },
  storeId: {
    type: String,
    required: true
  }
})

// Toast Configuration
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true
})

const filters = ref({
  type: '',
  period: 'all'
})

// Form handlers remain the same

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800'
  }
  return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[status] || ''}`
}

const getTransactionTypeClass = (type) => {
  return type === 'earn' 
    ? 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'
    : 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800'
}

const formatTransactionType = (type) => {
  return {
    earn: 'Earned',
    redeem: 'Redeemed'
  }[type] || type
}

const capitalizeFirstLetter = (string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleString()
}

const canRedeemPoints = computed(() => {
  return props.loyaltyCard.is_active && props.loyaltyCard.points > 0
})

const filteredTransactions = computed(() => {
  if (!Array.isArray(props.transactions)) {
    console.warn('transactions prop is not an array:', props.transactions)
    return []
  }

  let filtered = [...props.transactions]

  // Filter by type
  if (filters.value.type) {
    filtered = filtered.filter(t => t.type === filters.value.type)
  }

  // Filter by period
  const now = new Date()
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
  const thisWeek = new Date(today)
  thisWeek.setDate(today.getDate() - today.getDay())
  const thisMonth = new Date(now.getFullYear(), now.getMonth(), 1)
  const thisYear = new Date(now.getFullYear(), 0, 1)

  switch (filters.value.period) {
    case 'today':
      filtered = filtered.filter(t => new Date(t.created_at) >= today)
      break
    case 'week':
      filtered = filtered.filter(t => new Date(t.created_at) >= thisWeek)
      break
    case 'month':
      filtered = filtered.filter(t => new Date(t.created_at) >= thisMonth)
      break
    case 'year':
      filtered = filtered.filter(t => new Date(t.created_at) >= thisYear)
      break
  }

  return filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
})

// Form submission methods remain the same
</script>

<style scoped>
/* Responsive adjustments */
@media (max-width: 640px) {
  .container {
    padding: 1rem;
  }
}

/* Table adjustments for mobile */
@media (max-width: 768px) {
  .overflow-x-auto {
    margin: 0 -1rem;
  }
  
  .table-container {
    padding: 0 1rem;
  }
}
</style>