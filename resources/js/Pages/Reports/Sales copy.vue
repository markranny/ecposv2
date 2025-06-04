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

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});

const layoutComponent = computed(() => {
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

// Filter change handler
const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }

    // Reload the data from the server with the selected filters
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

// Currency formatter
const formatCurrency = (value) => {
    return `₱${parseFloat(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
};

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
    return filteredData.value.reduce((acc, row) => {
        return {
            total_netamount: acc.total_netamount + (parseFloat(row.total_netamount) || 0),
            total_discamount: acc.total_discamount + (parseFloat(row.total_discamount) || 0),
            total_grossamount: acc.total_grossamount + (parseFloat(row.total_grossamount) || 0)
        };
    }, {
        total_netamount: 0,
        total_discamount: 0,
        total_grossamount: 0,
    });
});

const columns = [
    { 
        data: 'store', 
        title: 'Store', 
        footer: 'Grand Total'
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: function(data) {
            return formatCurrency(parseFloat(data) || 0);
        },
        footer: function() {
            return footerTotals.value.total_netamount.toFixed(2);
        }
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: function(data) {
            return formatCurrency(parseFloat(data) || 0);
        },
        footer: function() {
            return footerTotals.value.total_discamount.toFixed(2);
        }
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: function(data) {
            return formatCurrency(parseFloat(data) || 0);
        },
        footer: function() {
            return footerTotals.value.total_grossamount.toFixed(2);
        }
    }
];

const options = {
    responsive: true,
    pageLength: 25,
    dom: 'Bfrtip',
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


// Export to Excel function
const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Report');

    worksheet.addRow(['Store', 'Net Amount', 'Discount Amount', 'Gross Amount']);

    worksheet.columns = [
        { header: 'Store', key: 'store', width: 20 },
        { header: 'Net Amount', key: 'netAmount', width: 20, style: { numFmt: '#,##0.00' } },
        { header: 'Discount Amount', key: 'discountAmount', width: 20, style: { numFmt: '#,##0.00' } },
        { header: 'Gross Amount', key: 'grossAmount', width: 20, style: { numFmt: '#,##0.00' } }
    ];

    props.ar.forEach(item => {
        worksheet.addRow({
            store: item.store,
            netAmount: item.total_netamount,
            discountAmount: item.total_discamount,
            grossAmount: item.total_grossamount
        });
    });

    worksheet.addRow({
        store: 'Grand Total',
        netAmount: footerTotals.value.total_netamount,
        discountAmount: footerTotals.value.total_discamount,
        grossAmount: footerTotals.value.total_grossamount
    });

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
    <component :is="layoutComponent" active-tab="REPORTS">
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
                        @click="clearFilters"
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
tfoot {
    background-color: #7c0000 !important;
    color: aliceblue !important;
}

tfoot td {
    background-color: #7c0000 !important;
    color: aliceblue !important;
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