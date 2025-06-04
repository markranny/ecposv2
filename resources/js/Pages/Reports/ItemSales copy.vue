<script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";  
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import ExcelJS from 'exceljs';
import { router } from '@inertiajs/vue3';
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

const props = defineProps({
    ar: {
        type: Array,
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
    console.log('userRole value:', props.userRole);
    console.log('Is Store?:', props.userRole.toUpperCase() === 'STORE');
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

const footerTotals = computed(() => {
    return props.ar.reduce((acc, curr) => {
        const netAmount = Number(curr.total_netamount || 0);
        const discAmount = Number(curr.total_discamount || 0);
        const grossAmount = Number(curr.total_grossamount || 0);
        const qty = Number(curr.total_qty || 0);  // Make sure total_qty is always treated as a number

        return {
            total_netamount: (acc.total_netamount * 100 + netAmount * 100) / 100,
            total_discamount: (acc.total_discamount * 100 + discAmount * 100) / 100,
            total_grossamount: (acc.total_grossamount * 100 + grossAmount * 100) / 100,
            total_qty: acc.total_qty + qty  // Summing up total_qty
        };
    }, {
        total_netamount: 0,
        total_discamount: 0,
        total_grossamount: 0,
        total_qty: 0  // Initialize total_qty properly here
    });
});

// Initialize filters from props
onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

// Handle filter changes
const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
    const params = {
        startDate: startDate.value || null,
        endDate: endDate.value || null,
        stores: selectedStores.value.length > 0 ? selectedStores.value : null
    };

    router.get(
        route('reports.itemsales'),
        params,
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

// Watch for filter changes with debounce
let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

// Cleanup on component unmount
onUnmounted(() => {
    clearTimeout(filterTimeout);
});

// Formatter function
const formatCurrency = (value) => {
    return `₱${parseFloat(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
};

// Columns for DataTable
const columns = [
    { 
        data: 'itemname', 
        title: 'ITEMNAME', 
        footer: 'Grand Total'
    },
    { 
        data: 'itemgroup', 
        title: 'Category',
        footer: ''
    },
    { 
        data: 'total_qty', 
        title: 'QTY',
        render: (data) => `${Math.floor(data)}`, // Truncate decimals without rounding
        footer: `${Math.floor(footerTotals.value.total_qty)}`
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => formatCurrency(data),
        footer: formatCurrency(footerTotals.value.total_netamount)
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => formatCurrency(data),
        footer: formatCurrency(footerTotals.value.total_discamount)
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => formatCurrency(data),
        footer: formatCurrency(footerTotals.value.total_grossamount)
    }
];

const options = {
    responsive: true,
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
    order: [[0, 'asc']]
};

const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Report');

    // Add header row with column titles
    worksheet.addRow(['Item Name', 'Category', 'QTY', 'Net Amount', 'Discount Amount', 'Gross Amount']);

    // Set column widths and formats
    worksheet.columns = [
        { header: 'Item Name', key: 'itemname', width: 30 },
        { header: 'Category', key: 'itemgroup', width: 20 },
        { header: 'QTY', key: 'total_qty', width: 15, style: { numFmt: '#,##0' } }, // Format QTY as integers
        { header: 'Net Amount', key: 'netAmount', width: 20, style: { numFmt: '#,##0.00' } }, // Format as currency
        { header: 'Discount Amount', key: 'discountAmount', width: 20, style: { numFmt: '#,##0.00' } },
        { header: 'Gross Amount', key: 'grossAmount', width: 20, style: { numFmt: '#,##0.00' } }
    ];

    // Add data rows
    props.ar.forEach(item => {
        worksheet.addRow({
            itemname: item.itemname,
            itemgroup: item.itemgroup,
            total_qty: Math.floor(item.total_qty || 0), // Make sure qty is an integer
            netAmount: item.total_netamount,
            discountAmount: item.total_discamount,
            grossAmount: item.total_grossamount
        });
    });

    // Add totals row
    worksheet.addRow({
        itemname: 'Grand Total',
        itemgroup: '',
        total_qty: Math.floor(footerTotals.value.total_qty), // Grand total of QTY
        netAmount: footerTotals.value.total_netamount,
        discountAmount: footerTotals.value.total_discamount,
        grossAmount: footerTotals.value.total_grossamount
    });

    // Styling the "Grand Total" row
    const lastRow = worksheet.lastRow;
    lastRow.font = { bold: true };

    // Create a buffer and download the Excel file
    workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'itemsales.xlsx';
        link.click();
        window.URL.revokeObjectURL(url);
    });
};

</script>


<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <!-- Filter controls -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999]">
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" class="flex-1 min-w-[200px]">
                  <MultiSelectDropdown
                    v-model="selectedStores"
                    :options="stores"
                    label="Stores"
                  />
                </div>
                
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

                <div class="flex items-end">
                    <button
                        @click="() => { selectedStores.value = []; startDate.value = ''; endDate.value = ''; handleFilterChange(); }"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <TableContainer>
                <DataTable 
                    :data="props.ar" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                />
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