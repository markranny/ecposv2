# StockCountingLine.vue
<script setup>
import Create from "@/Components/StockCountingLine/Create.vue";
import GetBWP from "@/Components/StockCountingLine/GetBWP.vue";
import CopyFrom from "@/Components/StockCountingLine/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import WasteTypeModal from "@/Components/Modals/WasteTypeModal.vue";
import Main from "@/Layouts/Main.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Cart from "@/Components/Svgs/Cart.vue";
import Transfer from "@/Components/Svgs/Transfer.vue";
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, reactive, onBeforeUnmount } from "vue";
import axios from 'axios';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';

DataTable.use(DataTablesCore);

const props = defineProps({
    stockcountingtrans: {
        type: Array,
        required: true,
    },
    journalid: {
        type: [String, Number],
        required: true,
    },
    items: {
        type: Array,
        required: true, 
    },
    isPosted: {
        type: Number,
        required: true,
    },
});

// State management
const JOURNALID = ref('');
const isLoading = ref(false);
const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);
const tableData = ref([]);
const updatedValues = reactive({});
const message = reactive({
    text: '',
    type: ''
});

// Column visibility state
const showColumnMenu = ref(false);
const columnVisibility = ref({
    ITEMID: true,
    itemname: true,
    itemgroup: true,
    ADJUSTMENT: true,
    RECEIVEDCOUNT: true,
    VARIANCE: true,
    TRANSFERCOUNT: true,
    WASTECOUNT: true,
    WASTETYPE: true,
    COUNTED: true
});

// DataTable reference
const dataTableRef = ref(null);

// Column definitions
const columns = ref([
    { 
        data: 'ITEMID',
        title: 'ITEMID',
        width: '120px',
        visible: columnVisibility.value.ITEMID
    },
    { 
        data: 'itemname',
        title: 'ITEMNAME',
        width: '200px',
        visible: columnVisibility.value.itemname
    },
    { 
        data: 'itemgroup',
        title: 'CATEGORY',
        width: '150px',
        visible: columnVisibility.value.itemgroup
    },
    {
        data: 'ADJUSTMENT',
        title: 'ORDER',
        width: '100px',
        visible: columnVisibility.value.ADJUSTMENT,
        render: function(data, type, row) {
            if (type === 'display') {
                const count = Number(data);
                // Always disable the order column
                return `
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${count.toFixed(0)}"
                        min="0"
                        data-field="ADJUSTMENT"
                        disabled
                        style="opacity: 0.6;"
                    >
                `;
            }
            return data;
        }
    },
    {
        data: 'RECEIVEDCOUNT',
        title: 'ACTUAL RECEIVED',
        width: '120px',
        visible: columnVisibility.value.RECEIVEDCOUNT,
        render: function(data, type, row) {
            if (type === 'display') {
                const count = Number(data);
                const now = new Date();
                const currentHour = now.getHours();
                const isCurrentDate = row.TRANSDATE === now.toISOString().split('T')[0];
                
                // Disable if time is between 12 PM (12) and 12 AM (0)
                const isWithinDisabledHours = currentHour >= 12 || currentHour === 0;
                
                // Combine all conditions for disabling
                const isDisabled = row.posted === 1 || !isCurrentDate || isWithinDisabledHours;
                
                return `
                    <input type="number" 
                        class="counted-input form-input w-full rounded-md"
                        value="${count.toFixed(0)}"
                        min="0"
                        data-field="RECEIVEDCOUNT"
                        ${isDisabled ? 'disabled' : ''}
                        style="${isDisabled ? 'opacity: 0.6;' : ''}"
                        title="${isWithinDisabledHours ? 'Receiving is disabled between 12 PM and 12 AM' : ''}"
                    >
                `;
            }
            return data;
        }
    },
    {
        data: null,
        title: 'VARIANCE',
        width: '100px',
        render: function(data, type, row) {
            if (type === 'display') {
                const order = Number(row.ADJUSTMENT);
                const received = Number(row.RECEIVEDCOUNT);
                const variance = order - received;
                const backgroundColor = variance === 0 ? '#f3f3f3' : 
                                      variance < 0 ? '#ffebee' : '#e8f5e9';
                return `
                    <div class="text-center p-2" 
                         style="background-color: ${backgroundColor}">
                        ${variance}
                    </div>
                `;
            }
            return '';
        }
    },
    {
    data: 'TRANSFERCOUNT',
    title: 'TRANSFER',
    width: '120px',
    visible: columnVisibility.value.TRANSFERCOUNT,
    render: function(data, type, row) {
        if (type === 'display') {
            const count = Number(data);
            // Get current date in YYYY-MM-DD format
            const currentDate = new Date().toISOString().split('T')[0];
            // Check if row date matches current date
            const isCurrentDate = row.TRANSDATE === currentDate;
            // Disable if posted or not current date
            const isDisabled = row.posted === 1 || !isCurrentDate;
            
            return `
                <input type="number" 
                    class="counted-input form-input w-full rounded-md"
                    value="${count.toFixed(0)}"
                    min="0"
                    data-field="TRANSFERCOUNT"
                    ${isDisabled ? 'disabled' : ''}
                    style="${isDisabled ? 'opacity: 0.6;' : ''}"
                >
            `;
        }
        return data;
    }
},
    {
        data: 'WASTECOUNT',
        title: 'WASTE COUNT',
        width: '120px',
        visible: columnVisibility.value.WASTECOUNT,
        render: function(data, type, row) {
            if (type === 'display') {
                const count = Number(data);
                const isDisabled = row.posted === 1 || row.WASTETYPE !== null;
                return `
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${count.toFixed(0)}"
                        min="0"
                        data-field="WASTECOUNT"
                        ${isDisabled ? 'disabled' : ''}
                        style="${isDisabled ? 'opacity: 0.6;' : ''}"
                    >
                `;
            }
            return data;
        }
    },
    {
        data: 'WASTETYPE',
        title: 'WASTE TYPE',
        width: '150px',
        visible: columnVisibility.value.WASTETYPE,
        render: function(data, type, row) {
            if (type === 'display') {
                const isDisabled = row.posted === 1 || data !== null;
                const options = ['throw_away', 'early_molds', 'pull_out', 'rat_bites', 'ant_bites'];
                const currentValue = data || '';
                
                return `
                    <select 
                        class="waste-type-select form-select w-full rounded-md"
                        data-field="WASTETYPE"
                        ${isDisabled ? 'disabled' : ''}>
                        <option value="">Select type</option>
                        ${options.map(option => `
                            <option value="${option}" ${currentValue === option ? 'selected' : ''}>
                                ${option.replace(/_/g, ' ')}
                            </option>
                        `).join('')}
                    </select>
                `;
            }
            return data;
        }
    },
    {
        data: 'COUNTED',
        title: 'ACTUAL COUNT',
        width: '120px',
        visible: columnVisibility.value.COUNTED,
        render: function(data, type, row) {
            if (type === 'display') {
                const count = Number(data);
                /* const isDisabled = row.posted === 1; */
                const isCurrentDate = row.TRANSDATE === new Date().toISOString().split('T')[0];
                const isDisabled = row.posted === 1 || !isCurrentDate;
                return `
                    <input type="number"
                        class="counted-input form-input w-full rounded-md"
                        value="${count.toFixed(0)}"
                        min="0"
                        data-field="COUNTED"
                        ${isDisabled ? 'disabled' : ''}
                        style="${isDisabled ? 'opacity: 0.6;' : ''}"
                    >
                `;
            }
            return data;
        }
    }
]);

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
    responsive: true,
    processing: true,
    stateSave: true,
    columns: columns.value,
    language: {
        processing: "Loading...",
    },
    initComplete: function(settings, json) {
        // Store reference to DataTable instance
        if (dataTableRef.value) {
            dataTableRef.value.dtInstance = this.api();
        }
    },
    drawCallback: function(settings) {
        const api = this.api();
        api.rows().every(function() {
            const rowData = this.data();
            const node = this.node();
            
            const inputs = node.querySelectorAll('.counted-input, .waste-type-select');
            inputs.forEach(input => {
                if (!rowData.posted) {
                    input.addEventListener('change', (event) => handleCountedChange(event, rowData));
                }
            });
        });
    }
};

const toggleColumnVisibility = (columnKey) => {
    // Update the visibility state
    columnVisibility.value[columnKey] = !columnVisibility.value[columnKey];
    
    // Find and update the column in our reactive columns array
    const column = columns.value.find(col => col.data === columnKey);
    if (column) {
        column.visible = columnVisibility.value[columnKey];
    }
    
    // Update the DataTable visibility if table is initialized
    if (dataTableRef.value) {
        try {
            const dtInstance = dataTableRef.value.dt;
            if (dtInstance) {
                const columnIndex = columns.value.findIndex(col => col.data === columnKey);
                if (columnIndex !== -1) {
                    dtInstance.column(columnIndex).visible(columnVisibility.value[columnKey]);
                }
            }
        } catch (error) {
            console.error('Error updating column visibility:', error);
        }
    }
};

const toggleColumnMenu = () => {
    showColumnMenu.value = !showColumnMenu.value;
};

const handleClickOutside = (event) => {
    const dropdown = document.querySelector('.column-visibility-dropdown');
    if (dropdown && !dropdown.contains(event.target)) {
        showColumnMenu.value = false;
    }
};


// Event handlers
const handleCountedChange = (event, rowData) => {
    const field = event.target.dataset.field;
    if (!field || rowData.posted) return;

    if (!updatedValues[rowData.ITEMID]) {
        updatedValues[rowData.ITEMID] = {};
    }

    const value = event.target.type === 'number' ? 
                 parseFloat(event.target.value) || 0 : 
                 event.target.value;

    updatedValues[rowData.ITEMID][field] = value;

    // Update UI
    if (event.target.type === 'number') {
        const backgroundColor = value === 0 ? '#f3f3f3' : 'white';
        event.target.style.backgroundColor = backgroundColor;
    }
};

const updateAllCountedValues = async () => {
    try {
        if (Object.keys(updatedValues).length === 0) {
            message.text = 'No changes to update';
            message.type = 'info';
            return;
        }

        isLoading.value = true;
        message.text = 'Updating values...';
        message.type = 'info';

        const response = await axios.post('/api/stock-counting-line/update-all-counted-values', {
            journalId: props.journalid,
            updatedValues: updatedValues
        });

        if (response.data.success) {
            message.text = response.data.message;
            message.type = 'success';
            
            // Update local data
            Object.entries(updatedValues).forEach(([itemId, values]) => {
                const row = tableData.value.find(r => r.ITEMID === itemId);
                if (row) Object.assign(row, values);
            });
            
            // Clear updates
            Object.keys(updatedValues).forEach(key => delete updatedValues[key]);
            
            // Reload page after successful update
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        console.error('Update error:', error);
        message.text = error.response?.data?.message || 'Update failed';
        message.type = 'error';
    } finally {
        isLoading.value = false;
        setTimeout(() => {
            message.text = '';
            message.type = '';
        }, 3000);
    }
};

// Navigation functions
const StockCounting = () => {
    window.location.href = '/StockCounting';
};

const ViewOrders = (journalid) => {
    window.location.href = `/ViewStockCountingLine/${journalid}`;
};

const TransferOrder = (journalid) => {
    window.location.href = `/getstocktransfer/${journalid}`;
};

// Modal handlers
const toggleCreateModal = (journalid) => {
    JOURNALID.value = journalid;
    showCreateModal.value = true;
};

const toggleGetBWModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetBWModal.value = true;
};

const toggleGetCFModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetCFModal.value = true;
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const GetBWModalHandler = () => {
    showGetBWModal.value = false;
};

const GetCFModalHandler = () => {
    showGetCFModal.value = false;
};

const handleSelectedItem = (item) => {
    console.log('Selected Item:', item);
};


/* const isActionDisabled = computed(() => props.isPosted === 1); */

/* const isActionDisabled = computed(() => {
    const currentDate = new Date().toISOString().split('T')[0];
    const hasCurrentDateRow = tableData.value.some(row => row.TRANSDATE === currentDate);
    return !hasCurrentDateRow;
}); */

const initializeDataTable = () => {
    if (dataTableRef.value && dataTableRef.value.dt) {
        const table = dataTableRef.value.dt;
        
        window.dataTableInstance = table;
        
        const toggleColumnVisibility = (columnKey) => {
            columnVisibility.value[columnKey] = !columnVisibility.value[columnKey];
            
            const column = columns.value.find(col => col.data === columnKey);
            if (column) {
                column.visible = columnVisibility.value[columnKey];
            }
            
            if (window.dataTableInstance) {
                const columnIndex = columns.value.findIndex(col => col.data === columnKey);
                if (columnIndex !== -1) {
                    window.dataTableInstance.column(columnIndex)
                        .visible(columnVisibility.value[columnKey])
                        .draw(false); 
                }
            }
        };
    }
};

onMounted(() => {
    tableData.value = props.stockcountingtrans;
    document.addEventListener('click', handleClickOutside);
    // Initialize after a short delay to ensure DataTable is ready
    setTimeout(initializeDataTable, 0);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Main active-tab="STOCK">
        <!-- Modals -->
        <template v-slot:modals>
            <Create
                :show-modal="showCreateModal"
                :JOURNALID="JOURNALID"
                :items="items"
                @toggle-active="createModalHandler"
                @select-item="handleSelectedItem"
            />
            <GetBWP
                :show-modal="showGetBWModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetBWModalHandler"
            />
            <CopyFrom
                :show-modal="showGetCFModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetCFModalHandler"
            />
        </template>

        <!-- Main Content -->
        <template v-slot:main>
            <TableContainer>
                <!-- Loading Overlay -->
                <div v-if="isLoading" 
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm">
                    <div class="text-white text-2xl">Loading...</div>
                </div>

                <div v-if="message.text" 
                     :class="[
                         'p-4 mb-4 rounded-md',
                         message.type === 'success' ? 'bg-green-100 text-green-700' :
                         message.type === 'error' ? 'bg-red-100 text-red-700' :
                         'bg-blue-100 text-blue-700'
                     ]">
                    {{ message.text }}
                </div>

                <!-- <div class="relative mb-4 pl-10 column-visibility-dropdown">
                    <button 
                        @click="toggleColumnMenu"
                        class="bg-navy px-4 py-2 rounded-md text-white flex items-center space-x-2"
                    >
                        <span>Show/Hide Columns</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-if="showColumnMenu" 
                         class="column-visibility-menu">
                        <div class="py-1" role="menu">
                            <label 
                                v-for="(visible, columnKey) in columnVisibility" 
                                :key="columnKey"
                                class="column-checkbox-label"
                            >
                                <input 
                                    type="checkbox"
                                    :checked="visible"
                                    @change="toggleColumnVisibility(columnKey)"
                                    class="column-checkbox"
                                >
                                {{ columnKey }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mb-4 flex justify-between">
                    <div class="flex space-x-2 pl-10">
                        <PrimaryButton
                            @click="Received"
                            class="bg-navy px-4 py-2"
                        >
                            <Back class="h-5 w-5" />
                        </PrimaryButton>

                        <PrimaryButton
                            @click="toggleGetBWModal(journalid)"
                            :class="{
                                'bg-navy px-4 py-2': !isActionDisabled,
                                'bg-gray-400 cursor-not-allowed px-4 py-2': isActionDisabled
                            }"
                            :disabled="isActionDisabled"
                        >
                            GENERATE
                        </PrimaryButton>

                        <PrimaryButton
                            @click="ViewOrders(journalid)"
                            class="bg-navy px-4 py-2"
                        >
                            <Cart class="h-5 w-5" />
                        </PrimaryButton>
                    </div>

                    <div class="flex space-x-2 pr-10">

                        <PrimaryButton
                            @click="updateAllCountedValues"
                            :class="{
                                'bg-navy px-4 py-2 ': !isActionDisabled,
                                'bg-gray-400 cursor-not-allowed px-4 py-2': isActionDisabled
                            }"
                            :disabled="isActionDisabled"
                        >
                            <Save class="h-5 w-5" />
                        </PrimaryButton>
                    </div>
                </div> -->

                <div class="mb-4 flex justify-between items-center px-10">
                    <div class="flex space-x-2">
                        <!-- Column Visibility Dropdown -->
                        <div class="relative column-visibility-dropdown">
                            <button 
                                @click="toggleColumnMenu"
                                class="bg-navy px-4 py-2 rounded-md text-white flex items-center space-x-2"
                            >
                                <span>Show/Hide Columns</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Column visibility menu -->
                            <div v-if="showColumnMenu" 
                                class="absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                <div class="py-1" role="menu">
                                    <label 
                                        v-for="(visible, columnKey) in columnVisibility" 
                                        :key="columnKey"
                                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                    >
                                        <input 
                                            type="checkbox"
                                            :checked="visible"
                                            @change="toggleColumnVisibility(columnKey)"
                                            class="mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        >
                                        {{ columnKey }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <PrimaryButton
                            @click="StockCounting"
                            class="bg-navy px-4 py-2"
                        >
                            <Back class="h-5 w-5" />
                        </PrimaryButton>

                        <PrimaryButton
                            @click="toggleGetBWModal(journalid)"
                            :class="{
                                'bg-navy px-4 py-2': !isActionDisabled,
                                'bg-gray-400 cursor-not-allowed px-4 py-2': isActionDisabled
                            }"
                            :disabled="isActionDisabled"
                        >
                            GENERATE
                        </PrimaryButton>

                        <PrimaryButton
                            @click="ViewOrders(journalid)"
                            class="bg-navy px-4 py-2"
                        >
                            <Cart class="h-5 w-5" />
                        </PrimaryButton>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-2">

                        <PrimaryButton
                            @click="updateAllCountedValues"
                            :class="{
                                'bg-navy px-4 py-2': !isActionDisabled,
                                'bg-gray-400 cursor-not-allowed px-4 py-2': isActionDisabled
                            }"
                            :disabled="isActionDisabled"
                        >
                            <Save class="h-5 w-5" />
                        </PrimaryButton>
                    </div>
                </div>

                <!-- DataTable -->
                <DataTable
                    ref="dataTableRef"
                    :data="stockcountingtrans"
                    :columns="columns"
                    class="w-full display nowrap"
                    :options="options"
                >
                    <template #action="data">
                        <label class="inline-flex items-center">
                            <input 
                                type="checkbox" 
                                class="form-checkbox h-5 w-5 text-blue-600 rounded"
                                :checked="data.selected"
                                @change="toggleRowSelection(data)"
                            />
                        </label>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>

<style scoped>
:deep(.dataTables_wrapper) {
    padding: 1rem;
}

:deep(.dataTables_scrollBody) {
    min-height: 400px;
}

/* Column visibility dropdown styles */
.column-visibility-dropdown {
    @apply relative;
}

.column-visibility-menu {
    @apply absolute z-50 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5;
}

.column-checkbox-label {
    @apply flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer;
}

.column-checkbox {
    @apply mr-2 rounded border-gray-300 text-blue-600 focus:ring-blue-500;
}

/* Table Styles */
:deep(.dataTables_wrapper) {
    padding: 1rem;
}

:deep(.dataTables_scrollBody) {
    min-height: 400px;
}

/* Input and Select Styles */
:deep(.counted-input),
:deep(.waste-type-select) {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500;
}

:deep(.counted-input:disabled),
:deep(.waste-type-select:disabled) {
    @apply bg-gray-100 cursor-not-allowed opacity-75;
}

/* Button Styles */
.bg-navy {
    @apply bg-blue-900 hover:bg-blue-800 text-white;
}

/* Loading and Message Styles */
.loading-overlay {
    @apply fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm;
}

.message-banner {
    @apply fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-40 transition-all duration-300;
}

.message-banner.success {
    @apply bg-green-100 text-green-800 border border-green-300;
}

.message-banner.error {
    @apply bg-red-100 text-red-800 border border-red-300;
}

.message-banner.info {
    @apply bg-blue-100 text-blue-800 border border-blue-300;
}

/* Table Cell Styles */
:deep(.variance-cell) {
    @apply text-center p-2;
}

:deep(.variance-cell.positive) {
    @apply bg-green-50;
}

:deep(.variance-cell.negative) {
    @apply bg-red-50;
}

:deep(.variance-cell.neutral) {
    @apply bg-gray-50;
}

/* Animation for messages */
@keyframes fadeOut {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}

.fade-out {
    animation: fadeOut 0.3s ease-out forwards;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    :deep(.dataTables_wrapper) {
        padding: 0.5rem;
    }
    
    .button-group {
        @apply flex-wrap gap-2;
    }
    
    :deep(.counted-input),
    :deep(.waste-type-select) {
        @apply text-sm;
    }
}
</style>

<script>
// Exported component definition
export default {
    name: 'StockCountingLine',
    inheritAttrs: false,
    mounted() {
        // Additional initialization if needed
        this.initializeTooltips();
        this.setupKeyboardShortcuts();
    },
    methods: {
        initializeTooltips() {
            // Initialize tooltips for better UX
            const tooltips = document.querySelectorAll('[data-tooltip]');
            tooltips.forEach(tooltip => {
                // Add tooltip functionality
                // You can use a library like tippy.js or implement custom logic
            });
        },
        setupKeyboardShortcuts() {
            // Add keyboard shortcuts for power users
            window.addEventListener('keydown', (e) => {
                // Ctrl/Cmd + S to save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    if (!this.isActionDisabled) {
                        this.updateAllCountedValues();
                    }
                }
            });
        },
        toggleRowSelection(data) {
            // Implement row selection logic if needed
            this.$emit('row-selection-change', data);
        }
    },
    beforeUnmount() {
        // Cleanup
        window.removeEventListener('keydown', this.setupKeyboardShortcuts);
    }
};
</script>