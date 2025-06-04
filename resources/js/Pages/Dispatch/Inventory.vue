<script setup>
import { defineProps, ref, computed, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Print from "@/Components/Svgs/PrintColored.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import Inventory from "@/Components/Svgs/Stocks.vue";

DataTable.use(DataTablesCore);

const emit = defineEmits();

const props = defineProps({
  orders: {
    type: Array,
    required: true,
  },
  sheetData: {
    type: Array,
    default: () => []
  }
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
    const { ITEMID, ITEMNAME, CATEGORY, COUNTED, createddatetime } = order;
    const date = new Date(createddatetime).toLocaleDateString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric' });
    const counted = parseInt(COUNTED, 10) || 0;

    if (!acc[ITEMID]) {
      acc[ITEMID] = {
        ITEMID,
        ITEMNAME,
        CATEGORY,
        TOTAL: 0,
        dates: Object.fromEntries(uniqueDates.value.map(date => [date, 0])),
      };
    }

    acc[ITEMID].dates[date] = (acc[ITEMID].dates[date] || 0) + counted;
    acc[ITEMID].TOTAL += counted;

    return acc;
  }, {});
});

const flattenedOrders = computed(() => {
  const orders = Object.values(groupedOrders.value);
  
  // Add total row
  const totalRow = {
    ITEMID: 'TOTAL',
    ITEMNAME: '',
    CATEGORY: '',
    TOTAL: orders.reduce((sum, order) => sum + order.TOTAL, 0),
    dates: Object.fromEntries(uniqueDates.value.map(date => [
      date,
      orders.reduce((sum, order) => sum + (order.dates[date] || 0), 0)
    ])),
  };
  
  orders.push(totalRow);
  
  return orders;
});


const uniqueDates = computed(() => {
  const today = new Date();
  const year = today.getFullYear();
  const month = today.getMonth();
  const lastDay = new Date(year, month + 1, 0).getDate();
  const dates = [];
  
  for (let i = 1; i <= lastDay; i++) {
    const date = new Date(year, month, i);
    dates.push(date.toLocaleDateString('en-US', { month: 'numeric', day: 'numeric', year: 'numeric' }));
  }
  
  return dates;
});

const columns = computed(() => [
  { title: 'ITEMID', data: 'ITEMID' },
  { title: 'ITEMNAME', data: 'ITEMNAME' },
  { title: 'CATEGORY', data: 'CATEGORY' },
  { title: 'TOTAL', data: 'TOTAL' },
  ...uniqueDates.value.map(date => ({
    title: date,
    data: null,
    render: (data, type, row) => row.dates[date] || 0
  }))
]);



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


const generateReceiptContent = () => {
  const date = new Date().toLocaleDateString();
  const time = new Date().toLocaleTimeString();
  
  let content = `
    <html>
      <head>
        <title>Order Receipt</title>
        <style>
          body {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            width: 8mm;
            margin: 0;
            padding: 0;
          }
          .center { text-align: center; }
          .bold { font-weight: bold; }
          .underline { border-bottom: 1px solid black; }
        </style>
      </head>
      <body>
        <div class="center bold" style="width: 200px">ORDER RECEIPT</div>
        <div class="center" style="width: 200px">${date} ${time}</div>
  `;

  // Group orders by store
  const ordersByStore = flattenedOrders.value.reduce((acc, order) => {
    Object.entries(order).forEach(([key, value]) => {
      if (typeof value === 'object' && value !== null && 'count' in value && 'STOREID' in value) {
        if (value.count > 0) {
          if (!acc[key]) {
            acc[key] = [];
          }
          acc[key].push({
            ITEMID: order.ITEMID,
            ITEMNAME: order.ITEMNAME,
            count: value.count
          });
        }
      }
    });
    return acc;
  }, {});

  // Generate receipt content for each store
  Object.entries(ordersByStore).forEach(([storeName, items]) => {
    content += `
      <div class="bold" style="width:200px; ">${storeName} Branch:</div>
      <table style="width:90%">
        <tr>
          <th>Item</th>
          <th>Qty</th>
        </tr>
    `;

    items.forEach(item => {
      content += `
        <tr>
          <td style="font-size: 12px">${item.ITEMNAME.substring(0, 12)}...</td>
          <td style="font-size: 12px">${item.count}</td>
        </tr>
      `;
    });

    content += `
      </table>
    `;
  });

  content += `
        <div class="bold" style="width: 200px">Total: ${grandTotal.value}</div>
        <div class="center" style="width: 200px">Thank you for your order!</div>
      </body>
    </html>
  `;

  return content;
};

const grandTotal = computed(() => {
  return flattenedOrders.value.reduce((sum, order) => {
    return sum + Object.values(order).reduce((storeSum, value) => {
      if (typeof value === 'object' && value !== null && 'count' in value) {
        return storeSum + value.count;
      }
      return storeSum;
    }, 0);
  }, 0);
});

const printReceipt = () => {
  const printContent = generateReceiptContent();
  const printWindow = window.open('', '_blank');
  printWindow.document.write(printContent);
  printWindow.document.close();
  printWindow.print();
};

const sync = (journalid) => {
    JOURNALID.value = journalid;
    showGetBWModal.value = true;
    console.log(JOURNALID.value);
};

const sheetData = ref([])

const fetchSheetData = async () => {
  try {
    const response = await axios.get('/api/google-sheet-data')
    sheetData.value = response.data
  } catch (error) {
    console.error('Error fetching sheet data:', error)
  }
}

onMounted(fetchSheetData)
</script>

<template>
<Main active-tab="FG">
  <template v-slot:main>
    <TableContainer>
      <div class="absolute adjust">
        <div class="flex justify-start items-center">
          
        
<a href="#" class="w-full flex flex-col items-center bg-white border border-gray-200 rounded-lg shadow md:flex-row md:max-w-xl hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700">

</a>


        </div>
      </div>

      <!-- <div>
        <table v-if="sheetData.length">
          <thead>
            <tr>
              <th v-for="(header, index) in sheetData[0]" :key="index">{{ header }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(row, rowIndex) in sheetData.slice(1)" :key="rowIndex">
              <td v-for="(cell, cellIndex) in row" :key="cellIndex">{{ cell }}</td>
            </tr>
          </tbody>
        </table>
      </div> -->
      
      <iframe 
        src="https://docs.google.com/spreadsheets/d/1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE/edit?usp=sharing&rm=minimal#gid=951114160" 
        class="w-full" style="height: 90vh;">
      </iframe>

      <!-- <iframe 
        src="https://docs.google.com/spreadsheets/d/1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE/edit?usp=sharing&chrome=false" 
        class="w-full" 
        style="height: 80vh;"
      >
      </iframe> -->

      <!-- <iframe 
        src="https://sheetdb.io/api/v1/w8cvcd56i114j" 
        class="w-full" 
        style="height: 80vh;"
      >
      </iframe> -->

      <!-- <iframe
      src="https://docs.google.com/spreadsheets/d/1qPRNb5MK135DogIi7QlunasYYa_hkYEe50wWCB18yWE/pubhtml?widget=true&amp;headers=false"
      ></iframe> -->

      <!-- <iframe class="airtable-embed" src="https://airtable.com/embed/appsCDdWqtwMxMw29/shrRHOn2wIcCmyYQ6?viewControls=on" 
      frameborder="0" onmousewheel="" width="100%" height="533" style="background: transparent; border: 1px solid #ccc;"></iframe> -->
      
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
    },
    uniqueDates() {
      const datesSet = new Set();
      this.flattenedOrders.forEach(order => {
        if (order.dates) {
          Object.keys(order.dates).forEach(date => datesSet.add(date));
        }
      });
      return Array.from(datesSet).sort();
    }
  },
  methods: {
    calculateFlattenedOrders(orders) {
      return orders.map(order => ({
        ITEMID: order.ITEMID,
        ITEMNAME: order.ITEMNAME,
        CATEGORY: order.CATEGORY,
        TOTAL: order.TOTAL,
        dates: order.dates || {}
      }));
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
        const worksheet = workbook.addWorksheet('Orders');
        // Define columns based on your DataTable structure
        const columns = [
          { header: 'ITEMID', key: 'ITEMID', width: 15 },
          { header: 'ITEMNAME', key: 'ITEMNAME', width: 30 },
          { header: 'CATEGORY', key: 'CATEGORY', width: 20 },
          { header: 'TOTAL', key: 'TOTAL', width: 15 },
        ];
        // Add date columns
        this.uniqueDates.forEach(date => {
          columns.push({ header: date, key: date, width: 15 });
        });
        worksheet.columns = columns;
        // Add data rows
        this.flattenedOrders.forEach(order => {
          const row = {
            ITEMID: order.ITEMID,
            ITEMNAME: order.ITEMNAME,
            CATEGORY: order.CATEGORY,
            TOTAL: order.TOTAL,
          };
          // Add data for each date 
          this.uniqueDates.forEach(date => {
            row[date] = order.dates[date] || 0;
          });
          worksheet.addRow(row);
        });
        // Style the header row
        worksheet.getRow(1).font = { bold: true };
        worksheet.getRow(1).alignment = { vertical: 'middle', horizontal: 'center' };
        const today = new Date();
        const filename = `Orders_${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}.xlsx`;
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
      URL.revokeObjectURL(link.href);
    },
  },
};
</script>