<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import ExcelJS from 'exceljs';

// Import icons for mobile menu
import MenuIcon from "@/Components/Svgs/Menu.vue";
import CloseIcon from "@/Components/Svgs/Close.vue";

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
    },
    url: {
        type: String,
        default: () => route('reports.inventory')
    }
});

const layoutComponent = computed(() => {
    return props.userRole.toUpperCase() === 'STORE' ? StorePanel : Main;
});

const selectedStores = ref(props.filters.selectedStores || []);
const startDate = ref(props.filters.startDate || '');
const endDate = ref(props.filters.endDate || '');
const isTableLoading = ref(true);

// Mobile responsive state
const showFloatingMenu = ref(false);
const isMobile = ref(false);

// Click tracking for mobile interactions
const clickTimeout = ref(null);
const clickCount = ref(0);
const longPressTimeout = ref(null);
const isLongPress = ref(false);

// Store search functionality
const storeSearchQuery = ref('');
const showStoreDropdown = ref(false);

// Filtered stores based on search - handle both string and object formats
const filteredStores = computed(() => {
    let stores = [];
    
    // Handle different store data formats
    if (Array.isArray(props.stores)) {
        stores = props.stores.map(store => {
            // Handle if store is an object with name property
            if (typeof store === 'object' && store !== null) {
                // Handle specific format like {"STOREID": "BW0011", "NAME": "ANCHETA"}
                if (store.NAME) {
                    return store.NAME;
                }
                // Handle other common name properties
                if (store.name) {
                    return store.name;
                }
                if (store.storename) {
                    return store.storename;
                }
                if (store.store_name) {
                    return store.store_name;
                }
                
                // Try to extract NAME from string representation
                const storeStr = JSON.stringify(store);
                const nameMatch = storeStr.match(/"NAME"\s*:\s*"([^"]+)"/);
                if (nameMatch) {
                    return nameMatch[1];
                }
                
                // Last resort - clean up the object string
                return storeStr.replace(/[{}":]/g, '').replace(/STOREID[^,]*,?\s*/g, '').replace(/NAME/g, '').trim() || 'Unknown Store';
            }
            // Handle if store is already a string
            return String(store);
        });
    }
    
    if (!storeSearchQuery.value) return stores;
    return stores.filter(store => 
        store.toLowerCase().includes(storeSearchQuery.value.toLowerCase())
    );
});

// Adjustment modal state
const showAdjustmentModal = ref(false);
const selectedItem = ref(null);
const adjustmentForm = ref({
    adjustment_value: '',
    adjustment_type: 'set',
    remarks: ''
});
const adjustmentLoading = ref(false);

// History modal state
const showHistoryModal = ref(false);
const adjustmentHistory = ref([]);
const historyLoading = ref(false);

// Mobile interaction handlers - Fixed to work on ALL screen sizes
const handleItemClick = (item) => {
    clearTimeout(clickTimeout.value);
    clickCount.value++;

    if (clickCount.value === 1) {
        clickTimeout.value = setTimeout(() => {
            clickCount.value = 0;
        }, 300);
    } else if (clickCount.value === 2) {
        // Double click - open adjustment modal
        clearTimeout(clickTimeout.value);
        openAdjustmentModal(item);
        clickCount.value = 0;
    }
};

const handleTouchStart = (item, event) => {
    isLongPress.value = false;
    longPressTimeout.value = setTimeout(() => {
        isLongPress.value = true;
        openHistoryModal(item);
        // Add haptic feedback if available
        if (navigator.vibrate) {
            navigator.vibrate(50);
        }
    }, 1000); // 1 second for long press
};

const handleTouchEnd = (item, event) => {
    clearTimeout(longPressTimeout.value);
    if (!isLongPress.value) {
        handleItemClick(item);
    }
};

const handleMouseDown = (item, event) => {
    // Prevent text selection during long press
    event.preventDefault();
    isLongPress.value = false;
    longPressTimeout.value = setTimeout(() => {
        isLongPress.value = true;
        openHistoryModal(item);
    }, 1000);
};

const handleMouseUp = (item, event) => {
    clearTimeout(longPressTimeout.value);
    if (!isLongPress.value) {
        handleItemClick(item);
    }
};

const clearTimeouts = () => {
    clearTimeout(longPressTimeout.value);
    clearTimeout(clickTimeout.value);
};

// Mobile menu functions
const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};

// Store selection functions
const toggleStoreSelection = (store) => {
    const index = selectedStores.value.indexOf(store);
    if (index > -1) {
        selectedStores.value.splice(index, 1);
    } else {
        selectedStores.value.push(store);
    }
};

const isStoreSelected = (store) => {
    return selectedStores.value.includes(store);
};

const clearStoreSelection = () => {
    selectedStores.value = [];
    showStoreDropdown.value = false;
};

const selectAllStores = () => {
    // Handle both string and object formats
    const allStores = props.stores.map(store => {
        if (typeof store === 'object' && store !== null) {
            // Handle specific format like {"STOREID": "BW0011", "NAME": "ANCHETA"}
            if (store.NAME) {
                return store.NAME;
            }
            // Handle other common name properties
            if (store.name) {
                return store.name;
            }
            if (store.storename) {
                return store.storename;
            }
            if (store.store_name) {
                return store.store_name;
            }
            
            // Try to extract NAME from string representation
            const storeStr = JSON.stringify(store);
            const nameMatch = storeStr.match(/"NAME"\s*:\s*"([^"]+)"/);
            if (nameMatch) {
                return nameMatch[1];
            }
            
            // Last resort - clean up the object string
            return storeStr.replace(/[{}":]/g, '').replace(/STOREID[^,]*,?\s*/g, '').replace(/NAME/g, '').trim() || 'Unknown Store';
        }
        return String(store);
    });
    selectedStores.value = allStores;
    showStoreDropdown.value = false;
};

// Detect mobile screen size
const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

// Compute totals efficiently with memoization
const totals = computed(() => {
    if (!props.inventory?.length) return {
        beginning: 0,
        receivedDelivery: 0,
        stockTransfer: 0,
        sales: 0,
        bundleSales: 0,
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
            bundleSales: acc.bundleSales + safeNum(item.bundle_sales || 0),
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
    }, {
        beginning: 0,
        receivedDelivery: 0,
        stockTransfer: 0,
        sales: 0,
        bundleSales: 0,
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

// Open adjustment modal
const openAdjustmentModal = (item) => {
    console.log('Opening adjustment modal for item:', item);
    selectedItem.value = item;
    adjustmentForm.value = {
        adjustment_value: '',
        adjustment_type: 'set',
        remarks: ''
    };
    showAdjustmentModal.value = true;
    closeFloatingMenu();
};

// Close adjustment modal
const closeAdjustmentModal = () => {
    showAdjustmentModal.value = false;
    selectedItem.value = null;
    adjustmentForm.value = {
        adjustment_value: '',
        adjustment_type: 'set',
        remarks: ''
    };
};

// Submit adjustment
const submitAdjustment = async () => {
    if (!adjustmentForm.value.adjustment_value || !adjustmentForm.value.remarks.trim()) {
        alert('Please fill in all required fields');
        return;
    }

    if (!selectedItem.value || !selectedItem.value.id) {
        alert('Item ID is missing. This might be an aggregated record. Please contact support if this persists.');
        console.error('Missing ID for item:', selectedItem.value);
        return;
    }

    adjustmentLoading.value = true;

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const requestData = {
            id: selectedItem.value.id,
            adjustment_value: parseFloat(adjustmentForm.value.adjustment_value),
            adjustment_type: adjustmentForm.value.adjustment_type,
            remarks: adjustmentForm.value.remarks.trim()
        };

        console.log('Submitting adjustment:', requestData);

        const response = await fetch('/inventory/adjust-item-count', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(requestData)
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error('HTTP Error Response:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('Response:', data);

        if (data.success) {
            alert('Item count adjusted successfully');
            closeAdjustmentModal();
            window.location.reload();
        } else {
            alert(data.message || 'Failed to adjust item count');
            if (data.errors) {
                console.error('Validation errors:', data.errors);
                Object.keys(data.errors).forEach(key => {
                    console.error(`${key}: ${data.errors[key].join(', ')}`);
                });
            }
        }
    } catch (error) {
        console.error('Error adjusting item count:', error);
        alert('An error occurred while adjusting item count: ' + error.message);
    } finally {
        adjustmentLoading.value = false;
    }
};

// Open history modal
const openHistoryModal = async (item) => {
    selectedItem.value = item;
    showHistoryModal.value = true;
    historyLoading.value = true;
    closeFloatingMenu();

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const response = await fetch('/inventory/adjustment-history', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                itemid: item.itemid,
                storename: item.storename
            })
        });

        const data = await response.json();

        if (data.success) {
            adjustmentHistory.value = data.data;
        } else {
            console.error('Failed to fetch adjustment history:', data.message);
        }
    } catch (error) {
        console.error('Error fetching adjustment history:', error);
    } finally {
        historyLoading.value = false;
    }
};

// Close history modal
const closeHistoryModal = () => {
    showHistoryModal.value = false;
    selectedItem.value = null;
    adjustmentHistory.value = [];
};

// DataTable columns for desktop - with enhanced interactions
const columns = [
    { 
        data: 'itemname',
        title: 'Item Name',
        className: 'min-w-[200px]'
    },
    {
        data: 'storename',
        title: 'Store',
        className: 'min-w-[120px]'
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
        title: 'Direct Sales',
        className: 'text-right',
        render: (data) => Number(data || 0).toFixed(2)
    },
    {
        data: 'bundle_sales',
        title: 'Bundle Sales',
        className: 'text-right',
        render: (data, type, row) => Number(row.bundle_sales || 0).toFixed(2)
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
        render: (data, type, row) => {
            const value = Number(data || 0).toFixed(2);
            const rowId = `adjust-${row.id || Math.random()}`;
            const historyId = `history-${row.id || Math.random()}`;
            return `
                <div class="flex items-center justify-between">
                    <span>${value}</span>
                    <div class="flex gap-1 ml-2">
                        <button 
                            id="${rowId}"
                            class="text-blue-600 hover:text-blue-800 text-xs px-2 py-1 border border-blue-300 rounded adjust-btn"
                            title="Adjust Item Count"
                            data-item='${JSON.stringify(row)}'
                        >
                            Adjust
                        </button>
                        <button 
                            id="${historyId}"
                            class="text-green-600 hover:text-green-800 text-xs px-2 py-1 border border-green-300 rounded history-btn"
                            title="View History"
                            data-item='${JSON.stringify(row)}'
                        >
                            History
                        </button>
                    </div>
                </div>
            `;
        }
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

// Setup event listeners for dynamically created buttons
const setupButtonEventListeners = () => {
    document.removeEventListener('click', handleButtonClick);
    document.addEventListener('click', handleButtonClick);
};

const handleButtonClick = (event) => {
    if (event.target.classList.contains('adjust-btn')) {
        const itemData = JSON.parse(event.target.getAttribute('data-item'));
        openAdjustmentModal(itemData);
    } else if (event.target.classList.contains('history-btn')) {
        const itemData = JSON.parse(event.target.getAttribute('data-item'));
        openHistoryModal(itemData);
    }
};

// DataTable options - with enhanced row interactions using vanilla JS
const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip',
    deferRender: true,
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
    ],
    createdRow: function(row, data, dataIndex) {
        // Add event listeners for long press and double click on desktop table rows
        let longPressTimer;
        let clickCount = 0;
        let clickTimer;
        
        // Add classes for styling
        row.classList.add('cursor-pointer', 'hover:bg-gray-50', 'select-none');
        
        const handleMouseDown = (e) => {
            e.preventDefault();
            longPressTimer = setTimeout(() => {
                openHistoryModal(data);
            }, 1000);
        };
        
        const handleMouseUp = () => {
            clearTimeout(longPressTimer);
        };
        
        const handleClick = (e) => {
            // Don't trigger on button clicks
            if (e.target.classList.contains('adjust-btn') || 
                e.target.classList.contains('history-btn') ||
                e.target.closest('.adjust-btn') || 
                e.target.closest('.history-btn')) {
                return;
            }
            
            clearTimeout(clickTimer);
            clickCount++;
            
            if (clickCount === 1) {
                clickTimer = setTimeout(() => {
                    clickCount = 0;
                }, 300);
            } else if (clickCount === 2) {
                clearTimeout(clickTimer);
                openAdjustmentModal(data);
                clickCount = 0;
            }
        };
        
        // Add event listeners
        row.addEventListener('mousedown', handleMouseDown);
        row.addEventListener('mouseup', handleMouseUp);
        row.addEventListener('mouseleave', handleMouseUp);
        row.addEventListener('click', handleClick);
        
        // Store cleanup function for later use if needed
        row._cleanupEvents = () => {
            row.removeEventListener('mousedown', handleMouseDown);
            row.removeEventListener('mouseup', handleMouseUp);
            row.removeEventListener('mouseleave', handleMouseUp);
            row.removeEventListener('click', handleClick);
            clearTimeout(longPressTimer);
            clearTimeout(clickTimer);
        };
    },
    drawCallback: function() {
        isTableLoading.value = false;
        setTimeout(setupButtonEventListeners, 100);
    }
};

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
    
    isTableLoading.value = true;
    
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
            onSuccess: () => {
                isTableLoading.value = false;
            },
            onError: (errors) => {
                console.error('Filter update failed:', errors);
                isTableLoading.value = false;
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
    closeFloatingMenu();
};

// Export functions for mobile menu
const exportToCsv = () => {
    if (window.DataTable) {
        const table = window.DataTable.tables()[0];
        if (table) {
            table.button('.buttons-csv').trigger();
        }
    }
    closeFloatingMenu();
};

const exportToExcel = () => {
    if (window.DataTable) {
        const table = window.DataTable.tables()[0];
        if (table) {
            table.button('.buttons-excel').trigger();
        }
    }
    closeFloatingMenu();
};

const exportToPdf = () => {
    if (window.DataTable) {
        const table = window.DataTable.tables()[0];
        if (table) {
            table.button('.buttons-pdf').trigger();
        }
    }
    closeFloatingMenu();
};

const printReport = () => {
    if (window.DataTable) {
        const table = window.DataTable.tables()[0];
        if (table) {
            table.button('.buttons-print').trigger();
        }
    }
    closeFloatingMenu();
};

// Click outside handlers
const handleClickOutside = (event) => {
    if (showStoreDropdown.value && !event.target.closest('.store-dropdown-container')) {
        showStoreDropdown.value = false;
    }
};

// Debounced filter handling
let filterTimeout;
watch([selectedStores, startDate, endDate], () => {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(handleFilterChange, 500);
}, { deep: true });

// Cleanup
onUnmounted(() => {
    clearTimeout(filterTimeout);
    clearTimeouts();
    document.removeEventListener('click', handleButtonClick);
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('resize', checkScreenSize);
});

// Initialize component
onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
    
    console.log("Inventory data:", props.inventory);
    
    // Setup event listeners
    setupButtonEventListeners();
    document.addEventListener('click', handleClickOutside);
    window.addEventListener('resize', checkScreenSize);
    checkScreenSize();
    
    setTimeout(() => {
        isTableLoading.value = false;
    }, 500);
});
</script>

<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <!-- Filters Section -->
            <div class="mb-4 flex flex-wrap gap-4 p-4 bg-white rounded-lg shadow z-[999]">
                <!-- Store Selection with Search -->
                <div 
                    v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" 
                    class="flex-1 min-w-[200px] store-dropdown-container relative"
                >
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stores</label>
                    <div class="relative">
                        <button
                            @click="showStoreDropdown = !showStoreDropdown"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-left bg-white"
                        >
                            <span v-if="selectedStores.length === 0" class="text-gray-500">Select stores...</span>
                            <span v-else-if="selectedStores.length === 1">{{ selectedStores[0] }}</span>
                            <span v-else>{{ selectedStores.length }} stores selected</span>
                            <svg class="float-right mt-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown -->
                        <div v-if="showStoreDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                            <!-- Search input -->
                            <div class="p-2 border-b border-gray-200">
                                <input
                                    v-model="storeSearchQuery"
                                    type="text"
                                    placeholder="Search stores..."
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    @click.stop
                                >
                            </div>

                            <!-- Action buttons -->
                            <div class="p-2 border-b border-gray-200 flex gap-2">
                                <button
                                    @click="selectAllStores"
                                    class="px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600"
                                >
                                    Select All
                                </button>
                                <button
                                    @click="clearStoreSelection"
                                    class="px-3 py-1 bg-gray-500 text-white text-xs rounded hover:bg-gray-600"
                                >
                                    Clear
                                </button>
                            </div>

                            <!-- Store options -->
                            <div class="max-h-40 overflow-auto">
                                <label 
                                    v-for="store in filteredStores" 
                                    :key="store"
                                    class="flex items-center px-3 py-2 hover:bg-gray-100 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        :checked="isStoreSelected(store)"
                                        @change="toggleStoreSelection(store)"
                                        class="mr-2 form-checkbox h-4 w-4 text-blue-600"
                                    >
                                    <span class="text-sm">{{ store }}</span>
                                </label>
                            </div>

                            <div v-if="filteredStores.length === 0" class="p-3 text-sm text-gray-500 text-center">
                                No stores found
                            </div>
                        </div>
                    </div>
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

            <!-- Touch/Click Instructions for All Devices -->
            <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
                <p class="text-sm text-blue-800">
                    <strong>Interaction Guide:</strong><br>
                    • <strong>Hold/Long press 1 second:</strong> View adjustment history<br>
                    • <strong>Double click/tap:</strong> Adjust item count<br>
                    • <strong>Single click buttons:</strong> Direct actions<br>
                    <span v-if="isMobile">• <strong>Use floating menu</strong> for exports and filters</span>
                </p>
            </div>

            <!-- Summary Section -->
            <details class="mb-4 bg-white rounded-lg shadow" open>
                <summary class="px-4 py-3 text-lg font-medium cursor-pointer">
                    Inventory Summary
                </summary>
                <div class="p-4">
                    <div v-if="isTableLoading" class="flex justify-center items-center py-8">
                        <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
                        <span class="ml-3">Loading data...</span>
                    </div>
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <!-- Sales (Direct + Bundle) -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-600">Total Sales</h3>
                            <div class="flex justify-between mt-1">
                                <div>
                                    <span class="text-sm text-gray-500">Direct:</span>
                                    <p class="text-lg">{{ totals.sales.toFixed(2) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Bundle:</span>
                                    <p class="text-lg">{{ totals.bundleSales.toFixed(2) }}</p>
                                </div>
                            </div>
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
                        <div class="bg-gray-50 p-4 rounded-lg col-span-1">
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

            <!-- Data Display -->
            <div class="bg-white rounded-lg shadow">
                <div v-if="isTableLoading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-indigo-500"></div>
                    <span class="ml-4 text-lg">Loading inventory data...</span>
                </div>
                
                <!-- Mobile View -->
                <div v-if="isMobile" class="overflow-hidden">
                    <div class="max-h-96 overflow-y-auto">
                        <div v-for="item in inventory" :key="`${item.itemid}-${item.storename}`" 
                             class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors select-none cursor-pointer"
                             @touchstart="handleTouchStart(item, $event)"
                             @touchend="handleTouchEnd(item, $event)"
                             @touchcancel="clearTimeouts"
                             @mousedown="handleMouseDown(item, $event)"
                             @mouseup="handleMouseUp(item, $event)"
                             @mouseleave="clearTimeouts"
                             @contextmenu.prevent>
                            
                            <div class="space-y-3">
                                <div>
                                    <div class="font-medium text-gray-900">{{ item?.itemname || '' }}</div>
                                    <div class="text-sm text-gray-500">{{ item?.storename || '' }}</div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Beginning:</span>
                                        <span class="font-medium ml-1">{{ Number(item?.beginning || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Received:</span>
                                        <span class="font-medium ml-1">{{ Number(item?.received_delivery || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Sales:</span>
                                        <span class="font-medium ml-1">{{ Number(item?.sales || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Item Count:</span>
                                        <span class="font-medium ml-1">{{ Number(item?.item_count || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Ending:</span>
                                        <span class="font-bold ml-1">{{ Number(item?.ending || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Variance:</span>
                                        <span :class="[
                                            'font-bold ml-1',
                                            Number(item?.variance || 0) < 0 ? 'text-red-600' : 'text-green-600'
                                        ]">{{ Number(item?.variance || 0).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-2 text-xs text-gray-600">
                                    <div>Throw Away: {{ Number(item?.throw_away || 0).toFixed(2) }}</div>
                                    <div>Early Molds: {{ Number(item?.early_molds || 0).toFixed(2) }}</div>
                                    <div>Pull Out: {{ Number(item?.pull_out || 0).toFixed(2) }}</div>
                                </div>

                                <div class="flex justify-end space-x-2 mt-2">
                                    <button
                                        @click.stop="openAdjustmentModal(item)"
                                        class="text-blue-600 hover:text-blue-900 text-sm px-2 py-1 border border-blue-300 rounded"
                                    >
                                        Adjust
                                    </button>
                                    <button
                                        @click.stop="openHistoryModal(item)"
                                        class="text-green-600 hover:text-green-900 text-sm px-2 py-1 border border-green-300 rounded"
                                    >
                                        History
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop DataTable -->
                <TableContainer v-else class="max-h-[80vh] overflow-x-auto overflow-y-auto">
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <p class="text-sm text-gray-600">
                            <strong>Desktop Interactions:</strong> 
                            Hold any row for 1 second to view adjustment history, or double-click to adjust item count.
                        </p>
                    </div>
                    <DataTable 
                        :data="inventory" 
                        :columns="columns" 
                        class="w-full relative display" 
                        :options="options"
                    />
                </TableContainer>
            </div>

            <!-- Mobile Floating Action Button and Menu -->
            <div v-if="isMobile" class="fixed bottom-6 right-6 z-40">
                <!-- Floating Menu Options -->
                <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                    
                    <!-- Export Options -->
                    <div class="px-4 py-2 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-700">Export Data</p>
                    </div>
                    
                    <button
                        @click="exportToCsv"
                        class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="h-4 w-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </button>

                    <button
                        @click="exportToExcel"
                        class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Excel
                    </button>

                    <button
                        @click="exportToPdf"
                        class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="h-4 w-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export PDF
                    </button>

                    <button
                        @click="printReport"
                        class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="h-4 w-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print Report
                    </button>

                    <div class="border-t border-gray-200 my-2"></div>

                    <!-- Filter Options -->
                    <button
                        @click="clearFilters"
                        class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                    >
                        <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z" />
                        </svg>
                        Clear All Filters
                    </button>
                </div>

                <!-- Main Floating Action Button -->
                <button
                    @click="toggleFloatingMenu"
                    class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105"
                    :class="{ 'rotate-45': showFloatingMenu }"
                >
                    <MenuIcon v-if="!showFloatingMenu" class="h-6 w-6" />
                    <CloseIcon v-else class="h-6 w-6" />
                </button>
            </div>

            <!-- Overlay to close floating menu -->
            <div v-if="showFloatingMenu" @click="closeFloatingMenu" class="fixed inset-0 bg-black bg-opacity-25 z-30"></div>

            <!-- Adjustment Modal -->
            <div v-if="showAdjustmentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Adjust Item Count
                        </h3>
                        
                        <div v-if="selectedItem" class="mb-4 p-3 bg-gray-50 rounded">
                            <p class="text-sm font-medium">{{ selectedItem.itemname }}</p>
                            <p class="text-sm text-gray-600">Store: {{ selectedItem.storename }}</p>
                            <p class="text-sm text-gray-600">Current Count: {{ Number(selectedItem.item_count || 0).toFixed(2) }}</p>
                            <p class="text-xs text-gray-500">ID: {{ selectedItem.id || 'No ID' }}</p>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Adjustment Type</label>
                                <select 
                                    v-model="adjustmentForm.adjustment_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="set">Set to Value</option>
                                    <option value="add">Add to Current</option>
                                    <option value="subtract">Subtract from Current</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    <span v-if="adjustmentForm.adjustment_type === 'set'">New Value</span>
                                    <span v-else-if="adjustmentForm.adjustment_type === 'add'">Amount to Add</span>
                                    <span v-else>Amount to Subtract</span>
                                </label>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    v-model="adjustmentForm.adjustment_value"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Enter value"
                                    required
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Remarks *</label>
                                <textarea 
                                    v-model="adjustmentForm.remarks"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Reason for adjustment (required)"
                                    required
                                ></textarea>
                            </div>

                            <div v-if="adjustmentForm.adjustment_type !== 'set' && adjustmentForm.adjustment_value && selectedItem" class="p-3 bg-blue-50 rounded">
                                <p class="text-sm text-blue-700">
                                    <span v-if="adjustmentForm.adjustment_type === 'add'">
                                        New value will be: {{ (Number(selectedItem.item_count || 0) + Number(adjustmentForm.adjustment_value || 0)).toFixed(2) }}
                                    </span>
                                    <span v-else>
                                        New value will be: {{ (Number(selectedItem.item_count || 0) - Number(adjustmentForm.adjustment_value || 0)).toFixed(2) }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button 
                                @click="closeAdjustmentModal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                                :disabled="adjustmentLoading"
                            >
                                Cancel
                            </button>
                            <button 
                                @click="submitAdjustment"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-300"
                                :disabled="adjustmentLoading || !adjustmentForm.adjustment_value || !adjustmentForm.remarks.trim()"
                            >
                                <span v-if="adjustmentLoading">Processing...</span>
                                <span v-else>Apply Adjustment</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- History Modal -->
            <div v-if="showHistoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-10 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Adjustment History
                        </h3>
                        
                        <div v-if="selectedItem" class="mb-4 p-3 bg-gray-50 rounded">
                            <p class="text-sm font-medium">{{ selectedItem.itemname }}</p>
                            <p class="text-sm text-gray-600">Store: {{ selectedItem.storename }}</p>
                        </div>

                        <div v-if="historyLoading" class="flex justify-center items-center py-8">
                            <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                            <span class="ml-3">Loading history...</span>
                        </div>

                        <div v-else-if="adjustmentHistory.length === 0" class="text-center py-8 text-gray-500">
                            No adjustment history found for this item.
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Value</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Value</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjustment</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adjusted By</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="record in adjustmentHistory" :key="record.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ new Date(record.created_at).toLocaleString() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                  :class="{
                                                    'bg-blue-100 text-blue-800': record.adjustment_type === 'set',
                                                    'bg-green-100 text-green-800': record.adjustment_type === 'add',
                                                    'bg-red-100 text-red-800': record.adjustment_type === 'subtract'
                                                  }">
                                                {{ record.adjustment_type.toUpperCase() }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ Number(record.old_item_count).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ Number(record.new_item_count).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm"
                                            :class="{
                                              'text-green-600': record.adjustment_type === 'add' || (record.adjustment_type === 'set' && record.new_item_count > record.old_item_count),
                                              'text-red-600': record.adjustment_type === 'subtract' || (record.adjustment_type === 'set' && record.new_item_count < record.old_item_count)
                                            }">
                                            <span v-if="record.adjustment_type === 'add'">+</span>
                                            <span v-else-if="record.adjustment_type === 'subtract'">-</span>
                                            {{ Number(record.adjustment_value).toFixed(2) }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="max-w-xs truncate" :title="record.remarks">
                                                {{ record.remarks }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ record.adjusted_by_name || 'Unknown' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="flex justify-end mt-6">
                            <button 
                                @click="closeHistoryModal"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </component>
</template>

<style>
.dt-buttons {
    display: flex;               
    justify-content: flex-end;
    align-items: center;    
    position: absolute;
    z-index: 1;
    margin: 10px;
    right: 0;
}

.dt-button, 
.dt-buttons .buttons-copy,
.dt-buttons .buttons-print {
    padding: 8px 12px;
    background-color: #3b82f6;
    margin-left: 8px;
    border-radius: 5px;
    color: white;
    transition: background-color 0.2s;
    border: none;
    cursor: pointer;
    font-size: 14px;
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
    margin-right: 200px;
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
    position: sticky;
    top: 0;
    z-index: 10;
}

table.dataTable tbody tr {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
    user-select: none;
    cursor: pointer;
}

table.dataTable tbody tr:hover {
    background-color: #f9fafb;
}

table.dataTable tbody tr.selected {
    background-color: #dbeafe;
}

.dataTables_wrapper .dataTables_paginate {
    padding: 15px;
    text-align: right;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    margin-left: 5px;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #3b82f6;
    color: white !important;
}

.dataTables_wrapper .dataTables_info {
    padding: 15px;
}

@media (max-width: 768px) {
    .dt-buttons {
        display: none; /* Hide DataTable buttons on mobile */
    }
    
    .dataTables_filter {
        display: none; /* Hide DataTable search on mobile */
    }

    table.dataTable {
        margin-top: 20px;
    }
}
</style>