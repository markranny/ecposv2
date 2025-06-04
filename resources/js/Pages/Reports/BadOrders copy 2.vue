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
            throw_away: (acc.throw_away || 0) + parseFloat(row.throw_away || 0),
            early_molds: (acc.early_molds || 0) + parseFloat(row.early_molds || 0),
            pull_out: (acc.pull_out || 0) + parseFloat(row.pull_out || 0),
            rat_bites: (acc.rat_bites || 0) + parseFloat(row.rat_bites || 0),
            ant_bites: (acc.ant_bites || 0) + parseFloat(row.ant_bites || 0)
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
        data: 'category',
        title: 'Category',
        footer: ''
    },
    {
        data: 'storename',
        title: 'Store Name',
        footer: ''
    },
    {
        data: 'batchdate',
        title: 'Batch Date',
        render: (data) => {
            return data ? new Date(data).toLocaleDateString() : '';
        },
        footer: ''
    },
    {
        data: 'waste_declaration_date',
        title: 'Waste Declaration Date',
        render: (data) => {
            return data ? new Date(data).toLocaleDateString() : '';
        },
        footer: ''
    },
    {
        data: 'throw_away',
        title: 'THROW AWAY',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: function() {
            const total = footerTotals.value.throw_away || 0;
            return parseFloat(total).toFixed(2);
        }
    },
    {
        data: 'early_molds',
        title: 'EARLY MOLDS',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: function() {
            const total = footerTotals.value.early_molds || 0;
            return parseFloat(total).toFixed(2);
        }
    },
    {
        data: 'pull_out',
        title: 'PULL OUT',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: function() {
            const total = footerTotals.value.pull_out || 0;
            return parseFloat(total).toFixed(2);
        }
    },
    {
        data: 'rat_bites',
        title: 'RAT BITES',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: function() {
            const total = footerTotals.value.rat_bites || 0;
            return parseFloat(total).toFixed(2);
        }
    },
    {
        data: 'ant_bites',
        title: 'ANT BITES',
        render: (data) => {
            const value = parseFloat(data || 0);
            return isNaN(value) ? '0.00' : value.toFixed(2);
        },
        footer: function() {
            const total = footerTotals.value.ant_bites || 0;
            return parseFloat(total).toFixed(2);
        }
    }
];

// DataTable options
const options = {
    responsive: true,
    order: [[3, 'asc']], // Sort by store name by default
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
        [6, 7, 8, 9, 10].forEach((colIndex, idx) => {
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
        { header: 'Category', key: 'category' },
        { header: 'Store Name', key: 'storename' },
        { header: 'Batch Date', key: 'batchdate' },
        { header: 'Waste Declaration Date', key: 'waste_declaration_date' },
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
            category: row.category,
            storename: row.storename,
            batchdate: row.batchdate ? new Date(row.batchdate).toLocaleDateString() : '',
            waste_declaration_date: row.waste_declaration_date ? new Date(row.waste_declaration_date).toLocaleDateString() : '',
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
        category: '',
        storename: '',
        batchdate: '',
        waste_declaration_date: '',
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
            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999]">
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" 
                     class="flex-1 min-w-[200px]">
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

            <!-- Table Section -->
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
    align-items: center;
    position: absolute;
    z-index: 1;
    margin: 10px 0;
    gap: 10px;
}

.dt-buttons .buttons-copy,
.dt-button,
.dt-buttons .buttons-print {
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.dt-buttons .buttons-copy:hover,
.dt-button:hover,
.dt-buttons .buttons-print:hover {
    background-color: darkblue;
}

/* Search Box Styling */
.dataTables_filter {
    float: right;
    padding-bottom: 20px;
    position: relative;
    z-index: 999;
}

.dataTables_filter input {
    padding: 8px 12px;
    border-radius: 4px;
    border: 1px solid #ccc;
    font-size: 14px;
    margin-left: 8px;
}

/* Pagination Styling */
.dataTables_paginate {
    margin-top: 15px;
    text-align: right;
}

.dataTables_paginate .paginate_button {
    padding: 5px 10px;
    margin: 0 2px;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
    background-color: #fff;
}

.dataTables_paginate .paginate_button.current {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
}

.dataTables_paginate .paginate_button:hover:not(.current) {
    background-color: #e9ecef;
}

/* Length Menu Styling */
.dataTables_length {
    margin-bottom: 15px;
}

.dataTables_length select {
    padding: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    margin: 0 5px;
}

/* Info Styling */
.dataTables_info {
    margin-top: 15px;
    color: #666;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dt-buttons {
        position: static;
        justify-content: center;
        margin-bottom: 15px;
    }

    .dataTables_filter {
        float: none;
        text-align: center;
        margin-bottom: 15px;
    }

    .dataTables_length {
        text-align: center;
    }

    .dataTables_paginate {
        text-align: center;
    }

    table.dataTable th, 
    table.dataTable td {
        padding: 8px;
        font-size: 12px;
    }

    .dt-buttons .buttons-copy,
    .dt-button,
    .dt-buttons .buttons-print {
        padding: 8px;
        font-size: 12px;
        margin: 5px;
    }
}

/* Print Styling */
@media print {
    .dt-buttons,
    .dataTables_filter,
    .dataTables_length,
    .dataTables_paginate {
        display: none !important;
    }

    table.dataTable {
        width: 100% !important;
    }

    table.dataTable th,
    table.dataTable td {
        padding: 8px;
        border: 1px solid #000;
    }
}

/* Loading State */
.dataTables_processing {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    padding: 1rem;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

/* Scrollbar Styling */
.dataTables_scrollBody::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #555;
}

</style>