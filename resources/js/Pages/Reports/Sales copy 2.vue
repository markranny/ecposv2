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

// State management
const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

// Props definition with proper validation
const props = defineProps({
    ar: {
        type: Array,
        required: true,
        default: () => []
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({})
    },
    stores: {
        type: Array,
        required: true,
        default: () => []
    },
    userRole: {
        type: String,
        required: true,
        validator: (value) => ['ADMIN', 'SUPERADMIN', 'STORE'].includes(value.toUpperCase())
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

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
};

// Computed properties
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
    return filteredData.value.reduce((acc, row) => ({
        total_netamount: acc.total_netamount + (Number(row.total_netamount) || 0),
        total_discamount: acc.total_discamount + (Number(row.total_discamount) || 0),
        total_grossamount: acc.total_grossamount + (Number(row.total_grossamount) || 0)
    }), {
        total_netamount: 0,
        total_discamount: 0,
        total_grossamount: 0,
    });
});

// DataTable configuration
const columns = [
    { 
        data: 'store', 
        title: 'Store', 
        footer: 'Grand Total'
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => formatCurrency(Number(data) || 0),
        footer: () => formatCurrency(footerTotals.value.total_netamount)
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => formatCurrency(Number(data) || 0),
        footer: () => formatCurrency(footerTotals.value.total_discamount)
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => formatCurrency(Number(data) || 0),
        footer: () => formatCurrency(footerTotals.value.total_grossamount)
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
            action: exportToExcel
        },
        'pdf',
        'print'
    ],
    order: [[0, 'asc']],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        updateFooterTotals(api);
    }
};

// Helper function to update footer totals
function updateFooterTotals(api) {
    const footerRow = api.table().footer().querySelectorAll('td, th');
    const columns = ['total_grossamount', 'total_discamount', 'total_netamount'];
    
    columns.forEach((column, idx) => {
        const total = footerTotals.value[column];
        const footerCell = footerRow[idx + 1];
        if (footerCell && typeof total === 'number') {
            footerCell.textContent = formatCurrency(total);
        }
    });
}

// Excel export function
async function exportToExcel() {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Report');

    // Define columns with proper formatting
    worksheet.columns = [
        { header: 'Store', key: 'store', width: 20 },
        { header: 'Net Amount', key: 'netAmount', width: 20, style: { numFmt: '₱"#,##0.00' } },
        { header: 'Discount Amount', key: 'discountAmount', width: 20, style: { numFmt: '"₱"#,##0.00' } },
        { header: 'Gross Amount', key: 'grossAmount', width: 20, style: { numFmt: '"₱"#,##0.00' } }
    ];

    // Add data rows
    filteredData.value.forEach(item => {
        worksheet.addRow({
            store: item.store,
            netAmount: Number(item.total_netamount) || 0,
            discountAmount: Number(item.total_discamount) || 0,
            grossAmount: Number(item.total_grossamount) || 0
        });
    });

    // Add totals row
    worksheet.addRow({
        store: 'Grand Total',
        netAmount: footerTotals.value.total_netamount,
        discountAmount: footerTotals.value.total_discamount,
        grossAmount: footerTotals.value.total_grossamount
    });

    // Style the header row
    worksheet.getRow(1).font = { bold: true };

    try {
        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { 
            type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
        });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `sales_report_${new Date().toISOString().split('T')[0]}.xlsx`;
        link.click();
        window.URL.revokeObjectURL(url);
    } catch (error) {
        console.error('Error exporting to Excel:', error);
        alert('Failed to export Excel file. Please try again.');
    }
}

// Filter handling
const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
    // Map store names to store IDs for the backend
    const selectedStoreIds = selectedStores.value.map(storeName => {
        const store = props.stores.find(s => s.NAME === storeName);
        return store ? store.STOREID : storeName;
    });
    
    router.get(
        route('reports.sales'),
        {
            startDate: startDate.value,
            endDate: endDate.value,
            stores: selectedStoreIds
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

// Cleanup on component unmount
onUnmounted(() => {
    clearTimeout(filterTimeout);
});
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template #main>
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow">
                <!-- Store filter -->
                <div 
                    v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" 
                    class="flex-1 min-w-[200px]"
                >
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
                        @click="() => { selectedStores.value = []; startDate = ''; endDate = ''; }"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md transition-colors"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Data table -->
            <TableContainer>
                <DataTable 
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                />
            </TableContainer>
        </template>
    </component>
</template>

<style>
.dt-scroll-foot{
    top: 0;
    bottom: 0;
    z-index: 3;
    background-color: #7c0000;
    color:aliceblue
}
.dt-buttons {
    display: flex;               
    justify-content: flex-start; 
    align-items: center;    
    position: absolute;
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
}
</style>