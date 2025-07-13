/* General Styling for DataTable */
table.dataTable {
    width: 100% !important;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Arial', sans-serif;
    margin-top: 60px;
    table-layout: fixed; /* Fixed layout for better column control */
}

table.dataTable thead {
    background-color: #343a40;
    color: #ffffff;
    text-align: center;
}

table.dataTable thead th {
    background-color: #f3f4f6;
    padding: 8px 6px; /* Reduced padding for tighter spacing */
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    color: #374151;
    font-size: 12px; /* Smaller font for headers */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

table.dataTable tbody {
    background-color: #f8f9fa;
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #ffffff;
}

table.dataTable tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

table.dataTable tbody tr:hover {
    background-color: #e3f2fd;
    cursor: pointer;
}

table.dataTable tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

table.dataTable th, table.dataTable td {
    padding: 6px 4px; /* Reduced padding for tighter spacing */
    text-align: left;
    border: 1px solid #dee2e6;
    font-size: 11px; /* Smaller font size */
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Text alignment classes */
table.dataTable .text-right {
    text-align: right !important;
}

table.dataTable .text-center {
    text-align: center !important;
}

/* Specific column width constraints */
table.dataTable th:nth-child(1), table.dataTable td:nth-child(1) { 
    width: 80px !important; 
    max-width: 80px !important; 
}
table.dataTable th:nth-child(2), table.dataTable td:nth-child(2) { 
    width: 100px !important; 
    max-width: 100px !important; 
}
table.dataTable th:nth-child(3), table.dataTable td:nth-child(3) { 
    width: 80px !important; 
    max-width: 80px !important; 
}
table.dataTable th:nth-child(4), table.dataTable td:nth-child(4) { 
    width: 60px !important; 
    max-width: 60px !important; 
}
table.dataTable th:nth-child(5), table.dataTable td:nth-child(5) { 
    width: 120px !important; 
    max-width: 120px !important; 
}
table.dataTable th:nth-child(6), table.dataTable td:nth-child(6) { 
    width: 100px !important; 
    max-width: 100px !important; 
}
table.dataTable th:nth-child(7), table.dataTable td:nth-child(7) { 
    width: 120px !important; 
    max-width: 120px !important; 
}
table.dataTable th:nth-child(8), table.dataTable td:nth-child(8) { 
    width: 150px !important; 
    max-width: 150px !important; 
}
table.dataTable th:nth-child(9), table.dataTable td:nth-child(9) { 
    width: 90px !important; 
    max-width: 90px !important; 
}
table.dataTable th:nth-child(10), table.dataTable td:nth-child(10) { 
    width: 90px !important; 
    max-width: 90px !important; 
}

/* Numeric columns - smaller widths */
table.dataTable th:nth-child(11), table.dataTable td:nth-child(11),
table.dataTable th:nth-child(12), table.dataTable td:nth-child(12),
table.dataTable th:nth-child(13), table.dataTable td:nth-child(13),
table.dataTable th:nth-child(14), table.dataTable td:nth-child(14),
table.dataTable th:nth-child(15), table.dataTable td:nth-child(15),
table.dataTable th:nth-child(16), table.dataTable td:nth-child(16),
table.dataTable th:nth-child(17), table.dataTable td:nth-child(17),
table.dataTable th:nth-child(18), table.dataTable td:nth-child(18),
table.dataTable th:nth-child(19), table.dataTable td:nth-child(19),
table.dataTable th:nth-child(20), table.dataTable td:nth-child(20),
table.dataTable th:nth-child(21), table.dataTable td:nth-child(21),
table.dataTable th:nth-child(22), table.dataTable td:nth-child(22),
table.dataTable th:nth-child(23), table.dataTable td:nth-child(23),
table.dataTable th:nth-child(24), table.dataTable td:nth-child(24),
table.dataTable th:nth-child(25), table.dataTable td:nth-child(25),
table.dataTable th:nth-child(26), table.dataTable td:nth-child(26),
table.dataTable th:nth-child(27), table.dataTable td:nth-child(27),
table.dataTable th:nth-child(28), table.dataTable td:nth-child(28),
table.dataTable th:nth-child(29), table.dataTable td:nth-child(29),
table.dataTable th:nth-child(30), table.dataTable td:nth-child(30),
table.dataTable th:nth-child(31), table.dataTable td:nth-child(31),
table.dataTable th:nth-child(32), table.dataTable td:nth-child(32),
table.dataTable th:nth-child(33), table.dataTable td:nth-child(33) {
    width: 70px !important;
    max-width: 70px !important;
    min-width: 70px !important;
}

/* Last column (NOTE) - wider */
table.dataTable th:nth-child(34), table.dataTable td:nth-child(34) { 
    width: 120px !important; 
    max-width: 120px !important; 
}

table.dataTable th {
    font-size: 11px;
    font-weight: bold;
}

table.dataTable td {
    font-size: 10px;
}

/* Styling for Footer */
.dataTable tfoot {
    background-color: #007bff;
    color: white;
    font-weight: bold;
    text-align: center;
}

.dataTable tfoot td {
    padding: 8px 4px;
    font-size: 10px;
}

/* Styling for DataTable Buttons */
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
    padding: 6px 10px;
    background-color: #3b82f6;
    margin-left: 6px;
    border-radius: 4px;
    color: white;
    transition: background-color 0.2s;
    border: none;
    cursor: pointer;
    font-size: 12px;
}

.dt-button:hover, 
.dt-buttons .buttons-copy:hover,
.dt-buttons .buttons-print:hover {
    background-color: #2563eb;
}

.dataTables_filter {
    float: right;
    padding: 15px;
    position: relative;
    z-index: 999;
    margin-right: 180px;
}

.dataTables_filter input {
    padding: 6px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-left: 6px;
    font-size: 12px;
}

.dataTables_wrapper .dataTables_paginate {
    padding: 10px;
    text-align: right;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    margin-left: 3px;
    padding: 4px 8px;
    border-radius: 3px;
    cursor: pointer;
    font-size: 11px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #3b82f6;
    color: white !important;
}

.dataTables_wrapper .dataTables_info {
    padding: 10px;
    font-size: 12px;
}

/* DataTable wrapper improvements */
.dataTables_wrapper {
    overflow-x: auto;
    white-space: nowrap;
}

/* Header improvements */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 8px;
}

/* Compact view adjustments */
.dt-buttons .dt-button {
    margin: 0 2px;
    padding: 4px 8px;
    font-size: 11px;
}

/* Table container improvements */
.table-container {
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    overflow: hidden;
}

/* Scrollbar styling */
.dataTables_scrollBody::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.dataTables_scrollBody::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Mobile responsive adjustments */
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

    /* Mobile card styling improvements */
    .mobile-card {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Responsive grid adjustments */
    .grid-mobile {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .grid-mobile-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.25rem;
    }

    /* Text size adjustments for mobile */
    .text-mobile-sm {
        font-size: 0.75rem;
    }

    .text-mobile-xs {
        font-size: 0.6875rem;
    }

    /* Floating action button positioning */
    .floating-menu {
        bottom: 5rem;
        right: 0;
        min-width: 14rem;
    }

    /* Mobile filters */
    .mobile-filters {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mobile-filters > div {
        min-width: 100%;
    }

    /* Store dropdown mobile optimization */
    .store-dropdown-mobile {
        max-height: 50vh;
        overflow-y: auto;
    }
}

/* Small mobile screens (portrait) */
@media (max-width: 480px) {
    .filter-container {
        padding: 1rem;
        gap: 0.75rem;
    }

    .filter-container > div {
        min-width: 100%;
        flex: none;
    }

    .summary-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .mobile-item-card {
        padding: 0.75rem;
    }

    .floating-action-button {
        bottom: 1rem;
        right: 1rem;
        width: 3.5rem;
        height: 3.5rem;
    }

    .floating-menu {
        bottom: 4.5rem;
        right: 0.5rem;
        width: 13rem;
    }

    /* Smaller text on very small screens */
    .mobile-text-adjust {
        font-size: 0.8125rem;
    }

    .mobile-text-adjust-sm {
        font-size: 0.75rem;
    }
}

/* Loading spinner */
.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Smooth transitions */
.transition-smooth {
    transition: all 0.2s ease-in-out;
}

/* Currency styling */
.currency-positive {
    color: #059669;
    font-weight: 600;
}

.currency-negative {
    color: #dc2626;
    font-weight: 600;
}

/* Card hover effects */
.card-hover:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Store dropdown styling */
.store-dropdown-container .relative > div {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Floating menu animation */
.floating-menu-enter-active,
.floating-menu-leave-active {
    transition: all 0.2s ease;
}

.floating-menu-enter-from,
.floating-menu-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}<style>
/* General Styling for DataTable */
table.dataTable {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Arial', sans-serif;
    margin-top: 60px;
}

table.dataTable thead {
    background-color: #343a40;
    color: #ffffff;
    text-align: center;
}

table.dataTable thead th {
    background-color: #f3f4f6;
    padding: 12px;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    color: #374151;
}

table.dataTable tbody {
    background-color: #f8f9fa;
}

table.dataTable tbody tr:nth-child(odd) {
    background-color: #e9ecef;
}

table.dataTable tbody tr:hover {
    background-color: #f9fafb;
    cursor: pointer;
}

table.dataTable tbody tr {
    padding: 12px;
    border-bottom: 1px solid #e5e7eb;
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

/* Mobile responsive adjustments */
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

    /* Mobile card styling improvements */
    .mobile-card {
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Responsive grid adjustments */
    .grid-mobile {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .grid-mobile-3 {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.25rem;
    }

    /* Text size adjustments for mobile */
    .text-mobile-sm {
        font-size: 0.75rem;
    }

    .text-mobile-xs {
        font-size: 0.6875rem;
    }

    /* Floating action button positioning */
    .floating-menu {
        bottom: 5rem;
        right: 0;
        min-width: 14rem;
    }

    /* Mobile filters */
    .mobile-filters {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mobile-filters > div {
        min-width: 100%;
    }

    /* Store dropdown mobile optimization */
    .store-dropdown-mobile {
        max-height: 50vh;
        overflow-y: auto;
    }
}

/* Small mobile screens (portrait) */
@media (max-width: 480px) {
    .filter-container {
        padding: 1rem;
        gap: 0.75rem;
    }

    .filter-container > div {
        min-width: 100%;
        flex: none;
    }

    .summary-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }

    .mobile-item-card {
        padding: 0.75rem;
    }

    .floating-action-button {
        bottom: 1rem;
        right: 1rem;
        width: 3.5rem;
        height: 3.5rem;
    }

    .floating-menu {
        bottom: 4.5rem;
        right: 0.5rem;
        width: 13rem;
    }

    /* Smaller text on very small screens */
    .mobile-text-adjust {
        font-size: 0.8125rem;
    }

    .mobile-text-adjust-sm {
        font-size: 0.75rem;
    }
}

/* Loading spinner */
.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Smooth transitions */
.transition-smooth {
    transition: all 0.2s ease-in-out;
}

/* Currency styling */
.currency-positive {
    color: #059669;
    font-weight: 600;
}

.currency-negative {
    color: #dc2626;
    font-weight: 600;
}

/* Card hover effects */
.card-hover:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Store dropdown styling */
.store-dropdown-container .relative > div {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Floating menu animation */
.floating-menu-enter-active,
.floating-menu-leave-active {
    transition: all 0.2s ease;
}

.floating-menu-enter-from,
.floating-menu-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}
</style><script setup>
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import 'datatables.net-buttons';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import 'datatables.net-buttons/js/buttons.html5.mjs';
import 'datatables.net-buttons/js/buttons.print.mjs';
import Swal from 'sweetalert2';
import ExcelJS from 'exceljs';

// Import icons for mobile menu
import MenuIcon from "@/Components/Svgs/Menu.vue";
import CloseIcon from "@/Components/Svgs/Close.vue";

DataTable.use(DataTablesCore);

const selectedStores = ref([]);
const startDate = ref('');
const endDate = ref('');
const isLoading = ref(false);
const isTableLoading = ref(true);

// Mobile responsive state
const showFloatingMenu = ref(false);
const isMobile = ref(false);

// Store search functionality
const storeSearchQuery = ref('');
const showStoreDropdown = ref(false);
const storeSearchInput = ref(null);

// Handle store search input
const handleStoreSearch = () => {
    // Force reactivity update
    storeSearchQuery.value = storeSearchQuery.value;
};

// Watch for dropdown open to focus search input
watch(showStoreDropdown, (newVal) => {
    if (newVal) {
        nextTick(() => {
            if (storeSearchInput.value) {
                storeSearchInput.value.focus();
            }
        });
    } else {
        // Clear search when dropdown closes
        storeSearchQuery.value = '';
    }
});

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

// Filtered stores based on search - handle both string and object formats
const filteredStores = computed(() => {
    let stores = [];
    
    // Handle different store data formats
    if (Array.isArray(props.stores)) {
        stores = props.stores.map(store => {
            // Handle if store is already a string
            if (typeof store === 'string') {
                return store;
            }
            
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
            
            // Fallback - convert to string
            return String(store);
        });
    }
    
    // Remove duplicates and sort
    stores = [...new Set(stores)].sort();
    
    // Filter based on search query
    if (!storeSearchQuery.value || storeSearchQuery.value.trim() === '') {
        return stores;
    }
    
    const searchTerm = storeSearchQuery.value.toLowerCase().trim();
    return stores.filter(store => 
        store.toLowerCase().includes(searchTerm)
    );
});

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
        // Handle if store is already a string
        if (typeof store === 'string') {
            return store;
        }
        
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
    
    // Remove duplicates
    selectedStores.value = [...new Set(allStores)];
    showStoreDropdown.value = false;
};

// Detect mobile screen size
const checkScreenSize = () => {
    isMobile.value = window.innerWidth < 768;
};

// Mobile menu functions
const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};

// Clear all filters
const clearFilters = () => {
    selectedStores.value = [];
    startDate.value = '';
    endDate.value = '';
    closeFloatingMenu();
};

onMounted(() => {
    selectedStores.value = props.filters.selectedStores || [];
    startDate.value = props.filters.startDate || '';
    endDate.value = props.filters.endDate || '';
    
    // Debug: Log the stores data format
    console.log('Stores data:', props.stores);
    console.log('First store item:', props.stores[0]);
    
    // Setup event listeners
    window.addEventListener('resize', checkScreenSize);
    document.addEventListener('click', handleClickOutside);
    checkScreenSize();
    
    setTimeout(() => {
        isTableLoading.value = false;
    }, 500);
});

// Click outside handlers
const handleClickOutside = (event) => {
    if (showStoreDropdown.value && !event.target.closest('.store-dropdown-container')) {
        showStoreDropdown.value = false;
    }
};

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    window.removeEventListener('resize', checkScreenSize);
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

        // Payment method totals
        acc.cash += (parseFloat(row.cash) || 0);
        acc.charge += (parseFloat(row.charge) || 0);
        acc.representation += (parseFloat(row.representation) || 0);
        acc.gcash += (parseFloat(row.gcash) || 0);
        acc.paymaya += (parseFloat(row.paymaya) || 0);
        acc.card += (parseFloat(row.card) || 0);
        acc.loyaltycard += (parseFloat(row.loyaltycard) || 0);
        acc.foodpanda += (parseFloat(row.foodpanda) || 0);
        acc.grabfood += (parseFloat(row.grabfood) || 0);
        acc.mrktgdisc += (parseFloat(row.mrktgdisc) || 0);
        acc.rddisc += (parseFloat(row.rddisc) || 0);

        acc.bw_products += (parseFloat(row.bw_products) || 0);
        acc.merchandise += (parseFloat(row.merchandise) || 0);
        acc.partycakes += (parseFloat(row.partycakes) || 0);
        acc.commission += (parseFloat(row.commission) || 0);

        return acc;
    }, {
        // Initialize all totals
        total_qty: 0,
        total_discamount: 0,
        total_costprice: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0,
        total_grossamount: 0,
        total_costamount: 0,
        
        // Payment method totals
        cash: 0,
        charge: 0,
        representation: 0,
        gcash: 0,
        paymaya: 0,
        card: 0,
        loyaltycard: 0,
        foodpanda: 0,
        grabfood: 0,
        mrktgdisc: 0,
        rddisc: 0,

        bw_products: 0,
        merchandise: 0,
        partycakes: 0,
        commission: 0
    });
});

const columns = [
    { 
        data: 'storename', 
        title: 'Store', 
        footer: 'Grand Total',
        className: 'min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'staff', 
        title: 'Staff', 
        footer: '',
        className: 'min-w-[100px] max-w-[140px]'
    },
    { 
        data: 'createddate', 
        title: 'Date', 
        footer: '',
        className: 'min-w-[85px] max-w-[100px]'
    },
    { 
        data: 'timeonly', 
        title: 'Time', 
        footer: '',
        className: 'min-w-[70px] max-w-[80px]'
    },
    { 
        data: 'transactionid', 
        title: 'Transaction ID', 
        footer: '',
        className: 'min-w-[120px] max-w-[140px]'
    },
    { 
        data: 'receiptid', 
        title: 'Receipt ID', 
        footer: '',
        className: 'min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'custaccount', 
        title: 'Customer', 
        footer: '',
        className: 'min-w-[120px] max-w-[150px]'
    },
    { 
        data: 'itemname', 
        title: 'Item Name', 
        footer: '',
        className: 'min-w-[150px] max-w-[200px]'
    },
    { 
        data: 'itemgroup', 
        title: 'Item Group', 
        footer: '',
        className: 'min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'discofferid', 
        title: 'PROMO', 
        footer: '',
        className: 'min-w-[100px] max-w-[120px]'
    },
    
    // Columns with footer calculations
    { 
        data: 'qty', 
        title: 'Qty',
        render: (data) => Math.round(data || 0),
        footer: '',
        className: 'text-right min-w-[60px] max-w-[80px]'
    },
    { 
        data: 'total_costprice', 
        title: 'Cost Price',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[90px] max-w-[110px]'
    },
    { 
        data: 'total_grossamount', 
        title: 'Gross Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'total_costamount', 
        title: 'Cost Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'total_discamount', 
        title: 'Discount Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[110px] max-w-[130px]'
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'vatablesales', 
        title: 'Vatable Sales',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'vat', 
        title: 'VAT',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },

    // Payment Method Columns
    { 
        data: 'cash', 
        title: 'Cash',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'charge', 
        title: 'Charge',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'representation', 
        title: 'Representation',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'gcash', 
        title: 'GCash',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'paymaya', 
        title: 'PayMaya',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'card', 
        title: 'Card',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'loyaltycard', 
        title: 'Loyalty Card',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[90px] max-w-[110px]'
    },
    { 
        data: 'foodpanda', 
        title: 'FoodPanda',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[90px] max-w-[110px]'
    },
    { 
        data: 'grabfood', 
        title: 'GrabFood',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[90px] max-w-[110px]'
    },
    { 
        data: 'mrktgdisc', 
        title: 'Mktg Disc',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'rddisc', 
        title: 'RD Disc',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px]'
    },
    { 
        data: 'bw_products', 
        title: 'BW PRODUCTS',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'merchandise', 
        title: 'MERCHANDISE',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'partycakes', 
        title: 'PARTYCAKES',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px]'
    },
    { 
        data: 'commission', 
        title: 'Commission',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[90px] max-w-[110px]'
    },
    { 
        data: 'remarks', 
        title: 'NOTE', 
        footer: '',
        className: 'min-w-[120px] max-w-[150px]'
    }
];

const options = {
    responsive: true,
    order: [[0, 'asc']],
    pageLength: 25,
    dom: 'Bfrtip',
    scrollX: true,
    scrollY: "50vh",
    autoWidth: false,
    columnDefs: [
        // Numeric columns - right align
        { 
            targets: [10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32], 
            className: 'text-right'
        },
        // Date column - center align
        { targets: [2], className: 'text-center' },
        // Time column - center align  
        { targets: [3], className: 'text-center' },
        // Specific width constraints for better spacing
        { targets: [0], width: '100px' }, // Store
        { targets: [1], width: '120px' }, // Staff
        { targets: [2], width: '90px' },  // Date
        { targets: [3], width: '70px' },  // Time
        { targets: [4], width: '130px' }, // Transaction ID
        { targets: [5], width: '110px' }, // Receipt ID
        { targets: [6], width: '140px' }, // Customer
        { targets: [7], width: '180px' }, // Item Name
        { targets: [8], width: '110px' }, // Item Group
        { targets: [9], width: '110px' }, // PROMO
        { targets: [10], width: '70px' }, // Qty
        { targets: [33], width: '140px' }, // NOTE
    ],
    buttons: [
        'copy',
        {
            text: 'Export Excel',
            action: function(e, dt, node, config) {
                exportToExcel(dt);
            }
        },
        'pdf',
        'print'
    ],
    drawCallback: function(settings) {
        const api = new DataTablesCore.Api(settings);
        isTableLoading.value = false;
        
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
            paymaya: 0,
            card: 0,
            loyaltycard: 0,
            foodpanda: 0,
            grabfood: 0,
            mrktgdisc: 0,
            rddisc: 0,
            bw_products: 0,
            merchandise: 0,
            partycakes: 0,
            commission: 0
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
        const footerRow = api.table().footer();
        if (footerRow) {
            const footerCells = footerRow.querySelectorAll('td, th');
            
            // Map of column indices to their corresponding total keys
            const columnMappings = [
                { index: 10, key: 'total_qty', round: true },
                { index: 11, key: 'total_costprice' },
                { index: 12, key: 'total_grossamount' },
                { index: 13, key: 'total_costamount' },
                { index: 14, key: 'total_discamount' },
                { index: 15, key: 'total_netamount' },
                { index: 16, key: 'vatablesales' },
                { index: 17, key: 'vat' },
                { index: 18, key: 'cash' },
                { index: 19, key: 'charge' },
                { index: 20, key: 'representation' },
                { index: 21, key: 'gcash' },
                { index: 22, key: 'paymaya' },
                { index: 23, key: 'card' },
                { index: 24, key: 'loyaltycard' },
                { index: 25, key: 'foodpanda' },
                { index: 26, key: 'grabfood' },
                { index: 27, key: 'mrktgdisc' },
                { index: 28, key: 'rddisc' },
                { index: 29, key: 'bw_products' },
                { index: 30, key: 'merchandise' },
                { index: 31, key: 'partycakes' },
                { index: 32, key: 'commission' }
            ];

            // Update footer cells with filtered totals
            columnMappings.forEach(({ index, key, round }) => {
                const total = filteredTotals[key];
                if (footerCells[index]) {
                    footerCells[index].textContent = round ? 
                        Math.round(total).toString() : 
                        total.toFixed(2);
                }
            });
        }
    }
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

const exportToExcel = (dt) => {
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('Sales Data');
    
    // Define columns with proper width and number formats
    const excelColumns = [
        { header: 'STORE', key: 'storename', width: 20 },
        { header: 'STAFF', key: 'staff', width: 15 },
        { header: 'DATE', key: 'createddate', width: 12 },
        { header: 'TIME', key: 'timeonly', width: 10 },
        { header: 'TRANSACTION ID', key: 'transactionid', width: 15 },
        { header: 'RECEIPT ID', key: 'receiptid', width: 15 },
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
        { header: 'PAYMAYA', key: 'paymaya', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'CARD', key: 'card', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'LOYALTY CARD', key: 'loyaltycard', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'FOODPANDA', key: 'foodpanda', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'GRABFOOD', key: 'grabfood', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'MKTG DISC', key: 'mrktgdisc', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'RD DISC', key: 'rddisc', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'BW PRODUCTS', key: 'bw_products', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'MERCHANDISE', key: 'merchandise', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'PARTY CAKES', key: 'partycakes', width: 15, style: { numFmt: '#,##0.00' } },
        { header: 'COMMISSION', key: 'commission', width: 12, style: { numFmt: '#,##0.00' } },
        { header: 'NOTE', key: 'remarks', width: 20 }
    ];

    worksheet.columns = excelColumns;

    // Style the header row
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true };
    headerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF333F4F' }
    };
    headerRow.font = { color: { argb: 'FFFFFFFF' }, bold: true };

    // Get filtered data from DataTable or use filteredData
    let dataToExport = filteredData.value;
    if (dt) {
        dataToExport = dt.rows({ search: 'applied' }).data().toArray();
    }
    
    // Add filtered data to worksheet
    dataToExport.forEach(row => {
        worksheet.addRow({
            storename: row.storename || '',
            staff: row.staff || '',
            createddate: row.createddate ? new Date(row.createddate) : null,
            timeonly: row.timeonly || '',
            transactionid: row.transactionid || '',
            receiptid: row.receiptid || '',
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
            paymaya: Number(row.paymaya) || 0,
            card: Number(row.card) || 0,
            loyaltycard: Number(row.loyaltycard) || 0,
            foodpanda: Number(row.foodpanda) || 0,
            grabfood: Number(row.grabfood) || 0,
            mrktgdisc: Number(row.mrktgdisc) || 0,
            rddisc: Number(row.rddisc) || 0,
            bw_products: Number(row.bw_products) || 0,
            merchandise: Number(row.merchandise) || 0,
            partycakes: Number(row.partycakes) || 0,
            commission: Number(row.commission) || 0,
            remarks: row.remarks || ''
        });
    });

    // Calculate totals for filtered data
    const filteredTotals = dataToExport.reduce((acc, row) => ({
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
        paymaya: acc.paymaya + Number(row.paymaya || 0),
        card: acc.card + Number(row.card || 0),
        loyaltycard: acc.loyaltycard + Number(row.loyaltycard || 0),
        foodpanda: acc.foodpanda + Number(row.foodpanda || 0),
        grabfood: acc.grabfood + Number(row.grabfood || 0),
        mrktgdisc: acc.mrktgdisc + Number(row.mrktgdisc || 0),
        rddisc: acc.rddisc + Number(row.rddisc || 0),
        bw_products: acc.bw_products + Number(row.bw_products || 0),
        merchandise: acc.merchandise + Number(row.merchandise || 0),
        partycakes: acc.partycakes + Number(row.partycakes || 0),
        commission: acc.commission + Number(row.commission || 0)
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
        paymaya: 0,
        card: 0,
        loyaltycard: 0,
        foodpanda: 0,
        grabfood: 0,
        mrktgdisc: 0,
        rddisc: 0,
        bw_products: 0,
        merchandise: 0,
        partycakes: 0,
        commission: 0
    });

    // Add totals row
    const totalsRow = worksheet.addRow({
        storename: 'GRAND TOTAL',
        staff: '',
        createddate: '',
        timeonly: '',
        transactionid: '',
        receiptid: '',
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
        paymaya: filteredTotals.paymaya,
        card: filteredTotals.card,
        loyaltycard: filteredTotals.loyaltycard,
        foodpanda: filteredTotals.foodpanda,
        grabfood: filteredTotals.grabfood,
        mrktgdisc: filteredTotals.mrktgdisc,
        rddisc: filteredTotals.rddisc,
        bw_products: filteredTotals.bw_products,
        merchandise: filteredTotals.merchandise,
        partycakes: filteredTotals.partycakes,
        commission: filteredTotals.commission,
        remarks: ''
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

const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

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
                        <div v-if="showStoreDropdown" class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-hidden">
                            <!-- Search input -->
                            <div class="p-2 border-b border-gray-200 sticky top-0 bg-white">
                                <input
                                    ref="storeSearchInput"
                                    v-model="storeSearchQuery"
                                    type="text"
                                    placeholder="Search stores..."
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    @click.stop
                                    @input="handleStoreSearch"
                                >
                            </div>

                            <!-- Action buttons -->
                            <div class="p-2 border-b border-gray-200 flex gap-2 sticky top-[46px] bg-white">
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
                            <div class="max-h-40 overflow-y-auto">
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

                <!-- Date filters -->
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                    <input
                        type="date"
                        v-model="startDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>
                
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                    <input
                        type="date"
                        v-model="endDate"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                    >
                </div>

                <!-- Clear filters button -->
                <div class="flex items-end">
                    <button
                        @click="clearFilters"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-md text-sm"
                    >
                        Clear Filters
                    </button>
                </div>

                <!-- Hidden sync button -->
                <button 
                    @click="syncPaymentMethods"
                    :disabled="isLoading"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md flex items-center hidden"
                >
                    <span v-if="!isLoading">Sync</span>
                    <span v-else>Syncing...</span>
                </button>
            </div>

            <!-- Summary Section for Mobile -->
            <div v-if="isMobile" class="mb-4 bg-white rounded-lg shadow p-4">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Sales Summary</h3>
                <div v-if="isTableLoading" class="flex justify-center items-center py-4">
                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-500"></div>
                    <span class="ml-3">Loading data...</span>
                </div>
                <div v-else class="grid grid-cols-2 gap-3 text-sm">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-xs text-gray-600">Total Qty</p>
                        <p class="text-lg font-bold">{{ footerTotals.total_qty.toLocaleString() }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-xs text-gray-600">Gross Amount</p>
                        <p class="text-lg font-bold">₱{{ footerTotals.total_grossamount.toFixed(2) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-xs text-gray-600">Net Amount</p>
                        <p class="text-lg font-bold">₱{{ footerTotals.total_netamount.toFixed(2) }}</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-xs text-gray-600">Total Cash</p>
                        <p class="text-lg font-bold">₱{{ footerTotals.cash.toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Display -->
            <div class="bg-white rounded-lg shadow">
                <div v-if="isTableLoading" class="flex justify-center items-center py-12">
                    <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-indigo-500"></div>
                    <span class="ml-4 text-lg">Loading sales data...</span>
                </div>
                
                <!-- Mobile View -->
                <div v-if="isMobile && !isTableLoading" class="overflow-hidden">
                    <div class="max-h-96 overflow-y-auto">
                        <div v-for="(item, index) in filteredData" :key="`${item.transactionid}-${index}`" 
                             class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                            
                            <div class="space-y-3">
                                <!-- Header Info -->
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ item?.itemname || '' }}</div>
                                        <div class="text-sm text-gray-500">{{ item?.storename || '' }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">{{ item?.createddate || '' }}</div>
                                        <div class="text-xs text-gray-400">{{ item?.timeonly || '' }}</div>
                                    </div>
                                </div>
                                
                                <!-- Transaction Details -->
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Receipt:</span>
                                        <span class="font-medium ml-1">{{ item?.receiptid || '' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Staff:</span>
                                        <span class="font-medium ml-1">{{ item?.staff || '' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Qty:</span>
                                        <span class="font-medium ml-1">{{ Math.round(item?.qty || 0) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Customer:</span>
                                        <span class="font-medium ml-1">{{ item?.custaccount || 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Financial Details -->
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div>
                                        <span class="text-gray-600">Gross:</span>
                                        <span class="font-medium ml-1">₱{{ Number(item?.total_grossamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Discount:</span>
                                        <span class="font-medium ml-1">₱{{ Number(item?.total_discamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Net:</span>
                                        <span class="font-bold ml-1 text-green-600">₱{{ Number(item?.total_netamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">VAT:</span>
                                        <span class="font-medium ml-1">₱{{ Number(item?.vat || 0).toFixed(2) }}</span>
                                    </div>
                                </div>

                                <!-- Payment Methods (only show if has value) -->
                                <div v-if="Number(item?.cash || 0) > 0 || Number(item?.gcash || 0) > 0 || Number(item?.card || 0) > 0" 
                                     class="grid grid-cols-3 gap-2 text-xs text-gray-600">
                                    <div v-if="Number(item?.cash || 0) > 0">Cash: ₱{{ Number(item.cash).toFixed(2) }}</div>
                                    <div v-if="Number(item?.gcash || 0) > 0">GCash: ₱{{ Number(item.gcash).toFixed(2) }}</div>
                                    <div v-if="Number(item?.card || 0) > 0">Card: ₱{{ Number(item.card).toFixed(2) }}</div>
                                    <div v-if="Number(item?.paymaya || 0) > 0">PayMaya: ₱{{ Number(item.paymaya).toFixed(2) }}</div>
                                    <div v-if="Number(item?.foodpanda || 0) > 0">FoodPanda: ₱{{ Number(item.foodpanda).toFixed(2) }}</div>
                                    <div v-if="Number(item?.grabfood || 0) > 0">GrabFood: ₱{{ Number(item.grabfood).toFixed(2) }}</div>
                                </div>

                                <!-- Additional Info -->
                                <div v-if="item?.itemgroup || item?.discofferid" class="text-xs text-gray-500">
                                    <span v-if="item.itemgroup">Group: {{ item.itemgroup }}</span>
                                    <span v-if="item.itemgroup && item.discofferid"> • </span>
                                    <span v-if="item.discofferid">Promo: {{ item.discofferid }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div v-if="filteredData.length === 0" class="text-center py-8 text-gray-500">
                            No sales data available for the selected filters.
                        </div>
                    </div>
                </div>

                <!-- Desktop DataTable -->
                <TableContainer v-if="!isMobile" class="max-h-[80vh] overflow-x-auto overflow-y-auto">
                    <DataTable 
                        v-if="filteredData.length > 0"
                        :data="filteredData" 
                        :columns="columns" 
                        class="w-full relative display" 
                        :options="options"
                    />

                    <!-- Fallback message when no data is available -->
                    <p v-else class="text-center py-8 text-gray-500">No data available for the selected filters.</p>
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
        </template>
    </component>
</template>