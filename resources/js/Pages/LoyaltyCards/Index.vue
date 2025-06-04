//resources/js/Pages/LoyaltyCards/Index.vue

<template>
  <Head title="Loyalty Cards">
    <meta name="description" content="Loyalty Cards Management System" />
  </Head>

  <AdminPanel :active-tab="'LOYALTY'" :user="$page.props.auth.user">
    <template #modals>
      <!-- Create Modal -->
      <Modal :show="showCreateModal" @close="closeCreateModal">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4">Create New Loyalty Card</h2>
          <form @submit.prevent="submitCreate">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Card Number</label>
              <input 
                v-model="form.card_number"
                type="text"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required
              />
              <p v-if="form.errors.card_number" class="mt-1 text-sm text-red-600">
                {{ form.errors.card_number }}
              </p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Customer</label>
              <select 
                v-model="form.customer_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required
              >
                <option value="">Select a customer</option>
                <option 
                  v-for="customer in customers" 
                  :key="customer.id" 
                  :value="customer.id"
                >
                  {{ customer.name }} ({{ customer.account }})
                </option>
              </select>
              <p v-if="form.errors.customer_id" class="mt-1 text-sm text-red-600">
                {{ form.errors.customer_id }}
              </p>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select 
                v-model="form.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
              <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">
                {{ form.errors.status }}
              </p>
            </div>
            <div class="flex justify-end gap-2">
              <button 
                type="button"
                @click="closeCreateModal"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
              >
                Cancel
              </button>
              <button 
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                :disabled="form.processing"
              >
                {{ form.processing ? 'Creating...' : 'Create' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>

      <!-- Edit Modal -->
      <Modal :show="showEditModal" @close="closeEditModal">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4">Edit Loyalty Card</h2>
          <form @submit.prevent="submitEdit">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select 
                v-model="editForm.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                required
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
              <p v-if="editForm.errors.status" class="mt-1 text-sm text-red-600">
                {{ editForm.errors.status }}
              </p>
            </div>
            <div class="flex justify-end gap-2">
              <button 
                type="button"
                @click="closeEditModal"
                class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
              >
                Cancel
              </button>
              <button 
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                :disabled="editForm.processing"
              >
                {{ editForm.processing ? 'Updating...' : 'Update' }}
              </button>
            </div>
          </form>
        </div>
      </Modal>
    </template>

    <template #main>
      <div class="container mx-auto px-4 mt-10 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
          <h1 class="text-2xl font-bold">Loyalty Cards Management</h1>
          <div class="flex gap-4 items-center w-full sm:w-auto">
            <!-- Search Bar -->
            <div class="flex-grow sm:flex-grow-0 sm:min-w-[300px]">
              <input
                type="text"
                placeholder="Search cards, accounts....."
                v-model="search"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              />
            </div>
            <button 
              @click="showCreateModal = true"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 whitespace-nowrap"
            >
              Create New Card
            </button>
          </div>
        </div>

        <!-- Cards List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card Number</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tier</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="card in loyaltyCards" :key="card.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">{{ card.card_number }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ card.points_formatted }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span v-if="card.tier" :class="getTierClass(card.tier)">
                      {{ capitalizeFirstLetter(card.tier) }}
                    </span>
                    <span v-else>-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="getStatusClass(card.status)">
                      {{ capitalizeFirstLetter(card.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(card.expiry_date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                      <Link 
                        :href="`/loyalty-cards/${card.customer_id}`"
                        class="text-indigo-600 hover:text-indigo-900"
                      >
                        View
                      </Link>
                      <button 
                        @click="editCard(card)"
                        class="text-green-600 hover:text-green-900"
                      >
                        Edit
                      </button>
                      <button 
                        @click="confirmDelete(card)"
                        class="text-red-600 hover:text-red-900"
                      >
                        Delete
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="loyaltyCards.length === 0">
                  <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    {{ search ? 'No loyalty cards found matching your search' : 'No loyalty cards found' }}
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
import { ref, watch } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AdminPanel from '@/Layouts/Main.vue'
import Modal from '@/Components/Modal.vue'
import { Head } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import debounce from 'lodash/debounce'

const props = defineProps({
  loyaltyCards: {
    type: Array,
    required: true
  },
  customers: {
    type: Array,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
    }),
  },
})

// Search functionality
const search = ref(props.filters.search)

// Debounced search function
const debouncedSearch = debounce((value) => {
  router.get(
    '/loyalty-cards',
    { search: value },
    { 
      preserveState: true,
      preserveScroll: true,
      replace: true
    }
  )
}, 300)

// Watch for changes in search input
watch(search, (value) => {
  debouncedSearch(value)
})

// Toast Configuration
const Toast = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
  didOpen: (toast) => {
    toast.addEventListener('mouseenter', Swal.stopTimer)
    toast.addEventListener('mouseleave', Swal.resumeTimer)
  }
})

const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedCard = ref(null)

const form = useForm({
  customer_id: '',
  status: 'active',
  card_number: ''
})

const editForm = useForm({
  status: ''
})

const getStatusClass = (status) => {
  if (!status) return ''
  
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800'
  }
  return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[status] || 'bg-gray-100 text-gray-800'}`
}

const getTierClass = (tier) => {
  if (!tier) return ''
  
  const classes = {
    bronze: 'bg-yellow-100 text-yellow-800',
    silver: 'bg-gray-100 text-gray-800',
    gold: 'bg-yellow-300 text-yellow-900',
    platinum: 'bg-purple-100 text-purple-800'
  }
  return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[tier] || 'bg-gray-100 text-gray-800'}`
}

const capitalizeFirstLetter = (string) => {
  if (!string) return ''
  return string.charAt(0).toUpperCase() + string.slice(1)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const submitCreate = () => {
  form.post('/loyalty-cards', {
    preserveScroll: true,
    onSuccess: () => {
      Toast.fire({
        icon: 'success',
        title: 'Loyalty card created successfully'
      })
      showCreateModal.value = false
      form.reset()
    },
    onError: () => {
      Toast.fire({
        icon: 'error',
        title: 'Failed to create loyalty card'
      })
    }
  })
}

const editCard = (card) => {
  selectedCard.value = card
  editForm.status = card.status
  showEditModal.value = true
}

const submitEdit = () => {
  editForm.put(`/loyalty-cards/${selectedCard.value.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      Toast.fire({
        icon: 'success',
        title: 'Loyalty card updated successfully'
      })
      showEditModal.value = false
      editForm.reset()
      selectedCard.value = null
    },
    onError: () => {
      Toast.fire({
        icon: 'error',
        title: 'Failed to update loyalty card'
      })
    }
  })
}

const confirmDelete = async (card) => {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: 'You will not be able to recover this loyalty card!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  })

  if (result.isConfirmed) {
    useForm().delete(`/loyalty-cards/${card.id}`, {
      preserveScroll: true,
      onSuccess: () => {
        Toast.fire({
          icon: 'success',
          title: 'Loyalty card deleted successfully'
        })
      },
      onError: (errors) => {
        Toast.fire({
          icon: 'error',
          title: errors.points || 'Failed to delete loyalty card'
        })
      }
    })
  }
}

const closeCreateModal = () => {
  showCreateModal.value = false
  form.reset()
  form.clearErrors()
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.reset()
  editForm.clearErrors()
  selectedCard.value = null
}
</script>

<style scoped>
/* Add any component-specific styles here */
.modal-overlay {
  background-color: rgba(0, 0, 0, 0.5);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .container {
    padding: 1rem;
  }
}
</style>