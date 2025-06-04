<template>
    <div class="container mx-auto p-4">
      <div class="mb-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold">Loyalty Cards Management</h1>
        <button 
          @click="showCreateModal = true"
          class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
        >
          Create New Card
        </button>
      </div>
  
      <FlashMessage v-if="$page.props.flash.success" class="mb-4">
        {{ $page.props.flash.success }}
      </FlashMessage>
  
      <!-- Cards List -->
      <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Card Number</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="card in loyaltyCards" :key="card.id">
              <td class="px-6 py-4 whitespace-nowrap">{{ card.card_number }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ card.points }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusClass(card.status)">
                  {{ card.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Link 
                  :href="`/loyalty-cards/${card.customer_id}`"
                  class="text-indigo-600 hover:text-indigo-900 mr-2"
                >
                  View
                </Link>
                <button 
                  @click="editCard(card)"
                  class="text-green-600 hover:text-green-900 mr-2"
                >
                  Edit
                </button>
                <button 
                  @click="confirmDelete(card)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
  
      <!-- Create Modal -->
      <Modal :show="showCreateModal" @close="closeCreateModal">
        <div class="p-6">
          <h2 class="text-xl font-bold mb-4">Create New Loyalty Card</h2>
          <form @submit.prevent="submitCreate">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Customer</label>
              <select 
                v-model="form.customer_id"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              >
                <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                  {{ customer.name }}
                </option>
              </select>
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Status</label>
              <select 
                v-model="form.status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
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
              >
                Create
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
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
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
              >
                Update
              </button>
            </div>
          </form>
        </div>
      </Modal>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import { Link, useForm } from '@inertiajs/vue3'
  import Modal from '@/Components/Modal.vue'
  import FlashMessage from '@/Components/FlashMessage.vue'
  
  const props = defineProps({
    loyaltyCards: Array,
    customers: Array
  })
  
  const showCreateModal = ref(false)
  const showEditModal = ref(false)
  const selectedCard = ref(null)
  
  const form = useForm({
    customer_id: '',
    status: 'active'
  })
  
  const editForm = useForm({
    status: ''
  })
  
  const getStatusClass = (status) => {
    const classes = {
      active: 'bg-green-100 text-green-800',
      inactive: 'bg-gray-100 text-gray-800',
      suspended: 'bg-red-100 text-red-800'
    }
    return `px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${classes[status]}`
  }
  
  const submitCreate = () => {
    form.post('/loyalty-cards', {
      onSuccess: () => {
        showCreateModal.value = false
        form.reset()
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
      onSuccess: () => {
        showEditModal.value = false
        editForm.reset()
        selectedCard.value = null
      }
    })
  }
  
  const confirmDelete = (card) => {
    if (confirm('Are you sure you want to delete this loyalty card?')) {
      useForm().delete(`/loyalty-cards/${card.id}`)
    }
  }
  
  const closeCreateModal = () => {
    showCreateModal.value = false
    form.reset()
  }
  
  const closeEditModal = () => {
    showEditModal.value = false
    editForm.reset()
    selectedCard.value = null
  }
  </script>