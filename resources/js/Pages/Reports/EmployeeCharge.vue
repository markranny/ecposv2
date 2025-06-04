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
    ec: {
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
    let filtered = [...props.ec];
    
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
            grossamount: (acc.grossamount || 0) + parseFloat(row.grossamount || 0),
            discamount: (acc.discamount || 0) + parseFloat(row.discamount || 0),
            netamount: (acc.netamount || 0) + parseFloat(row.netamount || 0),
            Vat: (acc.Vat || 0) + parseFloat(row.Vat || 0),
            Vatablesales: (acc.Vatablesales || 0) + parseFloat(row.Vatablesales || 0)
        };
    }, {
        grossamount: 0,
        discamount: 0,
        netamount: 0,
        Vat: 0,
        Vatablesales: 0
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
        data: 'custaccount',
        title: 'Customer',
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
        data: 'grossamount',
        title: 'Gross Amount',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.grossamount);
        },
        className: 'text-right'
    },
    {
        data: 'discamount',
        title: 'Discount',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.discamount);
        },
        className: 'text-right'
    },
    {
        data: 'netamount',
        title: 'Net Amount',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.netamount);
        },
        className: 'text-right'
    },
    {
        data: 'Vat',
        title: 'VAT',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.Vat);
        },
        className: 'text-right'
    },
    {
        data: 'Vatablesales',
        title: 'VATable Sales',
        render: (data) => {
            return formatNumber(data);
        },
        footer: function() {
            return formatNumber(footerTotals.value.Vatablesales);
        },
        className: 'text-right'
    }
];

// Function to export to Excel
const exportToExcel = (dt) => {
    try {
        isLoading.value = true;
        const workbook = new ExcelJS.Workbook();
        const worksheet = workbook.addWorksheet('Employee Charge Data');

        // Define columns in Excel sheet
        worksheet.columns = [
            { header: 'Receipt ID', key: 'receiptid', width: 15 },
            { header: 'Store Name', key: 'storename', width: 20 },
            { header: 'Customer', key: 'custaccount', width: 20 },
            { header: 'Created Date', key: 'createddate', width: 15 },
            { header: 'Gross Amount', key: 'grossamount', width: 15 },
            { header: 'Discount', key: 'discamount', width: 15 },
            { header: 'Net Amount', key: 'netamount', width: 15 },
            { header: 'VAT', key: 'Vat', width: 12 },
            { header: 'VATable Sales', key: 'Vatablesales', width: 15 }
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
                custaccount: row.custaccount,
                createddate: row.createddate ? new Date(row.createddate) : null,
                grossamount: parseFloat(row.grossamount || 0),
                discamount: parseFloat(row.discamount || 0),
                netamount: parseFloat(row.netamount || 0),
                Vat: parseFloat(row.Vat || 0),
                Vatablesales: parseFloat(row.Vatablesales || 0)
            });
            
            // Format numeric cells with 2 decimal places
            for (let i = 5; i <= 9; i++) {
                if (excelRow.getCell(i).value !== null) {
                    excelRow.getCell(i).numFmt = '#,##0.00';
                }
            }
            
            // Format date cells
            if (excelRow.getCell(4).value) excelRow.getCell(4).numFmt = 'yyyy-mm-dd';
        });

        // Calculate totals for filtered data
        const totalGrossAmount = filteredRows.reduce((acc, row) => acc + parseFloat(row.grossamount || 0), 0);
        const totalDiscAmount = filteredRows.reduce((acc, row) => acc + parseFloat(row.discamount || 0), 0);
        const totalNetAmount = filteredRows.reduce((acc, row) => acc + parseFloat(row.netamount || 0), 0);
        const totalVat = filteredRows.reduce((acc, row) => acc + parseFloat(row.Vat || 0), 0);
        const totalVatableSales = filteredRows.reduce((acc, row) => acc + parseFloat(row.Vatablesales || 0), 0);

        // Add totals row
        const totalRow = worksheet.addRow({
            receiptid: 'Total',
            storename: '',
            custaccount: '',
            createddate: '',
            grossamount: totalGrossAmount,
            discamount: totalDiscAmount,
            netamount: totalNetAmount,
            Vat: totalVat,
            Vatablesales: totalVatableSales
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
        for (let i = 5; i <= 9; i++) {
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
            link.download = `Employee_Charge_Report_${dateStr}.xlsx`;
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
        let totalGrossAmount = 0;
        let totalDiscAmount = 0;
        let totalNetAmount = 0;
        let totalVat = 0;
        let totalVatableSales = 0;

        // Use api.rows({ search: 'applied' }) to get only filtered/searched rows
        api.rows({ search: 'applied' }).every(function(rowIdx) {
            const data = this.data();
            totalGrossAmount += parseFloat(data.grossamount || 0);
            totalDiscAmount += parseFloat(data.discamount || 0);
            totalNetAmount += parseFloat(data.netamount || 0);
            totalVat += parseFloat(data.Vat || 0);
            totalVatableSales += parseFloat(data.Vatablesales || 0);
        });

        // Update footer cells with new totals including thousands separator
        const footerRow = api.table().footer().querySelectorAll('td, th');
        if (footerRow[4]) footerRow[4].textContent = formatNumber(totalGrossAmount);
        if (footerRow[5]) footerRow[5].textContent = formatNumber(totalDiscAmount);
        if (footerRow[6]) footerRow[6].textContent = formatNumber(totalNetAmount);
        if (footerRow[7]) footerRow[7].textContent = formatNumber(totalVat);
        if (footerRow[8]) footerRow[8].textContent = formatNumber(totalVatableSales);
    }
};

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
            route('reports.ec'),
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
                    <div class="text-xl font-semibold">Employee Charge Report</div>
                    <div v-if="hasData" class="text-sm text-gray-600">
                        Showing {{ totalCount }} record(s)
                    </div>
                </div>
                
                <div v-if="!hasData" class="p-8 text-center bg-gray-50 rounded-lg">
                    <div class="text-gray-500 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <p class="text-lg font-medium">No employee charges found</p>
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
                            <th></th>
                            <th>{{ formatNumber(footerTotals.grossamount) }}</th>
                            <th>{{ formatNumber(footerTotals.discamount) }}</th>
                            <th>{{ formatNumber(footerTotals.netamount) }}</th>
                            <th>{{ formatNumber(footerTotals.Vat) }}</th>
                            <th>{{ formatNumber(footerTotals.Vatablesales) }}</th>
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

</style>