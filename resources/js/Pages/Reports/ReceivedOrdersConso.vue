
<script setup>
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Reset from "@/Components/Resetter/reset.vue";
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import Refresh from "@/Components/Svgs/Refresh.vue";
import Warning from "@/Components/Svgs/Warning.vue";
import { ref, computed, isRef, unref, toRefs } from 'vue';
DataTable.use(DataTablesCore);

const emit = defineEmits();

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  auth: {
        type: Object,
        required: true,
    },
  noorders: {
    type: Array,
    required: true,
  },
  userRole: {
        type: String,
        required: true
    }
});

const layoutComponent = computed(() => {
    console.log('userRole value:', props.userRole);
    console.log('Is Store?:', props.userRole.toUpperCase() === 'STORE');
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});


const form = useForm({
  StartDate: '',
  EndDate: '',
});

const submitForm = () => {
form.get(route('receivedorderconso.getrange'), {
  preserveScroll: true,
});
};
const toggleActive = () => {
  emit('toggleActive');
};

const groupedOrders = computed(() => {
  const grouped = props.orders.reduce((acc, order) => {
    const { STORENAME, ITEMID, ITEMNAME, CATEGORY, COUNTED, STOREID, stocks, movementstocks } = order;
    const counted = parseInt((COUNTED ?? '').trim(), 10) || 0;

    // Skip this order if ITEMID is null or undefined
    if (ITEMID == null) return acc;

    if (!acc[ITEMID]) {
      acc[ITEMID] = {
        ITEMID,
        ITEMNAME,
        CATEGORY,
        stocks: parseInt(stocks) || 0,
        movementstocks: parseInt(movementstocks) || 0,
        TOTAL: 0,
      };
    }

    if (!acc[ITEMID][STORENAME]) {
      acc[ITEMID][STORENAME] = { count: 0, STOREID };
    }

    acc[ITEMID][STORENAME].count += counted;
    acc[ITEMID].TOTAL += counted;
    acc[ITEMID].BalanceCount = acc[ITEMID].stocks - acc[ITEMID].TOTAL;

    return acc;
  }, {});

  const groupedArray = Object.values(grouped);

  // Custom sorting function
  const customSort = (a, b) => {
    const aID = a.ITEMID || '';
    const bID = b.ITEMID || '';
    
    // Check if both IDs start with "BW"
    if (aID.startsWith("BW") && bID.startsWith("BW")) {
      const aNum = parseInt(aID.slice(2));
      const bNum = parseInt(bID.slice(2));
      
      // If both numbers are less than or equal to 10, sort numerically
      if (aNum <= 10 && bNum <= 10) {
        return aNum - bNum;
      }
      // If one is less than or equal to 10 and the other isn't, the smaller one comes first
      else if (aNum <= 10) {
        return -1;
      }
      else if (bNum <= 10) {
        return 1;
      }
    }
    
    // For all other cases, use default string comparison
    return aID.localeCompare(bID);
  };

  // Sort the array
  return groupedArray.sort(customSort);
});

const flattenedOrders = computed(() => {
  return Object.values(groupedOrders.value);
});

const storeNames = computed(() => {
  const names = new Set();
  flattenedOrders.value.forEach(order => {
    Object.keys(order).forEach(key => {
      if (typeof order[key] === 'object' && order[key] !== null && 'count' in order[key]) {
        names.add(key);
      }
    });
  });
  return Array.from(names).sort((a, b) => {
    const aId = a.split(' - ')[0];
    const bId = b.split(' - ')[0];
    return aId.localeCompare(bId, undefined, { numeric: true, sensitivity: 'base' });
  });
});

const columnTotals = computed(() => {
  const totals = {
    ITEMID: 'Grand Total',
    ITEMNAME: '',
    CATEGORY: '',
    TOTAL: 0
  };

  storeNames.value.forEach(store => {
    totals[store] = 0;
  });

  flattenedOrders.value.forEach(order => {
    totals.TOTAL += order.TOTAL;

    storeNames.value.forEach(store => {
      totals[store] += order[store]?.count || 0;
    });
  });

  return totals;
});

const columns = computed(() => {
  const baseColumns = [
    { 
      title: 'ITEMID', 
      data: 'ITEMID',
      className: 'frozen-column',
      footer: () => columnTotals.value.ITEMID,
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
    { 
      title: 'CATEGORY', 
      data: 'CATEGORY',
      className: 'frozen-column',
      footer: () => '',
      width: '120px'
    },
    {
      title: 'TOTAL',
      data: 'TOTAL',
      footer: () => columnTotals.value.TOTAL
    }
  ];

  const storeColumns = [];
  const storeSet = new Set();

  flattenedOrders.value.forEach(order => {
    Object.entries(order).forEach(([key, value]) => {
      if (typeof value === 'object' && value !== null && 'count' in value && 'STOREID' in value) {
        if (!storeSet.has(key)) {
          storeSet.add(key);
          storeColumns.push({
            title: `${value.STOREID}<br>${key}`,
            data: key,
            render: (data, type) => {
              if (type === 'sort' || type === 'type') {
                return data?.count || 0;
              }
              if (type === 'display') {
                return `${data?.count || 0}`;
              }
              return data?.count || 0;
            },
            footer: () => columnTotals.value[key]
          });
        }
      }
    });
  });

  storeColumns.sort((a, b) => {
    const aId = a.title.split('<br>')[0];
    const bId = b.title.split('<br>')[0];
    return aId.localeCompare(bId, undefined, { numeric: true, sensitivity: 'base' });
  });

  return [...baseColumns, ...storeColumns];
});


const options = {
  paging: false,
  scrollX: true,
  scrollY: "60vh",
  scrollCollapse: true,
  fixedColumns: {
    start: 3, 
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

function generateTextFileContent(flattenedOrders, columns) {
const headerRow = columns.map(column => column.title).join(',');
const dataRows = flattenedOrders.map(order => {
  const rowData = columns.map(column => order[column.data] || '');
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
const content = generateTextFileContent(flattenedOrders, columns);
downloadTextFile(filename, content);
}


const showResetModal = ref(false);

const toggleResetModal = () => {
    showResetModal.value = true;
};

const ResetModalHandler = () => {
  showResetModal.value = false;
};

const { user } = toRefs(props.auth);
const userRole = ref(user.value.role);
const isAdmin = computed(() => userRole.value === 'ADMIN');

const SYNCFG = () => {
    const userConfirmed = window.confirm('Reset Stocks');

    if (userConfirmed) {
        window.location.href = '/getcurrentstocks';
    } else {
        console.log('User cancelled the post operation.');
    }
};

const FIXED = () => {
    const userConfirmed = window.confirm('AUTO POST');

    if (userConfirmed) {
        window.location.href = '/autopost';
    } else {
        console.log('User cancelled the post operation.');
    }
};
</script>

<template>
<component :is="layoutComponent" active-tab="REPORTS">
  <template v-slot:modals>
            <Reset
                :show-modal="showResetModal"
                @toggle-active="ResetModalHandler"
            />
    </template>

  <template v-slot:main>
    <TableContainer>
      <div class="absolute adjust">

        <div class="flex justify-start items-center">
         
          <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
              <input type="hidden" name="_token" :value="$page.props.csrf_token">
              <div date-rangepicker  class="flex items-center">
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
          </TransparentButton>

          <details className="dropdown">
            <summary className="btn m-1 !bg-navy !text-white">TYPES</summary>
            <ul className="menu dropdown-content !bg-gray-100 rounded-box z-[1] w-52 p-2 shadow">
              <li><a href="/orderingconso">BW PRODUCTS</a></li>
              <li><a href="/receivedwarehouseconso">WAREHOUSE</a></li>
            </ul>
          </details>
              
          <details className="dropdown">
            <summary className="btn m-1 !bg-navy !text-white">RECENT</summary>
            <ul className="menu dropdown-content !bg-gray-100 rounded-box z-[1] w-52 p-2 shadow">
              <li><a href="/lastmonth">Last Month</a></li>
              <li><a href="/lastweek">Last Week</a></li>
              <li><a href="/yesterday">Yesterday</a></li>
            </ul>
          </details>

          <SuccessButton
                type="button"
                @click="exportToExcel"
                class="m-6 bg-green"
              >
                <ExcelIcon class="h-4" />
          </SuccessButton>

          <PrimaryButton
                type="button"
                v-if="isAdmin"
                @click="SYNCFG"
                class="bg-red-900"
              >
                 <Refresh class="h-4" />
          </PrimaryButton>
          
        </div>
      </div>
      
      <div class="custom-datatable">
        <DataTable :data="flattenedOrders" :columns="columns" class="w-full relative display" :options="options">
        <template #action="data">
          <div class="flex justify-start">
          </div>
        </template>
      </DataTable>
      </div>
      
    </TableContainer>
  </template>
</component>
</template>

<style>
div.dt-scroll-foot{
  font-weight: bold;
  background-color: #7c0000;
  color: #ddd;
}
</style>


<!-- <script>
import ExcelJS from 'exceljs';

export default {
props: {
  orders: {
    type: Array,
    required: true,
    default: () => []
  },
},

computed: {
  flattenedOrders() {
    return this.calculateFlattenedOrders(this.orders);
  },
  grandTotal() {
    return this.flattenedOrders.reduce((sum, order) => sum + (order.TOTAL || 0), 0);
  }
},

methods: {
  calculateFlattenedOrders(orders) {
    console.log('Calculating flattenedOrders');
    console.log('Input orders:', orders);

    if (!orders || !Array.isArray(orders)) {
      console.error('Invalid orders data');
      return [];
    }

    function compareSTOREID(a, b) {
      if (!a.STOREID || !b.STOREID) {
        console.warn('Missing STOREID:', a, b);
        return 0;
      }

      const aId = a.STOREID.toString();
      const bId = b.STOREID.toString();

      if (!isNaN(aId) && !isNaN(bId)) {
        return parseInt(aId) - parseInt(bId);
      }

      return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
    }

    orders.sort(compareSTOREID);

    const groupedOrders = orders.reduce((acc, order) => {
      if (order && typeof order === 'object') {
        const STORENAME = `${order.STOREID} - ${order.STORENAME}`; 
        const itemName = order.ITEMNAME || '';
        const ITEMID = order.ITEMID || '';
        const CATEGORY = order.CATEGORY || '';
        const counted = order.COUNTED ? parseInt(order.COUNTED.trim()) || 0 : 0;

        if (!acc[itemName]) {
          acc[itemName] = { ITEMID, ITEMNAME: itemName, CATEGORY };
        }

        if (!acc[itemName][STORENAME]) {
          acc[itemName][STORENAME] = 0;
        }

        acc[itemName][STORENAME] += counted;
      }
      return acc;
    }, {});

    const result = Object.values(groupedOrders).map(item => {
      let total = 0;
      const sortedItem = {};
      Object.keys(item)
        .sort((a, b) => {
          if (a === 'ITEMID' || a === 'ITEMNAME' || a === 'CATEGORY') return -1;
          if (b === 'ITEMID' || b === 'ITEMNAME' || b === 'CATEGORY') return 1;
          const aId = a.split(' - ')[0];
          const bId = b.split(' - ')[0];
          if (!isNaN(aId) && !isNaN(bId)) {
            return parseInt(aId) - parseInt(bId);
          }
          return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
        })
        .forEach(key => {
          if (key !== 'ITEMID' && key !== 'ITEMNAME' && key !== 'CATEGORY') {
            total += item[key];
          }
          sortedItem[key] = item[key];
        });
      return { ...sortedItem, TOTAL: total };
    });

    console.log('Calculated flattenedOrders:', result);
    return result;
  },

  async exportToExcel() {
  console.log('Starting exportToExcel');
  console.log('flattenedOrders:', this.flattenedOrders);

  try {
    if (!this.flattenedOrders || !Array.isArray(this.flattenedOrders) || this.flattenedOrders.length === 0) {
      console.error('No data to export');
      return;
    }

    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Flattened Orders');

    const columns = [
      { header: 'ITEMID', key: 'ITEMID', width: 20 },
      { header: 'ITEMNAME', key: 'ITEMNAME', width: 25 },
      { header: 'CATEGORY', key: 'CATEGORY', width: 20 },
    ];

    const storeNames = new Set();
    this.flattenedOrders.forEach(order => {
      if (order && typeof order === 'object') {
        Object.keys(order).forEach(key => {
          if (key !== 'ITEMID' && key !== 'ITEMNAME' && key !== 'CATEGORY' && key !== 'TOTAL') {
            storeNames.add(key);
          }
        });
      }
    });

    const sortedStoreNames = Array.from(storeNames).sort((a, b) => {
      const aId = a.split(' - ')[0];
      const bId = b.split(' - ')[0];
      
      if (!isNaN(aId) && !isNaN(bId)) {
        return parseInt(aId) - parseInt(bId);
      }
      
      return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
    });

    sortedStoreNames.forEach(storeName => {
      columns.push({ header: storeName, key: storeName, width: 25 });
    });

    columns.push({ header: 'TOTAL', key: 'TOTAL', width: 15 });

    worksheet.columns = columns;

    this.flattenedOrders.forEach(order => {
      if (order && typeof order === 'object') {
        const row = {
          ITEMID: order.ITEMID || '',
          ITEMNAME: order.ITEMNAME || '',
          CATEGORY: order.CATEGORY || '',
        };
        sortedStoreNames.forEach(storeName => {
          row[storeName] = order[storeName] || 0;
        });
        row['TOTAL'] = order.TOTAL || 0;
        worksheet.addRow(row);
      }
    });

    const columnTotals = {
      ITEMID: 'Grand Total',
      ITEMNAME: '',
      CATEGORY: '',
    };

    sortedStoreNames.forEach(storeName => {
      columnTotals[storeName] = this.flattenedOrders.reduce((sum, order) => sum + (order[storeName] || 0), 0);
    });

    columnTotals['TOTAL'] = this.flattenedOrders.reduce((sum, order) => sum + (order.TOTAL || 0), 0);

    worksheet.addRow(columnTotals);

    const totalsRow = worksheet.lastRow;
    totalsRow.eachCell((cell) => {
      cell.font = { bold: true };
      cell.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFFF00' }
      };
    });

    const today = new Date();
    const filename = `FlattenedOrders_${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}.xlsx`;
    const buffer = await workbook.xlsx.writeBuffer();
    this.saveExcelFile(buffer, filename);
  } catch (error) {
    console.error('Error exporting to Excel:', error);
  }
},
  

  saveExcelFile(buffer, filename) {
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  },
}
};

const mgcount = () => {
window.location.href = '/mgcount';
};
</script> -->


<script>
import ExcelJS from 'exceljs';

export default {
props: {
  orders: {
    type: Array,
    required: true,
    default: () => []
  },
},

computed: {
  flattenedOrders() {
    return this.calculateFlattenedOrders(this.orders);
  },
  grandTotal() {
    return this.flattenedOrders.reduce((sum, order) => sum + (order.TOTAL || 0), 0);
  }
},

methods: {
  calculateFlattenedOrders(orders) {
    console.log('Calculating flattenedOrders');
    console.log('Input orders:', orders);

    if (!orders || !Array.isArray(orders)) {
      console.error('Invalid orders data');
      return [];
    }

    // Improved sorting function
    function compareSTOREID(a, b) {
      if (!a.STOREID || !b.STOREID) {
        console.warn('Missing STOREID:', a, b);
        return 0;
      }

      const aId = a.STOREID.toString();
      const bId = b.STOREID.toString();

      if (!isNaN(aId) && !isNaN(bId)) {
        return parseInt(aId) - parseInt(bId);
      }

      return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
    }

    // Sort orders by STOREID
    orders.sort(compareSTOREID);

    const groupedOrders = orders.reduce((acc, order) => {
      if (order && typeof order === 'object') {
        const STORENAME = `${order.STOREID} - ${order.STORENAME}`; 
        const itemName = order.ITEMNAME || '';
        const ITEMID = order.ITEMID || '';
        const CATEGORY = order.CATEGORY || '';
        const counted = order.COUNTED ? parseInt(order.COUNTED.trim()) || 0 : 0;

        if (!acc[itemName]) {
          acc[itemName] = { ITEMID, ITEMNAME: itemName, CATEGORY };
        }

        if (!acc[itemName][STORENAME]) {
          acc[itemName][STORENAME] = 0;
        }

        acc[itemName][STORENAME] += counted;
      }
      return acc;
    }, {});

    const result = Object.values(groupedOrders).map(item => {
      let total = 0;
      const sortedItem = {};
      Object.keys(item)
        .sort((a, b) => {
          if (a === 'ITEMID' || a === 'ITEMNAME' || a === 'CATEGORY') return -1;
          if (b === 'ITEMID' || b === 'ITEMNAME' || b === 'CATEGORY') return 1;
          const aId = a.split(' - ')[0];
          const bId = b.split(' - ')[0];
          if (!isNaN(aId) && !isNaN(bId)) {
            return parseInt(aId) - parseInt(bId);
          }
          return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
        })
        .forEach(key => {
          if (key !== 'ITEMID' && key !== 'ITEMNAME' && key !== 'CATEGORY') {
            total += item[key];
          }
          sortedItem[key] = item[key];
        });
      return { ...sortedItem, TOTAL: total };
    });

    console.log('Calculated flattenedOrders:', result);
    return result;
  },

  async exportToExcel() {
    console.log('Starting exportToExcel');
    console.log('flattenedOrders:', this.flattenedOrders);

    try {
      if (!this.flattenedOrders || !Array.isArray(this.flattenedOrders) || this.flattenedOrders.length === 0) {
        console.error('No data to export');
        // You might want to show a message to the user here
        return;
      }

      const workbook = new ExcelJS.Workbook();
      const worksheet = workbook.addWorksheet('Flattened Orders');

      const columns = [
        { header: 'ITEMID', key: 'ITEMID', width: 20 },
        { header: 'ITEMNAME', key: 'ITEMNAME', width: 25 },
        { header: 'CATEGORY', key: 'CATEGORY', width: 20 },
        { header: 'TOTAL', key: 'TOTAL', width: 15 },
      ];

      const storeNames = new Set();
      this.flattenedOrders.forEach(order => {
        if (order && typeof order === 'object') {
          Object.keys(order).forEach(key => {
            if (key !== 'ITEMID' && key !== 'ITEMNAME' && key !== 'CATEGORY' && key !== 'TOTAL') {
              storeNames.add(key);
            }
          });
        }
      });

      // Improved store names sorting
      const sortedStoreNames = Array.from(storeNames).sort((a, b) => {
        const aId = a.split(' - ')[0];
        const bId = b.split(' - ')[0];
        
        if (!isNaN(aId) && !isNaN(bId)) {
          return parseInt(aId) - parseInt(bId);
        }
        
        return aId.localeCompare(bId, undefined, {numeric: true, sensitivity: 'base'});
      });

      sortedStoreNames.forEach(storeName => {
        columns.push({ header: storeName, key: storeName, width: 25 });
      });

      worksheet.columns = columns;

      this.flattenedOrders.forEach(order => {
        if (order && typeof order === 'object') {
          const row = {
            ITEMID: order.ITEMID || '',
            ITEMNAME: order.ITEMNAME || '',
            CATEGORY: order.CATEGORY || '',
            TOTAL: order.TOTAL || 0,
          };
          sortedStoreNames.forEach(storeName => {
            row[storeName] = order[storeName] || 0;
          });
          worksheet.addRow(row);
        }
      });

      const today = new Date();
      const filename = `FlattenedOrders_${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}.xlsx`;
      const buffer = await workbook.xlsx.writeBuffer();
      this.saveExcelFile(buffer, filename);
    } catch (error) {
      console.error('Error exporting to Excel:', error);
      // You might want to show an error message to the user here
    }
  },

  saveExcelFile(buffer, filename) {
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
  },
}
};

const mgcount = () => {
window.location.href = '/mgcount';
};
</script>

<style>
.table.dataTable thead tr > .dtfc-fixed-start, table.dataTable thead tr > .dtfc-fixed-end, table.dataTable tfoot tr > .dtfc-fixed-start, table.dataTable tfoot tr > .dtfc-fixed-end
{
    top: 0;
    bottom: 0;
    z-index: 3;
    background-color: #02721a;
}
</style>