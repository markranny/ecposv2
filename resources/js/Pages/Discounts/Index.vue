<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import Excel from "@/Components/Exports/Excel.vue";

const props = defineProps({
    discounts: {
        type: Array,
        required: true,
        default: () => []
    },
    auth: {
        type: Object,
        required: true,
        default: () => ({})
    },
    flash: {
        type: Object,
        default: () => ({})
    }
});

// Reactive state
const searchQuery = ref('');
const selectedDiscountType = ref('');
const showConfirmDelete = ref(false);
const discountToDelete = ref(null);
const showFloatingMenu = ref(false);

// Computed properties
const user = computed(() => props.auth?.user || {});
const userRole = computed(() => user.value?.role || '');
const layoutComponent = computed(() => {
    return userRole.value === 'STORE' ? StorePanel : Main;
});

const isAdmin = computed(() => ['SUPERADMIN', 'ADMIN', 'OPIC'].includes(userRole.value));

const discountTypes = computed(() => {
    if (!props.discounts || !Array.isArray(props.discounts)) return [];
    const types = new Set(props.discounts.map(discount => discount?.DISCOUNTTYPE).filter(Boolean));
    return Array.from(types).sort();
});

const filteredDiscounts = computed(() => {
    if (!props.discounts || !Array.isArray(props.discounts)) return [];
    
    let filtered = [...props.discounts];

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(discount => 
            discount?.DISCOFFERNAME?.toLowerCase().includes(query) ||
            discount?.DISCOUNTTYPE?.toLowerCase().includes(query)
        );
    }

    // Type filter
    if (selectedDiscountType.value) {
        filtered = filtered.filter(discount => discount?.DISCOUNTTYPE === selectedDiscountType.value);
    }

    return filtered;
});

// Methods
const formatDiscountValue = (discount) => {
    if (!discount) return '';
    
    switch (discount.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return `${discount.PARAMETER}%`;
        case 'FIXED':
        case 'FIXEDTOTAL':
            return `₱${Number(discount.PARAMETER).toFixed(2)}`;
        default:
            return discount.PARAMETER;
    }
};

const getDiscountTypeClass = (type) => {
    switch (type) {
        case 'PERCENTAGE':
            return 'bg-blue-100 text-blue-800';
        case 'FIXED':
            return 'bg-green-100 text-green-800';
        case 'FIXEDTOTAL':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getDiscountDescription = (discount) => {
    switch (discount.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return `${discount.PARAMETER}% off the total amount`;
        case 'FIXED':
            return `₱${Number(discount.PARAMETER).toFixed(2)} off per item (max discount per item)`;
        case 'FIXEDTOTAL':
            return `₱${Number(discount.PARAMETER).toFixed(2)} off the total amount`;
        default:
            return 'Unknown discount type';
    }
};

const confirmDelete = (discount) => {
    discountToDelete.value = discount;
    showConfirmDelete.value = true;
};

// FIXED: Delete function with proper route and method
const deleteDiscount = () => {
    if (!discountToDelete.value) return;
    
    // Use Inertia's delete method with proper route
    router.delete(route('discountsv2.destroy', { discount: discountToDelete.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            showConfirmDelete.value = false;
            discountToDelete.value = null;
        },
        onError: (errors) => {
            console.error('Delete failed:', errors);
            showConfirmDelete.value = false;
            discountToDelete.value = null;
        }
    });
};

const cancelDelete = () => {
    showConfirmDelete.value = false;
    discountToDelete.value = null;
};

const getExportData = () => {
    if (!props.discounts || !Array.isArray(props.discounts)) return [];
    
    return props.discounts.map(discount => ({
        'DISCOUNT NAME': discount?.DISCOFFERNAME || '',
        'TYPE': discount?.DISCOUNTTYPE || '',
        'VALUE': formatDiscountValue(discount),
        'DESCRIPTION': getDiscountDescription(discount),
        'CREATED': discount?.created_at ? new Date(discount.created_at).toLocaleDateString() : ''
    }));
};

const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};
</script>

<template>
    <Head title="Discount Management" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <div class="min-h-screen bg-gray-50 p-2 sm:p-4 lg:p-6">
                <!-- Flash Messages -->
                <div v-if="flash.message" 
                     :class="[
                         'mb-4 px-4 py-2 rounded-md',
                         flash.isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                     ]">
                    {{ flash.message }}
                </div>

                <!-- Mobile Header -->
                <div class="mb-4 lg:hidden">
                    <h1 class="text-xl font-bold text-gray-900 mb-2">Discounts</h1>
                    <p class="text-sm text-gray-600">Manage store discounts and promotional offers</p>
                </div>

                <!-- Desktop Header Section -->
                <div class="hidden lg:block p-6 bg-white rounded-lg shadow-sm mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Discount Management</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Manage store discounts and promotional offers
                            </p>
                        </div>

                        <!-- Desktop Action Buttons -->
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-if="isAdmin"
                                :href="route('discountsv2.create')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Add New Discount
                            </Link>

                            <!-- Export Button -->
                            <Excel
                                v-if="isAdmin"
                                :data="getExportData()"
                                :headers="['DISCOUNT NAME', 'TYPE', 'VALUE', 'DESCRIPTION', 'CREATED']"
                                :row-name-props="['DISCOUNT NAME', 'TYPE', 'VALUE', 'DESCRIPTION', 'CREATED']"
                                class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md transition-colors"
                                filename="discounts"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel
                            </Excel>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow-sm mb-4 lg:mb-6">
                    <div class="p-3 lg:p-4 border-b border-gray-200">
                        <div class="flex flex-col space-y-3 sm:flex-row sm:space-y-0 sm:space-x-4">
                            <!-- Search -->
                            <div class="flex-1">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Search discounts by name or type..."
                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                            </div>
                            
                            <!-- Type Filter -->
                            <div>
                                <select
                                    v-model="selectedDiscountType"
                                    class="px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="">All Types</option>
                                    <option v-for="type in discountTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Discounts Display -->
                    <div class="overflow-hidden">
                        <!-- Mobile Card View -->
                        <div class="lg:hidden">
                            <div v-if="filteredDiscounts.length === 0" class="p-8 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-lg font-medium">No discounts found</p>
                                <p class="text-sm text-gray-400 mt-1">
                                    {{ searchQuery || selectedDiscountType ? 'Try adjusting your search filters' : 'Get started by creating your first discount' }}
                                </p>
                            </div>

                            <div v-for="discount in filteredDiscounts" :key="discount.id" 
                                 class="border-b border-gray-200 p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-gray-900 mb-1">
                                            {{ discount.DISCOFFERNAME }}
                                        </h3>
                                        <div class="flex items-center space-x-2 mb-2">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                getDiscountTypeClass(discount.DISCOUNTTYPE)
                                            ]">
                                                {{ discount.DISCOUNTTYPE }}
                                            </span>
                                            <span class="text-sm font-medium text-green-600">
                                                {{ formatDiscountValue(discount) }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600">
                                            {{ getDiscountDescription(discount) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div v-if="isAdmin" class="flex justify-end space-x-3 mt-3 pt-3 border-t border-gray-100">
                                    <!-- FIXED: Proper route parameters -->
                                    <Link
                                        :href="route('discountsv2.show', { discount: discount.id })"
                                        class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="route('discountsv2.edit', { discount: discount.id })"
                                        class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(discount)"
                                        class="text-red-600 hover:text-red-900 text-sm font-medium"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Table View -->
                        <div class="hidden lg:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Discount Name
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Value
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Created
                                        </th>
                                        <th v-if="isAdmin" scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="filteredDiscounts.length === 0">
                                        <td :colspan="isAdmin ? 6 : 5" class="px-6 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center">
                                                <svg class="h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                </svg>
                                                <p class="text-lg font-medium">No discounts found</p>
                                                <p class="text-sm text-gray-400 mt-1">
                                                    {{ searchQuery || selectedDiscountType ? 'Try adjusting your search filters' : 'Get started by creating your first discount' }}
                                                </p>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr v-for="discount in filteredDiscounts" 
                                        :key="discount.id" 
                                        class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ discount.DISCOFFERNAME }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                getDiscountTypeClass(discount.DISCOUNTTYPE)
                                            ]">
                                                {{ discount.DISCOUNTTYPE }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ formatDiscountValue(discount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-600 max-w-xs">
                                                {{ getDiscountDescription(discount) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ discount.created_at ? new Date(discount.created_at).toLocaleDateString() : '' }}
                                        </td>
                                        <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <!-- FIXED: Proper route parameters for all actions -->
                                                <Link
                                                    :href="route('discountsv2.show', { discount: discount.id })"
                                                    class="text-blue-600 hover:text-blue-900 transition-colors"
                                                >
                                                    View
                                                </Link>
                                                <Link
                                                    :href="route('discountsv2.edit', { discount: discount.id })"
                                                    class="text-indigo-600 hover:text-indigo-900 transition-colors"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    @click="confirmDelete(discount)"
                                                    class="text-red-600 hover:text-red-900 transition-colors"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Summary Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                    <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-xl lg:text-2xl font-bold text-gray-900">{{ props.discounts?.length || 0 }}</div>
                                <div class="text-sm text-gray-600">Total Discounts</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-xl lg:text-2xl font-bold text-gray-900">
                                    {{ props.discounts?.filter(d => d.DISCOUNTTYPE === 'FIXED' || d.DISCOUNTTYPE === 'FIXEDTOTAL').length || 0 }}
                                </div>
                                <div class="text-sm text-gray-600">Fixed Amount</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6 sm:col-span-2 lg:col-span-1">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-xl lg:text-2xl font-bold text-gray-900">
                                    {{ props.discounts?.filter(d => d.DISCOUNTTYPE === 'PERCENTAGE').length || 0 }}
                                </div>
                                <div class="text-sm text-gray-600">Percentage Based</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Floating Action Button and Menu -->
                <div class="lg:hidden fixed bottom-6 right-6 z-40">
                    <!-- Floating Menu Options -->
                    <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                        <!-- Add Discount -->
                        <Link
                            v-if="isAdmin"
                            :href="route('discountsv2.create')"
                            @click="closeFloatingMenu"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center"
                        >
                            <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add New Discount
                        </Link>

                        <!-- Export -->
                        <div v-if="isAdmin" class="px-4 py-3">
                            <Excel
                                :data="getExportData()"
                                :headers="['DISCOUNT NAME', 'TYPE', 'VALUE', 'DESCRIPTION', 'CREATED']"
                                :row-name-props="['DISCOUNT NAME', 'TYPE', 'VALUE', 'DESCRIPTION', 'CREATED']"
                                class="w-full text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center p-0 bg-transparent border-0 hover:text-gray-900"
                                filename="discounts"
                                @click="closeFloatingMenu"
                            >
                                <svg class="h-4 w-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Export Excel
                            </Excel>
                        </div>

                        <!-- Search & Filters for Mobile -->
                        <div class="border-t border-gray-200 my-2"></div>
                        
                        <div class="px-4 py-2">
                            <p class="text-xs text-gray-500 mb-2">Quick Actions</p>
                            <button
                                @click="searchQuery = ''; selectedDiscountType = ''; closeFloatingMenu();"
                                class="w-full text-left text-sm text-gray-700 hover:bg-gray-100 py-2 px-2 rounded"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>

                    <!-- Main Floating Action Button -->
                    <button
                        @click="toggleFloatingMenu"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105"
                        :class="{ 'rotate-45': showFloatingMenu }"
                    >
                        <svg v-if="!showFloatingMenu" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Overlay to close floating menu -->
                <div v-if="showFloatingMenu" @click="closeFloatingMenu" class="lg:hidden fixed inset-0 bg-black bg-opacity-25 z-30"></div>
            </div>
        </template>
    </component>

    <!-- Delete Confirmation Modal -->
    <div v-if="showConfirmDelete" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Discount</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to delete the discount "{{ discountToDelete?.DISCOFFERNAME }}"?
                    This action cannot be undone.
                </p>
                <div class="flex justify-center space-x-3">
                    <button
                        @click="cancelDelete"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteDiscount"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>