<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import ExcelJS from 'exceljs';
DataTable.use(DataTablesCore);

const props = defineProps({
    inventory: {
        type: Array,
        required: true,
        default: () => []
    },
    stores: {
        type: Array,
        required: true,
        default: () => []
    },
    userRole: {
        type: String,
        required: true
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

const selectedStores = ref(props.filters.selectedStores || []);
const startDate = ref(props.filters.startDate || '');
const endDate = ref(props.filters.endDate || '');

// Initialize totals with default values
const defaultTotals = {
    beginning: 0,
    receivedDelivery: 0,
    stockTransfer: 0,
    sales: 0,
    waste: 0,
    itemcount: 0,
    ending: 0,
    variance: 0,
    throwAway: 0,
    earlyMolds: 0,
    pullOut: 0,
    ratBites: 0,
    antBites: 0
};

const totals = computed(() => {
    if (!props.inventory?.length) return defaultTotals;
    
    return props.inventory.reduce((acc, item) => {
        const safeNum = (val) => Number(val || 0);
        const itemWaste = safeNum(item.throw_away) + safeNum(item.early_molds) + 
                         safeNum(item.pull_out) + safeNum(item.rat_bites) + 
                         safeNum(item.ant_bites);

        return {
            beginning: acc.beginning + safeNum(item.beginning),
            receivedDelivery: acc.receivedDelivery + safeNum(item.received_delivery),
            stockTransfer: acc.stockTransfer + safeNum(item.stock_transfer),
            sales: acc.sales + safeNum(item.sales),
            waste: acc.waste + itemWaste,
            itemcount: acc.itemcount + safeNum(item.item_count),
            ending: acc.ending + safeNum(item.ending),
            variance: acc.variance + safeNum(item.variance),
            throwAway: acc.throwAway + safeNum(item.throw_away),
            earlyMolds: acc.earlyMolds + safeNum(item.early_molds),
            pullOut: acc.pullOut + safeNum(item.pull_out),
            ratBites: acc.ratBites + safeNum(item.rat_bites),
            antBites: acc.antBites + safeNum(item.ant_bites)
        };
    }, defaultTotals);
});

const totalNegativeVariance = computed(() => {
    return props.inventory?.reduce((sum, item) => {
        const variance = Number(item.variance || 0);
        return variance < 0 ? sum + variance : sum;
    }, 0) || 0;
});

const totalPositiveVariance = computed(() => {
    return props.inventory?.reduce((sum, item) => {
        const variance = Number(item.variance || 0);
        return variance > 0 ? sum + variance : sum;
    }, 0) || 0;
});

const columns = [
    { 
        data: 'itemname',
        title: 'Item Name',
        className: 'min-w-[200px]'
    },
    {
        data: 'beginning',
        title: 'Beginning',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'received_delivery',
        title: 'Received',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'stock_transfer',
        title: 'Stock Transfer',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'sales',
        title: 'Sales',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'throw_away',
        title: 'Throw Away',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'early_molds',
        title: 'Early Molds',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'pull_out',
        title: 'Pull Out',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'rat_bites',
        title: 'Rat Bites',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'ant_bites',
        title: 'Ant Bites',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'item_count',
        title: 'Item Count',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'ending',
        title: 'Ending',
        className: 'text-right font-bold',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'variance',
        title: 'Variance',
        className: 'text-right',
        render: (data, type, row) => {
            const value = Number(data || 0);
            const colorClass = value < 0 ? 'text-red-600' : 'text-green-600';
            return `<span class="${colorClass} font-bold">${value.toFixed(2)}</span>`;
        }
    }
];

const options = {
    serverSide: true,
    processing: true,
    ajax: {
        url: '/api/inventory',
        data: function(d) {
            // Add custom filters
            return {
                ...d,
                startDate: startDate.value,
                endDate: endDate.value,
                selectedStores: selectedStores.value
            };
        }
    },
    pageLength: 25,
    // Enable lazy loading for better performance
    deferRender: true,
    // Optimize column rendering
    columns: columns.map(col => ({
        ...col,
        // Only sort/search on visible columns
        orderable: !col.render,
        searchable: !col.render
    })),
    // Enable caching for better performance
    stateSave: true,
    // Optimize DOM structure
    dom: '<"top"Bfr>t<"bottom"lip>',
    // Enable virtual scrolling for large datasets
    scroller: true,
    scrollY: '50vh',
    scrollCollapse: true
};
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999]">
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

            <!-- Summary Section -->
            <details class="mb-4 bg-white rounded-lg shadow" open>
                <summary class="px-4 py-3 text-lg font-medium cursor-pointer">
                    Inventory Summary
                </summary>
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Beginning Balance -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Beginning Balance</h3>
                            <p class="text-2xl mt-1">{{ totals.beginning.toFixed(2) }}</p>
                        </div>
                        <!-- Received -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Total Received</h3>
                            <p class="text-2xl mt-1">{{ totals.receivedDelivery.toFixed(2) }}</p>
                        </div>
                        <!-- Stock Transfer -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Stock Transfer</h3>
                            <p class="text-2xl mt-1">{{ totals.stockTransfer.toFixed(2) }}</p>
                        </div>
                        <!-- Sales -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Total Sales</h3>
                            <p class="text-2xl mt-1">{{ totals.sales.toFixed(2) }}</p>
                        </div>
                        <!-- Total Waste -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Total Waste</h3>
                            <p class="text-2xl mt-1">{{ totals.waste.toFixed(2) }}</p>
                        </div>
                        <!-- Item Count -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Current Count</h3>
                            <p class="text-2xl mt-1">{{ totals.itemcount.toFixed(2) }}</p>
                        </div>
                        <!-- Ending -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Ending Balance</h3>
                            <p class="text-2xl mt-1 font-bold">{{ totals.ending.toFixed(2) }}</p>
                        </div>
                        <!-- Variance -->
                        <div class="bg-gray-50 p-4 rounded-lg col-span-2">
                            <h3 class="text-sm font-semibold text-gray-600">Total Variance</h3>
                            <div class="flex justify-between mt-1">
                                <div>
                                    <span class="text-sm text-gray-500">Negative:</span>
                                    <p class="text-xl text-red-600">{{ totalNegativeVariance.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Positive:</span>
                                    <p class="text-xl text-green-600">{{ totalPositiveVariance.toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Waste Breakdown -->
                        <div class="bg-gray-50 p-4 rounded-lg col-span-full">
                            <h3 class="text-sm font-semibold text-gray-600 mb-2">Waste Breakdown</h3>
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Throw Away</p>
                                    <p class="text-lg">{{ totals.throwAway.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Early Molds</p>
                                    <p class="text-lg">{{ totals.earlyMolds.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Pull Out</p>
                                    <p class="text-lg">{{ totals.pullOut.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Rat Bites</p>
                                    <p class="text-lg">{{ totals.ratBites.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Ant Bites</p>
                                    <p class="text-lg">{{ totals.antBites.toFixed(2) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </details>

            <!-- DataTable -->
            <div class="bg-white rounded-lg shadow">
                <TableContainer>
                    <DataTable 
                        :data="inventory" 
                        :columns="columns" 
                        class="w-full relative display" 
                        :options="options"
                    />
                </TableContainer>
            </div>
        </template>
    </component>
</template>

<style>
.dt-buttons {
    display: flex;               
    justify-content: flex-start; 
    align-items: center;    
    position: absolute;
    z-index: 1;
    margin: 10px;
}

.dt-button, 
.dt-buttons .buttons-copy,
.dt-buttons .buttons-print {
    padding: 10px;
    background-color: #3b82f6;
    margin-right: 10px;
    border-radius: 5px;
    color: white;
    transition: background-color 0.2s;
}

.dt-button:hover, 
.dt-buttons .buttons-copy:hover,
.dt-buttons .buttons-print:hover {
    background-color: #2563eb;
}

.dataTables_filter {
    float: right;
    padding: 20px;
    position: relative;
    z-index: 999;
}

.dataTables_filter input {
    padding: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    margin-left: 8px;
}

table.dataTable {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 60px;
}

table.dataTable thead th {
    background-color: #f3f4f6;
    padding: 12px;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
}

table.dataTable tbody td {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
}

table.dataTable tbody tr:hover {
    background-color: #f9fafb;
}

@media (max-width: 768px) {
    .dt-buttons {
        position: static;
        justify-content: center;
        margin-bottom: 20px;
    }
    
    .dataTables_filter {
        float: none;
        text-align: center;
        padding: 10px;
    }
}
</style>