<script setup>
import { ref, onMounted, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  initialItems: {
    type: Array,
    default: () => []
  },
  currentMonth: String,
});

const items = ref([]);
const daysInMonth = ref([]);

onMounted(() => {
  // Initialize items
  items.value = (props.initialItems || []).map(item => ({
    ...item,
    quantities: item.quantities || {}
  }));

  // Set up days in month
  if (props.currentMonth) {
    const date = new Date(props.currentMonth);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    daysInMonth.value = Array.from({ length: lastDay }, (_, i) => i + 1);
  }
});

const form = useForm({
  items: items,
});

const updateInventory = () => {
  form.post('/update-inventory', {
    preserveScroll: true,
  });
};

const getQuantity = (item, day) => {
  return (item.quantities && item.quantities[day]) || 0;
};

const setQuantity = (item, day, value) => {
  if (!item.quantities) item.quantities = {};
  item.quantities[day] = parseInt(value) || 0;
};

const itemTotal = (item) => {
  return item.quantities ? Object.values(item.quantities).reduce((sum, qty) => sum + (qty || 0), 0) : 0;
};

const dayTotal = (day) => {
  return items.value.reduce((sum, item) => sum + getQuantity(item, day), 0);
};

const grandTotal = computed(() => {
  return items.value.reduce((sum, item) => sum + itemTotal(item), 0);
});
</script>

<template>
  <div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Inventory for {{ currentMonth }}</h1>
    <div v-if="items.length && daysInMonth.length" class="overflow-x-auto">
      <table class="min-w-full bg-white border">
        <thead>
          <tr>
            <th class="px-4 py-2 bg-gray-100 border">ITEMS</th>
            <th class="px-4 py-2 bg-gray-100 border">CATEGORY</th>
            <th v-for="day in daysInMonth" :key="day" class="px-4 py-2 bg-gray-100 border">
              {{ day }}/{{ currentMonth.split(' ')[0].substring(0, 3) }}
            </th>
            <th class="px-4 py-2 bg-gray-100 border">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in items" :key="item.id">
            <td class="border px-4 py-2">{{ item.name }}</td>
            <td class="border px-4 py-2">{{ item.category }}</td>
            <td v-for="day in daysInMonth" :key="day" class="border px-4 py-2">
              <input
                type="number"
                :value="getQuantity(item, day)"
                @input="setQuantity(item, day, $event.target.value)"
                class="w-16 px-2 py-1 border rounded"
                min="0"
              >
            </td>
            <td class="border px-4 py-2 font-bold">
              {{ itemTotal(item) }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2" class="border px-4 py-2 font-bold">TOTAL</td>
            <td v-for="day in daysInMonth" :key="day" class="border px-4 py-2 font-bold">
              {{ dayTotal(day) }}
            </td>
            <td class="border px-4 py-2 font-bold">
              {{ grandTotal }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div v-else class="text-center py-4">
      No inventory data available.
    </div>
    <button @click="updateInventory" class="mt-4 px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
      Update Inventory
    </button>
  </div>
</template>