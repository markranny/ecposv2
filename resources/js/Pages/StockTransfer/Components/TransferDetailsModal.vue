# resources/js/Pages/StockTransfer/Components/TransferDetailsModal.vue

<template>
  <div 
    class="modal fade" 
    :class="{ 'show d-block': modelValue }"
    tabindex="-1"
    style="background-color: rgba(0, 0, 0, 0.5)"
  >
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            Transfer Details - {{ transfer.transfer_number }}
          </h5>
          <button 
            type="button" 
            class="btn-close" 
            @click="$emit('update:modelValue', false)"
          ></button>
        </div>
        <div class="modal-body">
          <div class="mb-4">
            <h6 class="border-bottom pb-2">Transfer Information</h6>
            <div class="row">
              <div class="col-md-6">
                <p><strong>From Store:</strong> {{ transfer.from_store.name }}</p>
                <p><strong>Status:</strong> 
                  <span :class="getStatusBadgeClass(transfer.status)">
                    {{ transfer.status }}
                  </span>
                </p>
              </div>
              <div class="col-md-6">
                <p><strong>To Store:</strong> {{ transfer.to_store.name }}</p>
                <p><strong>Date:</strong> {{ formatDate(transfer.created_at) }}</p>
              </div>
            </div>
          </div>

          <h6 class="border-bottom pb-2">Transfer Items</h6>
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th class="text-center">Quantity</th>
                  <th>Handling ID</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in transfer.items" :key="item.id">
                  <td>{{ item.item.name }}</td>
                  <td class="text-center">{{ item.quantity }}</td>
                  <td>
                    <code class="bg-light px-2 py-1 rounded">
                      {{ item.handling_id }}
                    </code>
                  </td>
                </tr>
                <tr v-if="transfer.items.length === 0">
                  <td colspan="3" class="text-center">No items found</td>
                </tr>
              </tbody>
              <tfoot v-if="transfer.items.length > 0">
                <tr>
                  <td><strong>Total Items:</strong></td>
                  <td class="text-center">
                    <strong>{{ totalQuantity }}</strong>
                  </td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <button 
            type="button" 
            class="btn btn-secondary" 
            @click="$emit('update:modelValue', false)"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { format } from 'date-fns';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  transfer: {
    type: Object,
    required: true
  }
});

defineEmits(['update:modelValue']);

const totalQuantity = computed(() => {
  return props.transfer.items.reduce((sum, item) => sum + item.quantity, 0);
});

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'badge bg-warning',
    completed: 'badge bg-success',
    cancelled: 'badge bg-danger'
  };
  return classes[status] || 'badge bg-secondary';
};

const formatDate = (date) => {
  return format(new Date(date), 'MMM dd, yyyy HH:mm');
};
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}

.modal.show {
  display: block;
}
</style>