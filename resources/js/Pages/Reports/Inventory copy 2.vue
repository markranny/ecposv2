// resources/js/Pages/Reports/Inventory.vue
<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { router } from '@inertiajs/vue3';
import Main from "@/Layouts/Main.vue";
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
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

// Debug logging
onMounted(() => {
    console.log('Inventory prop:', props.inventory);
    console.log('Inventory type:', typeof props.inventory);
    console.log('Is Array:', Array.isArray(props.inventory));
});

// Initialize reactive refs with filter values
const selectedStores = ref(props.filters.selectedStores || []);
const startDate = ref(props.filters.startDate || '');
const endDate = ref(props.filters.endDate || '');

// DataTable columns configuration
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
        title: 'Received Delivery',
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
            return `<span class="${colorClass}">${value.toFixed(2)}</span>`;
        }
    }
];

// DataTable options
const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip',
    buttons: [
        'copy', 
        {
            extend: 'csv',
            title: 'Inventory Report'
        },
        {
            extend: 'excel',
            title: 'Inventory Report'
        },
        {
            extend: 'pdf',
            title: 'Inventory Report'
        },
        'print'
    ]
};

// Calculate totals from inventory data with safety checks
const totals = computed(() => {
    if (!props.inventory || !Array.isArray(props.inventory)) {
        console.warn('Inventory is not an array:', props.inventory);
        return {
            beginning: 0,
            receivedDelivery: 0,
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
    }

    return props.inventory.reduce((acc, item) => {
        const safeNumber = (value) => Number(value || 0);

        const itemWaste = 
            safeNumber(item.throw_away) +
            safeNumber(item.early_molds) +
            safeNumber(item.pull_out) +
            safeNumber(item.rat_bites) +
            safeNumber(item.ant_bites);

        return {
            beginning: acc.beginning + safeNumber(item.beginning),
            receivedDelivery: acc.receivedDelivery + safeNumber(item.received_delivery),
            sales: acc.sales + safeNumber(item.sales),
            waste: acc.waste + itemWaste,
            itemcount: acc.itemcount + safeNumber(item.item_count),
            ending: acc.ending + safeNumber(item.ending),
            variance: acc.variance + safeNumber(item.variance),
            throwAway: acc.throwAway + safeNumber(item.throw_away),
            earlyMolds: acc.earlyMolds + safeNumber(item.early_molds),
            pullOut: acc.pullOut + safeNumber(item.pull_out),
            ratBites: acc.ratBites + safeNumber(item.rat_bites),
            antBites: acc.antBites + safeNumber(item.ant_bites)
        };
    }, {
        beginning: 0,
        receivedDelivery: 0,
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
    });
});

// Handle filter changes with validation
const handleFilterChange = () => {
    if (startDate.value && endDate.value) {
        const start = new Date(startDate.value);
        const end = new Date(endDate.value);
        
        if (start > end) {
            alert('Start date cannot be later than end date');
            return;
        }
    }
    
    router.get(
        route('reports.inventory'),
        {
            startDate: startDate.value,
            endDate: endDate.value,
            stores: selectedStores.value
        },
        {
            preserveState: true,
            preserveScroll: true,
            onError: (errors) => {
                console.error('Filter update failed:', errors);
            }
        }
    );
};

// Clear all filters
const clearFilters = () => {
    selectedStores.value = [];
    startDate.value = '';
    endDate.value = '';
    handleFilterChange();
};

const totalNegativeVariance = computed(() => {
    if (!props.inventory || !Array.isArray(props.inventory)) {
        return 0;
    }

    // Filter out negative variance values and sum them up
    return props.inventory
        .map(item => Number(item.variance || 0))  // Convert to number and handle null/undefined
        .filter(variance => variance < 0)  // Only keep negative variances
        .reduce((sum, variance) => sum + variance, 0);  // Sum up the negative variances
});

const totalPositiveVariance = computed(() => {
    if (!props.inventory || !Array.isArray(props.inventory)) {
        return 0;
    }

    // Filter out negative variance values and sum them up
    return props.inventory
        .map(item => Number(item.variance || 0))  // Convert to number and handle null/undefined
        .filter(variance => variance > 0)  // Only keep negative variances
        .reduce((sum, variance) => sum + variance, 0);  // Sum up the negative variances
});


// Watch for filter changes with debounce
let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

// Cleanup
onUnmounted(() => {
    clearTimeout(filterTimeout);
});

// Initialize component
onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
});
</script>

<template>
    <Main active-tab="REPORTS">
        <template v-slot:main>
            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow">
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

            <details class="collapse collapse-plus bg-gray-100 rounded-none">
                    <summary class="collapse-title text-sm font-medium">SUMMARY</summary>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Beginning Balance</h3>
                            <p class="text-2xl">{{ totals.beginning.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Received</h3>
                            <p class="text-2xl">{{ totals.receivedDelivery.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Waste</h3>
                            <p class="text-2xl">{{ totals.waste.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Sales</h3>
                            <p class="text-2xl">{{ totals.sales.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Item Count</h3>
                            <p class="text-2xl">{{ totals.itemcount.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Ending Balance</h3>
                            <p class="text-2xl">{{ totals.ending.toFixed(2) }}</p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Negative Variance</h3>
                            <p class="text-2xl" :class="totalNegativeVariance < 0 ? 'text-red-600' : 'text-green-600'">
                                {{ totalNegativeVariance.toFixed(2) }}
                            </p>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="text-lg font-semibold">Total Negative Variance</h3>
                            <p class="text-2xl" :class="totalPositiveVariance > 0 ? 'text-red-600' : 'text-green-600'">
                                {{ totalPositiveVariance.toFixed(2) }}
                            </p>
                        </div>
                    </div>   

            </details>

            <!-- DataTable -->
            <TableContainer>
                <DataTable 
                    :data="inventory" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                />
            </TableContainer>
        </template>
    </Main>
</template>