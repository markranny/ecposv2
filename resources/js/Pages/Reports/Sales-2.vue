<script setup>
import Main from "@/Layouts/Main.vue";  
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
    
    // Make sure we're sending the correct format for stores
    const params = {
        startDate: startDate.value || null,
        endDate: endDate.value || null,
        stores: selectedStores.value.length > 0 ? selectedStores.value : null
    };

    router.get(
        route('reports.sales'),
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

// DataTable columns configuration
const columns = [
    { data: 'store', title: 'Store' },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => `₱${parseFloat(data).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => `₱${parseFloat(data).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => `₱${parseFloat(data).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`
    }
];

const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip', // Ensure buttons are configured with this
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
    ]
};

const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Report');

    // Add the header row
    worksheet.addRow(['Store', 'Net Amount', 'Discount Amount', 'Gross Amount']);

    // Format columns
    worksheet.columns = [
        { header: 'Store', key: 'store', width: 20 },
        { header: 'Net Amount', key: 'netAmount', width: 20, style: { numFmt: '#,##0.00' } },
        { header: 'Discount Amount', key: 'discountAmount', width: 20, style: { numFmt: '#,##0.00' } },
        { header: 'Gross Amount', key: 'grossAmount', width: 20, style: { numFmt: '#,##0.00' } }
    ];

    // Add the data rows
    props.ar.forEach(item => {
        worksheet.addRow({
            store: item.store,
            netAmount: item.total_netamount,
            discountAmount: item.total_discamount,
            grossAmount: item.total_grossamount
        });
    });

    // Create a buffer and download the Excel file
    workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'sales_report.xlsx';
        link.click();
        window.URL.revokeObjectURL(url);
    });
};
</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:main>
            <!-- Filter controls -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow">
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
    </Main>
</template>