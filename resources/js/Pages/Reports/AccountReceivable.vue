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
import 'datatables.net-buttons/js/buttons.colVis.mjs';
import ExcelJS from 'exceljs';
import jQuery from 'jquery';
import { router } from '@inertiajs/vue3';

// Initialize DataTables with jQuery
window.$ = window.jQuery = jQuery;
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);
const totalCount = ref(0);

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

// Helper function to format numbers consistently
const formatNumber = (value) => {
    const num = parseFloat(value || 0);
    return isNaN(num) ? '0.00' : num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
};

const layoutComponent = computed(() => {
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const filteredData = computed(() => {
    let filtered = [...props.ar];
    
    if (selectedStores.value.length > 0) {
        filtered = filtered.filter(item => 
            selectedStores.value.includes(item.storename)
        );
    }
    
    return filtered;
});

// Check if we have data to display
const hasData = computed(() => {
    return filteredData.value && filteredData.value.length > 0;
});

const footerTotals = computed(() => {
    return filteredData.value.reduce((acc, row) => {
        return {
            charge: (acc.charge || 0) + parseFloat(row.charge || 0),
            gcash: (acc.gcash || 0) + parseFloat(row.gcash || 0),
            paymaya: (acc.paymaya || 0) + parseFloat(row.paymaya || 0),
            card: (acc.card || 0) + parseFloat(row.card || 0),
            loyaltycard: (acc.loyaltycard || 0) + parseFloat(row.loyaltycard || 0),
            foodpanda: (acc.foodpanda || 0) + parseFloat(row.foodpanda || 0),
            grabfood: (acc.grabfood || 0) + parseFloat(row.grabfood || 0),
            representation: (acc.representation || 0) + parseFloat(row.representation || 0)
        };
    }, {
        charge: 0,
        gcash: 0,
        paymaya: 0,
        card: 0,
        loyaltycard: 0,
        foodpanda: 0,
        grabfood: 0,
        representation: 0
    });
});

const columns = [
    { 
        data: 'receiptid',
        title: 'Receipt ID',
        footer: 'Grand Total'
    },
    {
        data: 'storename',
        title: 'Store Name',
        footer: ''
    },
    {
        data: 'createddate',
        title: 'Created Date',
        render: (data) => {
            return data ? new Date(data).toLocaleDateString() : '';
        },
        footer: ''
    },
    {
        data: 'charge',
        title: 'Charge',
        render: (data) => {
            // Format with thousands separator and 2 decimal places
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.charge);
        },
        className: 'text-right'
    },
    {
        data: 'gcash',
        title: 'GCash',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.gcash);
        },
        className: 'text-right'
    },
    {
        data: 'paymaya',
        title: 'PayMaya',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.paymaya);
        },
        className: 'text-right'
    },
    {
        data: 'card',
        title: 'Card',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.card);
        },
        className: 'text-right'
    },
    {
        data: 'loyaltycard',
        title: 'Loyalty Card',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.loyaltycard);
        },
        className: 'text-right'
    },
    {
        data: 'foodpanda',
        title: 'Foodpanda',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.foodpanda);
        },
        className: 'text-right'
    },
    {
        data: 'grabfood',
        title: 'GrabFood',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.grabfood);
        },
        className: 'text-right'
    },
    {
        data: 'representation',
        title: 'Representation',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.representation);
        },
        className: 'text-right'
    }
];

// Function to export to Excel
const exportToExcel = (dt) => {
    try {
        isLoading.value = true;
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Account Receivable Data');

        // Define columns in Excel sheet
        worksheet.columns = [
            { header: 'Receipt ID', key: 'receiptid', width: 15 },
            { header: 'Store Name', key: 'storename', width: 20 },
            { header: 'Created Date', key: 'createddate', width: 15 },
            { header: 'Charge', key: 'charge', width: 12 },
            { header: 'GCash', key: 'gcash', width: 12 },
            { header: 'PayMaya', key: 'paymaya', width: 12 },
            { header: 'Card', key: 'card', width: 12 },
            { header: 'Loyalty Card', key: 'loyaltycard', width: 15 },
            { header: 'Foodpanda', key: 'foodpanda', width: 12 },
            { header: 'GrabFood', key: 'grabfood', width: 12 },
            { header: 'Representation', key: 'representation', width: 15 }
        ];

        // Apply styling to header row
        worksheet.getRow(1).font = { bold: true };
        worksheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF343A40' }
        };
        worksheet.getRow(1).font = { color: { argb: 'FFFFFFFF' } };

        // Get filtered data from DataTable
        const filteredRows = dt.rows({ search: 'applied' }).data().toArray();
        
        // Add filtered data to worksheet with proper formatting
        filteredRows.forEach(row => {
            const excelRow = worksheet.addRow({
                receiptid: row.receiptid,
                storename: row.storename,
                createddate: row.createddate ? new Date(row.createddate) : null,
                charge: parseFloat(row.charge || 0),
                gcash: parseFloat(row.gcash || 0),
                paymaya: parseFloat(row.paymaya || 0),
                card: parseFloat(row.card || 0),
                loyaltycard: parseFloat(row.loyaltycard || 0),
                foodpanda: parseFloat(row.foodpanda || 0),
                grabfood: parseFloat(row.grabfood || 0),
                representation: parseFloat(row.representation || 0)
            });
            
            // Format numeric cells with 2 decimal places
            for (let i = 4; i <= 11; i++) {
                if (excelRow.getCell(i).value !== null) {
                    excelRow.getCell(i).numFmt = '#,##0.00';
                }
            }
            
            // Format date cells
            if (excelRow.getCell(3).value) excelRow.getCell(3).numFmt = 'yyyy-mm-dd';
        });

        // Calculate totals for filtered data
        const totalCharge = filteredRows.reduce((acc, row) => acc + parseFloat(row.charge || 0), 0);
        const totalGcash = filteredRows.reduce((acc, row) => acc + parseFloat(row.gcash || 0), 0);
        const totalPaymaya = filteredRows.reduce((acc, row) => acc + parseFloat(row.paymaya || 0), 0);
        const totalCard = filteredRows.reduce((acc, row) => acc + parseFloat(row.card || 0), 0);
        const totalLoyaltycard = filteredRows.reduce((acc, row) => acc + parseFloat(row.loyaltycard || 0), 0);
        const totalFoodpanda = filteredRows.reduce((acc, row) => acc + parseFloat(row.foodpanda || 0), 0);
        const totalGrabfood = filteredRows.reduce((acc, row) => acc + parseFloat(row.grabfood || 0), 0);
        const totalRepresentation = filteredRows.reduce((acc, row) => acc + parseFloat(row.representation || 0), 0);

        // Add totals row
        const totalRow = worksheet.addRow({
            receiptid: 'Total',
            storename: '',
            createddate: '',
            charge: totalCharge,
            gcash: totalGcash,
            paymaya: totalPaymaya,
            card: totalCard,
            loyaltycard: totalLoyaltycard,
            foodpanda: totalFoodpanda,
            grabfood: totalGrabfood,
            representation: totalRepresentation
        });
        
        // Style and format the totals row
        totalRow.font = { bold: true };
        totalRow.fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF007BFF' }
        };
        totalRow.font = { color: { argb: 'FFFFFFFF' } };
        
        // Format numeric cells in totals row with 2 decimal places and thousands separator
        for (let i = 4; i <= 11; i++) {
            if (totalRow.getCell(i).value !== null) {
                totalRow.getCell(i).numFmt = '#,##0.00';
            }
        }

        // Generate and download Excel file
        workbook.xlsx.writeBuffer().then((buffer) => {
            const blob = new Blob([buffer], { type: 'application/octet-stream' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            const now = new Date();
            const dateStr = `${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2, '0')}-${now.getDate().toString().padStart(2, '0')}`;
            link.download = `Account_Receivable_Report_${dateStr}.xlsx`;
            link.click();
            isLoading.value = false;
            alert('Export completed successfully!');
        }).catch(error => {
            console.error('Excel export error:', error);
            isLoading.value = false;
            alert('Error exporting to Excel: ' + error.message);
        });
    } catch (error) {
        console.error('Error in Excel export:', error);
        isLoading.value = false;
        alert('Error preparing Excel export: ' + error.message);
    }
};

const options = {
    responsive: true,
    order: [[1, 'asc'], [0, 'asc']], // Sort by store, then receipt id
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
    dom: 'Blfrtip',
    scrollX: true,
    scrollY: "50vh",
    buttons: [
        'copy',
        {
            text: 'Export Excel',
            action: function(e, dt, node, config) {
                exportToExcel(dt);
            }
        },
        'pdf',
        'print'
    ],
    drawCallback: function(settings) {
        // Get the DataTable API instance
        const api = new DataTablesCore.Api(settings);
        
        // Update total count
        totalCount.value = api.rows({ search: 'applied' }).count();
        
        // Calculate totals based on currently filtered/displayed data
        let totalCharge = 0;
        let totalGcash = 0;
        let totalPaymaya = 0;
        let totalCard = 0;
        let totalLoyaltycard = 0;
        let totalFoodpanda = 0;
        let totalGrabfood = 0;
        let totalRepresentation = 0;

        // Use api.rows({ search: 'applied' }) to get only filtered/searched rows
        api.rows({ search: 'applied' }).every(function(rowIdx) {
            const data = this.data();
            totalCharge += parseFloat(data.charge || 0);
            totalGcash += parseFloat(data.gcash || 0);
            totalPaymaya += parseFloat(data.paymaya || 0);
            totalCard += parseFloat(data.card || 0);
            totalLoyaltycard += parseFloat(data.loyaltycard || 0);
            totalFoodpanda += parseFloat(data.foodpanda || 0);
            totalGrabfood += parseFloat(data.grabfood || 0);
            totalRepresentation += parseFloat(data.representation || 0);
        });

        // Update footer cells with new totals including thousands separator
        const footerRow = api.table().footer().querySelectorAll('td, th');
        if (footerRow[3]) footerRow[3].textContent = formatNumber(totalCharge);
        if (footerRow[4]) footerRow[4].textContent = formatNumber(totalGcash);
        if (footerRow[5]) footerRow[5].textContent = formatNumber(totalPaymaya);
        if (footerRow[6]) footerRow[6].textContent = formatNumber(totalCard);
        if (footerRow[7]) footerRow[7].textContent = formatNumber(totalLoyaltycard);
        if (footerRow[8]) footerRow[8].textContent = formatNumber(totalFoodpanda);
        if (footerRow[9]) footerRow[9].textContent = formatNumber(totalGrabfood);
        if (footerRow[10]) footerRow[10].textContent = formatNumber(totalRepresentation);
    }
}

// Modify your filter change handler to add a delay
const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
    isLoading.value = true;
    
    // Add a small delay to ensure the component is ready
    setTimeout(() => {
        router.get(
            route('reports.ar'),
            {
                startDate: startDate.value,
                endDate: endDate.value,
                stores: selectedStores.value
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    isLoading.value = false;
                },
                onError: (errors) => {
                    isLoading.value = false;
                    alert('Error loading data: ' + Object.values(errors).join(', '));
                }
            }
        );
    }, 100);
};

// Clear all filters
const clearFilters = () => {
    selectedStores.value = []; 
    startDate.value = ''; 
    endDate.value = '';
    handleFilterChange();
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
            <!-- Loading Overlay -->
            <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-30 z-50 flex items-center justify-center">
                <div class="bg-white p-4 rounded-lg shadow-lg">
                    <div class="flex items-center">
                        <svg class="animate-spin h-6 w-6 mr-3 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading...</span>
                    </div>
                </div>
            </div>
            
            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999] sticky top-0">
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
                        @click="clearFilters"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <TableContainer>
                <div class="flex justify-between items-center mb-4">
                    <div class="text-xl font-semibold">Account Receivable Report</div>
                    <div v-if="hasData" class="text-sm text-gray-600">
                        Showing {{ totalCount }} record(s)
                    </div>
                </div>
                
                <div v-if="!hasData" class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-gray-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-lg font-medium">No receivables found</p>
                    </div>
                    <p class="text-gray-600">
                        Try adjusting your search filters or select a different date range.
                    </p>
                </div>
                
                <DataTable 
                    v-if="hasData"
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <tfoot>
                        <tr>
                            <th>Grand Total</th>
                            <th></th>
                            <th></th>
                            <th>{{ parseFloat(footerTotals.charge).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.gcash).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.paymaya).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.card).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.loyaltycard).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.foodpanda).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.grabfood).toFixed(2) }}</th>
                            <th>{{ parseFloat(footerTotals.representation).toFixed(2) }}</th>
                        </tr>
                    </tfoot>
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

.dataTable tfoot td, .dataTable tfoot th {
    padding: 12px 15px;
}

/* Styling for DataTable Buttons */
.dt-buttons {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    position: absolute;
    z-index: 1;
    margin-left: 250px;
    margin-top: -10px;
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

/* Row highlighting */
.highlight {
    background-color: #ffffcc !important;
}

/* Numerical column alignment */
table.dataTable td:nth-child(4),
table.dataTable td:nth-child(5),
table.dataTable td:nth-child(6),
table.dataTable td:nth-child(7),
table.dataTable td:nth-child(8),
table.dataTable td:nth-child(9),
table.dataTable td:nth-child(10),
table.dataTable td:nth-child(11) {
    text-align: right;
}

/* Header and footer alignment for numerical columns */
table.dataTable thead th:nth-child(4),
table.dataTable thead th:nth-child(5),
table.dataTable thead th:nth-child(6),
table.dataTable thead th:nth-child(7),
table.dataTable thead th:nth-child(8),
table.dataTable thead th:nth-child(9),
table.dataTable thead th:nth-child(10),
table.dataTable thead th:nth-child(11),
table.dataTable tfoot th:nth-child(4),
table.dataTable tfoot th:nth-child(5),
table.dataTable tfoot th:nth-child(6),
table.dataTable tfoot th:nth-child(7),
table.dataTable tfoot th:nth-child(8),
table.dataTable tfoot th:nth-child(9),
table.dataTable tfoot th:nth-child(10),
table.dataTable tfoot th:nth-child(11) {
    text-align: right;
}

/* Toast notifications */
#toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.toast {
    margin-bottom: 10px;
    padding: 15px 20px;
    border-radius: 4px;
    color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s ease-in-out;
    max-width: 300px;
}

.toast-success {
    background-color: #28a745;
}

.toast-error {
    background-color: #dc3545;
}

.toast-info {
    background-color: #17a2b8;
}

.toast-warning {
    background-color: #ffc107;
    color: #212529;
}

.toast-removing {
    animation: slideOut 0.3s ease-in-out forwards;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOut {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(100%);
        opacity: 0;
    }
}

</style>