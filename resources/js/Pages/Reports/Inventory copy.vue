<script setup>
import Main from "@/Layouts/Main.vue";  
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import { router } from '@inertiajs/vue3';
DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');

const props = defineProps({
    inventory: {
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

const filteredData = computed(() => {
    let filtered = [...props.inventory];
    
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

// Calculate totals
const totals = computed(() => {
    const total = {
        beginning: 0,
        receivedDelivery: 0,
        sales: 0,
        throwAway: 0,
        earlyMolds: 0,
        pullOut: 0,
        ratBites: 0,
        antBites: 0,
        ending: 0,
        variance: 0
    };

    filteredData.value.forEach(item => {
        total.beginning += Number(item.beginning) || 0;
        total.receivedDelivery += Number(item.received_delivery) || 0;
        total.sales += Number(item.sales) || 0;
        total.throwAway += Number(item.throw_away) || 0;
        total.earlyMolds += Number(item.early_molds) || 0;
        total.pullOut += Number(item.pull_out) || 0;
        total.ratBites += Number(item.rat_bites) || 0;
        total.antBites += Number(item.ant_bites) || 0;
        total.ending += Number(item.ending) || 0;
        total.variance += Number(item.variance) || 0;
    });

    return total;
});
</script>

<template>
    <Main active-tab="REPORTS">
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
                        @click="() => { selectedStores = []; startDate = ''; endDate = ''; }"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md"
                    >
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Updated Summary Cards -->
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
                        <h3 class="text-lg font-semibold">Total Sales</h3>
                        <p class="text-2xl">{{ totals.sales.toFixed(2) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold">Ending Balance</h3>
                        <p class="text-2xl">{{ totals.ending.toFixed(2) }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-semibold">Total Variance</h3>
                        <p class="text-2xl" :class="totals.variance < 0 ? 'text-red-600' : 'text-green-600'">
                            {{ totals.variance.toFixed(2) }}
                        </p>
                    </div>
                </div>

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
    </Main>
</template>