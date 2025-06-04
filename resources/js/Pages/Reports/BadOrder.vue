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

const columns = [
    { data: 'storename', title: 'STORE' },
    { data: 'receiptid', title: 'RECEIPT' },
    { 
        data: 'createddate', 
        title: 'CREATED DATE', 
        render: function(data, type, row) {
            const date = new Date(data);
            return date.toLocaleDateString();  
        }
    },
    { 
        data: 'charge', 
        title: 'CHARGE',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'gcash', 
        title: 'GCASH',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'paymaya', 
        title: 'PAYMAYA',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'card', 
        title: 'CARD',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'loyaltycard', 
        title: 'LOYALTY CARD',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'foodpanda', 
        title: 'FOODPANDA',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'grabfood', 
        title: 'GRABFOOD',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    },
    { 
        data: 'representation', 
        title: 'REPRESENTATION',
        render: function(data) {
            return parseFloat(data).toFixed(2);
        }
    }
];

const options = {
    paging: true,
    pageLength: 25,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ],
};

const handleFilterChange = () => {
    if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
        alert('Start date cannot be later than end date');
        startDate.value = '';
        endDate.value = '';
        return;
    }
    
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
        }
    );
};

let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

onUnmounted(() => {
    clearTimeout(filterTimeout);
});

const totals = computed(() => {
    return filteredData.value.reduce((acc, curr) => {
        acc.charge += parseFloat(curr.charge || 0);
        acc.gcash += parseFloat(curr.gcash || 0);
        acc.paymaya += parseFloat(curr.paymaya || 0);
        acc.card += parseFloat(curr.card || 0);
        acc.loyaltycard += parseFloat(curr.loyaltycard || 0);
        acc.foodpanda += parseFloat(curr.foodpanda || 0);
        acc.grabfood += parseFloat(curr.grabfood || 0);
        acc.representation += parseFloat(curr.representation || 0);
        return acc;
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
</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:main>
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow">
                
                <div v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'MANAGER'" class="flex-1 min-w-[200px]">
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

            <details class="collapse collapse-plus bg-gray-100 rounded-none">
                    <summary class="collapse-title text-sm font-medium">AR DETAILS</summary>
                    <div class="collapse-content"> 
                      <div class="mb-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                          <div v-for="(value, key) in totals" :key="key" 
                              class="bg-white p-4 rounded-lg shadow">
                              <h3 class="text-sm font-medium text-gray-500 uppercase">Total {{ key }}</h3>
                              <p class="mt-1 text-lg font-semibold">₱ {{ value.toFixed(2) }}</p>
                          </div>
                      </div>
                    </div>
              </details>

            <TableContainer>
                <DataTable 
                    :data="filteredData" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <template #action="data">
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>