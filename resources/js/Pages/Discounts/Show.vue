<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";

const props = defineProps({
    discount: {
        type: Object,
        required: true
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

// Computed properties
const user = computed(() => props.auth?.user || {});
const userRole = computed(() => user.value?.role || '');
const layoutComponent = computed(() => {
    return userRole.value === 'STORE' ? StorePanel : Main;
});

const isAdmin = computed(() => ['SUPERADMIN', 'ADMIN', 'OPIC'].includes(userRole.value));

// Reactive state
const previewAmount = ref(100);
const showDeleteModal = ref(false);

// Computed properties
const discountTypeLabel = computed(() => {
    switch (props.discount.DISCOUNTTYPE) {
        case 'FIXED':
            return 'Fixed Amount';
        case 'FIXEDTOTAL':
            return 'Fixed Total';
        case 'PERCENTAGE':
            return 'Percentage';
        default:
            return props.discount.DISCOUNTTYPE;
    }
});

const discountTypeDescription = computed(() => {
    switch (props.discount.DISCOUNTTYPE) {
        case 'FIXED':
            return 'Fixed amount off per item (cannot exceed item price)';
        case 'FIXEDTOTAL':
            return 'Fixed amount off the total bill';
        case 'PERCENTAGE':
            return 'Percentage off the total amount';
        default:
            return 'Unknown discount type';
    }
});

const discountTypeClass = computed(() => {
    switch (props.discount.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return 'bg-blue-100 text-blue-800';
        case 'FIXED':
            return 'bg-green-100 text-green-800';
        case 'FIXEDTOTAL':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const formatDiscountValue = computed(() => {
    switch (props.discount.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return `${props.discount.PARAMETER}%`;
        case 'FIXED':
        case 'FIXEDTOTAL':
            return `₱${Number(props.discount.PARAMETER).toFixed(2)}`;
        default:
            return props.discount.PARAMETER;
    }
});

const discountPreview = computed(() => {
    if (!previewAmount.value) return null;

    const originalAmount = previewAmount.value;
    const parameter = parseFloat(props.discount.PARAMETER);
    let discountAmount = 0;
    let finalAmount = originalAmount;

    switch (props.discount.DISCOUNTTYPE) {
        case 'FIXED':
            discountAmount = Math.min(parameter, originalAmount);
            finalAmount = originalAmount - discountAmount;
            break;
        case 'FIXEDTOTAL':
            discountAmount = parameter;
            finalAmount = Math.max(0, originalAmount - discountAmount);
            break;
        case 'PERCENTAGE':
            discountAmount = (originalAmount * parameter) / 100;
            finalAmount = originalAmount - discountAmount;
            break;
    }

    return {
        originalAmount,
        discountAmount: discountAmount.toFixed(2),
        finalAmount: finalAmount.toFixed(2),
        savingsPercentage: ((discountAmount / originalAmount) * 100).toFixed(1)
    };
});

// Methods
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
};

const confirmDelete = () => {
    showDeleteModal.value = true;
};

const deleteDiscount = () => {
    router.delete(route('discountsv2.destroy', props.discount.id), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
        },
        onError: () => {
            showDeleteModal.value = false;
        }
    });
};

const cancelDelete = () => {
    showDeleteModal.value = false;
};

// Calculate example scenarios
const calculateExample = (amount) => {
    const parameter = parseFloat(props.discount.PARAMETER);
    let discountAmount = 0;
    
    switch (props.discount.DISCOUNTTYPE) {
        case 'FIXED':
            discountAmount = Math.min(parameter, amount);
            break;
        case 'FIXEDTOTAL':
            discountAmount = parameter;
            break;
        case 'PERCENTAGE':
            discountAmount = (amount * parameter) / 100;
            break;
    }
    
    return {
        original: amount,
        discount: discountAmount,
        final: Math.max(0, amount - discountAmount)
    };
};
</script>

<template>
    <Head :title="discount.DISCOFFERNAME" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <TableContainer>
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ discount.DISCOFFERNAME }}</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Discount details and preview calculator
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link
                                v-if="isAdmin"
                                :href="route('discountsv2.edit', discount.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Discount
                            </Link>
                            <Link
                                :href="route('discountsv2.index')"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                Back to Discounts
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div v-if="flash.message" 
                     :class="[
                         'mb-6 px-4 py-2 rounded-md',
                         flash.isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                     ]">
                    {{ flash.message }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Discount Details Section -->
                    <div class="space-y-6">
                        <!-- Basic Information Card -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-lg font-semibold text-gray-900">Discount Information</h2>
                                <span :class="[
                                    'px-3 py-1 text-sm font-medium rounded-full',
                                    discountTypeClass
                                ]">
                                    {{ discountTypeLabel }}
                                </span>
                            </div>
                            
                            <div class="space-y-6">
                                <!-- Discount Name -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Discount Name</label>
                                    <div class="p-3 bg-gray-50 rounded-md border">
                                        <p class="text-lg font-semibold text-gray-900">{{ discount.DISCOFFERNAME }}</p>
                                    </div>
                                </div>

                                <!-- Discount Type & Description -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type</label>
                                    <div class="p-3 bg-gray-50 rounded-md border">
                                        <p class="font-medium text-gray-900 mb-1">{{ discountTypeLabel }}</p>
                                        <p class="text-sm text-gray-600">{{ discountTypeDescription }}</p>
                                    </div>
                                </div>

                                <!-- Discount Value -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value</label>
                                    <div class="p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-md border border-green-200">
                                        <p class="text-3xl font-bold text-green-600">{{ formatDiscountValue }}</p>
                                        <p class="text-sm text-gray-600 mt-1">
                                            <span v-if="discount.DISCOUNTTYPE === 'FIXED'">Per item discount (max per item)</span>
                                            <span v-else-if="discount.DISCOUNTTYPE === 'FIXEDTOTAL'">Total bill discount</span>
                                            <span v-else-if="discount.DISCOUNTTYPE === 'PERCENTAGE'">Percentage off total</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Metadata -->
                                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Created</label>
                                        <p class="text-sm text-gray-900">{{ new Date(discount.created_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Last Updated</label>
                                        <p class="text-sm text-gray-900">{{ new Date(discount.updated_at).toLocaleDateString('en-US', { 
                                            year: 'numeric', 
                                            month: 'short', 
                                            day: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        }) }}</p>
                                    </div>
                                </div>

                                <!-- Discount ID -->
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Discount ID</label>
                                    <p class="text-sm font-mono text-gray-600 bg-gray-100 px-2 py-1 rounded">#{{ discount.id }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Card -->
                        <div v-if="isAdmin" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Management Actions</h2>
                            
                            <div class="space-y-3">
                                <Link
                                    :href="route('discountsv2.edit', discount.id)"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Edit Discount Details
                                </Link>
                                
                                <button
                                    @click="confirmDelete"
                                    class="w-full inline-flex items-center justify-center px-4 py-3 border border-red-300 text-sm font-medium rounded-md text-red-700 bg-red-50 hover:bg-red-100 transition-colors"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Delete Discount
                                </button>
                            </div>

                            <!-- Warning Notice -->
                            <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                <div class="flex">
                                    <svg class="h-4 w-4 text-yellow-400 mt-0.5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    <div>
                                        <p class="text-xs text-yellow-800">
                                            <strong>Note:</strong> Deleting this discount may affect historical transaction records and ongoing promotions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Calculator & Examples Section -->
                    <div class="space-y-6">
                        <!-- Interactive Discount Calculator -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Discount Calculator</h2>
                            
                            <div class="space-y-4">
                                <!-- Amount Input -->
                                <div>
                                    <label for="previewAmount" class="block text-sm font-medium text-gray-700 mb-2">
                                        Enter Amount to Calculate Discount
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-lg">₱</span>
                                        </div>
                                        <input
                                            id="previewAmount"
                                            v-model.number="previewAmount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full pl-8 pr-3 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium"
                                            placeholder="0.00"
                                        >
                                    </div>
                                </div>

                                <!-- Calculation Results -->
                                <div v-if="discountPreview" class="space-y-3 p-5 bg-gradient-to-br from-blue-50 via-green-50 to-purple-50 rounded-lg border-2 border-blue-200">
                                    <!-- Original Amount -->
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                        <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                    </div>
                                    
                                    <!-- Discount Amount -->
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                        <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                    </div>
                                    
                                    <hr class="border-gray-300">
                                    
                                    <!-- Final Amount -->
                                    <div class="flex justify-between items-center py-2">
                                        <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                        <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                    </div>
                                    
                                    <!-- Savings Summary -->
                                    <div class="text-center pt-3 border-t border-gray-200">
                                        <div class="inline-flex items-center space-x-2">
                                            <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                                {{ discountPreview.savingsPercentage }}% Total Savings
                                            </span>
                                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">
                                                Saves {{ formatCurrency(discountPreview.discountAmount) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Usage Examples -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Example Scenarios</h2>
                            
                            <div class="space-y-4">
                                <!-- Example 1: Small Purchase -->
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                                        <span class="inline-block w-6 h-6 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">1</span>
                                        Small Purchase: ₱100.00
                                    </h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Original Amount:</span>
                                            <span class="font-medium">₱{{ calculateExample(100).original.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Discount Applied:</span>
                                            <span class="font-medium text-red-600">-₱{{ calculateExample(100).discount.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                            <span>Customer Pays:</span>
                                            <span>₱{{ calculateExample(100).final.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Example 2: Medium Purchase -->
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                                        <span class="inline-block w-6 h-6 bg-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">2</span>
                                        Medium Purchase: ₱500.00
                                    </h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Original Amount:</span>
                                            <span class="font-medium">₱{{ calculateExample(500).original.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Discount Applied:</span>
                                            <span class="font-medium text-red-600">-₱{{ calculateExample(500).discount.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                            <span>Customer Pays:</span>
                                            <span>₱{{ calculateExample(500).final.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Example 3: Large Purchase -->
                                <div class="p-4 bg-gray-50 rounded-lg border">
                                    <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                                        <span class="inline-block w-6 h-6 bg-purple-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">3</span>
                                        Large Purchase: ₱1,000.00
                                    </h3>
                                    <div class="text-sm text-gray-600 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Original Amount:</span>
                                            <span class="font-medium">₱{{ calculateExample(1000).original.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Discount Applied:</span>
                                            <span class="font-medium text-red-600">-₱{{ calculateExample(1000).discount.toFixed(2) }}</span>
                                        </div>
                                        <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                            <span>Customer Pays:</span>
                                            <span>₱{{ calculateExample(1000).final.toFixed(2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discount Rules & Information -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Discount Rules & Guidelines</h2>
                            
                            <div class="space-y-4">
                                <!-- Type-specific rules -->
                                <div v-if="discount.DISCOUNTTYPE === 'FIXED'" class="p-4 bg-green-50 border border-green-200 rounded-md">
                                    <h3 class="font-medium text-green-900 mb-2 flex items-center">
                                        <svg class="w-4 h-4 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Fixed Amount Discount Rules
                                    </h3>
                                    <ul class="text-sm text-green-800 space-y-1">
                                        <li>• Discount applies per item in the transaction</li>
                                        <li>• Cannot exceed the individual item's price</li>
                                        <li>• If item costs less than discount amount, discount = item price</li>
                                        <li>• Example: ₱50 discount on ₱30 item = ₱30 discount applied</li>
                                    </ul>
                                </div>
                                
                                <div v-if="discount.DISCOUNTTYPE === 'FIXEDTOTAL'" class="p-4 bg-purple-50 border border-purple-200 rounded-md">
                                    <h3 class="font-medium text-purple-900 mb-2 flex items-center">
                                        <svg class="w-4 h-4 text-purple-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Fixed Total Discount Rules
                                    </h3>
                                    <ul class="text-sm text-purple-800 space-y-1">
                                        <li>• Fixed amount deducted from total bill</li>
                                        <li>• Minimum final amount is ₱0.00</li>
                                        <li>• Applied once per transaction regardless of items</li>
                                        <li>• Example: ₱100 off means customer saves exactly ₱100</li>
                                    </ul>
                                </div>
                                
                                <div v-if="discount.DISCOUNTTYPE === 'PERCENTAGE'" class="p-4 bg-blue-50 border border-blue-200 rounded-md">
                                    <h3 class="font-medium text-blue-900 mb-2 flex items-center">
                                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Percentage Discount Rules
                                    </h3>
                                    <ul class="text-sm text-blue-800 space-y-1">
                                        <li>• Percentage discount applied to total amount</li>
                                        <li>• Higher bills result in higher savings</li>
                                        <li>• Applied once per transaction</li>
                                        <li>• Example: {{ discount.PARAMETER }}% off ₱1000 = ₱{{ ((1000 * discount.PARAMETER) / 100).toFixed(2) }} savings</li>
                                    </ul>
                                </div>

                                <!-- General guidelines -->
                                <div class="space-y-2">
                                    <h3 class="font-medium text-gray-900 mb-2">General Guidelines</h3>
                                    <div class="grid grid-cols-1 gap-2 text-sm text-gray-600">
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Valid for all eligible transactions unless specified otherwise</span>
                                        </div>
                                        
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Can be combined with other promotions depending on store policy</span>
                                        </div>
                                        
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Applicable for in-store and online transactions</span>
                                        </div>
                                        
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-blue-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                            </svg>
                                            <span>No minimum purchase required unless specified</span>
                                        </div>
                                        
                                        <div class="flex items-start space-x-2">
                                            <svg class="w-4 h-4 text-orange-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <span>Subject to terms and conditions of the store</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- POS Integration Info -->
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">POS System Integration</h2>
                            
                            <div class="space-y-3">
                                <div class="p-4 bg-gray-50 rounded-md border">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <h3 class="font-medium text-gray-900">Automatic Application</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">This discount is automatically available in all POS terminals and can be applied during checkout.</p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-md border">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V8z" clip-rule="evenodd" />
                                        </svg>
                                        <h3 class="font-medium text-gray-900">Real-time Calculation</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">Discount amounts are calculated in real-time based on current transaction total and applied instantly.</p>
                                </div>

                                <div class="p-4 bg-gray-50 rounded-md border">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <svg class="w-5 h-5 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <h3 class="font-medium text-gray-900">Receipt Integration</h3>
                                    </div>
                                    <p class="text-sm text-gray-600">Discount details appear on customer receipts with discount name and amount clearly displayed.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </TableContainer>
        </template>
    </component>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="showDeleteModal = false">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-md shadow-lg rounded-md bg-white" @click.stop>
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                
                <h3 class="text-lg font-medium text-gray-900 mb-2">Delete Discount</h3>
                
                <div class="mb-4">
                    <p class="text-sm text-gray-500 mb-2">
                        Are you sure you want to delete this discount?
                    </p>
                    
                    <div class="p-3 bg-gray-50 rounded-md border text-left">
                        <p class="font-medium text-gray-900">{{ discount.DISCOFFERNAME }}</p>
                        <p class="text-sm text-gray-600">{{ discountTypeLabel }} - {{ formatDiscountValue }}</p>
                    </div>
                    
                    <p class="text-xs text-red-600 mt-3">
                        <strong>Warning:</strong> This action cannot be undone and may affect existing transactions and POS systems.
                    </p>
                </div>
                
                <div class="flex justify-center space-x-3">
                    <button
                        @click="cancelDelete"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteDiscount"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-red-500"
                    >
                        Delete Discount
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>