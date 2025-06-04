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
import jQuery from 'jquery';
import { router } from '@inertiajs/vue3';

// Initialize DataTables with jQuery
window.$ = window.jQuery = jQuery;
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

const props = defineProps({
    bo: {
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

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const filteredData = computed(() => {
    let filtered = [...props.bo];
    
    if (selectedStores.value.length > 0) {
        filtered = filtered.filter(item => 
            selectedStores.value.includes(item.storename)
        );
    }
    
    return filtered;
});

const footerTotals = computed(() => {
    return filteredData.value.reduce((acc, row) => {
        return {
            throw_away: acc.throw_away + Number(row.throw_away || 0),
            early_molds: acc.early_molds + Number(row.early_molds || 0),
            pull_out: acc.pull_out + Number(row.pull_out || 0),
            rat_bites: acc.rat_bites + Number(row.rat_bites || 0),
            ant_bites: acc.ant_bites + Number(row.ant_bites || 0)
        };
    }, {
        throw_away: 0,
        early_molds: 0,
        pull_out: 0,
        rat_bites: 0,
        ant_bites: 0
    });
});

const columns = [
    { 
        data: 'itemid',
        title: 'Item ID',
        footer: 'Grand Total'
    },
    {
        data: 'itemname',
        title: 'Item Name',
        footer: ''
    },
    {
        data: 'storename',
        title: 'Store Name',
        footer: ''
    },
    {
        data: 'throw_away',
        title: 'THROW AWAY',
        render: (data) => (data || 0).toFixed(2),
        footer: function() {
            return footerTotals.value.throw_away.toFixed(2);
        }
    },
    {
        data: 'early_molds',
        title: 'EARLY MOLDS',
        render: (data) => (data || 0).toFixed(2),
        footer: function() {
            return footerTotals.value.early_molds.toFixed(2);
        }
    },
    {
        data: 'pull_out',
        title: 'PULL OUT',
        render: (data) => (data || 0).toFixed(2),
        footer: function() {
            return footerTotals.value.pull_out.toFixed(2);
        }
    },
    {
        data: 'rat_bites',
        title: 'RAT BITES',
        render: (data) => (data || 0).toFixed(2),
        footer: function() {
            return footerTotals.value.rat_bites.toFixed(2);
        }
    },
    {
        data: 'ant_bites',
        title: 'ANT BITES',
        render: (data) => (data || 0).toFixed(2),
        footer: function() {
            return footerTotals.value.ant_bites.toFixed(2);
        }
    }
];

// Add custom button for Excel export
const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip', // Ensure buttons are configured with this
    scrollX: true,
    scrollY: "50vh",
    buttons: [
        'copy', // This should work
        {
            text: 'Export Excel',
            action: function(e, dt, node, config) {
                exportToExcel();
            }
        },
        'pdf', // This should work
        'print' // This should work
    ],
    order: [[2, 'asc']],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        const footerRow = api.table().footer().querySelectorAll('td, th');
        [3, 4, 5, 6, 7].forEach((colIndex, idx) => {
            const total = footerTotals.value[
                ['throw_away', 'early_molds', 'pull_out', 'rat_bites', 'ant_bites'][idx]
            ];
            if (footerRow[colIndex]) {
                footerRow[colIndex].textContent = total.toFixed(2);
            }
        });
    }
};

// Export function to Excel
const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('BO Data');

    // Define columns in Excel sheet
    worksheet.columns = [
        { header: 'Item ID', key: 'itemid' },
        { header: 'Item Name', key: 'itemname' },
        { header: 'Store Name', key: 'storename' },
        { header: 'Throw Away', key: 'throw_away' },
        { header: 'Early Molds', key: 'early_molds' },
        { header: 'Pull Out', key: 'pull_out' },
        { header: 'Rat Bites', key: 'rat_bites' },
        { header: 'Ant Bites', key: 'ant_bites' }
    ];

    // Add filtered data to the worksheet
    filteredData.value.forEach(row => {
        worksheet.addRow({
            itemid: row.itemid,
            itemname: row.itemname,
            storename: row.storename,
            throw_away: (row.throw_away || 0).toFixed(2),
            early_molds: (row.early_molds || 0).toFixed(2),
            pull_out: (row.pull_out || 0).toFixed(2),
            rat_bites: (row.rat_bites || 0).toFixed(2),
            ant_bites: (row.ant_bites || 0).toFixed(2)
        });
    });

    // Add a row for totals
    worksheet.addRow({
        itemid: 'Total',
        itemname: '',
        storename: '',
        throw_away: footerTotals.value.throw_away.toFixed(2),
        early_molds: footerTotals.value.early_molds.toFixed(2),
        pull_out: footerTotals.value.pull_out.toFixed(2),
        rat_bites: footerTotals.value.rat_bites.toFixed(2),
        ant_bites: footerTotals.value.ant_bites.toFixed(2)
    });

    // Generate the Excel file and trigger the download
    workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], { type: 'application/octet-stream' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'bo_data.xlsx';
        link.click();
    });
};

// Handle filter changes and refresh data
const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
    router.get(
        route('reports.bo'),
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

let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

onUnmounted(() => {
    clearTimeout(filterTimeout);
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
                        @click="() => { selectedStores = []; startDate = ''; endDate = ''; handleFilterChange(); }"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <TableContainer>
                <DataTable 
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
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