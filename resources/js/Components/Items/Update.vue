<!-- resources/js/Components/Items/Update.vue -->
<template>
  <div v-if="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Update Item</h3>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <!-- Item ID and Name -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Item ID</label>
              <input
                v-model="form.itemid"
                type="text"
                readonly
                class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Item Name <span class="text-red-500">*</span></label>
              <input
                v-model="form.itemname"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Category and Production -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
              <select
                v-model="form.itemgroup"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select a category</option>
                <option v-for="category in availableCategories" :key="category" :value="category">
                  {{ category }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Production <span class="text-red-500">*</span></label>
              <input
                v-model="form.production"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Cost, Price, MOQ -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Cost <span class="text-red-500">*</span></label>
              <input
                v-model="form.cost"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">SRP <span class="text-red-500">*</span></label>
              <input
                v-model="form.price"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">MOQ (Optional)</label>
              <input
                v-model="form.moq"
                type="number"
                step="1"
                min="0"
                placeholder="Leave empty for no MOQ"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Manila and Mall Prices -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Manila Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.manilaprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mall Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.mallprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Delivery Platform Prices -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Foodpanda Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.foodpandaprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">GrabFood Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.grabfoodprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Mall Platform Prices -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Foodpanda Mall Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.foodpandamallprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">GrabFood Mall Price <span class="text-red-500">*</span></label>
              <input
                v-model="form.grabfoodmallprice"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
            </div>
          </div>

          <!-- Default Settings -->
          <div class="border-t pt-4">
            <h4 class="text-md font-medium text-gray-900 mb-3">Default Settings</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="flex items-center">
                <input
                  v-model="form.default1"
                  type="checkbox"
                  id="default1"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="default1" class="ml-2 block text-sm text-gray-900">Default 1</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.default2"
                  type="checkbox"
                  id="default2"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="default2" class="ml-2 block text-sm text-gray-900">Default 2</label>
              </div>
              <div class="flex items-center">
                <input
                  v-model="form.default3"
                  type="checkbox"
                  id="default3"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                >
                <label for="default3" class="ml-2 block text-sm text-gray-900">Default 3</label>
              </div>
            </div>
          </div>

          <!-- Confirmation Checkbox -->
          <div class="border-t pt-4">
            <div class="flex items-center">
              <input
                v-model="form.confirm_defaults"
                type="checkbox"
                id="confirm_defaults"
                required
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              >
              <label for="confirm_defaults" class="ml-2 block text-sm text-gray-900">
                <span class="text-red-500">*</span> I confirm that all default settings are properly configured before saving
              </label>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="form.processing || !form.confirm_defaults"
              class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
            >
              {{ form.processing ? 'Updating...' : 'Update Item' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch, onMounted, computed } from 'vue';

const props = defineProps({
  showModal: {
    type: Boolean,
    default: false
  },
  itemid: {
    type: String,
    default: ''
  },
  itemname: {
    type: String,
    default: ''
  },
  itemgroup: {
    type: String,
    default: ''
  },
  price: {
    type: [Number, String],
    default: 0
  },
  cost: {
    type: [Number, String],
    default: 0
  },
  moq: {
    type: [Number, String, null],
    default: null
  },
  manilaprice: {
    type: [Number, String],
    default: 0
  },
  foodpandaprice: {
    type: [Number, String],
    default: 0
  },
  grabfoodprice: {
    type: [Number, String],
    default: 0
  },
  mallprice: {
    type: [Number, String],
    default: 0
  },
  foodpandamallprice: {
    type: [Number, String],
    default: 0
  },
  grabfoodmallprice: {
    type: [Number, String],
    default: 0
  },
  production: {
    type: String,
    default: ''
  },
  default1: {
    type: [Boolean, Number, String],
    default: false
  },
  default2: {
    type: [Boolean, Number, String],
    default: false
  },
  default3: {
    type: [Boolean, Number, String],
    default: false
  },
  rboinventitemretailgroups: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['toggle-active']);

const form = useForm({
  itemid: '',
  itemname: '',
  itemgroup: '',
  cost: 0,
  price: 0,
  moq: null,
  manilaprice: 0,
  foodpandaprice: 0,
  grabfoodprice: 0,
  mallprice: 0,
  foodpandamallprice: 0,
  grabfoodmallprice: 0,
  production: '',
  default1: false,
  default2: false,
  default3: false,
  confirm_defaults: false
});

// Available categories from retail groups
const availableCategories = computed(() => {
  if (!props.rboinventitemretailgroups || !Array.isArray(props.rboinventitemretailgroups)) {
    return [];
  }
  return props.rboinventitemretailgroups.map(group => group.NAME || group.name).filter(Boolean);
});

// Helper function to convert values to boolean - FIXED
const toBool = (value) => {
  if (typeof value === 'boolean') return value;
  if (typeof value === 'number') return value === 1;
  if (typeof value === 'string') return value === '1' || value.toLowerCase() === 'true';
  return false;
};

// Watch for prop changes and update form - FIXED to properly handle default values
watch(() => props.showModal, (newVal) => {
  if (newVal) {
    // Reset form first
    form.reset();
    
    // Then populate with current values
    form.itemid = props.itemid;
    form.itemname = props.itemname;
    form.itemgroup = props.itemgroup;
    form.cost = props.cost;
    form.price = props.price;
    form.moq = props.moq;
    form.manilaprice = props.manilaprice;
    form.foodpandaprice = props.foodpandaprice;
    form.grabfoodprice = props.grabfoodprice;
    form.mallprice = props.mallprice;
    form.foodpandamallprice = props.foodpandamallprice;
    form.grabfoodmallprice = props.grabfoodmallprice;
    form.production = props.production;
    
    // FIXED: Properly convert and set default values
    form.default1 = toBool(props.default1);
    form.default2 = toBool(props.default2);
    form.default3 = toBool(props.default3);
    
    form.confirm_defaults = false;
  }
});

const submitForm = () => {
  form.put(route('items.update', props.itemid), {
    preserveScroll: true,
    onSuccess: () => {
      emit('toggle-active');
      form.reset();
    },
    onError: (errors) => {
      console.error('Update failed:', errors);
    }
  });
};

const closeModal = () => {
  emit('toggle-active');
  form.reset();
};
</script>