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
    console.log('userRole value:', props.userRole);
    console.log('Is Store?:', props.userRole.toUpperCase() === 'STORE');
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

const footerTotals = computed(() => {
    return props.ar.reduce((acc, curr) => {
        const netAmount = Number(curr.total_netamount || 0);
        const discAmount = Number(curr.total_discamount || 0);
        const grossAmount = Number(curr.total_grossamount || 0);
        const costAmount = Number(curr.total_costamount || 0);
        const costPrice = Number(curr.total_costprice || 0);
        const price = Number(curr.price || 0);
        const qty = Number(curr.qty || 0);
        const vatSales = Number(curr.vatablesales || 0);
        const vat = Number(curr.vat || 0);

        return {
            total_netamount: parseFloat((acc.total_netamount + netAmount).toFixed(2)),
            total_discamount: parseFloat((acc.total_discamount + discAmount).toFixed(2)),
            total_grossamount: parseFloat((acc.total_grossamount + grossAmount).toFixed(2)),
            total_costamount: parseFloat((acc.total_costamount + costAmount).toFixed(2)),
            total_costprice: parseFloat((acc.total_costprice + costPrice).toFixed(2)),
            total_price: parseFloat((acc.total_price + price).toFixed(2)),
            total_qty: parseFloat((acc.total_qty + qty).toFixed(2)),
            total_vatablesales: parseFloat((acc.total_vatablesales + vatSales).toFixed(2)),
            total_vat: parseFloat((acc.total_vat + vat).toFixed(2))
        };
    }, {
        total_netamount: 0,
        total_discamount: 0,
        total_grossamount: 0,
        total_costamount: 0,
        total_costprice: 0,
        total_price: 0,
        total_qty: 0,
        total_vatablesales: 0,
        total_vat: 0
    });
});

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

    router.get(route('reports.tsales'), params, {
        preserveState: true,
        preserveScroll: true,
    });
};

let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

const formatCurrency = (value) => {
    return `₱${parseFloat(value).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
};

const columns = [
    { data: 'store', title: 'Store', footer: 'Grand Total' },
    { data: 'staff', title: 'Staff' },
    { data: 'dateonly', title: 'Date' },
    { data: 'timeonly', title: 'Time' },
    { data: 'transactionid', title: 'Transaction ID' },
    { data: 'receiptid', title: 'Receipt ID' },
    { data: 'paymentmethod', title: 'Payment Method' },
    { data: 'custaccount', title: 'Customer' },
    { data: 'itemname', title: 'Item Name' },
    { data: 'itemgroup', title: 'Item Group' },
    { data: 'price', title: 'Price', render: (data) => formatCurrency(data ?? 0) },
    { data: 'qty', title: 'Quantity', render: (data) => data ?? 0 },
    { data: 'total_costprice', title: 'Cost Price', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.total_costprice ?? 0) },
    { data: 'total_grossamount', title: 'Gross Amount', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.total_grossamount ?? 0) },
    { data: 'total_costamount', title: 'Cost Amount', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.total_costamount ?? 0) },
    { data: 'total_discamount', title: 'Discount Amount', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.total_discamount ?? 0) },
    { data: 'total_netamount', title: 'Net Amount', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.total_netamount ?? 0) },
    { data: 'vatablesales', title: 'Vatable Sales', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.vatablesales ?? 0) },
    { data: 'vat', title: 'VAT', render: (data) => formatCurrency(data ?? 0), footer: formatCurrency(footerTotals.value.vat ?? 0) }
];


const options = {
    responsive: true,
    dom: 'lBfrtip',
    /* scrollX: true,
    scrollY: "70vh", */
    scrollCollapse: true,
    buttons: ['copy', { text: 'Export Excel', action: (e, dt, node, config) => exportToExcel() }, 'pdf', 'print'],
    order: [[0, 'asc']]
};

const exportToExcel = () => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Report');

    // Define all columns with proper formatting
    worksheet.columns = [
        { header: 'Store', key: 'store', width: 20 },
        { header: 'Staff', key: 'staff', width: 15 },
        { header: 'Date', key: 'dateonly', width: 12 },
        { header: 'Time', key: 'timeonly', width: 10 },
        { header: 'Transaction ID', key: 'transactionid', width: 15 },
        { header: 'Receipt ID', key: 'receiptid', width: 15 },
        { header: 'Payment Method', key: 'paymentmethod', width: 15 },
        { header: 'Customer', key: 'custaccount', width: 15 },
        { header: 'Item Name', key: 'itemname', width: 30 },
        { header: 'Item Group', key: 'itemgroup', width: 15 },
        { header: 'Price', key: 'price', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Quantity', key: 'qty', width: 10, style: { numFmt: '#,##0' } },
        { header: 'Cost Price', key: 'total_costprice', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Gross Amount', key: 'total_grossamount', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Cost Amount', key: 'total_costamount', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Discount Amount', key: 'total_discamount', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Net Amount', key: 'total_netamount', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'Vatable Sales', key: 'vatablesales', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'VAT', key: 'vat', width: 12, style: { numFmt: '#,##0.00' } }
    ];

    // Add styling to header row
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true };
    headerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFE0E0E0' }
    };

    // Add data rows with null value handling
    props.ar.forEach(item => {
        worksheet.addRow({
            store: item.store || '',
            staff: item.staff || '',
            dateonly: item.dateonly || '',
            timeonly: item.timeonly || '',
            transactionid: item.transactionid || '',
            receiptid: item.receiptid || '',
            paymentmethod: item.paymentmethod || '',
            itemname: item.itemname || '',
            itemgroup: item.itemgroup || '',
            price: Number(item.price) || 0,
            qty: Number(item.qty) || 0,
            total_costprice: Number(item.total_costprice) || 0,
            total_grossamount: Number(item.total_grossamount) || 0,
            total_costamount: Number(item.total_costamount) || 0,
            total_discamount: Number(item.total_discamount) || 0,
            total_netamount: Number(item.total_netamount) || 0,
            vatablesales: Number(item.vatablesales) || 0,
            vat: Number(item.vat) || 0
        });
    });

    // Calculate totals with null handling
    const totals = props.ar.reduce((acc, item) => ({
        qty: acc.qty + (Number(item.qty) || 0),
        total_costprice: acc.total_costprice + (Number(item.total_costprice) || 0),
        total_grossamount: acc.total_grossamount + (Number(item.total_grossamount) || 0),
        total_costamount: acc.total_costamount + (Number(item.total_costamount) || 0),
        total_discamount: acc.total_discamount + (Number(item.total_discamount) || 0),
        total_netamount: acc.total_netamount + (Number(item.total_netamount) || 0),
        vatablesales: acc.vatablesales + (Number(item.vatablesales) || 0),
        vat: acc.vat + (Number(item.vat) || 0)
    }), {
        qty: 0,
        total_costprice: 0,
        total_grossamount: 0,
        total_costamount: 0,
        total_discamount: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0
    });

    // Add footer row with calculated totals
    const footerRow = worksheet.addRow({
        store: 'Grand Total',
        qty: totals.qty,
        total_costprice: totals.total_costprice,
        total_grossamount: totals.total_grossamount,
        total_costamount: totals.total_costamount,
        total_discamount: totals.total_discamount,
        total_netamount: totals.total_netamount,
        vatablesales: totals.vatablesales,
        vat: totals.vat
    });

    // Style footer row
    footerRow.font = { bold: true };
    footerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFF0F0F0' }
    };

    // Apply borders to all cells
    worksheet.eachRow((row) => {
        row.eachCell((cell) => {
            cell.border = {
                top: { style: 'thin' },
                left: { style: 'thin' },
                bottom: { style: 'thin' },
                right: { style: 'thin' }
            };
        });
    });

    // Generate and download the file
    workbook.xlsx.writeBuffer().then((buffer) => {
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'transactions.xlsx';
        link.click();
        window.URL.revokeObjectURL(url);
    });
};
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
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

            <!-- Table with Scrollbar Container -->
            <div class="h-full w-full">
                <TableContainer class="overflow-auto">
                    <DataTable 
                    :data="props.ar" 
                    :columns="columns" 
                    class="min-w-full"
                    :options="options"
                    />
                </TableContainer>
            </div>
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

.dt-buttons .buttons-copy {
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}

.dt-button {
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}

.dt-buttons .buttons-print {
    padding: 10px;
    background-color: blue;
    margin: 10px;
    border-radius: 5px;
    color: white;
}

.dt-search {
    float: right;
    padding-bottom: 20px;
}

div.dt-container .dt-length, div.dt-container .dt-search, div.dt-container .dt-info, div.dt-container .dt-processing, div.dt-container .dt-paging {
padding: 10px;
}

div.dt-container .dt-length{
    width: 20%;
}
.dt-length .dt-input{
    width: 20%;
}

/* Make sure the table is responsive and scrollable */
.overflow-auto {
    overflow: auto;
}
</style>
