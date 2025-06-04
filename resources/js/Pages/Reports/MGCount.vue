<script setup>
import { defineProps, defineEmits } from 'vue';
import { useForm } from '@inertiajs/vue3';
import CopyFrom from "@/Components/OpicModal/CopyFrom.vue";
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
import SelectOption from '@/Components/SelectOption/SelectOption.vue';
/* import 'datatables.net-fixedcolumns'; */
import 'datatables.net-fixedcolumns';
import { ref, computed, isRef, unref, reactive, onMounted } from 'vue';
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
    routes: {
    type: String,
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
  order: [[1, 'asc']],
  fixedColumns: {
    start: 6,
  },
  
  error: function (xhr, error, thrown) {
    console.error("DataTables error:", error);
  },
  footerCallback: function(tfoot, data, start, end, display) {
    const api = this.api();
    const footerRow = api.table().footer().querySelectorAll('th');
    columns.value.forEach((column, index) => {
      if (column.footer) {
        footerRow[index].textContent = column.footer();
      }
    });
  },
  
};


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

const footerTotals = computed(() => {
  const totals = {
    ITEMID: 'Grand Total',
    ITEMNAME: '',
    CATEGORY: '',
    MGCount: 0,
    BalanceCount: 0,
    TOTAL: 0
  };

  storeNames.value.forEach(store => {
    totals[store] = 0;
  });

  processedOrders.value.forEach(order => {
    totals.MGCount += Number(order.MGCount) || 0;
    totals.BalanceCount += Number(order.BalanceCount) || 0;
    totals.TOTAL += Number(order.TOTAL) || 0;

    storeNames.value.forEach(store => {
      totals[store] += Number(order[store]) || 0;
    });
  });

  return totals;
});

const columns = computed(() => [
  { 
    title: 'ITEMID', 
    data: 'ITEMID',
    className: 'frozen-column',
    footer: () => footerTotals.value.ITEMID,
    width: '120px'
  },
  { 
    title: 'ITEMS', 
    data: 'ITEMNAME',
    className: 'frozen-column',
    footer: () => '',
    orderable: true,
    width: '200px'
  },
  /* { 
    title: 'POSTEDDATETIME', 
    data: 'POSTEDDATETIME',
    className: 'frozen-column',
    footer: () => '',
    orderable: true
  }, */
  { 
    title: 'CATEGORY', 
    data: 'CATEGORY',
    className: 'frozen-column',
    footer: () => '',
    width: '120px'
  },
  
  { 
    title: 'ACTUAL INV COUNT', 
    data: 'MGCount',
    className: 'frozen-column',
    footer: () => footerTotals.value.MGCount,
    width: '180px'
  },
  { 
    title: 'REMAINING STOCKS', 
    data: 'BalanceCount',
    className: 'frozen-column',
    footer: () => footerTotals.value.BalanceCount,
    width: '120px'
  },
  { 
    title: 'TOTAL ALLOCATION', 
    data: 'TOTAL',
    className: 'frozen-column',
    footer: () => footerTotals.value.TOTAL,
    width: '130px'
  },
  /* ...Array.from(storeNames.value).map(storeName => ({ 
    title: storeName, 
    data: storeName,
    render: (data, type, row) => {
      if (type === 'display') {
        return `<input type="number" value="${data}" onchange="window.updateCounted('${row.ITEMID}', '${storeName}', this.value)" />`;
      }
      return data;
    },
    footer: () => footerTotals.value[storeName]
  })), */
  ...Array.from(storeNames.value).map(storeName => ({ 
  title: storeName, 
  data: storeName,
  render: (data, type, row) => {
    if (type === 'sort' || type === 'type') {
      return data;
    }
    if (type === 'display') {
      return `<input type="number" value="${data}" onchange="window.updateCounted('${row.ITEMID}', '${storeName}', this.value)" />`;
    }
    return data;
  },
  footer: () => footerTotals.value[storeName]
})),
]);


const showMessage = ref(false);
const messageText = ref('');
const saveAllData = () => {
  isLoading.value = true;
  disableAllInputs(true);

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
      isLoading.value = false;
      disableAllInputs(false);
    });
};

const disableAllInputs = (disabled) => {
  const inputs = document.querySelectorAll('input, button');
  inputs.forEach(input => {
    input.disabled = disabled;
  });
};

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
      console.log('MGCount update response:', response.data);
      if (response.data.success) {
        console.log('MGCount updated successfully');
      } else {
        console.error('Failed to update MGCount:', response.data.message);
      }
    })
    .catch(error => {
      console.error('Error updating MGCount:', error.response ? error.response.data : error);
    });
  }
}

window.updateCounted = function(itemId, storeName, value) {
  const item = groupedOrders[itemId];
  if (item) {
    item[storeName] = parseInt(value, 10) || 0;
    
    item.TOTAL = Array.from(storeNames.value).reduce((sum, store) => {
      return sum + (item[store] || 0);
    }, 0);
    
    item.BalanceCount = item.MGCount - item.TOTAL;

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

/* async function exportToExcel() {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Flattened Orders');
    worksheet.columns = columns.value.map(column => ({
      header: column.title,
      key: column.data,
      width: 15
    }));

    worksheet.addRow(columns.value.map(column => column.title));

    const filteredOrders = processedOrders.value.filter(order => {
      const storeCounts = Array.from(storeNames.value).map(store => order[store] || 0);
      return storeCounts.some(count => count > 0);
    });

    filteredOrders.forEach(order => {
      const rowData = columns.value.map(column => {
        const value = order[column.data];
        return value !== null && value !== undefined ? value : 0;
      });
      worksheet.addRow(rowData);
    });

    const filteredTotals = columns.value.reduce((acc, column) => {
      if (column.footer) {
        acc[column.data] = filteredOrders.reduce((sum, order) => {
          return sum + (Number(order[column.data]) || 0);
        }, 0);
      }
      return acc;
    }, {});

    const footerRow = worksheet.addRow(columns.value.map(column => {
      if (column.footer) {
        return filteredTotals[column.data] || 0;
      }
      return '';
    }));
    footerRow.font = { bold: true };
    footerRow.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF7C0000' }
    };
    footerRow.font = { color: { argb: 'FFDDDDDD' } };

    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FFF8F9FA' }
    };

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
} */

async function exportToExcel() {
  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Flattened Orders');
    
    // Define the columns
    worksheet.columns = columns.value.map(column => ({
      header: column.title,
      key: column.data,
      width: 15
    }));

    // Add the header row
    worksheet.addRow(columns.value.map(column => column.title));

    // Filter and sort the data
    const filteredAndSortedOrders = processedOrders.value
      .filter(order => {
        const storeCounts = Array.from(storeNames.value).map(store => order[store] || 0);
        return storeCounts.some(count => count > 0);
      })
      .sort((a, b) => {
        // Sort by Category first
        if (a.CATEGORY < b.CATEGORY) return -1;
        if (a.CATEGORY > b.CATEGORY) return 1;
        
        // If Categories are the same, sort by ITEMNAME
        if (a.ITEMNAME < b.ITEMNAME) return -1;
        if (a.ITEMNAME > b.ITEMNAME) return 1;
        
        return 0;
      });

    // Add the sorted data to the worksheet
    filteredAndSortedOrders.forEach(order => {
      const rowData = columns.value.map(column => {
        const value = order[column.data];
        return value !== null && value !== undefined ? value : 0;
      });
      worksheet.addRow(rowData);
    });

    // Add the footer row with totals
    const filteredTotals = columns.value.reduce((acc, column) => {
      if (column.footer) {
        acc[column.data] = filteredAndSortedOrders.reduce((sum, order) => {
          return sum + (Number(order[column.data]) || 0);
        }, 0);
      }
      return acc;
    }, {});

    const footerRow = worksheet.addRow(columns.value.map(column => {
      if (column.footer) {
        return filteredTotals[column.data] || 0;
      }
      return '';
    }));
    footerRow.font = { bold: true };
    footerRow.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF7C0000' }
    };
    footerRow.font = { color: { argb: 'FFDDDDDD' } };

    // Style the header row
    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FFF8F9FA' }
    };

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

const specialorder = () => { 
    window.location.href = '/special-orders';
};

const inputpartycakes = () => { 
    window.location.href = '/add-partycakes';
};

const details = () => { 
    window.location.href = '/dispatch/add-details';
};

const inventory = () => { 
    window.location.href = '/inventory';
};

const postorders = () => {
    const userConfirmed = window.confirm('Are you sure you want to post all process?');

    if (userConfirmed) {
        window.location.href = '/opic/post';
    } else {
        console.log('User cancelled the post operation.');
    }
};

const showGetCFModal = ref(false);

const toggleGetCFModal = () => {
    showGetCFModal.value = true;
    console.log(JOURNALID.value);
};

const GetCFModalHandler = () => {
    showGetCFModal.value = false;
};

const SYNCFG = () => {
    const userConfirmed = window.confirm('Get Finish Goods!');

    if (userConfirmed) {
        window.location.href = '/finish-goods/sync';
    } else {
        console.log('User cancelled the post operation.');
    }
};

/* const SYNCFG = () => { 
    window.location.href = '/finish-goods/sync';
}; */

</script>

<template>
  <div v-if="showMessage" class="fixed top-5 right-5 bg-green-900 text-white border border-gray-300 rounded-lg shadow-lg p-4 z-50">
    {{ messageText }}
  </div>
  <Main active-tab="FGCOUNT">

    <template v-slot:modals>
            <CopyFrom
                :show-modal="showGetCFModal"
                @toggle-active="GetCFModalHandler"
            />
        </template>
    <template v-slot:main>
      <TableContainer>
        <div class="absolute adjust">
          <div class="flex justify-start items-center">
            <SuccessButton
              type="button"
              @click="exportToExcel"
              class="ml-6 bg-green"
            >
              <ExcelIcon class="h-4" />
            </SuccessButton>

            <PrimaryButton
              type="button"
              @click="picklist"
              class="m-1 bg-navy"
            >
               BREAD&PASTRIES
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="cakes"
              class="m-1 bg-navy"
            >
               CAKELAB
            </PrimaryButton>

            <PrimaryButton
            type="button"
            @click="specialorder"
            class="ml-2 bg-navy "
            >
            SPECIAL ORDER
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="details"
              class="m-2 sm:bg-red-900"
            >
                DETAILS
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="SYNCFG"
              class="sm:bg-red-900"
            >
                SYNC FG
            </PrimaryButton>

              <!-- <details className="dropdown">
                <summary className="btn m-1">Select Route</summary>
                <ul className="menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                  <li><a href="/mgcount">ALL</a></li>
                  <li><a href="/south1">SOUTH 1</a></li>
                  <li><a href="/south2">SOUTH 2</a></li>
                  <li><a href="/south3">SOUTH 3</a></li>
                  <li><a href="/north1">NORTH 1</a></li>
                  <li><a href="/north2">NORTH 2</a></li>
                  <li><a href="/central">CENTRAL</a></li>
                  <li><a href="/east">EAST</a></li>
                </ul>
              </details> -->

              <!-- <h6 class="ml-2">{{ routes }}</h6> -->
          </div>
        </div>
        
        <div class="custom-datatable">
          <div :class="{ 'blur-overlay': isLoading }">  
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
          </div>
        </div>
        
      </TableContainer>
    </template>
  </Main>
</template>
<style>
.table.dataTable thead tr > .dtfc-fixed-start, table.dataTable thead tr > .dtfc-fixed-end, table.dataTable tfoot tr > .dtfc-fixed-start, table.dataTable tfoot tr > .dtfc-fixed-end
{
    top: 0;
    bottom: 0;
    z-index: 3;
    background-color: #7c0000;
}
</style>

<style scoped>
.custom-datatable ::v-deep table.dataTable {
  font-size: 0.85rem;
  table-layout: fixed;
  width: 100%;
}

.custom-datatable ::v-deep thead th {
  font-weight: bold;
  background-color: #f8f9fa;
  color: #000;
  position: sticky;
  top: 0;
  z-index: 2;
  height: auto; /* Allow the height to adjust */
  padding: 10px 5px; /* Increase padding */
  white-space: normal; /* Allow text to wrap */
  vertical-align: top; /* Align text to top */
}

.custom-datatable ::v-deep .dataTables_scrollHead th.frozen-column,
.custom-datatable ::v-deep .dataTables_scrollBody td.frozen-column {
  position: sticky;
  background-color: white;
  z-index: 1;
}

.custom-datatable ::v-deep th, 
.custom-datatable ::v-deep td {
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(-n+6),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(-n+6) {
  position: sticky;
  background-color: white;
  z-index: 1;
}

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(1),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(1) { left: 0; width: 100px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(2),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(2) { left: 100px; width: 200px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(3),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(3) { left: 300px; width: 150px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(4),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(4) { left: 450px; width: 120px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(5),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(5) { left: 570px; width: 120px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(6),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(6) { left: 690px; width: 120px; }

.custom-datatable ::v-deep .dataTables_scrollHead th:nth-child(-n+6),
.custom-datatable ::v-deep .dataTables_scrollBody td:nth-child(-n+6) {
  box-shadow: 2px 0 5px rgba(0,0,0,0.1);
}

.custom-datatable ::v-deep input[type="number"] {
  width: 100%;
  padding: 2px 6px;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
  font-size: 16px;
  color: #333;
  background-color: #fff;
  transition: all 0.3s ease;
}

.custom-datatable ::v-deep thead th {
  font-weight: bold;
  background-color: #f8f9fa;
}

.custom-datatable ::v-deep td,
.custom-datatable ::v-deep th {
  padding: 6px;
}

.custom-datatable ::v-deep tbody tr:nth-of-type(even) {
  background-color: #f3f4f6;
}

.custom-datatable ::v-deep tbody tr:hover {
  background-color: #e5e7eb;
}


.blur-overlay {
  filter: blur(5px);
  pointer-events: none;
  user-select: none;
}

.custom-datatable ::v-deep input[type="number"]:disabled {
  background-color: #e9ecef;
  cursor: not-allowed;
}

.custom-datatable ::v-deep tfoot {
  font-weight: bold;
  background-color: #7c0000;
  color: #ddd;
}

.custom-datatable ::v-deep tfoot td {
  border-top: 2px solid #ddd;
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