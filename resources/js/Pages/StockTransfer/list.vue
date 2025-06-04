<template>
    <Head title="Stock Transfers">
      <meta name="description" content="Stock Transfer Management System" />
    </Head>
  
    <AdminPanel :active-tab="'STOCKTRANSFER'" :user="$page.props.auth.user">
      <template #main>
    <div class="py-6">
        <!-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8"> -->
        <div class="max-w-12xl mx-auto sm:px-6 lg:px-8">
            <!-- Error Alert -->
            <div v-if="error" class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                {{ error }}
                <button @click="error = ''" class="absolute top-2 right-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </div>

            <!-- Success Alert -->
            <div v-if="successMessage" class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative">
                {{ successMessage }}
                <button @click="successMessage = ''" class="absolute top-2 right-2">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </div>

            <!-- Header -->
            <div class="mb-6 flex justify-between items-center">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Stock Transfers</h2>
                    <p class="mt-1 text-sm text-gray-600">Current Store: {{ currentStoreName }}</p>
                </div>
                <button
                    @click="showModal = true"
                    :disabled="!props.currentStore"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                >
                    <span class="mr-2">+</span> Create Stock Transfer
                </button>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow mb-6">
                <div class="flex flex-wrap gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-[200px]">
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Search transfers..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <!-- Status Filter -->
                    <div class="w-48">
                        <select
                            v-model="filters.status"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All Status</option>
                            <option value="request">Request</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <!-- Date Range Filter -->
                    <div class="w-48">
                        <input
                            v-model="filters.dateFrom"
                            type="date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                    <div class="w-48">
                        <input
                            v-model="filters.dateTo"
                            type="date"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Datatable -->
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th 
                                    v-for="column in columns" 
                                    :key="column.key"
                                    @click="column.sortable ? sortBy(column.key) : null"
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap"
                                    :class="{ 'cursor-pointer hover:bg-gray-100': column.sortable }"
                                >
                                    <div class="flex items-center">
                                        {{ column.label }}
                                        <template v-if="column.sortable">
                                            <svg 
                                                class="ml-2 h-4 w-4" 
                                                :class="{
                                                    'text-gray-400': sortKey !== column.key,
                                                    'text-indigo-600': sortKey === column.key
                                                }"
                                                fill="none" 
                                                viewBox="0 0 24 24" 
                                                stroke="currentColor"
                                            >
                                                <path 
                                                    v-if="sortKey === column.key && sortOrder === 'desc'" 
                                                    stroke-linecap="round" 
                                                    stroke-linejoin="round" 
                                                    stroke-width="2" 
                                                    d="M19 9l-7 7-7-7"
                                                />
                                                <path 
                                                    v-else
                                                    stroke-linecap="round" 
                                                    stroke-linejoin="round" 
                                                    stroke-width="2" 
                                                    d="M5 15l7-7 7 7"
                                                />
                                            </svg>
                                        </template>
                                    </div>
                                </th>
                                <th class="relative px-6 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="transfer in paginatedData" :key="transfer.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ transfer.transfer_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(transfer.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ transfer.from_store?.NAME }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ transfer.to_store?.NAME }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span 
                                        class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        :class="getStatusClass(transfer.status)"
                                    >
                                        {{ transfer.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ transfer.items_count }} items
                                </td>
                                <!-- <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatCurrency(transfer.total_amount) }}
                                </td> -->
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button 
                                        @click="viewTransfer(transfer)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-3"
                                    >
                                        View
                                    </button>
                                    <template v-if="transfer.status === 'request' && canManageTransfer(transfer)">
                                        <button 
                                            @click="updateStatus(transfer.id, 'approved')"
                                            class="text-green-600 hover:text-green-900 mr-3"
                                        >
                                            Approve
                                        </button>
                                        <button 
                                            @click="updateStatus(transfer.id, 'rejected')"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Reject
                                        </button>
                                    </template>
                                </td>
                            </tr>
                            <!-- Empty State -->
                            <tr v-if="paginatedData.length === 0">
                                <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                    No transfers found matching your criteria
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <button
                                @click="currentPage--"
                                :disabled="currentPage === 1"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Previous
                            </button>
                            <button
                                @click="currentPage++"
                                :disabled="currentPage >= totalPages"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                            >
                                Next
                            </button>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ paginationStart }}</span>
                                    to
                                    <span class="font-medium">{{ paginationEnd }}</span>
                                    of
                                    <span class="font-medium">{{ filteredData.length }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <button
                                        @click="currentPage = 1"
                                        :disabled="currentPage === 1"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <span class="sr-only">First</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M15.707 15.707a1 1 0 01-1.414 0L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0l5 5a1 1 0 010 1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button
                                        v-for="page in displayedPages"
                                        :key="page"
                                        @click="currentPage = page"
                                        :class="[
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                            currentPage === page
                                                ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                                                : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                    <button
                                        @click="currentPage = totalPages"
                                        :disabled="currentPage === totalPages"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <span class="sr-only">Last</span>
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414l-5 5a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Create Modal -->
            <div v-if="showModal" class="fixed inset-0 z-50">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                            <!-- Modal Header -->
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Create New Stock Transfer
                                    </h3>
                                    <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Form Content -->
                                <div class="mt-5">
                                    <!-- Store Selection -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Destination Store
                                        </label>
                                        <select
                                            v-model="form.destinationStore"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        >
                                            <option value="">Select Store</option>
                                            <option v-for="store in stores" :key="store.STOREID" :value="store.STOREID">
                                                {{ store.NAME }}
                                            </option>
                                        </select>
                                        <p v-if="formErrors.destinationStore" class="mt-1 text-sm text-red-600">
                                            {{ formErrors.destinationStore }}
                                        </p>
                                    </div>

                                    <!-- Item Selection -->
                                    <div class="mb-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <label class="block text-sm font-medium text-gray-700">
                                                Select Items and Quantities
                                            </label>
                                            <div class="flex items-center">
                                                <input
                                                    type="text"
                                                    v-model="searchQuery"
                                                    placeholder="Search items..."
                                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                />
                                            </div>
                                        </div>
                                        <div class="mt-2 border rounded-md max-h-60 overflow-y-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50 sticky top-0">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Item Name</th>
                                                        <!-- <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Unit</th> -->
                                                        <!-- <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Price</th> -->
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Quantity</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="item in filteredItems" :key="item.itemid">
                                                        <td class="px-4 py-2 text-sm">{{ item.itemname }}</td>
                                                        <!-- <td class="px-4 py-2 text-sm">{{ item.unitid }}</td> -->
                                                        <!-- <td class="px-4 py-2 text-sm">{{ formatCurrency(item.price) }}</td> -->
                                                        <td class="px-4 py-2">
                                                            <input
                                                                type="number"
                                                                v-model.number="quantities[item.itemid]"
                                                                min="0"
                                                                class="w-20 rounded-md border-gray-300"
                                                                @input="handleQuantityChange(item.itemid)"
                                                            >
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <p v-if="formErrors.items" class="mt-1 text-sm text-red-600">
                                            {{ formErrors.items }}
                                        </p>
                                    </div>

                                    <!-- Notes -->
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Notes (Optional)
                                        </label>
                                        <textarea
                                            v-model="form.notes"
                                            rows="3"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="Add any notes..."
                                        ></textarea>
                                    </div>

                                    <!-- Selected Items Summary -->
                                    <div v-if="selectedItemsCount > 0" class="mb-4">
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">Selected Items Summary</h4>
                                        <div class="bg-gray-50 rounded-md p-3">
                                            <p class="text-sm text-gray-600">
                                                Total Items: {{ selectedItemsCount }}
                                            </p>
                                            <!-- <p class="text-sm text-gray-600">
                                                Total Amount: {{ formatCurrency(totalAmount) }}
                                            </p> -->
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button
                                    type="button"
                                    @click="createTransfer"
                                    :disabled="loading || !isValid"
                                    class="inline-flex w-full justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50"
                                >
                                    {{ loading ? 'Creating...' : 'Create Transfer' }}
                                </button>
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Modal -->
            <div v-if="showViewModal" class="fixed inset-0 z-50">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl">
                            <!-- Modal Header -->
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">
                                        Transfer Details #{{ selectedTransfer?.transfer_number }}
                                    </h3>
                                    <button @click="closeViewModal" class="text-gray-400 hover:text-gray-500">
                                        <span class="sr-only">Close</span>
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Transfer Information -->
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">From Store</p>
                                        <p class="font-medium">{{ selectedTransfer?.from_store?.NAME }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">To Store</p>
                                        <p class="font-medium">{{ selectedTransfer?.to_store?.NAME }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Date Created</p>
                                        <p class="font-medium">{{ formatDate(selectedTransfer?.created_at) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Status</p>
                                        <span 
                                            class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                                            :class="getStatusClass(selectedTransfer?.status)"
                                        >
                                            {{ selectedTransfer?.status }}
                                        </span>
                                    </div>
                                    <div v-if="selectedTransfer?.processed_at">
                                        <p class="text-sm text-gray-500">Processed Date</p>
                                        <p class="font-medium">{{ formatDate(selectedTransfer?.processed_at) }}</p>
                                    </div>
                                    <div v-if="selectedTransfer?.notes">
                                        <p class="text-sm text-gray-500">Notes</p>
                                        <p class="font-medium">{{ selectedTransfer?.notes }}</p>
                                    </div>
                                </div>

                                <!-- Items Table -->
                                <div class="mt-6">
                                    <h4 class="text-md font-medium mb-2">Transfer Items</h4>
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item ID</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="item in selectedTransfer?.items" :key="item.id">
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.itemid }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                        {{ getItemName(item.itemid) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ getItemUnit(item.itemid) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.quantity }}</td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ formatCurrency(item.unit_price) }}
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {{ formatCurrency(item.quantity * item.unit_price) }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                            <tr>
                                            <td colspan="5" class="px-6 py-4 text-sm font-medium text-gray-900 text-right">Total</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ formatCurrency(getTransferTotal(selectedTransfer)) }}
                                            </td>
                                            </tr>
                                        </tfoot>
                                        </table>
                    </div>
                </div>

                <!-- Modal Footer with Action Buttons -->
                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6" v-if="selectedTransfer?.status === 'request' && canManageTransfer(selectedTransfer)">
                    <button
                        type="button"
                        @click="updateStatus(selectedTransfer.id, 'approved')"
                        :disabled="loading"
                        class="inline-flex w-full justify-center rounded-md border border-transparent bg-green-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        {{ loading ? 'Processing...' : 'Approve Transfer' }}
                    </button>
                    <button
                        type="button"
                        @click="updateStatus(selectedTransfer.id, 'rejected')"
                        :disabled="loading"
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-red-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Reject Transfer
                    </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
</AdminPanel>
</template>

<script setup>
import { ref, computed, reactive, onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3'
import AdminPanel from '@/Layouts/Main.vue'
import { format } from 'date-fns'
import axios from 'axios'

const props = defineProps({
  transfers: {
    type: Array,
    required: true,
    default: () => []
  },
  stores: {
    type: Array,
    required: true,
    default: () => []
  },
  items: {
    type: Array,
    required: true,
    default: () => []
  },
  currentStore: {
    type: String,
    required: true
  }
})

// Column definitions
const columns = [
    { key: 'transfer_number', label: 'Transfer #', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true },
    { key: 'from_store', label: 'From Store', sortable: true },
    { key: 'to_store', label: 'To Store', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'items_count', label: 'Items', sortable: true },
    /* { key: 'total_amount', label: 'Total Amount', sortable: true } */
]

// State
const showModal = ref(false)
const showViewModal = ref(false)
const loading = ref(false)
const error = ref('')
const successMessage = ref('')
const searchQuery = ref('')
const quantities = ref({})
const currentPage = ref(1)
const itemsPerPage = 10
const sortKey = ref('created_at')
const sortOrder = ref('desc')
const selectedTransfer = ref(null)
const formErrors = ref({})

// Form state
const form = reactive({
    destinationStore: '',
    notes: ''
})

// Filters state
const filters = ref({
    search: '',
    status: '',
    dateFrom: '',
    dateTo: ''
})

// Computed Properties
const currentStoreName = computed(() => {
    const store = props.stores.find(s => s.STOREID === props.currentStore)
    return store ? store.NAME : props.currentStore || 'No Store Selected'
})

const filteredData = computed(() => {
    let data = [...props.transfers]

    if (filters.value.search) {
        const searchTerm = filters.value.search.toLowerCase()
        data = data.filter(item => 
            item.transfer_number?.toLowerCase().includes(searchTerm) ||
            item.from_store?.NAME?.toLowerCase().includes(searchTerm) ||
            item.to_store?.NAME?.toLowerCase().includes(searchTerm)
        )
    }

    if (filters.value.status) {
        data = data.filter(item => item.status === filters.value.status)
    }

    if (filters.value.dateFrom) {
        const fromDate = new Date(filters.value.dateFrom)
        data = data.filter(item => new Date(item.created_at) >= fromDate)
    }

    if (filters.value.dateTo) {
        const toDate = new Date(filters.value.dateTo)
        toDate.setHours(23, 59, 59)
        data = data.filter(item => new Date(item.created_at) <= toDate)
    }

    // Apply sorting
    data.sort((a, b) => {
        let aValue = a[sortKey.value]
        let bValue = b[sortKey.value]
        
        if (sortKey.value === 'from_store' || sortKey.value === 'to_store') {
            aValue = a[sortKey.value]?.NAME
            bValue = b[sortKey.value]?.NAME
        } else if (sortKey.value === 'total_amount') {
            aValue = getTransferTotal(a)
            bValue = getTransferTotal(b)
        }
        
        if (sortOrder.value === 'desc') {
            [aValue, bValue] = [bValue, aValue]
        }
        
        return aValue < bValue ? -1 : aValue > bValue ? 1 : 0
    })

    return data
})

const paginatedData = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage
    return filteredData.value.slice(start, start + itemsPerPage)
})

const totalPages = computed(() => 
    Math.ceil(filteredData.value.length / itemsPerPage)
)

const displayedPages = computed(() => {
    const total = totalPages.value
    const current = currentPage.value
    const pages = []
    
    if (total <= 7) {
        for (let i = 1; i <= total; i++) {
            pages.push(i)
        }
    } else {
        if (current <= 3) {
            for (let i = 1; i <= 5; i++) pages.push(i)
            pages.push('...')
            pages.push(total)
        } else if (current >= total - 2) {
            pages.push(1)
            pages.push('...')
            for (let i = total - 4; i <= total; i++) pages.push(i)
        } else {
            pages.push(1)
            pages.push('...')
            for (let i = current - 1; i <= current + 1; i++) pages.push(i)
            pages.push('...')
            pages.push(total)
        }
    }
    
    return pages
})

const paginationStart = computed(() => 
    ((currentPage.value - 1) * itemsPerPage) + 1
)

const paginationEnd = computed(() => 
    Math.min(currentPage.value * itemsPerPage, filteredData.value.length)
)

const filteredItems = computed(() => {
    if (!searchQuery.value) return props.items
    
    const query = searchQuery.value.toLowerCase()
    return props.items.filter(item => 
        item.itemname.toLowerCase().includes(query) ||
        item.itemid.toString().toLowerCase().includes(query)
    )
})

const selectedItemsCount = computed(() => 
    Object.values(quantities.value).filter(qty => qty > 0).length
)

const totalAmount = computed(() => {
    return Object.entries(quantities.value).reduce((sum, [itemId, quantity]) => {
        if (quantity > 0) {
            const item = props.items.find(i => i.itemid === itemId)
            // Price is now priceincltax from the backend
            return sum + (quantity * (item?.price || 0))
        }
        return sum
    }, 0)
})

const isValid = computed(() => {
    return form.destinationStore && selectedItemsCount.value > 0
})

// Methods
const handleQuantityChange = (itemId) => {
    if (quantities.value[itemId] < 0) {
        quantities.value[itemId] = 0
    }
}

const createTransfer = async () => {
    if (!isValid.value) return
    
    try {
        loading.value = true
        formErrors.value = {}

        const transferItems = Object.entries(quantities.value)
            .filter(([_, quantity]) => quantity > 0)
            .map(([itemid, quantity]) => ({
                itemid,
                quantity: parseInt(quantity)
            }))

        const response = await axios.post('/stock-transfer', {
            to_store_id: form.destinationStore,
            items: transferItems,
            notes: form.notes
        })

        successMessage.value = 'Stock transfer created successfully'
        closeModal()
        window.location.reload()
    } catch (err) {
        console.error('Transfer creation error:', err)
        if (err.response?.data?.errors) {
            formErrors.value = err.response.data.errors
        } else {
            error.value = err.response?.data?.message || 'Failed to create transfer'
        }
    } finally {
        loading.value = false
    }
}

const updateStatus = async (transferId, status) => {
    if (!transferId) return
    
    try {
        loading.value = true
        await axios.patch(`/stock-transfer/${transferId}/status`, { status })
        
        successMessage.value = `Transfer ${status} successfully`
        if (showViewModal.value) {
            closeViewModal()
        }
        
        window.location.reload()
    } catch (err) {
        console.error('Status update error:', err)
        error.value = err.response?.data?.message || `Failed to ${status} transfer`
    } finally {
        loading.value = false
    }
}

const resetForm = () => {
    form.destinationStore = ''
    form.notes = ''
    quantities.value = {}
    formErrors.value = {}
    searchQuery.value = ''
}

const closeModal = () => {
    showModal.value = false
    resetForm()
}

const closeViewModal = () => {
    showViewModal.value = false
    selectedTransfer.value = null
}

const viewTransfer = (transfer) => {
    selectedTransfer.value = transfer
    showViewModal.value = true
}

const sortBy = (key) => {
    if (sortKey.value === key) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortKey.value = key
        sortOrder.value = 'asc'
    }
}

const getStatusClass = (status) => {
    const classes = {
        request: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-green-100 text-green-800',
        rejected: 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
    if (!date) return ''
    return format(new Date(date), 'MMM dd, yyyy HH:mm')
}

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value || 0)
}

const getItemName = (itemId) => {
    return props.items.find(item => item.itemid === itemId)?.itemname || ''
}

const getItemUnit = (itemId) => {
    return props.items.find(item => item.itemid === itemId)?.unitid || ''
}

const getTransferTotal = (transfer) => {
    if (!transfer?.items) return 0
    return transfer.items.reduce((sum, item) => 
        sum + (item.quantity * item.unit_price), 0
    )
}

const canManageTransfer = (transfer) => {
    return transfer.to_store_id === props.currentStore
}

// Watchers
watch(showModal, (newValue) => {
    if (!newValue) {
        resetForm()
    }
})

watch([filters, sortKey, sortOrder], () => {
    currentPage.value = 1
})

// Lifecycle hooks
onMounted(() => {
    if (!props.currentStore) {
        error.value = 'Store information not found. Please refresh the page.'
    }
})
</script>