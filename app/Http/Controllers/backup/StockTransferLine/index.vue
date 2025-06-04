<script setup>
import { ref, onMounted, computed, reactive } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Cart from "@/Components/Svgs/Cart.vue";
import GetBWP from "@/Components/StockTransferLine/GetBWP.vue";
import { usePage } from '@inertiajs/vue3';

DataTable.use(DataTablesCore);

const props = defineProps({
    stocktransfertrans: {
        type: Array,
        default: () => [],
    }
});

// State management
const isLoading = ref(false);
const showGetBWModal = ref(false);
const tableData = ref([]);
const updatedValues = reactive({});
const message = reactive({
    text: '',
    type: ''
});

// Initialize journalId as a ref
const JOURNALID = ref('');

onMounted(() => {
    // Initialize tableData and get journalId from URL
    tableData.value = props.stocktransfertrans || [];
    const urlParts = window.location.pathname.split('/');
    JOURNALID.value = urlParts[urlParts.length - 1] || '';
    console.log('Journal ID on mount:', JOURNALID.value);
});

// Column definitions
const columns = [
    { 
        data: 'ITEMID',
        title: 'Item ID',
        width: '120px'
    },
    { 
        data: 'itemname',
        title: 'Item Name',
        width: '200px'
    },
    {
        data: 'COUNTED',
        title: 'Quantity',
        width: '100px',
        render: function(data, type, row) {
            if (type === 'display') {
                const qty = Number(data);
                return `
                    <input type="number" 
                        class="qty-input form-input w-full rounded-md"
                        value="${qty.toFixed(0)}"
                        min="0"
                        data-field="qty"
                    >
                `;
            }
            return data;
        }
    }
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
    responsive: true,
    processing: true,
    language: {
        processing: "Loading...",
    },
    drawCallback: function(settings) {
        const api = this.api();
        api.rows().every(function() {
            const rowData = this.data();
            const node = this.node();
            
            const inputs = node.querySelectorAll('.qty-input');
            inputs.forEach(input => {
                input.addEventListener('change', (event) => handleQtyChange(event, rowData));
            });
        });
    }
};

const GetBWModalHandler = () => {
    showGetBWModal.value = false;
    window.location.reload();
};

const toggleGetBWModal = () => {
    console.log('Toggling modal. JOURNALID:', JOURNALID.value);
    if (JOURNALID.value) {
        showGetBWModal.value = !showGetBWModal.value;
        console.log('Modal state:', showGetBWModal.value);
    } else {
        console.warn('Journal ID is not available');
        message.text = 'Journal ID is not available';
        message.type = 'error';
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

        // Transform the updatedValues structure to match backend expectations
        const transformedValues = {};
        Object.entries(updatedValues).forEach(([itemId, values]) => {
            transformedValues[itemId] = values.qty || 0;
        });

        const response = await axios.post('/api/stock-transfer-line/update-all-values', {
            journalId: JOURNALID.value,
            updatedValues: transformedValues
        });

        if (response.data.success) {
            message.text = response.data.message;
            message.type = 'success';
            
            // Update local data
            Object.entries(transformedValues).forEach(([itemId, value]) => {
                const row = tableData.value.find(r => r.ITEMID === itemId);
                if (row) row.COUNTED = value;
            });
            
            // Clear updates
            Object.keys(updatedValues).forEach(key => delete updatedValues[key]);
            
            // Reload page after successful update
            setTimeout(() => window.location.reload(), 1000);
        }
    } catch (error) {
        console.error('Update error:', error);
        const errorMessage = error.response?.data?.message;
        message.text = Array.isArray(errorMessage) 
            ? Object.values(errorMessage).flat().join('\n')
            : errorMessage || error.message || 'Update failed';
        message.type = 'error';
    } finally {
        isLoading.value = false;
        setTimeout(() => {
            message.text = '';
            message.type = '';
        }, 3000);
    }
};

const handleQtyChange = (event, rowData) => {
    const field = event.target.dataset.field;
    if (!field) return;

    if (!updatedValues[rowData.ITEMID]) {
        updatedValues[rowData.ITEMID] = {};
    }

    const value = parseFloat(event.target.value) || 0;
    updatedValues[rowData.ITEMID][field] = value;

    // Update input background color
    event.target.style.backgroundColor = value === 0 ? '#f3f3f3' : 'white';
};

const BackToList = () => {
    window.location.href = '/StockTransfer';
};

const ViewTransfers = (journalid) => {
    window.location.href = `/ViewStockTransferLine/${journalid}`;
};
</script>

<template>
    <Main active-tab="STOCK">
        <!-- Modals -->
        <template v-slot:modals>
            <GetBWP
                :show-modal="showGetBWModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetBWModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <!-- Loading Overlay -->
                <div v-if="isLoading" 
                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
                    <div class="text-white text-2xl">Loading...</div>
                </div>

                <!-- Message Display -->
                <div v-if="message.text" 
                     :class="[
                         'p-4 mb-4 rounded-md',
                         message.type === 'success' ? 'bg-green-100 text-green-700' :
                         message.type === 'error' ? 'bg-red-100 text-red-700' :
                         'bg-blue-100 text-blue-700'
                     ]">
                    {{ message.text }}
                </div>

                <!-- Action Buttons -->
                <div class="mb-4 flex space-x-2">
                    <PrimaryButton
                        @click="BackToList"
                        class="bg-navy px-4 py-2"
                    >
                        <Back class="h-5 w-5" />
                    </PrimaryButton>

                    <PrimaryButton
                        @click="toggleGetBWModal"
                        class="bg-navy px-4 py-2"
                    >
                        GENERATE
                    </PrimaryButton>

                    <PrimaryButton
                        @click="updateAllCountedValues"
                        class="bg-navy px-4 py-2"
                        :disabled="isLoading || Object.keys(updatedValues).length === 0"
                    >
                        <Save class="h-5 w-5" />
                    </PrimaryButton>
                </div>

                <!-- DataTable -->
                <DataTable
                    :data="tableData"
                    :columns="columns"
                    class="w-full display nowrap"
                    :options="options"
                />
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

/* Input Styles */
:deep(.qty-input) {
    @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500;
}

/* Button Styles */
.bg-navy {
    @apply bg-blue-900 hover:bg-blue-800 text-white;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    :deep(.dataTables_wrapper) {
        padding: 0.5rem;
    }
    
    .button-group {
        @apply flex-wrap gap-2;
    }
    
    :deep(.qty-input) {
        @apply text-sm;
    }
}
</style>