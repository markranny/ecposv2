
<script setup>
import { defineProps } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import { ref, computed, isRef, unref } from 'vue';
DataTable.use(DataTablesCore);

const emit = defineEmits();

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  startDate: String,
  endDate: String,
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

const groupedOrders = computed(() => {
  return props.orders.reduce((acc, order) => {
    const { STORENAME, ITEMID, ITEMNAME, CATEGORY, COUNTED, STOREID } = order;
    const counted = parseInt((COUNTED ?? '').trim(), 10) || 0;

    if (!acc[ITEMID]) {
      acc[ITEMID] = {
        ITEMID,
        ITEMNAME,
        CATEGORY,
        TOTAL: 0,
      };
    }

    if (!acc[ITEMID][STORENAME]) {
      acc[ITEMID][STORENAME] = { count: 0, STOREID };
    }

    acc[ITEMID][STORENAME].count += counted;
    acc[ITEMID].TOTAL += counted;

    return acc;
  }, {});
});

const flattenedOrders = computed(() => {
  return Object.values(groupedOrders.value);
});

const columns = computed(() => {
  const baseColumns = [
    { title: 'ITEMID', data: 'ITEMID' },
    { title: 'ITEMS', data: 'ITEMNAME' },
    { title: 'CATEGORY', data: 'CATEGORY' },
    { title: 'TOTAL', data: 'TOTAL' },
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
            render: (data) => data?.count || 0
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


const StartDate = ref(null);
const formattedDate1 = computed(() => {
const startDate = unref(StartDate);
if (startDate) {
  const date = new Date(startDate);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}
return '';
});

const EndDate = ref(null);
const formattedDate2 = computed(() => {
const endDate = unref(EndDate);
if (endDate) {
  const date = new Date(endDate);
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

</script>

<template>
<Main active-tab="REPORTS">
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

          <p class="font-bold text:navy font-xs">{{ startDate }} | {{ endDate }}</p>
          
        </div>
      </div>
      
      <DataTable :data="flattenedOrders" :columns="columns" class="w-full relative display" :options="options">
        <template #action="data">
          <div class="flex justify-start">
          </div>
        </template>
      </DataTable>
    </TableContainer>
  </template>
</Main>
</template>


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
</script>