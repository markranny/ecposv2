<script setup>
import { ref, onMounted, computed } from 'vue';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import FixedColumns from 'datatables.net-fixedcolumns';

DataTable.use(DataTablesCore);
DataTable.use(FixedColumns);

const props = defineProps({
  initialItems: {
    type: Array,
    default: () => []
  },
  currentMonth: String,
  JOURNALID: String,
});

const items = ref(props.initialItems.map(item => ({
  ...item,
  quantities: item.quantities || {}
})));
const daysInMonth = ref([]);

const showCreateModal = ref(false);
const showGetBWModal = ref(false);
const showGetCFModal = ref(false);

onMounted(() => {
  console.log('Initial items:', props.initialItems);
  console.log('Processed items:', items.value);

  // Set up days in month
  if (props.currentMonth) {
    const date = new Date(props.currentMonth);
    const lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    daysInMonth.value = Array.from({ length: lastDay }, (_, i) => i + 1);
  }
});

const getQuantity = (item, day) => {
  return item.quantities[day] || 0;
};

const setQuantity = (item, day, value) => {
  item.quantities[day] = Number(value);
};

const itemTotal = (item) => {
  return daysInMonth.value.reduce((total, day) => total + getQuantity(item, day), 0);
};

const dayTotal = (day) => {
  return items.value.reduce((total, item) => total + getQuantity(item, day), 0);
};

const grandTotal = () => {
  return items.value.reduce((total, item) => total + itemTotal(item), 0);
};

const columns = computed(() => [
  { title: 'ITEMS', data: 'name' },
  { title: 'CATEGORY', data: 'category' },
  ...daysInMonth.value.map(day => ({
    title: `${day}/${props.currentMonth.split(' ')[0].substring(0, 3)}`,
    data: null,
    render: (data, type, row) => {
      return `
        <input
          type="number"
          value="${getQuantity(row, day)}"
          onchange="window.setQuantity('${row.id}', ${day}, this.value)"
          class="w-16 px-2 py-1 border rounded"
          min="0"
        >
      `;
    }
  })),
  { 
    title: 'TOTAL', 
    data: null,
    render: (data, type, row) => itemTotal(row)
  }
]);

const options = {
  responsive: true,
  processing: true,
  serverSide: false,
  paging: false,
  scrollX: true,
  scrollY: "70vh",
  scrollCollapse: true,
  searching: true,
  info: true,
  ordering: false,
  fixedColumns: {
    leftColumns: 2  
  }
};

window.setQuantity = (itemId, day, value) => {
  const item = items.value.find(i => i.id === itemId);
  if (item) {
    setQuantity(item, day, value);
  }
};

const updateInventory = () => {
  // Implementation for updating the inventory
  console.log('Updating inventory...');
  // You would typically make an API call here to update the backend
};

async function exportToExcel() {
  console.log('Starting export to Excel');
  
  if (!items.value.length || !daysInMonth.value.length) {
    alert('No data available to export.');
    return;
  }

  try {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Inventory');
    
    console.log('Defining columns');
    worksheet.columns = columns.value.map(column => ({
      header: column.title,
      key: column.title,
      width: 15
    }));

    console.log('Adding header row');
    worksheet.addRow(columns.value.map(column => column.title));

    console.log('Adding data rows');
    items.value.forEach(item => {
      const rowData = columns.value.map(column => {
        if (column.title === 'ITEMS') return item.name;
        if (column.title === 'CATEGORY') return item.category;
        if (column.title === 'TOTAL') return itemTotal(item);
        const day = parseInt(column.title.split('/')[0]);
        return getQuantity(item, day);
      });
      worksheet.addRow(rowData);
    });

    console.log('Adding footer row');
    const footerRow = worksheet.addRow(columns.value.map(column => {
      if (column.title === 'TOTAL') return grandTotal();
      if (column.title !== 'ITEMS' && column.title !== 'CATEGORY') {
        const day = parseInt(column.title.split('/')[0]);
        return dayTotal(day);
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

    console.log('Styling header row');
    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FFF8F9FA' }
    };

    console.log('Generating Excel file');
    const filename = `Inventory_${props.currentMonth.replace(' ', '_')}.xlsx`;
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    saveAs(blob, filename);

    console.log('Excel export completed');
  } catch (error) {
    console.error('Error exporting to Excel:', error);
    alert('An error occurred while exporting to Excel. Please try again.');
  }
}

const createModalHandler = () => {
  showCreateModal.value = !showCreateModal.value;
};

const GetBWModalHandler = () => {
  showGetBWModal.value = !showGetBWModal.value;
};

const GetCFModalHandler = () => {
  showGetCFModal.value = !showGetCFModal.value;
};

const handleSelectedItem = (item) => {
  // Handle the selected item
  console.log('Selected item:', item);
};

</script>

<template>
  <Main active-tab="ORDER">
    <template v-slot:modals>
    </template>

    <template v-slot:main>
      <TableContainer>
        <div class="absolute flex justify-start items-center">
          <button @click="updateInventory" class="ml-4 mt-4 px-4 py-2 bg-blue-500 text-white rounded bg-navy hover:bg-blue-600">
            Update Inventory
          </button>
          <SuccessButton
            type="button"
            @click="exportToExcel"
            class="ml-6 bg-green"
          >
            <ExcelIcon class="h-4" />
          </SuccessButton>
        </div>

        <div v-if="items.length && daysInMonth.length" class="overflow-x-auto">
          <DataTable
            :columns="columns"
            :data="items"
            :options="options"
            class="w-full relative display" 
          />
        </div>

        <div v-else class="text-center py-4">
          No inventory data available.
        </div>
      </TableContainer>
    </template>
  </Main>
</template>

<style scoped>
/* Customize DataTable header styles */
.dataTables_wrapper .dataTables_paginate .paginate_button {
  background-color: #003366; /* Navy background color */
  color: #ffffff; /* White text color */
}

.dataTables_wrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner .dataTables_scrollHead {
  overflow: hidden;
}

.dataTables_wrapper .dataTables_scroll .dataTables_scrollHead .dataTables_scrollHeadInner th {
  background-color: #003366; /* Navy background color */
  color: #ffffff; /* White text color */
  font-weight: bold;
}
</style>