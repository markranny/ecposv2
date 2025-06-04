<script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted } from "vue";
import 'datatables.net-buttons';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import ExcelJS from 'exceljs';

DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

const props = defineProps({
    ec: {
        type: Array,
        required: true,
    },
    auth: {
        type: Object,
        required: true,
    },
    stores: {
        type: Array,
        required: true,
    },
    userRole: {
        type: String,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
        default: () => ({
            startDate: '',
            endDate: '',
            selectedStores: []
        })
    }
});

const layoutComponent = computed(() => {
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const filteredData = computed(() => {
    let filtered = [...props.ec];
    
    // Filter by selected stores
    if (selectedStores.value.length > 0) {
        filtered = filtered.filter(item => selectedStores.value.includes(item.storename));
    }
    
    // Filter by date range
    if (startDate.value && endDate.value) {
        filtered = filtered.filter(item => {
            const itemDate = new Date(item.createddate);
            const start = new Date(startDate.value);
            const end = new Date(endDate.value);
            return itemDate >= start && itemDate <= end;
        });
    }
    
    return filtered;
});

const footerTotals = computed(() => {
    return filteredData.value.reduce((acc, row) => {
        acc.total_discamount += (parseFloat(row.total_discamount) || 0);
        acc.total_costprice += (parseFloat(row.total_costprice) || 0);
        acc.total_netamount += (parseFloat(row.total_netamount) || 0);
        acc.vatablesales += (parseFloat(row.vatablesales) || 0);
        acc.vat += (parseFloat(row.vat) || 0);
        acc.total_grossamount += (parseFloat(row.total_grossamount) || 0);
        acc.total_costamount += (parseFloat(row.total_costamount) || 0);
        acc.total_qty += Math.round(row.qty || 0);
        return acc;
    }, {
        total_qty: 0,
        total_discamount: 0,
        total_costprice: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0,
        total_grossamount: 0,
        total_costamount: 0,
    });
});

const columns = [
    { data: 'discofferid', title: 'DISCOUNT NAME', footer: 'Grand Total'  },
    { data: 'price', title: 'Price' },
    { 
        data: 'qty', 
        title: 'Qty',
        render: (data) => Math.round(data),  
        footer: () => Math.round(footerTotals.value.total_qty) 
    },
    { 
        data: 'total_costprice', 
        title: 'Cost Price',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_costprice.toFixed(2)
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_grossamount.toFixed(2)
    },
    { 
        data: 'total_costamount', 
        title: 'Cost Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_costamount.toFixed(2)
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_discamount.toFixed(2)
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_netamount.toFixed(2)
    },
    { 
        data: 'vatablesales', 
        title: 'Vatable Sales',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.vatablesales.toFixed(2)
    },
    { 
        data: 'vat', 
        title: 'VAT',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.vat.toFixed(2)
    },
];

const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip',
    scrollX: true,
    scrollY: "50vh",
    buttons: [
        'copy', 
        {
            text: 'Export Excel',
            action: function(e, dt, node, config) {
                exportToExcel();
            }
        },
        'pdf', 
        'print'
    ],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        const footerRow = api.table().footer().querySelectorAll('td, th');
        const columns = ['total_qty','total_costprice', 'total_grossamount', 'total_costamount', 'total_discamount', 'total_netamount', 'vatablesales', 'vat'];

        columns.forEach((column, idx) => {
            const total = footerTotals.value[column];
            const footerCell = footerRow[idx + 2]; 
            if (footerCell && typeof total === 'number') {
                footerCell.textContent = total.toFixed(2);
            }
        });
    }
};

// Excel Export Function
const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Data');
    
    // Define columns with proper formatting
    worksheet.columns = [
        { header: 'DiscountName', key: 'discofferid', width: 30 },
        { header: 'Price', key: 'price', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Qty', key: 'qty', width: 10, style: { numFmt: '#,##0' } },
        { header: 'Cost Price', key: 'total_costprice', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Gross Amount', key: 'total_grossamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Cost Amount', key: 'total_costamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Discount Amount', key: 'total_discamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Net Amount', key: 'total_netamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'Vatable Sales', key: 'vatablesales', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'VAT', key: 'vat', width: 15, style: { numFmt: '#,##0.00' } }
    ];

    // Style the header row
    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF343A40' }
    };
    worksheet.getRow(1).font = { color: { argb: 'FFFFFFFF' }, bold: true };

    // Add data rows
    filteredData.value.forEach((row) => {
        worksheet.addRow({
            discofferid: row.discofferid || 'N/A',
            price: Number(row.price) || 0,
            qty: Math.round(Number(row.qty)) || 0,
            total_costprice: Number(row.total_costprice) || 0,
            total_grossamount: Number(row.total_grossamount) || 0,
            total_costamount: Number(row.total_costamount) || 0,
            total_discamount: Number(row.total_discamount) || 0,
            total_netamount: Number(row.total_netamount) || 0,
            vatablesales: Number(row.vatablesales) || 0,
            vat: Number(row.vat) || 0
        });
    });

    // Add totals row
    const totalRow = worksheet.addRow({
        discofferid: 'Grand Total',
        price: '',
        qty: footerTotals.value.total_qty,
        total_costprice: footerTotals.value.total_costprice,
        total_grossamount: footerTotals.value.total_grossamount,
        total_costamount: footerTotals.value.total_costamount,
        total_discamount: footerTotals.value.total_discamount,
        total_netamount: footerTotals.value.total_netamount,
        vatablesales: footerTotals.value.vatablesales,
        vat: footerTotals.value.vat
    });

    // Style the totals row
    totalRow.font = { bold: true };
    totalRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF007BFF' }
    };
    totalRow.font = { color: { argb: 'FFFFFFFF' }, bold: true };

    // Auto-fit columns
    worksheet.columns.forEach(column => {
        column.width = Math.max(
            column.width || 10,
            15 // minimum width
        );
    });

    // Generate and download the file
    workbook.xlsx.writeBuffer().then(buffer => {
        const blob = new Blob([buffer], { 
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `ItemSales_Report_${new Date().toISOString().split('T')[0]}.xlsx`;
        link.click();
        window.URL.revokeObjectURL(url);
    });
};

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999]">
                
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" class="flex-1 min-w-[200px]">
                  <MultiSelectDropdown
                    v-model="selectedStores"
                    :options="stores"
                    label="Stores"
                  />
                </div>

                <!-- Date filters -->
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input
                        type="date"
                        v-model="startDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>
                
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input
                        type="date"
                        v-model="endDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                </div>

                <!-- Clear filters button -->
                <div class="flex items-end">
                    <button
                        @click="() => { selectedStores = []; startDate = ''; endDate = ''; }"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Data table -->
            <TableContainer class="overflow-auto">
                <DataTable 
                    v-if="filteredData.length > 0"
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <template #action="data">
                    </template>
                </DataTable>

                <!-- Fallback message when no data is available -->
                <p v-else>No data available for the selected filters.</p>
            </TableContainer>
        </template>
    </component>
</template>


<style>
/* General Styling for DataTable */
table.dataTable {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Arial', sans-serif;
}

table.dataTable thead {
    background-color: #343a40;
    color: #ffffff;
    text-align: center;
}

table.dataTable tbody {
    background-color: #f8f9fa;
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #e9ecef;
}

table.dataTable tbody tr:hover {
    background-color: #ddd;
    cursor: pointer;
}

table.dataTable th, table.dataTable td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #dee2e6;
}

table.dataTable th {
    font-size: 14px;
    font-weight: bold;
}

table.dataTable td {
    font-size: 13px;
}

/* Styling for Footer */
.dataTable tfoot {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-align: center;
}

.dataTable tfoot td {
    padding: 12px 15px;
}

/* Styling for DataTable Buttons */
.dt-buttons {
    display: flex;
    justify-content: flex-start;
    margin: 10px 0;
    gap: 10px;
}

.dt-button {
    padding: 8px 16px;
    background-color: #28a745;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.dt-button:hover {
    background-color: #218838;
}

/* Copy, Print, Export to Excel Button Styling */
.dt-buttons .buttons-copy,
.dt-buttons .buttons-print,
.dt-buttons .buttons-excel {
    padding: 10px 15px;
    border-radius: 5px;
    background-color: #007bff;
    color: white;
    border: none;
}

.dt-buttons .buttons-copy:hover,
.dt-buttons .buttons-print:hover,
.dt-buttons .buttons-excel:hover {
    background-color: #0056b3;
}

/* Search Box Styling */
.dataTables_filter {
    float: right;
    margin-bottom: 20px;
}

.dataTables_filter input {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
}

/* Clear Filters Button */
button.clear-filters {
    padding: 10px 15px;
    background-color: #ffc107;
    border-radius: 5px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    margin-top: 10px;
    transition: background-color 0.3s ease;
}

button.clear-filters:hover {
    background-color: #e0a800;
}

/* Responsive Design */
@media (max-width: 768px) {
    table.dataTable th, table.dataTable td {
        padding: 8px 10px;
    }

    .dt-buttons {
        flex-wrap: wrap;
        justify-content: center;
    }

    .dt-button {
        margin: 5px;
    }
}

/* Styling for DataTable Buttons */
.dt-buttons {
    display: flex;                 /* Align buttons horizontally */
    justify-content: flex-start;   /* Align buttons to the left */
    gap: 10px;                     /* Add space between buttons */
    margin: 10px 0;
}

.dt-button {
    padding: 8px 16px;
    background-color: #28a745;
    border: none;
    border-radius: 4px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.dt-button:hover {
    background-color: #218838;
}

/* Search Box Styling */
.dataTables_filter {
    display: flex;                 /* Display search box inline with buttons */
    align-items: center;           /* Align vertically */
    gap: 10px;                     /* Add space between search input and buttons */
    margin-left: auto;             /* Align search box to the right */
}

.dataTables_filter input {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dt-buttons {
        flex-wrap: wrap;            /* Allow buttons to wrap on smaller screens */
        justify-content: center;    /* Center the buttons */
    }

    .dataTables_filter {
        margin: 0;                  /* Remove margin when wrapping */
    }
}

.dt-buttons {
    display: flex;               
    justify-content: flex-start; 
    align-items: center;    
    position: absolute;
    z-index: 1;  
}

.dt-buttons .buttons-copy{
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}
.dt-button{
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}
.dt-buttons .buttons-print{
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}
.dt-search{
    float: right;
    padding-bottom: 20px;
    position: relative;
    z-index: 999;  
}

</style>