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
                
                <!-- Mobile View with Long Press -->
                <div v-if="isMobile && !isTableLoading" class="overflow-hidden">
                    <div class="max-h-96 overflow-y-auto">
                        <div v-for="(item, index) in filteredData" :key="`${item.transactionid}-${index}`" 
                             class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors cursor-pointer select-none mobile-sales-item"
                             @touchstart="handleTouchStart(item, $event)"
                             @touchend="handleTouchEnd($event)"
                             @touchcancel="handleTouchEnd($event)">
                            
                            <div class="space-y-3">
                                <!-- Header Info -->
                                <div class="flex justify-between items-start">
                                    <div class="flex-1 min-w-0">
                                        <div class="font-medium text-gray-900 truncate">{{ item?.itemname || '' }}</div>
                                        <div class="text-sm text-gray-500">{{ item?.storename || '' }}</div>
                                        <div class="text-xs text-blue-600 mt-1">Long press for details</div>
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
                        class="w-full relative display compact-table" 
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

            <!-- Mobile Item Detail Modal -->
            <div v-if="showItemDetail && selectedItem" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                 @click="closeItemDetail">
                <div class="bg-white rounded-lg max-w-md w-full max-h-[90vh] overflow-y-auto" @click.stop>
                    <div class="p-4">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Item Details</h3>
                            <button @click="closeItemDetail" class="text-gray-500 hover:text-gray-700">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Item Information -->
                        <div class="space-y-4">
                            <!-- Basic Info -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">{{ selectedItem.itemname }}</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div><span class="font-medium">Store:</span> {{ selectedItem.storename }}</div>
                                    <div><span class="font-medium">Staff:</span> {{ selectedItem.staff }}</div>
                                    <div><span class="font-medium">Date:</span> {{ selectedItem.createddate }}</div>
                                    <div><span class="font-medium">Time:</span> {{ selectedItem.timeonly }}</div>
                                </div>
                            </div>

                            <!-- Transaction Details -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Transaction</h4>
                                <div class="grid grid-cols-1 gap-2 text-sm">
                                    <div><span class="font-medium">Transaction ID:</span> {{ selectedItem.transactionid }}</div>
                                    <div><span class="font-medium">Receipt ID:</span> {{ selectedItem.receiptid }}</div>
                                    <div><span class="font-medium">Customer:</span> {{ selectedItem.custaccount || 'Walk-in Customer' }}</div>
                                    <div><span class="font-medium">Item Group:</span> {{ selectedItem.itemgroup }}</div>
                                    <div><span class="font-medium">Promo:</span> {{ selectedItem.discofferid || 'None' }}</div>
                                    <div><span class="font-medium">Quantity:</span> {{ Math.round(selectedItem.qty || 0) }}</div>
                                </div>
                            </div>

                            <!-- Financial Details -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Financial</h4>
                                <div class="grid grid-cols-1 gap-2 text-sm">
                                    <div><span class="font-medium">Cost Price:</span> ₱{{ Number(selectedItem.total_costprice || 0).toFixed(2) }}</div>
                                    <div><span class="font-medium">Gross Amount:</span> ₱{{ Number(selectedItem.total_grossamount || 0).toFixed(2) }}</div>
                                    <div><span class="font-medium">Cost Amount:</span> ₱{{ Number(selectedItem.total_costamount || 0).toFixed(2) }}</div>
                                    <div><span class="font-medium">Discount Amount:</span> ₱{{ Number(selectedItem.total_discamount || 0).toFixed(2) }}</div>
                                    <div class="text-lg"><span class="font-medium">Net Amount:</span> <span class="font-bold text-green-600">₱{{ Number(selectedItem.total_netamount || 0).toFixed(2) }}</span></div>
                                    <div><span class="font-medium">Vatable Sales:</span> ₱{{ Number(selectedItem.vatablesales || 0).toFixed(2) }}</div>
                                    <div><span class="font-medium">VAT:</span> ₱{{ Number(selectedItem.vat || 0).toFixed(2) }}</div>
                                </div>
                            </div>

                            <!-- Payment Methods -->
                            <div class="border-b pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Payment Methods</h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div v-if="Number(selectedItem.cash || 0) > 0"><span class="font-medium">Cash:</span> ₱{{ Number(selectedItem.cash).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.charge || 0) > 0"><span class="font-medium">Charge:</span> ₱{{ Number(selectedItem.charge).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.representation || 0) > 0"><span class="font-medium">Representation:</span> ₱{{ Number(selectedItem.representation).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.gcash || 0) > 0"><span class="font-medium">GCash:</span> ₱{{ Number(selectedItem.gcash).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.paymaya || 0) > 0"><span class="font-medium">PayMaya:</span> ₱{{ Number(selectedItem.paymaya).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.card || 0) > 0"><span class="font-medium">Card:</span> ₱{{ Number(selectedItem.card).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.loyaltycard || 0) > 0"><span class="font-medium">Loyalty Card:</span> ₱{{ Number(selectedItem.loyaltycard).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.foodpanda || 0) > 0"><span class="font-medium">FoodPanda:</span> ₱{{ Number(selectedItem.foodpanda).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.grabfood || 0) > 0"><span class="font-medium">GrabFood:</span> ₱{{ Number(selectedItem.grabfood).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.mrktgdisc || 0) > 0"><span class="font-medium">Marketing Disc:</span> ₱{{ Number(selectedItem.mrktgdisc).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.rddisc || 0) > 0"><span class="font-medium">RD Disc:</span> ₱{{ Number(selectedItem.rddisc).toFixed(2) }}</div>
                                </div>
                            </div>

                            <!-- Product Categories -->
                            <div class="pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Product Categories</h4>
                                <div class="grid grid-cols-1 gap-2 text-sm">
                                    <div v-if="Number(selectedItem.bw_products || 0) > 0"><span class="font-medium">BW Products:</span> ₱{{ Number(selectedItem.bw_products).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.merchandise || 0) > 0"><span class="font-medium">Merchandise:</span> ₱{{ Number(selectedItem.merchandise).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.partycakes || 0) > 0"><span class="font-medium">Party Cakes:</span> ₱{{ Number(selectedItem.partycakes).toFixed(2) }}</div>
                                    <div v-if="Number(selectedItem.commission || 0) > 0"><span class="font-medium">Commission:</span> ₱{{ Number(selectedItem.commission).toFixed(2) }}</div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="selectedItem.remarks" class="pb-4">
                                <h4 class="font-medium text-gray-900 mb-2">Notes</h4>
                                <p class="text-sm text-gray-600">{{ selectedItem.remarks }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </component>
</template>

<script setup>
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

// Mobile item detail modal
const showItemDetail = ref(false);
const selectedItem = ref(null);
const longPressTimer = ref(null);

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

// Mobile long press handlers with better visual feedback
const handleTouchStart = (item, event) => {
    event.preventDefault();
    
    // Add visual feedback class
    const element = event.currentTarget;
    element.classList.add('touching', 'long-press-indicator', 'pressing');
    
    longPressTimer.value = setTimeout(() => {
        selectedItem.value = item;
        showItemDetail.value = true;
        
        // Remove visual feedback
        element.classList.remove('touching', 'pressing');
        
        // Add haptic feedback if available
        if (navigator.vibrate) {
            navigator.vibrate([50, 30, 50]); // More sophisticated vibration pattern
        }
    }, 500); // 500ms long press
};

const handleTouchEnd = (event) => {
    if (longPressTimer.value) {
        clearTimeout(longPressTimer.value);
        longPressTimer.value = null;
    }
    
    // Remove visual feedback classes
    if (event && event.currentTarget) {
        const element = event.currentTarget;
        element.classList.remove('touching', 'long-press-indicator', 'pressing');
    }
};

const closeItemDetail = () => {
    showItemDetail.value = false;
    selectedItem.value = null;
};

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
    if (longPressTimer.value) {
        clearTimeout(longPressTimer.value);
    }
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
    scrollY: "60vh",
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
        { targets: [3], className: 'text-center' }
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
            // Update numerical totals - fix qty calculation
            filteredTotals.total_qty += Math.round(Number(data.qty) || 0);
            filteredTotals.total_costprice += Number(data.total_costprice) || 0;
            filteredTotals.total_grossamount += Number(data.total_grossamount) || 0;
            filteredTotals.total_costamount += Number(data.total_costamount) || 0;
            filteredTotals.total_discamount += Number(data.total_discamount) || 0;
            filteredTotals.total_netamount += Number(data.total_netamount) || 0;
            filteredTotals.vatablesales += Number(data.vatablesales) || 0;
            filteredTotals.vat += Number(data.vat) || 0;
            filteredTotals.cash += Number(data.cash) || 0;
            filteredTotals.charge += Number(data.charge) || 0;
            filteredTotals.representation += Number(data.representation) || 0;
            filteredTotals.gcash += Number(data.gcash) || 0;
            filteredTotals.paymaya += Number(data.paymaya) || 0;
            filteredTotals.card += Number(data.card) || 0;
            filteredTotals.loyaltycard += Number(data.loyaltycard) || 0;
            filteredTotals.foodpanda += Number(data.foodpanda) || 0;
            filteredTotals.grabfood += Number(data.grabfood) || 0;
            filteredTotals.mrktgdisc += Number(data.mrktgdisc) || 0;
            filteredTotals.rddisc += Number(data.rddisc) || 0;
            filteredTotals.bw_products += Number(data.bw_products) || 0;
            filteredTotals.merchandise += Number(data.merchandise) || 0;
            filteredTotals.partycakes += Number(data.partycakes) || 0;
            filteredTotals.commission += Number(data.commission) || 0;
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

<style scoped>
/* Better spacing for desktop table */


.compact-table {
    font-size: 13px;
}

.compact-table :deep(.dataTable) {
    width: 100% !important;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: 'Arial', sans-serif;
    margin-top: 20px;
    table-layout: auto;
}


.compact-table :deep(.dataTable thead th) {
    background-color: #1f2937;
    color: #ffffff;
    padding: 12px 8px;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 13px;
    white-space: nowrap;
    text-align: center;
}

.compact-table :deep(.dataTable tbody tr) {
    border-bottom: 1px solid #e5e7eb;
}

.compact-table :deep(.dataTable tbody tr:nth-child(odd)) {
    background-color: #ffffff;
}

.compact-table :deep(.dataTable tbody tr:nth-child(even)) {
    background-color: #f8f9fa;
}

.compact-table :deep(.dataTable tbody tr:hover) {
    background-color: #e3f2fd;
    cursor: pointer;
}

.compact-table :deep(.dataTable th),
.compact-table :deep(.dataTable td) {
    padding: 10px 8px;
    text-align: left;
    border: 1px solid #dee2e6;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Right align numeric columns */
.compact-table :deep(.dataTable .text-right) {
    text-align: right !important;
}

.compact-table :deep(.dataTable .text-center) {
    text-align: center !important;
}

/* Footer styling - Maroon background */
.compact-table :deep(.dataTable tfoot) {
    background-color: #800020 !important; /* Maroon background */
    color: white !important;
    font-weight: bold;
    text-align: center;
}

.compact-table :deep(.dataTable tfoot td),
.compact-table :deep(.dataTable tfoot th) {
    padding: 12px 8px !important;
    font-size: 12px !important;
    font-weight: bold !important;
    background-color: #800020 !important;
    color: white !important;
    border: 1px solid #600018 !important;
}

/* DataTable controls styling - Button design */
.compact-table :deep(.dt-buttons) {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    position: absolute;
    z-index: 1000;
    margin: 15px;
    right: 0;
    top: 0;
    gap: 8px;
}

.compact-table :deep(.dt-button),
.compact-table :deep(.buttons-copy),
.compact-table :deep(.buttons-print),
.compact-table :deep(.buttons-excel) {
    padding: 10px 16px !important;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%) !important;
    margin: 0 !important;
    border-radius: 8px !important;
    color: white !important;
    border: 2px solid #1d4ed8 !important;
    cursor: pointer !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s ease !important;
    box-shadow: 0 4px 6px rgba(59, 130, 246, 0.3) !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 80px !important;
}

.compact-table :deep(.dt-button:hover),
.compact-table :deep(.buttons-copy:hover),
.compact-table :deep(.buttons-print:hover),
.compact-table :deep(.buttons-excel:hover) {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%) !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 6px 12px rgba(59, 130, 246, 0.4) !important;
}

.compact-table :deep(.dt-button:active),
.compact-table :deep(.buttons-copy:active),
.compact-table :deep(.buttons-print:active),
.compact-table :deep(.buttons-excel:active) {
    transform: translateY(0px) !important;
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3) !important;
}

.compact-table :deep(.dataTables_filter) {
    float: right;
    padding: 15px;
    position: relative;
    z-index: 999;
    margin-right: 200px;
}

.compact-table :deep(.dataTables_filter input) {
    padding: 8px;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    margin-left: 8px;
    font-size: 13px;
}

.compact-table :deep(.dataTables_wrapper .dataTables_paginate) {
    padding: 15px;
    text-align: right;
}

.compact-table :deep(.dataTables_wrapper .dataTables_paginate .paginate_button) {
    margin-left: 5px;
    padding: 5px 10px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
}

.compact-table :deep(.dataTables_wrapper .dataTables_paginate .paginate_button.current) {
    background-color: #3b82f6;
    color: white !important;
}

.compact-table :deep(.dataTables_wrapper .dataTables_info) {
    padding: 15px;
    font-size: 13px;
}

/* Mobile long press visual feedback */
@media (max-width: 768px) {
    .mobile-item:active,
    .mobile-item.touching {
        background-color: #e3f2fd !important;
        transform: scale(0.98);
        transition: all 0.1s ease;
    }
    
    /* Better mobile card interaction */
    .mobile-sales-item {
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
        -webkit-tap-highlight-color: transparent;
    }
    
    .mobile-sales-item:active {
        background-color: #e3f2fd !important;
        transform: scale(0.98);
    }
    
    /* Long press indicator */
    .long-press-indicator {
        position: relative;
        overflow: hidden;
    }
    
    .long-press-indicator::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
        transition: left 0.5s ease;
    }
    
    .long-press-indicator.pressing::before {
        left: 100%;
    }
}

/* Scrollbar styling for desktop */
.compact-table :deep(.dataTables_scrollBody::-webkit-scrollbar) {
    height: 8px;
    width: 8px;
}

.compact-table :deep(.dataTables_scrollBody::-webkit-scrollbar-track) {
    background: #f1f1f1;
    border-radius: 4px;
}

.compact-table :deep(.dataTables_scrollBody::-webkit-scrollbar-thumb) {
    background: #c1c1c1;
    border-radius: 4px;
}

.compact-table :deep(.dataTables_scrollBody::-webkit-scrollbar-thumb:hover) {
    background: #a8a8a8;
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
</style>