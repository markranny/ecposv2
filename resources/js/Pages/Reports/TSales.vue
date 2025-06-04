<script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted } from "vue";
import 'datatables.net-buttons';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import Swal from 'sweetalert2';
import ExcelJS from 'exceljs';

DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);

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

const footerTotals = computed(() => {
    return filteredData.value.reduce((acc, row) => {
        // Existing totals
        acc.total_discamount += (parseFloat(row.total_discamount) || 0);
        acc.total_costprice += (parseFloat(row.total_costprice) || 0);
        acc.total_netamount += (parseFloat(row.total_netamount) || 0);
        acc.vatablesales += (parseFloat(row.vatablesales) || 0);
        acc.vat += (parseFloat(row.vat) || 0);
        acc.total_grossamount += (parseFloat(row.total_grossamount) || 0);
        acc.total_costamount += (parseFloat(row.total_costamount) || 0);
        acc.total_qty += Math.round(row.qty || 0);

        // New totals
        acc.cash += (parseFloat(row.cash) || 0);
        acc.charge += (parseFloat(row.charge) || 0);
        acc.representation += (parseFloat(row.representation) || 0);
        acc.gcash += (parseFloat(row.gcash) || 0);
        acc.foodpanda += (parseFloat(row.foodpanda) || 0);
        acc.grabfood += (parseFloat(row.grabfood) || 0);
        acc.mrktgdisc += (parseFloat(row.mrktgdisc) || 0);
        acc.rddisc += (parseFloat(row.rddisc) || 0);
        acc.discofferid += (parseFloat(row.discofferid) || 0);

        acc.bw_products += (parseFloat(row.bw_products) || 0);
        acc.merchandise += (parseFloat(row.merchandise) || 0);

        acc.partycakes += (parseFloat(row.partycakes) || 0);
        

        return acc;
    }, {
        // Initialize all new totals
        total_qty: 0,
        total_discamount: 0,
        total_costprice: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0,
        total_grossamount: 0,
        total_costamount: 0,
        
        // New totals
        cash: 0,
        charge: 0,
        representation: 0,
        gcash: 0,
        foodpanda: 0,
        grabfood: 0,
        mrktgdisc: 0,
        rddisc: 0,

        bw_products: 0,
        merchandise: 0,
        partycakes: 0
        
    });
});

const columns = [
    { data: 'storename', title: 'Store', footer: 'Grand Total' },
    { data: 'staff', title: 'Staff' },
    { data: 'createddate', title: 'Date' },
    { data: 'timeonly', title: 'Time' },
    { data: 'transactionid', title: 'Transaction ID' },
    { data: 'receiptid', title: 'Receipt ID' },
    { data: 'paymentmethod', title: 'Payment Method' },
    { data: 'custaccount', title: 'Customer' },
    { data: 'itemname', title: 'Item Name' },
    { data: 'itemgroup', title: 'Item Group' },
    { data: 'discofferid', title: 'PROMO' },
    
    // Existing columns
    { 
        data: 'qty', 
        title: 'Qty',
        render: (data) => Math.round(data || 0),
        footer: () => Math.round(footerTotals.value.total_qty)
    },
    { 
        data: 'total_costprice', 
        title: 'Cost Price',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_costprice.toFixed(2)
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_grossamount.toFixed(2)
    },
    { 
        data: 'total_costamount', 
        title: 'Cost Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_costamount.toFixed(2)
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_discamount.toFixed(2)
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.total_netamount.toFixed(2)
    },
    { 
        data: 'vatablesales', 
        title: 'Vatable Sales',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.vatablesales.toFixed(2)
    },
    { 
        data: 'vat', 
        title: 'VAT',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.vat.toFixed(2)
    },

    // Payment Method Columns
    { 
        data: 'cash', 
        title: 'Cash',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.cash.toFixed(2)
    },
    { 
        data: 'charge', 
        title: 'Charge',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.charge.toFixed(2)
    },
    { 
        data: 'representation', 
        title: 'Representation',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.representation.toFixed(2)
    },
    { 
        data: 'gcash', 
        title: 'GCash',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.gcash.toFixed(2)
    },
    { 
        data: 'foodpanda', 
        title: 'FoodPanda',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.foodpanda.toFixed(2)
    },
    { 
        data: 'grabfood', 
        title: 'GrabFood',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.grabfood.toFixed(2)
    },
    { 
        data: 'mrktgdisc', 
        title: 'Mktg Disc',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.mrktgdisc.toFixed(2)
    },
    { 
        data: 'rddisc', 
        title: 'RD Disc',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.rddisc.toFixed(2)
    },
    { 
        data: 'bw_products', 
        title: 'BW PRODUCTS',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: (data) => {
            const total = footerTotals.value.bw_products || 0;
            return total.toFixed(2);
        }
    },
    { 
        data: 'merchandise', 
        title: 'MERCHANDISE',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: (data) => {
            const total = footerTotals.value.merchandise || 0;
            return total.toFixed(2);
        }
    },
    { 
        data: 'partycakes', 
        title: 'PARTYCAKES',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: () => footerTotals.value.partycakes.toFixed(2)
    }
];

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
                // Pass the DataTable instance to export only filtered data
                exportToExcel(dt);
            }
        },
        'pdf',
        'print'
    ],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        
        // Initialize totals object for filtered data
        const filteredTotals = {
            total_qty: 0,
            total_costprice: 0,
            total_grossamount: 0,
            total_costamount: 0,
            total_discamount: 0,
            total_netamount: 0,
            vatablesales: 0,
            vat: 0,
            cash: 0,
            charge: 0,
            representation: 0,
            gcash: 0,
            foodpanda: 0,
            grabfood: 0,
            mrktgdisc: 0,
            rddisc: 0,
            bw_products: 0,
            merchandise: 0,
            partycakes: 0
        };

        // Calculate totals only for filtered/searched rows
        api.rows({ search: 'applied' }).every(function(rowIdx) {
            const data = this.data();
            // Update numerical totals
            Object.keys(filteredTotals).forEach(key => {
                if (key === 'total_qty') {
                    filteredTotals[key] += Math.round(Number(data[key]) || 0);
                } else {
                    filteredTotals[key] += Number(data[key]) || 0;
                }
            });
        });

        // Update footer with new totals
        const footerRow = api.table().footer().querySelectorAll('td, th');
        
        // Map of column indices to their corresponding total keys
        const columnMappings = [
            { index: 11, key: 'total_qty', round: true },
            { index: 12, key: 'total_costprice' },
            { index: 13, key: 'total_grossamount' },
            { index: 14, key: 'total_costamount' },
            { index: 15, key: 'total_discamount' },
            { index: 16, key: 'total_netamount' },
            { index: 17, key: 'vatablesales' },
            { index: 18, key: 'vat' },
            { index: 19, key: 'cash' },
            { index: 20, key: 'charge' },
            { index: 21, key: 'representation' },
            { index: 22, key: 'gcash' },
            { index: 23, key: 'foodpanda' },
            { index: 24, key: 'grabfood' },
            { index: 25, key: 'mrktgdisc' },
            { index: 26, key: 'rddisc' },
            { index: 27, key: 'bw_products' },
            { index: 28, key: 'merchandise' },
            { index: 29, key: 'partycakes' }
        ];

        // Update footer cells with filtered totals
        columnMappings.forEach(({ index, key, round }) => {
            const total = filteredTotals[key];
            if (footerRow[index]) {
                footerRow[index].textContent = round ? 
                    Math.round(total).toString() : 
                    total.toFixed(2);
            }
        });
    }
};

const exportToExcel = (dt) => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Data');
    
    // Define columns with proper width and number formats
    const columns = [
        { header: 'STORE', key: 'storename', width: 20 },
        { header: 'STAFF', key: 'staff', width: 15 },
        { header: 'DATE', key: 'createddate', width: 12 },
        { header: 'TIME', key: 'timeonly', width: 10 },
        { header: 'TRANSACTION ID', key: 'transactionid', width: 15 },
        { header: 'RECEIPT ID', key: 'receiptid', width: 15 },
        { header: 'PAYMENT METHOD', key: 'paymentmethod', width: 15 },
        { header: 'CUSTOMER', key: 'custaccount', width: 20 },
        { header: 'ITEM NAME', key: 'itemname', width: 25 },
        { header: 'ITEM GROUP', key: 'itemgroup', width: 15 },
        { header: 'PROMO', key: 'discofferid', width: 15 },
        { header: 'QTY', key: 'qty', width: 10 },
        { header: 'COST PRICE', key: 'total_costprice', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'GROSS AMOUNT', key: 'total_grossamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'COST AMOUNT', key: 'total_costamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'DISCOUNT AMOUNT', key: 'total_discamount', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'NET AMOUNT', key: 'total_netamount', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'VATABLE SALES', key: 'vatablesales', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'VAT', key: 'vat', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'CASH', key: 'cash', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'CHARGE', key: 'charge', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'REPRESENTATION', key: 'representation', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'GCASH', key: 'gcash', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'FOODPANDA', key: 'foodpanda', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'GRABFOOD', key: 'grabfood', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'MKTG DISC', key: 'mrktgdisc', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'RD DISC', key: 'rddisc', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'BW PRODUCTS', key: 'bw_products', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'MERCHANDISE', key: 'merchandise', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'PARTY CAKES', key: 'partycakes', width: 15, style: { numFmt: '#,##0.00' } }
    ];

    worksheet.columns = columns;

    // Style the header row
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true };
    headerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF333F4F' }
    };
    headerRow.font = { color: { argb: 'FFFFFFFF' }, bold: true };

    // Get filtered data from DataTable
    const filteredRows = dt.rows({ search: 'applied' }).data().toArray();
    
    // Add filtered data to worksheet
    filteredRows.forEach(row => {
        worksheet.addRow({
            storename: row.storename || '',
            staff: row.staff || '',
            createddate: row.createddate ? new Date(row.createddate) : null,
            timeonly: row.timeonly || '',
            transactionid: row.transactionid || '',
            receiptid: row.receiptid || '',
            paymentmethod: row.paymentmethod || '',
            custaccount: row.custaccount || '',
            itemname: row.itemname || '',
            itemgroup: row.itemgroup || '',
            discofferid: row.discofferid || '',
            qty: Math.round(Number(row.qty) || 0),
            total_costprice: Number(row.total_costprice) || 0,
            total_grossamount: Number(row.total_grossamount) || 0,
            total_costamount: Number(row.total_costamount) || 0,
            total_discamount: Number(row.total_discamount) || 0,
            total_netamount: Number(row.total_netamount) || 0,
            vatablesales: Number(row.vatablesales) || 0,
            vat: Number(row.vat) || 0,
            cash: Number(row.cash) || 0,
            charge: Number(row.charge) || 0,
            representation: Number(row.representation) || 0,
            gcash: Number(row.gcash) || 0,
            foodpanda: Number(row.foodpanda) || 0,
            grabfood: Number(row.grabfood) || 0,
            mrktgdisc: Number(row.mrktgdisc) || 0,
            rddisc: Number(row.rddisc) || 0,
            bw_products: Number(row.bw_products) || 0,
            merchandise: Number(row.merchandise) || 0,
            partycakes: Number(row.partycakes) || 0
        });
    });

    // Calculate totals for filtered data
    const filteredTotals = filteredRows.reduce((acc, row) => ({
        total_qty: acc.total_qty + Math.round(Number(row.qty) || 0),
        total_costprice: acc.total_costprice + Number(row.total_costprice || 0),
        total_grossamount: acc.total_grossamount + Number(row.total_grossamount || 0),
        total_costamount: acc.total_costamount + Number(row.total_costamount || 0),
        total_discamount: acc.total_discamount + Number(row.total_discamount || 0),
        total_netamount: acc.total_netamount + Number(row.total_netamount || 0),
        vatablesales: acc.vatablesales + Number(row.vatablesales || 0),
        vat: acc.vat + Number(row.vat || 0),
        cash: acc.cash + Number(row.cash || 0),
        charge: acc.charge + Number(row.charge || 0),
        representation: acc.representation + Number(row.representation || 0),
        gcash: acc.gcash + Number(row.gcash || 0),
        foodpanda: acc.foodpanda + Number(row.foodpanda || 0),
        grabfood: acc.grabfood + Number(row.grabfood || 0),
        mrktgdisc: acc.mrktgdisc + Number(row.mrktgdisc || 0),
        rddisc: acc.rddisc + Number(row.rddisc || 0),
        bw_products: acc.bw_products + Number(row.bw_products || 0),
        merchandise: acc.merchandise + Number(row.merchandise || 0),
        partycakes: acc.partycakes + Number(row.partycakes || 0)
    }), {
        total_qty: 0,
        total_costprice: 0,
        total_grossamount: 0,
        total_costamount: 0,
        total_discamount: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0,
        cash: 0,
        charge: 0,
        representation: 0,
        gcash: 0,
        foodpanda: 0,
        grabfood: 0,
        mrktgdisc: 0,
        rddisc: 0,
        bw_products: 0,
        merchandise: 0,
        partycakes: 0
    });

    // Add totals row
    const totalsRow = worksheet.addRow({
        storename: 'GRAND TOTAL',
        staff: '',
        createddate: '',
        timeonly: '',
        transactionid: '',
        receiptid: '',
        paymentmethod: '',
        custaccount: '',
        itemname: '',
        itemgroup: '',
        discofferid: '',
        qty: filteredTotals.total_qty,
        total_costprice: filteredTotals.total_costprice,
        total_grossamount: filteredTotals.total_grossamount,
        total_costamount: filteredTotals.total_costamount,
        total_discamount: filteredTotals.total_discamount,
        total_netamount: filteredTotals.total_netamount,
        vatablesales: filteredTotals.vatablesales,
        vat: filteredTotals.vat,
        cash: filteredTotals.cash,
        charge: filteredTotals.charge,
        representation: filteredTotals.representation,
        gcash: filteredTotals.gcash,
        foodpanda: filteredTotals.foodpanda,
        grabfood: filteredTotals.grabfood,
        mrktgdisc: filteredTotals.mrktgdisc,
        rddisc: filteredTotals.rddisc,
        bw_products: filteredTotals.bw_products,
        merchandise: filteredTotals.merchandise,
        partycakes: filteredTotals.partycakes
    });

    // Style totals row
    totalsRow.font = { bold: true };
    totalsRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFE9ECEF' }
    };

    // Format date cells
    worksheet.getColumn('createddate').numFmt = 'yyyy-mm-dd';

    // Add autofilter
    worksheet.autoFilter = {
        from: { row: 1, column: 1 },
        to: { row: 1, column: worksheet.columns.length }
    };

    // Apply borders to all cells
    worksheet.eachRow((row, rowNumber) => {
        row.eachCell((cell) => {
            cell.border = {
                top: { style: 'thin' },
                left: { style: 'thin' },
                bottom: { style: 'thin' },
                right: { style: 'thin' }
            };
        });
    });

    // Freeze the header row
    worksheet.views = [
        { state: 'frozen', xSplit: 0, ySplit: 1, topLeftCell: 'A2', activeCell: 'A2' }
    ];

    // Generate and download the file
    try {
        workbook.xlsx.writeBuffer()
            .then(buffer => {
                const blob = new Blob([buffer], { 
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = `Sales_Report_${new Date().toISOString().split('T')[0]}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error generating Excel file:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Failed',
                    text: 'Failed to generate Excel file. Please try again.'
                });
            });
    } catch (error) {
        console.error('Error in Excel export:', error);
        Swal.fire({
            icon: 'error',
            title: 'Export Failed',
            text: 'An error occurred while exporting to Excel.'
        });
    }
};

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

const populatePaymentMethodColumns = (data) => {
    return data.map(row => {
        const updatedRow = {
            ...row,
            cash: 0,
            charge: 0,
            representation: 0,
            gcash: 0,
            foodpanda: 0,
            grabfood: 0,
            mrktgdisc: 0,
            rddisc: 0,
            bw_products: 0,
            merchandise: 0,
            partycakes: 0
        };

        const paymentMethod = row.paymentmethod ? row.paymentmethod.toLowerCase() : '';
        const grossAmount = parseFloat(row.total_grossamount) || 0;
        const discAmount = parseFloat(row.total_discamount) || 0;
        const netAmount = parseFloat(row.total_netamount) || 0; // Use the actual netamount from data
        const promoType = row.discofferid ? row.discofferid.toUpperCase() : '';
        const itemGroupValue = row.itemgroup ? row.itemgroup.toUpperCase() : '';
        const itemName = row.itemname ? row.itemname.toUpperCase() : '';

        // Handle payment methods - use netAmount for all payment methods
        // For negative values, we should still use netAmount to maintain consistency
        const paymentAmount = netAmount; // This ensures consistency regardless of positive/negative values

        switch(paymentMethod) {
            case 'cash':
                updatedRow.cash = paymentAmount;
                break;
            case 'charge':
                updatedRow.charge = paymentAmount;
                break;
            case 'representation':
                updatedRow.representation = paymentAmount;
                break;
            case 'gcash':
                updatedRow.gcash = paymentAmount;
                break;
            case 'foodpanda':
                updatedRow.foodpanda = paymentAmount;
                break;
            case 'grabfood':
                updatedRow.grabfood = paymentAmount;
                break;
            default:
                console.warn(`Unhandled payment method: ${paymentMethod}`);
        }

        // Handle PROMO discounts
        if (promoType === 'SENIOR DISCOUNT' || promoType === 'PWD DISCOUNT') {
            updatedRow.rddisc = discAmount;
        } else if (promoType && discAmount > 0) {
            updatedRow.mrktgdisc = discAmount;
        }

        // Handle product classifications
        if (itemName === 'PARTY CAKES') {
            updatedRow.partycakes = grossAmount;
        } else if (itemGroupValue.includes('BW') || itemGroupValue.includes('CEBU') || itemGroupValue.includes('SVN')) {
            updatedRow.bw_products = grossAmount;
        } else if (itemGroupValue) {
            updatedRow.merchandise = grossAmount;
        }

        return updatedRow;
    });
};

const syncPaymentMethods = async () => {
    isLoading.value = true;
    try {
        const loadingAlert = Swal.fire({
            title: 'Syncing Payment Methods',
            text: 'Please wait while we synchronize the payment methods...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        props.ec.splice(0, props.ec.length, ...populatePaymentMethodColumns(props.ec));

        await new Promise(resolve => setTimeout(resolve, 500));

        Swal.close();

        Swal.fire({
            icon: 'success',
            title: 'Sync Successful!',
            text: 'Payment methods have been synchronized and exported to Excel.',
            timer: 3000,
            timerProgressBar: true,
            showConfirmButton: false
        });
    } catch (error) {
        Swal.close();
        Swal.fire({
            icon: 'error',
            title: 'Sync Failed',
            text: `An error occurred while syncing: ${error.message}`,
            confirmButtonText: 'OK'
        });

        console.error('Error syncing payment methods:', error);
    } finally {
        isLoading.value = false;  
    }
};


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

                <button 
                        @click="syncPaymentMethods"
                        :disabled="isLoading"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center"
                    >
                        <span v-if="!isLoading">Sync</span>
                        <span v-else>Syncing...</span>
                    </button>
            </div>

            <!-- Data table -->
            <TableContainer class="overflow-auto">
                <DataTable 
                    v-if="filteredData.length > 0"
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <template #action="data">
                    </template>
                </DataTable>

                <!-- Fallback message when no data is available -->
                <p v-else>No data available for the selected filters.</p>
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