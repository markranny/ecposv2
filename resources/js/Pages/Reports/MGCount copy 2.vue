<script setup>
import { defineProps, defineEmits } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Bread from "@/Components/Svgs/Bread.vue";
import Save from "@/Components/Svgs/Save.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import 'datatables.net-fixedcolumns';
import { ref, computed, isRef, unref, reactive } from 'vue';
import ExcelJS from 'exceljs';
import axios from 'axios';

DataTable.use(DataTablesCore);

const emit = defineEmits();

const isLoading = ref(false);

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
  error: function (xhr, error, thrown) {
    console.error("DataTables error:", error);
  }
};

/* const groupedOrders = reactive(props.orders.reduce((acc, order) => {
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
}, {})); */

const groupedOrders = reactive(props.orders.reduce((acc, order) => {
  const { STORENAME, ITEMID, ITEMNAME, CATEGORY, COUNTED, MGCOUNT } = order;
  const counted = parseInt(COUNTED) || 0;
  const mgCount = parseInt(MGCOUNT) || 0;

  if (!acc[ITEMID]) {
    acc[ITEMID] = {
      ITEMID,
      ITEMNAME,
      CATEGORY,
      TOTAL: 0,
      MGCount: mgCount,
      BalanceCount: 0,
    };
  }

  if (!acc[ITEMID][STORENAME]) {
    acc[ITEMID][STORENAME] = 0;
  }

  acc[ITEMID][STORENAME] += counted;
  acc[ITEMID].TOTAL += counted;
  acc[ITEMID].BalanceCount = acc[ITEMID].MGCount - acc[ITEMID].TOTAL;

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
  return names;
});

/* const columns = computed(() => [
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
  ...Array.from(storeNames.value).map(storeName => ({ 
    title: storeName, 
    data: storeName,
    render: (data, type, row) => {
      if (type === 'display') {
        return `<input type="number" value="${data}" onchange="window.updateCounted('${row.ITEMID}', '${storeName}', this.value)" />`;
      }
      return data;
    }
  }))
]); */

const columns = computed(() => [
  { 
    title: 'ITEMID', 
    data: 'ITEMID',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  },
  { 
    title: 'ITEMS', 
    data: 'ITEMNAME',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  },
  { 
    title: 'CATEGORY', 
    data: 'CATEGORY',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  },
  /* { 
    title: 'ACTUAL INV COUNT', 
    data: 'MGCount',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  }, */
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
  { 
    title: 'REMAINING STOCKS', 
    data: 'BalanceCount',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  },
  { 
    title: 'TOTAL ALLOCATION', 
    data: 'TOTAL',
    render: (data, type, row) => {
      if (type === 'display') {
        return `<span>${data}</span>`;
      }
      return data;
    }
  },
  ...Array.from(storeNames.value).map(storeName => ({ 
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

/* const saveAllData = () => {
  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    Array.from(storeNames.value).forEach(storeName => {
      counted[storeName] = order[storeName] || 0;
    });

    return {
      itemId: order.ITEMID,
      storeName: order.STORENAME,
      mgCount: order.MGCount,
      balanceCount: order.BalanceCount,
      counted: counted
    };
  });

  console.log('Data to save:', dataToSave); 

  axios.post(route('save.all.data'), { data: dataToSave })
    .then(response => {
      console.log('All data saved successfully');
    })
    .catch(error => {
      console.error('Error saving data:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
    });
}; */

/* const saveAllData = () => {
  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    Array.from(storeNames.value).forEach(storeName => {
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
}; */

const showMessage = ref(false);
const messageText = ref('');

/* const saveAllData = () => {
  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    Array.from(storeNames.value).forEach(storeName => {
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
      messageText.value = 'Data saved successfully!';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 3000); // Hide message after 3 seconds
    })
    .catch(error => {
      console.error('Error saving data:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
      messageText.value = 'Error saving data. Please try again.';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 3000); // Hide message after 3 seconds
    });
}; */

const saveAllData = () => {
  isLoading.value = true; // Set loading to true when starting

  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    Array.from(storeNames.value).forEach(storeName => {
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
      messageText.value = 'Data saved successfully!';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 3000);
    })
    .catch(error => {
      console.error('Error saving data:', error);
      if (error.response) {
        console.error('Error response:', error.response.data);
      }
      messageText.value = 'Error saving data. Please try again.';
      showMessage.value = true;
      setTimeout(() => {
        showMessage.value = false;
      }, 3000);
    })
    .finally(() => {
      isLoading.value = false; // Set loading to false when finished
    });
};

/* const saveAllData = () => {
  const dataToSave = flattenedOrders.value.map(order => {
    const counted = {};
    Array.from(storeNames.value).forEach(storeName => {
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
}; */

/* window.updateMGCount = function(itemId, value) {
  const item = groupedOrders[itemId];
  if (item) {
    item.MGCount = parseInt(value, 10) || 0;
    item.BalanceCount =  item.MGCount - item.TOTAL;
    
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
} */

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
    
    // Recalculate TOTAL based on all store counts
    item.TOTAL = Array.from(storeNames.value).reduce((sum, store) => {
      return sum + (item[store] || 0);
    }, 0);
    
    // Recalculate BalanceCount
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
const formattedDate1 = computed(() => {
  if (StartDate.value) {
    const date = new Date(StartDate.value);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  return '';
});

const EndDate = ref(null);
const formattedDate2 = computed(() => {
  if (EndDate.value) {
    const date = new Date(EndDate.value);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  return '';
});

const currentDate = computed(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  return `${year}${month}${day}`;
});

function generateTextFileContent(processedOrders, columns) {
  const headerRow = columns.value.map(column => column.title).join(',');
  const dataRows = processedOrders.map(order => {
    const rowData = columns.value.map(column => order[column.data] || '');
    return rowData.join(',');
  });

  return [headerRow, ...dataRows].join('\n');
}

function downloadTextFile(filename, content) {
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}

function generateAndDownloadTextFile() {
  const filename = `BW0001${isRef(currentDate) ? unref(currentDate) : currentDate.value}.txt`;
  const content = generateTextFileContent(processedOrders.value, columns);
  downloadTextFile(filename, content);
}

async function exportToExcel() {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Flattened Orders');

    // Define columns
    worksheet.columns = columns.value.map(column => ({
      header: column.title,
      key: column.data,
      width: 15
    }));

    // Add rows
    processedOrders.value.forEach(order => {
      worksheet.addRow(order);
    });

    // Generate Excel file
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

const cakes = () => {
  window.location.href = '/cakepicklist';
};
</script>

<template>
  <div :class="{ 'blur-overlay': isLoading }">
  <div v-if="showMessage" class="fixed top-5 right-5 bg-green-900 text-white border border-gray-300 rounded-lg shadow-lg p-4 z-50">
    {{ messageText }}
  </div>
  <Main active-tab="REPORTS">
    <template v-slot:main>
      <TableContainer>
        <div class="absolute adjust">
          <div class="flex justify-start items-center">
            <!-- <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
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
                    @input="formattedDate1"
                    :placeholder="formattedDate1"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start"
                    required
                  />
                  <InputError :message="form.errors.StartDate" class="mt-2" />
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
                    @input="formattedDate2"
                    :placeholder="formattedDate2"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end"
                    required
                  />
                  <InputError :message="form.errors.EndDate" class="mt-2" />
                </div>
              </div>
            </form>
            <TransparentButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
              <Search class="h-8" />
            </TransparentButton> -->
            <SuccessButton
              type="button"
              @click="exportToExcel"
              class="m-6 bg-green"
            >
              <ExcelIcon class="h-4" />
            </SuccessButton>

            <!-- <PrimaryButton
              type="button"
              @click="saveAllData"
              class="m-6"
            >
              <Save class="h-4" />
            </PrimaryButton> -->

            <PrimaryButton
              type="button"
              @click="saveAllData"
              class="m-6"
              :disabled="isLoading"
            >
              <!-- <template v-if="isLoading">
                <svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                &nbsp Saving...
              </template> -->

              <div v-if="isLoading" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center z-50">
              <div class="bg-white p-5 rounded-lg flex items-center">
                <svg class="animate-spin h-5 w-5 mr-3 text-blue-500" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-500">Saving data...</span>
              </div>
            </div>
              <template v-else>
                <Save class="h-4" />
                &nbsp Save
              </template>
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="picklist"
              class="m-1 bg-green"
            >
              <!-- <Bread class="h-4" /> -->
               Breads
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="cakes"
              class="m-1 bg-green"
            >
              <!-- <PartyCake class="h-4" /> -->
               Cakes
            </PrimaryButton>
          </div>
        </div>
        
        <DataTable 
          :data="processedOrders" 
          :columns="columns" 
          class="w-full relative display custom-datatable" 
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
</div>
</template>

<style>
.custom-datatable table.dataTable {
  font-size: 0.85rem;
  table-layout: fixed; /* This ensures that the percentage widths are respected */
  width: 100%;
}

.custom-datatable .dataTables_scrollHead th.frozen-column,
.custom-datatable .dataTables_scrollBody td.frozen-column {
  position: sticky;
  background-color: white;
  z-index: 1;
}

/* Set width for frozen columns */
.custom-datatable .dataTables_scrollHead th:nth-child(-n+6),
.custom-datatable .dataTables_scrollBody td:nth-child(-n+6) {
  width: max(5%, 10px); /* Use whichever is larger: 5% of table width or 10px */
  min-width: 10px;
  max-width: 100px; /* Prevent columns from becoming too wide on large screens */
  font-size: 0.75rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
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
  left: 0;
}

.custom-datatable .dataTables_scrollHead th:nth-child(2),
.custom-datatable .dataTables_scrollBody td:nth-child(2) { left: 100px; }

.custom-datatable .dataTables_scrollHead th:nth-child(3),
.custom-datatable .dataTables_scrollBody td:nth-child(3) { left: 200px; }

.custom-datatable .dataTables_scrollHead th:nth-child(4),
.custom-datatable .dataTables_scrollBody td:nth-child(4) { left: 300px; }

.custom-datatable .dataTables_scrollHead th:nth-child(5),
.custom-datatable .dataTables_scrollBody td:nth-child(5) { left: 400px; }

.custom-datatable .dataTables_scrollHead th:nth-child(6),
.custom-datatable .dataTables_scrollBody td:nth-child(6) { left: 500px; }

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
  font-size: 0.75rem;
}

/* Improve readability of table headers */
.custom-datatable thead th {
  font-weight: bold;
  background-color: #f8f9fa;
}

/* Add some padding to table cells for better spacing */
.custom-datatable td,
.custom-datatable th {
  padding: 6px;
}

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
    font-size: 0.7rem;
  }
  
  .custom-datatable .dataTables_scrollHead th:nth-child(-n+6),
  .custom-datatable .dataTables_scrollBody td:nth-child(-n+6) {
    font-size: 0.65rem;
    padding: 3px;
  }
}

.custom-datatable input[type="number"]:disabled {
    background-color: #e9ecef;
    cursor: not-allowed;
  }

  .blur-overlay {
    filter: blur(5px);
    pointer-events: none;
    user-select: none;
  }

.message-box-enter-active,
.message-box-leave-active {
  transition: opacity 0.5s;
}
.message-box-enter-from,
.message-box-leave-to {
  opacity: 0;
}
</style>