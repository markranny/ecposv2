<!-- Script setup section -->
<script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { router } from '@inertiajs/vue3';
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
    console.log('userRole value:', props.userRole);
    console.log('Is Store?:', props.userRole.toUpperCase() === 'STORE');
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const filteredData = computed(() => {
    let filtered = [...props.ec];
    
    if (selectedStores.value.length > 0) {
        filtered = filtered.filter(item => 
            selectedStores.value.includes(item.storename)
        );
    }
    
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

const initialGrandTotal = ref({
    grossamount: 0,
    discamount: 0,
    netamount: 0,
    Vat: 0,
    Vatablesales: 0
});

const footerTotals = computed(() => {
    // If no filters are applied, return the initial grand total
    if (selectedStores.value.length === 0 && !startDate.value && !endDate.value) {
        return {
            storename: 'Grand Total',
            grossamount: initialGrandTotal.value.grossamount,
            discamount: initialGrandTotal.value.discamount,
            netamount: initialGrandTotal.value.netamount,
            Vat: initialGrandTotal.value.Vat,
            Vatablesales: initialGrandTotal.value.Vatablesales
        };
    }

    // Calculate totals based on filtered data
    return filteredData.value.reduce((acc, row) => {
        return {
            storename: 'Grand Total',
            grossamount: acc.grossamount + (parseFloat(row.grossamount) || 0),
            discamount: acc.discamount + (parseFloat(row.discamount) || 0),
            netamount: acc.netamount + (parseFloat(row.netamount) || 0),
            Vat: acc.Vat + (parseFloat(row.Vat) || 0),
            Vatablesales: acc.Vatablesales + (parseFloat(row.Vatablesales) || 0)
        };
    }, {
        storename: 'Grand Total',
        grossamount: 0,
        discamount: 0,
        netamount: 0,
        Vat: 0,
        Vatablesales: 0
    });
});

const columns = [
    { 
        data: 'storename', 
        title: 'STORE', 
        footer: () => 'Grand Total' 
    },
    { 
        data: 'grossamount', 
        title: 'GROSSAMOUNT',
        render: function(data) {
            return (parseFloat(data) || 0).toFixed(2);
        },
        footer: function() {
            return footerTotals.value.grossamount.toFixed(2);
        }
    },
    { 
        data: 'discamount', 
        title: 'DISCOUNT',
        render: function(data) {
            return (parseFloat(data) || 0).toFixed(2);
        },
        footer: function() {
            return footerTotals.value.discamount.toFixed(2);
        }
    },
    { 
        data: 'netamount', 
        title: 'NETSALES',
        render: function(data) {
            return (parseFloat(data) || 0).toFixed(2);
        },
        footer: function() {
            return footerTotals.value.netamount.toFixed(2);
        }
    }
];

const totals = computed(() => {
    return filteredData.value.reduce((acc, curr) => ({
        grossamount: acc.grossamount + (parseFloat(curr.grossamount) || 0),
        discamount: acc.discamount + (parseFloat(curr.discamount) || 0),
        netamount: acc.netamount + (parseFloat(curr.netamount) || 0),
        Vat: acc.Vat + (parseFloat(curr.Vat) || 0),
        Vatablesales: acc.Vatablesales + (parseFloat(curr.Vatablesales) || 0)
    }), {
        grossamount: 0,
        discamount: 0,
        netamount: 0,
        Vat: 0,
        Vatablesales: 0
    });
});

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
    order: [[2, 'asc']],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        const footerRow = api.table().footer().querySelectorAll('td, th');
        
        // Set the first column (storename) to 'Grand Total'
        if (footerRow[0]) {
            footerRow[0].textContent = 'Grand Total';
        }

        // Update the totals in the subsequent columns
        const columns = ['grossamount', 'discamount', 'netamount'];
        
        columns.forEach((column, idx) => {
            const total = footerTotals.value[column];
            const footerCell = footerRow[idx + 1];
            if (footerCell && typeof total === 'number') {
                footerCell.textContent = total.toFixed(2);
            }
        });
    }
};

const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Data');

    // Define the columns for the Excel sheet
    worksheet.columns = [
        { header: 'STORE', key: 'storename', width: 15 },
        { header: 'GROSSAMOUNT', key: 'grossamount', width: 15 },
        { header: 'DISCOUNT', key: 'discamount', width: 15 },
        { header: 'NETSALES', key: 'netamount', width: 15 },
    ];

    // Add header row with styling
    worksheet.getRow(1).font = { bold: true };
    worksheet.getRow(1).fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFE0E0E0' }
    };

    // Calculate running totals while adding data
    const runningTotals = {
        grossamount: 0,
        discamount: 0,
        netamount: 0
    };

    // Add data rows and accumulate totals
    filteredData.value.forEach((row) => {
        // Add the row
        worksheet.addRow({
            storename: row.storename,
            grossamount: Number(row.grossamount || 0),
            discamount: Number(row.discamount || 0),
            netamount: Number(row.netamount || 0)
        });

        // Accumulate totals
        runningTotals.grossamount += Number(row.grossamount || 0);
        runningTotals.discamount += Number(row.discamount || 0);
        runningTotals.netamount += Number(row.netamount || 0);
    });

    // Add empty row before totals
    worksheet.addRow([]);

    // Add totals row with the calculated values
    const totalsRow = worksheet.addRow({
        storename: 'Total',
        grossamount: runningTotals.grossamount,
        discamount: runningTotals.discamount,
        netamount: runningTotals.netamount
    });

    // Style totals row
    totalsRow.font = { bold: true };
    totalsRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFF0F0F0' }
    };

    // Format number columns to show 2 decimal places
    const numberColumns = ['grossamount', 'discamount', 'netamount'];
    worksheet.eachRow((row, rowNumber) => {
        numberColumns.forEach(colKey => {
            const col = worksheet.getColumn(colKey);
            const cell = row.getCell(col.number);
            if (rowNumber > 1) { // Skip header row
                cell.numFmt = '#,##0.00';
            }
        });
    });

    // Write the workbook to a buffer and initiate the download
    workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], { type: 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'sales_data.xlsx';
        link.click();
        URL.revokeObjectURL(link.href);
    });
};


const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
    router.get(
        route('reports.sales'),
        {
            startDate: startDate.value,
            endDate: endDate.value,
            stores: selectedStores.value
        },
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

onMounted(() => {
    // Capture initial totals when the component mounts
    initialGrandTotal.value = props.ec.reduce((acc, row) => {
        return {
            grossamount: acc.grossamount + (parseFloat(row.grossamount) || 0),
            discamount: acc.discamount + (parseFloat(row.discamount) || 0),
            netamount: acc.netamount + (parseFloat(row.netamount) || 0),
            Vat: acc.Vat + (parseFloat(row.Vat) || 0),
            Vatablesales: acc.Vatablesales + (parseFloat(row.Vatablesales) || 0)
        };
    }, {
        grossamount: 0,
        discamount: 0,
        netamount: 0,
        Vat: 0,
        Vatablesales: 0
    });
});

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
            <TableContainer>
                <DataTable 
                    :data="props.ec" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <template #action="data">
                    </template>
                </DataTable>
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