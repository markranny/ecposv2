<script setup>
import { defineProps, defineEmits } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-fixedcolumns';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Bread from "@/Components/Svgs/Bread.vue";
import Save from "@/Components/Svgs/Save.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import { ref, computed, isRef, unref, reactive } from 'vue';
import ExcelJS from 'exceljs';
import axios from 'axios';

DataTable.use(DataTablesCore);

const emit = defineEmits();

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
});

const form = useForm({
  StartDate: '',
  EndDate: '',
});

const submitForm = () => {
  form.get(route('orderingconso.getrange'), {
    preserveScroll: true,
  });
};

const toggleActive = () => {
  emit('toggleActive');
};

const options = {
  paging: false,
  scrollX: true,
  scrollY: "60vh",
  scrollCollapse: true,
  fixedColumns: {
    left: 6
  },
  columnDefs: [
    { width: '5%', targets: 0 }, // ITEMID
    { width: '5%', targets: 1 }, // ITEMS
    { width: '5%', targets: 2 }, // CATEGORY
    { width: '8%', targets: 3 }, // ACTUAL INV COUNT
    { width: '8%', targets: 4 }, // REMAINING STOCKS
    { width: '8%', targets: 5 }, // TOTAL DISPATCH
    { width: '8%', targets: '_all' } // All other columns
  ],
  error: function (xhr, error, thrown) {
    console.error("DataTables error:", error);
  },
  initComplete: function(settings, json) {
    $(this).closest('.dataTables_wrapper').addClass('custom-datatable');
  }
};

const groupedOrders = reactive(props.orders.reduce((acc, order) => {
  const { STORENAME, ITEMID, ITEMNAME, CATEGORY, COUNTED } = order;
  const counted = parseInt(COUNTED) || 0;

  if (!acc[ITEMID]) {
    acc[ITEMID] = {
      ITEMID,
      ITEMNAME,
      CATEGORY,
      TOTAL: 0,
      MGCount: 0,
      BalanceCount: 0,
    };
  }

  if (!acc[ITEMID][STORENAME]) {
    acc[ITEMID][STORENAME] = 0;
  }

  acc[ITEMID][STORENAME] += counted;
  acc[ITEMID].TOTAL += counted;
  acc[ITEMID].BalanceCount = acc[ITEMID].TOTAL - acc[ITEMID].MGCount;

  return acc;
}, {}));

const flattenedOrders = computed(() => Object.values(groupedOrders));

const storeNames = computed(() => {
  const names = new Set();
  flattenedOrders.value.forEach(order => {
    Object.keys(order).forEach(key => {
      if (key !== 'ITEMID' && key !== 'ITEMNAME' && key !== 'CATEGORY' && key !== 'TOTAL' && key !== 'MGCount' && key !== 'BalanceCount') {
        names.add(key);
      }
    });
  });
  return Array.from(names);
});

const columns = computed(() => [
  { title: 'ITEMID', data: 'ITEMID' },
  { title: 'ITEMS', data: 'ITEMNAME' },
  { title: 'CATEGORY', data: 'CATEGORY' },
  { 
    title: 'ACTUAL INV COUNT', 
    data: 'MGCount',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<input type="number" value="${data}" onchange="window.updateMGCount('${row.ITEMID}', this.value)" />`;
      }
      return data;
    }
  },
  { title: 'REMAINING STOCKS', data: 'BalanceCount' },
  { title: 'TOTAL DISPATCH', data: 'TOTAL' },
  ...storeNames.value.map(storeName => ({ 
    title: storeName, 
    data: storeName,
    render: (data, type, row) => {
      if (type === 'display') {
        return `<input type="number" value="${data}" onchange="window.updateCounted('${row.ITEMID}', '${storeName}', this.value)" />`;
      }
      return data;
    }
  }))
]);

window.updateMGCount = function(itemId, value) {
  const item = groupedOrders[itemId];
  if (item) {
    item.MGCount = parseInt(value, 10) || 0;
    item.BalanceCount = item.MGCount - item.TOTAL;
    
    axios.post('/api/update-mgcount', {
      itemId: itemId,
      mgCount: item.MGCount
    })
    .then(response => {
      console.log('MGCount updated successfully');
    })
    .catch(error => {
      console.error('Error updating MGCount:', error);
    });
  }
}

window.updateCounted = function(itemId, storeName, value) {
  const item = groupedOrders[itemId];
  if (item) {
    item[storeName] = parseInt(value, 10) || 0;
    
    item.TOTAL = storeNames.value.reduce((sum, store) => {
      return sum + (item[store] || 0);
    }, 0);
    
    item.BalanceCount = item.TOTAL - item.MGCount;

    axios.post('/api/update-counted', {
      itemId: itemId,
      storeName: storeName,
      counted: item[storeName],
      total: item.TOTAL,
      balanceCount: item.BalanceCount
    })
    .then(response => {
      console.log('Counted updated successfully');
    })
    .catch(error => {
      console.error('Error updating Counted:', error);
    });
  }
}

const processedOrders = computed(() => {
  return flattenedOrders.value.map(order => {
    const processedOrder = {...order};
    columns.value.forEach(column => {
      if (!processedOrder.hasOwnProperty(column.data)) {
        processedOrder[column.data] = '0';
      }
    });
    return processedOrder;
  });
});

const StartDate = ref(null);
const EndDate = ref(null);

const currentDate = computed(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}${month}${day}`;
});

const saveAllData = () => {
  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    storeNames.value.forEach(storeName => {
      counted[storeName] = order[storeName] || 0;
    });

    return {
      itemId: order.ITEMID,
      mgCount: order.MGCount,
      balanceCount: order.BalanceCount,
      counted: counted
    };
  });

  console.log('Data to save:', JSON.stringify(dataToSave, null, 2));

  axios.post(route('save.all.data'), { data: dataToSave })
    .then(response => {
      console.log('All data saved successfully:', response.data);
    })
    .catch(error => {
      console.error('Error saving data:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
    });
};

async function exportToExcel() {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Flattened Orders');

    worksheet.columns = columns.value.map(column => ({
      header: column.title,
      key: column.data,
      width: 15
    }));

    processedOrders.value.forEach(order => {
      worksheet.addRow(order);
    });

    const today = new Date();
    const filename = `FlattenedOrders_${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}.xlsx`;
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  } catch (error) {
    console.error('Error exporting to Excel:', error);
  }
}

const picklist = () => {
  window.location.href = '/picklist';
};

</script>

<template>
  <Main active-tab="REPORTS">
    <template v-slot:main>
      <TableContainer>
        <div class="absolute adjust">
          <div class="flex justify-start items-center">
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
              <input type="hidden" name="_token" :value="$page.props.csrf_token">
              <div date-rangepicker class="flex items-center">
                <div class="relative ml-5 ">
                  <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                  </div>
                  <input
                    id="StartDate"
                    type="date"
                    v-model="form.StartDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start"
                    required
                  />
                </div>
                <span class="mx-4 text-gray-500">to</span>
                <div class="relative">
                  <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                  </div>
                  <input
                    id="EndDate"
                    type="date"
                    v-model="form.EndDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end"
                    required
                  />
                </div>
              </div>
            </form>
            <TransparentButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
              <Search class="h-8" />
            </TransparentButton>
            <SuccessButton
              type="button"
              @click="exportToExcel"
              class="m-6 bg-green"
            >
              <ExcelIcon class="h-4" />
            </SuccessButton>

            <PrimaryButton
              type="button"
              @click="saveAllData"
              class="m-6"
            >
              <Save class="h-4" />
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="picklist"
              class="m-1 bg-green"
            >
              <Bread class="h-4" />
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="Cake"
              class="m-1 bg-green"
            >
              <PartyCake class="h-4" />
            </PrimaryButton>
          </div>
        </div>
        
        <DataTable 
          :data="processedOrders" 
          :columns="columns" 
          class="w-full relative display" 
          :options="options"
        >
          <template #action="data">
            <div class="flex justify-start">
            </div>
          </template>
        </DataTable>
      </TableContainer>
    </template>
  </Main>
</template>

<style>
.custom-datatable table.dataTable {
  /* font-size: 0.85rem; */
  font-size: 5px;
}

.custom-datatable .dataTables_scrollHead,
.custom-datatable .dataTables_scrollBody {
  overflow: visible !important;
}

.custom-datatable .dataTables_scrollHead th:nth-child(-n+6),
.custom-datatable .dataTables_scrollBody td:nth-child(-n+6) {
  position: sticky;
  background-color: white;
  z-index: 1;
}

.custom-datatable .dataTables_scrollHead th:nth-child(1),
.custom-datatable .dataTables_scrollBody td:nth-child(1) {
  left: 0;
}

.custom-datatable .dataTables_scrollHead th:nth-child(2),
.custom-datatable .dataTables_scrollBody td:nth-child(2) {
  left: calc(1 * var(--column-width));
}

.custom-datatable .dataTables_scrollHead th:nth-child(3),
.custom-datatable .dataTables_scrollBody td:nth-child(3) {
  left: calc(2 * var(--column-width));
}

.custom-datatable .dataTables_scrollHead th:nth-child(4),
.custom-datatable .dataTables_scrollBody td:nth-child(4) {
  left: calc(3 * var(--column-width));
}

.custom-datatable .dataTables_scrollHead th:nth-child(5),
.custom-datatable .dataTables_scrollBody td:nth-child(5) {
  left: calc(4 * var(--column-width));
}

.custom-datatable .dataTables_scrollHead th:nth-child(6),
.custom-datatable .dataTables_scrollBody td:nth-child(6) {
  left: calc(5 * var(--column-width));
}

:root {
  /* --column-width: 150px;  */
  --column-width: 20px; 
}

/* Additional styles for better visibility of frozen columns */
.custom-datatable .dataTables_scrollHead th:nth-child(-n+6),
.custom-datatable .dataTables_scrollBody td:nth-child(-n+6) {
  box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

/* Ensure input fields in table cells don't overflow */
.custom-datatable input[type="number"] {
  width: 100%;
  box-sizing: border-box;
  padding: 2px;
}

/* Improve readability of table headers */
.custom-datatable thead th {
  font-weight: bold;
  background-color: #f8f9fa;
}

/* Add some padding to table cells for better spacing */
/* .custom-datatable td,
.custom-datatable th {
  padding: 8px;
} */

/* Style for alternating row colors */
.custom-datatable tbody tr:nth-of-type(even) {
  background-color: #f3f4f6;
}

/* Hover effect for rows */
.custom-datatable tbody tr:hover {
  background-color: #e5e7eb;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .custom-datatable table.dataTable {
    font-size: 0.75rem;
  }
  
  .custom-datatable td,
  .custom-datatable th {
    padding: 4px;
  }
}
</style>