<template>
    <component :is="layoutComponent" active-tab="REPORTS">
        <template v-slot:main>
            <!-- Enhanced Header Section -->
            <!-- <div class="mb-6">
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-6 rounded-lg shadow-lg">
                    <h1 class="text-2xl font-bold mb-2">Transaction Sales Report</h1>
                    <p class="text-blue-100">Detailed sales transaction analysis with payment methods and commissions</p>
                </div>
            </div> -->

            <!-- Enhanced Filters Section -->
            <div class="mb-6 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.414A1 1 0 013 6.707V4z"></path>
                        </svg>
                        Filters & Options
                    </h3>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <!-- Store Selection -->
                        <div 
                            v-if="userRole.toUpperCase() === 'ADMIN' || userRole.toUpperCase() === 'SUPERADMIN'" 
                            class="store-dropdown-container relative"
                        >
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Store Selection
                            </label>
                            <div class="relative">
                                <button
                                    @click="showStoreDropdown = !showStoreDropdown"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-left bg-white hover:bg-gray-50 transition-colors"
                                >
                                    <span v-if="selectedStores.length === 0" class="text-gray-500">Select stores...</span>
                                    <span v-else-if="selectedStores.length === 1" class="text-gray-900">{{ selectedStores[0] }}</span>
                                    <span v-else class="text-gray-900">{{ selectedStores.length }} stores selected</span>
                                    <svg class="float-right mt-1 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Enhanced Dropdown -->
                                <div v-if="showStoreDropdown" class="absolute z-50 w-full mt-2 bg-white border border-gray-300 rounded-lg shadow-xl max-h-72 overflow-hidden">
                                    <!-- Search input -->
                                    <div class="p-3 border-b border-gray-200 bg-gray-50">
                                        <input
                                            ref="storeSearchInput"
                                            v-model="storeSearchQuery"
                                            type="text"
                                            placeholder="Search stores..."
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                            @click.stop
                                            @input="handleStoreSearch"
                                        >
                                    </div>

                                    <!-- Action buttons -->
                                    <div class="p-3 border-b border-gray-200 flex gap-2 bg-gray-50">
                                        <button
                                            @click="selectAllStores"
                                            class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700 transition-colors"
                                        >
                                            Select All
                                        </button>
                                        <button
                                            @click="clearStoreSelection"
                                            class="flex-1 px-3 py-2 bg-gray-600 text-white text-sm rounded-md hover:bg-gray-700 transition-colors"
                                        >
                                            Clear
                                        </button>
                                    </div>

                                    <!-- Store options -->
                                    <div class="max-h-48 overflow-y-auto">
                                        <label 
                                            v-for="store in filteredStores" 
                                            :key="store"
                                            class="flex items-center px-4 py-3 hover:bg-blue-50 cursor-pointer transition-colors"
                                        >
                                            <input
                                                type="checkbox"
                                                :checked="isStoreSelected(store)"
                                                @change="toggleStoreSelection(store)"
                                                class="mr-3 form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                            >
                                            <span class="text-sm text-gray-700">{{ store }}</span>
                                        </label>
                                    </div>

                                    <div v-if="filteredStores.length === 0" class="p-4 text-sm text-gray-500 text-center">
                                        No stores found
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Date filters -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Start Date
                            </label>
                            <input
                                type="date"
                                v-model="startDate"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                End Date
                            </label>
                            <input
                                type="date"
                                v-model="endDate"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                            >
                        </div>

                        <!-- Action buttons -->
                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors flex items-center justify-center"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Summary Section for Mobile -->
            <div v-if="isMobile" class="mb-6 bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    Sales Summary
                </h3>
                <div v-if="isTableLoading" class="flex justify-center items-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                    <span class="ml-3 text-gray-600">Loading data...</span>
                </div>
                <div v-else class="grid grid-cols-2 gap-4 text-sm">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg">
                        <p class="text-xs text-blue-600 font-medium">Total Qty</p>
                        <p class="text-xl font-bold text-blue-800">{{ footerTotals.total_qty.toLocaleString() }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg">
                        <p class="text-xs text-green-600 font-medium">Gross Amount</p>
                        <p class="text-xl font-bold text-green-800">₱{{ footerTotals.total_grossamount.toFixed(2) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg">
                        <p class="text-xs text-purple-600 font-medium">Net Amount</p>
                        <p class="text-xl font-bold text-purple-800">₱{{ footerTotals.total_netamount.toFixed(2) }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg">
                        <p class="text-xs text-orange-600 font-medium">Commission</p>
                        <p class="text-xl font-bold text-orange-800">₱{{ footerTotals.commission.toFixed(2) }}</p>
                    </div>
                </div>
            </div>

            <!-- Enhanced Data Display -->
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <!-- Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Transaction Sales Data
                        </h3>
                        <div class="text-sm text-gray-500">
                            {{ filteredData.length }} transactions
                        </div>
                    </div>
                </div>

                <div v-if="isTableLoading" class="flex justify-center items-center py-16">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-t-2 border-b-2 border-blue-500 mx-auto"></div>
                        <span class="mt-4 text-lg text-gray-600 block">Loading sales data...</span>
                    </div>
                </div>
                
                <!-- Mobile View with Enhanced Cards -->
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
                                        <div class="text-sm text-gray-500 flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            {{ item?.storename || '' }}
                                        </div>
                                        <div class="text-xs text-blue-600 mt-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m-9 1v1a1 1 0 001 1h8a1 1 0 001-1V5M7 10h10"></path>
                                            </svg>
                                            Long press for details
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm text-gray-500">{{ item?.createddate || '' }}</div>
                                        <div class="text-xs text-gray-400">{{ item?.timeonly || '' }}</div>
                                    </div>
                                </div>
                                
                                <!-- Key Financial Info -->
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="bg-green-50 p-3 rounded-lg">
                                        <span class="text-xs text-green-600 font-medium">Net Amount</span>
                                        <p class="text-lg font-bold text-green-800">₱{{ Number(item?.total_netamount || 0).toFixed(2) }}</p>
                                    </div>
                                    <div class="bg-orange-50 p-3 rounded-lg">
                                        <span class="text-xs text-orange-600 font-medium">Commission</span>
                                        <p class="text-lg font-bold text-orange-800">₱{{ Number(item?.commission || 0).toFixed(2) }}</p>
                                    </div>
                                </div>

                                <!-- Transaction Basic Info -->
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
                            </div>
                        </div>
                        
                        <div v-if="filteredData.length === 0" class="text-center py-12 text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p>No sales data available for the selected filters.</p>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Desktop DataTable -->
                <div v-if="!isMobile && !isTableLoading" class="overflow-hidden">
                    <TableContainer class="max-h-[75vh] overflow-x-auto overflow-y-auto">
                        <DataTable 
                            v-if="filteredData.length > 0"
                            :data="filteredData" 
                            :columns="columns" 
                            class="w-full relative display enhanced-table" 
                            :options="options"
                        />

                        <!-- Fallback message when no data is available -->
                        <div v-else class="text-center py-12 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">No data available</p>
                            <p class="text-sm text-gray-400 mt-1">Try adjusting your filters or date range</p>
                        </div>
                    </TableContainer>
                </div>
            </div>

            <!-- Mobile Floating Action Button and Menu -->
            <div v-if="isMobile" class="fixed bottom-6 right-6 z-40">
                <!-- Floating Menu Options -->
                <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-xl border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                    
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

            <!-- Enhanced Mobile Item Detail Modal -->
            <div v-if="showItemDetail && selectedItem" 
                 class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4"
                 @click="closeItemDetail">
                <div class="bg-white rounded-xl max-w-md w-full max-h-[90vh] overflow-y-auto shadow-2xl" @click.stop>
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex justify-between items-start mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Transaction Details</h3>
                            <button @click="closeItemDetail" class="text-gray-500 hover:text-gray-700 p-1">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Item Information -->
                        <div class="space-y-6">
                            <!-- Basic Info -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Basic Information
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Item:</span>
                                        <span class="text-gray-900">{{ selectedItem.itemname }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Store:</span>
                                        <span class="text-gray-900">{{ selectedItem.storename }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Staff:</span>
                                        <span class="text-gray-900">{{ selectedItem.staff }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Date & Time:</span>
                                        <span class="text-gray-900">{{ selectedItem.createddate }} {{ selectedItem.timeonly }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Transaction Details -->
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Transaction Details
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Transaction ID:</span>
                                        <span class="text-gray-900">{{ selectedItem.transactionid }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Receipt ID:</span>
                                        <span class="text-gray-900">{{ selectedItem.receiptid }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Customer:</span>
                                        <span class="text-gray-900">{{ selectedItem.custaccount || 'Walk-in Customer' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Item Group:</span>
                                        <span class="text-gray-900">{{ selectedItem.itemgroup }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Promo:</span>
                                        <span class="text-gray-900">{{ selectedItem.discofferid || 'None' }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Quantity:</span>
                                        <span class="text-gray-900">{{ Math.round(selectedItem.qty || 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Details -->
                            <div class="bg-green-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                    Financial Details
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Cost Price:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.total_costprice || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Gross Amount:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.total_grossamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Cost Amount:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.total_costamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Discount Amount:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.total_discamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t border-green-200 pt-2">
                                        <span class="font-bold text-gray-700">Net Amount:</span>
                                        <span class="font-bold text-green-700">₱{{ Number(selectedItem.total_netamount || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between border-t border-green-200 pt-2">
                                        <span class="font-bold text-gray-700">Commission:</span>
                                        <span class="font-bold text-orange-600">₱{{ Number(selectedItem.commission || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">Vatable Sales:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.vatablesales || 0).toFixed(2) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium text-gray-600">VAT:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.vat || 0).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Methods -->
                            <div class="bg-purple-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Payment Methods
                                </h4>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div v-if="Number(selectedItem.cash || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Cash:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.cash).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.charge || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Charge:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.charge).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.representation || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Representation:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.representation).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.gcash || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">GCash:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.gcash).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.paymaya || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">PayMaya:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.paymaya).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.card || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Card:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.card).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.loyaltycard || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Loyalty Card:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.loyaltycard).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.foodpanda || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">FoodPanda:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.foodpanda).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.grabfood || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">GrabFood:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.grabfood).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.mrktgdisc || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Marketing Disc:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.mrktgdisc).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.rddisc || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">RD Disc:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.rddisc).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Product Categories -->
                            <div v-if="Number(selectedItem.bw_products || 0) > 0 || Number(selectedItem.merchandise || 0) > 0 || Number(selectedItem.partycakes || 0) > 0" 
                                 class="bg-yellow-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    Product Categories
                                </h4>
                                <div class="space-y-2 text-sm">
                                    <div v-if="Number(selectedItem.bw_products || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">BW Products:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.bw_products).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.merchandise || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Merchandise:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.merchandise).toFixed(2) }}</span>
                                    </div>
                                    <div v-if="Number(selectedItem.partycakes || 0) > 0" class="flex justify-between">
                                        <span class="font-medium text-gray-600">Party Cakes:</span>
                                        <span class="text-gray-900">₱{{ Number(selectedItem.partycakes).toFixed(2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div v-if="selectedItem.remarks" class="bg-gray-50 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 mb-2 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Notes
                                </h4>
                                <p class="text-sm text-gray-600 bg-white p-3 rounded border">{{ selectedItem.remarks }}</p>
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

// Mobile long press handlers
const handleTouchStart = (item, event) => {
    event.preventDefault();
    
    const element = event.currentTarget;
    element.classList.add('touching', 'long-press-indicator', 'pressing');
    
    longPressTimer.value = setTimeout(() => {
        selectedItem.value = item;
        showItemDetail.value = true;
        
        element.classList.remove('touching', 'pressing');
        
        if (navigator.vibrate) {
            navigator.vibrate([50, 30, 50]);
        }
    }, 500);
};

const handleTouchEnd = (event) => {
    if (longPressTimer.value) {
        clearTimeout(longPressTimer.value);
        longPressTimer.value = null;
    }
    
    if (event && event.currentTarget) {
        const element = event.currentTarget;
        element.classList.remove('touching', 'long-press-indicator', 'pressing');
    }
};

const closeItemDetail = () => {
    showItemDetail.value = false;
    selectedItem.value = null;
};

// Filtered stores based on search
const filteredStores = computed(() => {
    let stores = [];
    
    if (Array.isArray(props.stores)) {
        stores = props.stores.map(store => {
            if (typeof store === 'string') {
                return store;
            }
            
            if (typeof store === 'object' && store !== null) {
                if (store.NAME) {
                    return store.NAME;
                }
                if (store.name) {
                    return store.name;
                }
                if (store.storename) {
                    return store.storename;
                }
                if (store.store_name) {
                    return store.store_name;
                }
                
                const storeStr = JSON.stringify(store);
                const nameMatch = storeStr.match(/"NAME"\s*:\s*"([^"]+)"/);
                if (nameMatch) {
                    return nameMatch[1];
                }
                
                return storeStr.replace(/[{}":]/g, '').replace(/STOREID[^,]*,?\s*/g, '').replace(/NAME/g, '').trim() || 'Unknown Store';
            }
            
            return String(store);
        });
    }
    
    stores = [...new Set(stores)].sort();
    
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
    const allStores = props.stores.map(store => {
        if (typeof store === 'string') {
            return store;
        }
        
        if (typeof store === 'object' && store !== null) {
            if (store.NAME) {
                return store.NAME;
            }
            if (store.name) {
                return store.name;
            }
            if (store.storename) {
                return store.storename;
            }
            if (store.store_name) {
                return store.store_name;
            }
            
            const storeStr = JSON.stringify(store);
            const nameMatch = storeStr.match(/"NAME"\s*:\s*"([^"]+)"/);
            if (nameMatch) {
                return nameMatch[1];
            }
            
            return storeStr.replace(/[{}":]/g, '').replace(/STOREID[^,]*,?\s*/g, '').replace(/NAME/g, '').trim() || 'Unknown Store';
        }
        return String(store);
    });
    
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
        acc.total_discamount += (parseFloat(row.total_discamount) || 0);
        acc.total_costprice += (parseFloat(row.total_costprice) || 0);
        acc.total_netamount += (parseFloat(row.total_netamount) || 0);
        acc.vatablesales += (parseFloat(row.vatablesales) || 0);
        acc.vat += (parseFloat(row.vat) || 0);
        acc.total_grossamount += (parseFloat(row.total_grossamount) || 0);
        acc.total_costamount += (parseFloat(row.total_costamount) || 0);
        acc.total_qty += Math.round(row.qty || 0);
        acc.commission += (parseFloat(row.commission) || 0);

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

        return acc;
    }, {
        total_qty: 0,
        total_discamount: 0,
        total_costprice: 0,
        total_netamount: 0,
        vatablesales: 0,
        vat: 0,
        total_grossamount: 0,
        total_costamount: 0,
        commission: 0,
        
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
        partycakes: 0
    });
});

const columns = [
    { 
        data: 'storename', 
        title: 'Store', 
        footer: 'Grand Total',
        className: 'min-w-[100px] max-w-[120px] font-medium'
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
        className: 'min-w-[85px] max-w-[100px] text-center'
    },
    { 
        data: 'timeonly', 
        title: 'Time', 
        footer: '',
        className: 'min-w-[70px] max-w-[80px] text-center'
    },
    { 
        data: 'transactionid', 
        title: 'Transaction ID', 
        footer: '',
        className: 'min-w-[120px] max-w-[140px] font-mono text-sm'
    },
    { 
        data: 'receiptid', 
        title: 'Receipt ID', 
        footer: '',
        className: 'min-w-[100px] max-w-[120px] font-mono text-sm'
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
        className: 'min-w-[150px] max-w-[200px] font-medium'
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
        className: 'min-w-[100px] max-w-[200px] text-sm'
    },
    
    // Columns with footer calculations
    { 
        data: 'qty', 
        title: 'Qty',
        render: (data) => Math.round(data || 0),
        footer: '',
        className: 'text-right min-w-[60px] max-w-[80px] font-semibold'
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
        className: 'text-right min-w-[100px] max-w-[120px] font-semibold'
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
        className: 'text-right min-w-[110px] max-w-[130px] text-red-600'
    },
    { 
        data: 'total_netamount', 
        title: 'Net Amount',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px] font-bold text-green-600'
    },
    { 
        data: 'commission', 
        title: 'Commission',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[100px] max-w-[120px] font-bold text-orange-600'
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
        className: 'text-right min-w-[80px] max-w-[100px] text-purple-600'
    },
    { 
        data: 'rddisc', 
        title: 'RD Disc',
        render: (data) => (parseFloat(data) || 0).toFixed(2),
        footer: '',
        className: 'text-right min-w-[80px] max-w-[100px] text-blue-600'
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
        data: 'remarks', 
        title: 'NOTE', 
        footer: '',
        className: 'min-w-[120px] max-w-[150px] text-sm'
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
        {
            text: '<i class="fas fa-copy"></i> Copy',
            extend: 'copy',
            className: 'btn-export btn-copy'
        },
        {
            text: '<i class="fas fa-file-excel"></i> Excel',
            action: function(e, dt, node, config) {
                exportToExcel(dt);
            },
            className: 'btn-export btn-excel'
        },
        {
            text: '<i class="fas fa-file-pdf"></i> PDF',
            extend: 'pdf',
            className: 'btn-export btn-pdf'
        },
        {
            text: '<i class="fas fa-print"></i> Print',
            extend: 'print',
            className: 'btn-export btn-print'
        }
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
            commission: 0,
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
            partycakes: 0
        };

        // Calculate totals only for filtered/searched rows
        api.rows({ search: 'applied' }).every(function(rowIdx) {
            const data = this.data();
            filteredTotals.total_qty += Math.round(Number(data.qty) || 0);
            filteredTotals.total_costprice += Number(data.total_costprice) || 0;
            filteredTotals.total_grossamount += Number(data.total_grossamount) || 0;
            filteredTotals.total_costamount += Number(data.total_costamount) || 0;
            filteredTotals.total_discamount += Number(data.total_discamount) || 0;
            filteredTotals.total_netamount += Number(data.total_netamount) || 0;
            filteredTotals.commission += Number(data.commission) || 0;
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
        });

        // Helper function to format numbers with commas and 2 decimal places
        function formatNumber(num, isInteger = false) {
            if (isInteger) {
                return Math.round(num).toLocaleString('en-US');
            }
            return num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

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
                { index: 16, key: 'commission' },
                { index: 17, key: 'vatablesales' },
                { index: 18, key: 'vat' },
                { index: 19, key: 'cash' },
                { index: 20, key: 'charge' },
                { index: 21, key: 'representation' },
                { index: 22, key: 'gcash' },
                { index: 23, key: 'paymaya' },
                { index: 24, key: 'card' },
                { index: 25, key: 'loyaltycard' },
                { index: 26, key: 'foodpanda' },
                { index: 27, key: 'grabfood' },
                { index: 28, key: 'mrktgdisc' },
                { index: 29, key: 'rddisc' },
                { index: 30, key: 'bw_products' },
                { index: 31, key: 'merchandise' },
                { index: 32, key: 'partycakes' }
            ];

            // Update footer cells with filtered totals
            columnMappings.forEach(({ index, key, round }) => {
                const total = filteredTotals[key];
                if (footerCells[index]) {
                    footerCells[index].textContent = formatNumber(total, round);
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
    const worksheet = workbook.addWorksheet('Transaction Sales Data');
    
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
        { header: 'COMMISSION', key: 'commission', width: 12, style: { numFmt: '#,##0.00' } },
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
        { header: 'NOTE', key: 'remarks', width: 20 }
    ];

    worksheet.columns = excelColumns;

    // Style the header row
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true, color: { argb: 'FFFFFFFF' } };
    headerRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF4F46E5' }
    };

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
            commission: Number(row.commission) || 0,
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
        commission: acc.commission + Number(row.commission || 0),
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
        partycakes: acc.partycakes + Number(row.partycakes || 0)
    }), {
        total_qty: 0,
        total_costprice: 0,
        total_grossamount: 0,
        total_costamount: 0,
        total_discamount: 0,
        total_netamount: 0,
        commission: 0,
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
        commission: filteredTotals.commission,
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
        remarks: ''
    });

    // Style totals row
    totalsRow.font = { bold: true };
    totalsRow.fill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FFE5E7EB' }
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
                link.download = `Transaction_Sales_Report_${new Date().toISOString().split('T')[0]}.xlsx`;
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
/* Enhanced table styling */
.enhanced-table {
    font-size: 13px;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
}

.enhanced-table :deep(.dataTable) {
    width: 100% !important;
    border-collapse: collapse;
    border-spacing: 0;
    margin-top: 0;
    table-layout: auto;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.enhanced-table :deep(.dataTable thead th) {
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    color: #ffffff;
    padding: 16px 12px;
    border-bottom: 3px solid #0ea5e9;
    font-weight: 600;
    position: sticky;
    top: 0;
    z-index: 10;
    font-size: 13px;
    white-space: nowrap;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.enhanced-table :deep(.dataTable tbody tr) {
    border-bottom: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}

.enhanced-table :deep(.dataTable tbody tr:nth-child(odd)) {
    background-color: #ffffff;
}

.enhanced-table :deep(.dataTable tbody tr:nth-child(even)) {
    background-color: #f8fafc;
}

.enhanced-table :deep(.dataTable tbody tr:hover) {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    transform: scale(1.01);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
    cursor: pointer;
}

.enhanced-table :deep(.dataTable th),
.enhanced-table :deep(.dataTable td) {
    padding: 12px 10px;
    text-align: left;
    border: 1px solid #e2e8f0;
    font-size: 12px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

/* Color coding for different types of data */
.enhanced-table :deep(.dataTable .text-green-600) {
    color: #059669 !important;
    font-weight: 600;
}

.enhanced-table :deep(.dataTable .text-red-600) {
    color: #dc2626 !important;
    font-weight: 600;
}

.enhanced-table :deep(.dataTable .text-orange-600) {
    color: #ea580c !important;
    font-weight: 600;
}

.enhanced-table :deep(.dataTable .text-purple-600) {
    color: #9333ea !important;
    font-weight: 600;
}

.enhanced-table :deep(.dataTable .text-blue-600) {
    color: #2563eb !important;
    font-weight: 600;
}

/* Enhanced footer styling */
.enhanced-table :deep(.dataTable tfoot) {
    background: linear-gradient(135deg, #7c2d12 0%, #991b1b 100%);
    color: white !important;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.1);
}

.enhanced-table :deep(.dataTable tfoot td),
.enhanced-table :deep(.dataTable tfoot th) {
    padding: 16px 12px !important;
    font-size: 13px !important;
    font-weight: bold !important;
    background: linear-gradient(135deg, #7c2d12 0%, #991b1b 100%) !important;
    color: white !important;
    border: 1px solid #92400e !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Enhanced button styling */
.enhanced-table :deep(.dt-buttons) {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    position: absolute;
    z-index: 1000;
    margin: 20px;
    right: 0;
    top: 0;
    gap: 12px;
}

.enhanced-table :deep(.btn-export) {
    padding: 12px 20px !important;
    margin: 0 !important;
    border-radius: 10px !important;
    color: white !important;
    border: none !important;
    cursor: pointer !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    transition: all 0.3s ease !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    min-width: 100px !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
}

.enhanced-table :deep(.btn-copy) {
    background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%) !important;
}

.enhanced-table :deep(.btn-excel) {
    background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
}

.enhanced-table :deep(.btn-pdf) {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
}

.enhanced-table :deep(.btn-print) {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
}

.enhanced-table :deep(.btn-export:hover) {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2) !important;
}

.enhanced-table :deep(.btn-export:active) {
    transform: translateY(0px) !important;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
}

/* Enhanced search and pagination */
.enhanced-table :deep(.dataTables_filter) {
    float: right;
    padding: 20px;
    position: relative;
    z-index: 999;
    margin-right: 220px;
}

.enhanced-table :deep(.dataTables_filter input) {
    padding: 10px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    margin-left: 8px;
    font-size: 14px;
    transition: all 0.2s ease;
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.enhanced-table :deep(.dataTables_filter input:focus) {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    outline: none;
}

.enhanced-table :deep(.dataTables_wrapper .dataTables_paginate) {
    padding: 20px;
    text-align: right;
}

.enhanced-table :deep(.dataTables_wrapper .dataTables_paginate .paginate_button) {
    margin-left: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 13px;
    border: 1px solid #e2e8f0;
    background: white;
    transition: all 0.2s ease;
}

.enhanced-table :deep(.dataTables_wrapper .dataTables_paginate .paginate_button:hover) {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.enhanced-table :deep(.dataTables_wrapper .dataTables_paginate .paginate_button.current) {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white !important;
    border-color: #2563eb;
}

.enhanced-table :deep(.dataTables_wrapper .dataTables_info) {
    padding: 20px;
    font-size: 14px;
    color: #64748b;
    font-weight: 500;
}

/* Mobile long press visual feedback */
@media (max-width: 768px) {
    .mobile-item:active,
    .mobile-item.touching {
        background-color: #dbeafe !important;
        transform: scale(0.98);
        transition: all 0.1s ease;
    }
    
    .mobile-sales-item {
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
        -webkit-tap-highlight-color: transparent;
    }
    
    .mobile-sales-item:active {
        background-color: #dbeafe !important;
        transform: scale(0.98);
    }
    
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

/* Enhanced scrollbar styling */
.enhanced-table :deep(.dataTables_scrollBody::-webkit-scrollbar) {
    height: 10px;
    width: 10px;
}

.enhanced-table :deep(.dataTables_scrollBody::-webkit-scrollbar-track) {
    background: #f1f5f9;
    border-radius: 5px;
}

.enhanced-table :deep(.dataTables_scrollBody::-webkit-scrollbar-thumb) {
    background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 5px;
}

.enhanced-table :deep(.dataTables_scrollBody::-webkit-scrollbar-thumb:hover) {
    background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
}

/* Store dropdown styling */
.store-dropdown-container .relative > div {
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
}

/* Loading spinner */
.loading-spinner {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Gradient backgrounds for cards */
.bg-gradient-to-br {
    background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

/* Smooth transitions */
.transition-smooth {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Enhanced card hover effects */
.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

/* Floating menu animation */
.floating-menu-enter-active,
.floating-menu-leave-active {
    transition: all 0.3s ease;
}

.floating-menu-enter-from,
.floating-menu-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}
</style>