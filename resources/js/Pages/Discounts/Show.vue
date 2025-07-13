<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";

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
const showFloatingMenu = ref(false);
const showCalculatorPanel = ref(false);
const showDeleteModal = ref(false);
const showExamplesPanel = ref(false);

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
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'FIXED':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'FIXEDTOTAL':
            return 'bg-purple-100 text-purple-800 border-purple-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
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
    closeFloatingMenu();
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

const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};

const toggleCalculatorPanel = () => {
    showCalculatorPanel.value = !showCalculatorPanel.value;
    closeFloatingMenu();
};

const toggleExamplesPanel = () => {
    showExamplesPanel.value = !showExamplesPanel.value;
    closeFloatingMenu();
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

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head :title="discount.DISCOFFERNAME" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <div class="min-h-screen bg-gray-50">
                <!-- Mobile Header (Only visible on mobile) -->
                <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-gray-200 px-4 py-3 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <Link
                                :href="route('discountsv2.index')"
                                class="text-gray-600 hover:text-gray-900 transition-colors"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                            </Link>
                            <div class="min-w-0 flex-1">
                                <h1 class="text-lg font-semibold text-gray-900 truncate">{{ discount.DISCOFFERNAME }}</h1>
                                <p class="text-xs text-gray-600">Discount Details</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Edit Button -->
                            <Link
                                v-if="isAdmin"
                                :href="route('discountsv2.edit', discount.id)"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Desktop Header (Only visible on desktop) -->
                <div class="hidden lg:block p-6 bg-white rounded-lg shadow-sm mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h1 class="text-3xl font-bold text-gray-900">{{ discount.DISCOFFERNAME }}</h1>
                                    <span :class="[
                                        'px-3 py-1 text-sm font-medium rounded-full border',
                                        discountTypeClass
                                    ]">
                                        {{ discountTypeLabel }}
                                    </span>
                                </div>
                                <p class="text-gray-600 mb-2">{{ discountTypeDescription }}</p>
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a1.994 1.994 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        ID: #{{ discount.id }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                                        </svg>
                                        Value: {{ formatDiscountValue }}
                                    </span>
                                    <span class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-2m0 0V7m0 4H7m0 0h4m0 0h4" />
                                        </svg>
                                        Created: {{ formatDate(discount.created_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <Link
                                v-if="isAdmin"
                                :href="route('discountsv2.edit', discount.id)"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Edit Discount
                            </Link>
                            <Link
                                :href="route('discountsv2.index')"
                                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors"
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
                         'mb-4 lg:mb-6 px-4 py-3 rounded-lg mx-4 lg:mx-0',
                         flash.isSuccess ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-700 border border-red-200'
                     ]">
                    <div class="flex items-center">
                        <svg v-if="flash.isSuccess" class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <svg v-else class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-medium">{{ flash.message }}</span>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-4 lg:p-0">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-8">
                        <!-- Mobile Content (Only visible on mobile) -->
                        <div class="lg:hidden space-y-6">
                            <!-- Discount Overview Card -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                                <!-- Header -->
                                <div class="p-4 sm:p-6 border-b border-gray-200">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ discount.DISCOFFERNAME }}</h2>
                                            <div class="flex items-center space-x-3 mb-3">
                                                <span :class="[
                                                    'px-3 py-1 text-sm font-medium rounded-full border',
                                                    discountTypeClass
                                                ]">
                                                    {{ discountTypeLabel }}
                                                </span>
                                                <span class="text-2xl font-bold text-green-600">{{ formatDiscountValue }}</span>
                                            </div>
                                            <p class="text-sm text-gray-600">{{ discountTypeDescription }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Stats -->
                                <div class="grid grid-cols-2 divide-x divide-gray-200">
                                    <div class="p-4 text-center">
                                        <div class="text-lg font-semibold text-gray-900">{{ formatDiscountValue }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Discount Value</div>
                                    </div>
                                    <div class="p-4 text-center">
                                        <div class="text-lg font-semibold text-gray-900">#{{ discount.id }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Discount ID</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Calculator -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Calculator</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Test Amount (₱)
                                        </label>
                                        <input
                                            v-model.number="previewAmount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Enter amount to test discount"
                                        >
                                    </div>

                                    <div v-if="discountPreview" class="p-4 bg-gradient-to-r from-blue-50 to-green-50 rounded-lg border border-blue-200">
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Original Amount:</span>
                                                <span class="font-medium">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Discount:</span>
                                                <span class="font-medium text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                            </div>
                                            <div class="flex justify-between border-t pt-2">
                                                <span class="font-bold text-gray-900">Final Amount:</span>
                                                <span class="font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                            </div>
                                            <div class="text-center pt-2 border-t">
                                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                                    {{ discountPreview.savingsPercentage }}% Total Savings
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Discount Details -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Discount Information</h3>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Created</label>
                                            <p class="text-sm text-gray-900 mt-1">{{ formatDate(discount.created_at) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</label>
                                            <p class="text-sm text-gray-900 mt-1">{{ formatDate(discount.updated_at) }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Discount Rules -->
                                    <div class="pt-4 border-t border-gray-200">
                                        <h4 class="font-medium text-gray-900 mb-3">How This Discount Works</h4>
                                        <div v-if="discount.DISCOUNTTYPE === 'FIXED'" class="p-3 bg-green-50 border border-green-200 rounded-lg">
                                            <ul class="text-sm text-green-800 space-y-1">
                                                <li>• ₱{{ Number(discount.PARAMETER).toFixed(2) }} discount applied per item</li>
                                                <li>• Cannot exceed the individual item's price</li>
                                                <li>• If item costs less than discount, full item price is discounted</li>
                                            </ul>
                                        </div>
                                        
                                        <div v-if="discount.DISCOUNTTYPE === 'FIXEDTOTAL'" class="p-3 bg-purple-50 border border-purple-200 rounded-lg">
                                            <ul class="text-sm text-purple-800 space-y-1">
                                                <li>• ₱{{ Number(discount.PARAMETER).toFixed(2) }} deducted from total bill</li>
                                                <li>• Applied once per transaction</li>
                                                <li>• Minimum final amount is ₱0.00</li>
                                            </ul>
                                        </div>
                                        
                                        <div v-if="discount.DISCOUNTTYPE === 'PERCENTAGE'" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <ul class="text-sm text-blue-800 space-y-1">
                                                <li>• {{ discount.PARAMETER }}% discount applied to total amount</li>
                                                <li>• Higher bills result in higher savings</li>
                                                <li>• Applied once per transaction</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Layout (Only visible on desktop) -->
                        <!-- Left Column - Discount Info -->
                        <div class="hidden lg:block lg:col-span-2 space-y-6">
                            <!-- Discount Overview -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Value Display -->
                                    <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg border border-blue-200">
                                        <div class="text-4xl font-bold text-blue-600 mb-2">{{ formatDiscountValue }}</div>
                                        <div class="text-sm text-blue-700 font-medium">Discount Value</div>
                                        <div class="text-xs text-blue-600 mt-1">{{ discountTypeLabel }}</div>
                                    </div>
                                    
                                    <!-- Quick Stats -->
                                    <div class="space-y-4">
                                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                                            <div class="text-2xl font-semibold text-gray-900">#{{ discount.id }}</div>
                                            <div class="text-xs text-gray-500 mt-1">Discount ID</div>
                                        </div>
                                        <div class="text-center p-4 bg-green-50 rounded-lg">
                                            <div class="text-lg font-semibold text-green-700">Active</div>
                                            <div class="text-xs text-green-600 mt-1">Status</div>
                                        </div>
                                    </div>

                                    <!-- Details -->
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Created</label>
                                            <p class="text-sm text-gray-900 mt-1">{{ formatDate(discount.created_at) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</label>
                                            <p class="text-sm text-gray-900 mt-1">{{ formatDate(discount.updated_at) }}</p>
                                        </div>
                                        <div>
                                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Type</label>
                                            <p class="text-sm text-gray-900 mt-1">{{ discountTypeLabel }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- How It Works -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-4">How This Discount Works</h3>
                                <p class="text-gray-600 mb-4">{{ discountTypeDescription }}</p>
                                
                                <div v-if="discount.DISCOUNTTYPE === 'FIXED'" class="p-4 bg-green-50 border border-green-200 rounded-lg">
                                    <h4 class="font-medium text-green-800 mb-2">Fixed Amount Rules</h4>
                                    <ul class="text-sm text-green-700 space-y-2">
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            ₱{{ Number(discount.PARAMETER).toFixed(2) }} discount applied per item
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Cannot exceed the individual item's price
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            If item costs less than discount, full item price is discounted
                                        </li>
                                    </ul>
                                </div>
                                
                                <div v-if="discount.DISCOUNTTYPE === 'FIXEDTOTAL'" class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                                    <h4 class="font-medium text-purple-800 mb-2">Fixed Total Rules</h4>
                                    <ul class="text-sm text-purple-700 space-y-2">
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            ₱{{ Number(discount.PARAMETER).toFixed(2) }} deducted from total bill
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Applied once per transaction
                                        </li>
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Minimum final amount is ₱0.00
                                        </li>
                                    </ul>
                                </div>
                                
                                <div v-if="discount.DISCOUNTTYPE === 'PERCENTAGE'" class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                    <h4 class="font-medium text-blue-800 mb-2">Percentage Rules</h4>
                                    <ul class="text-sm text-blue-700 space-y-2">
                                        <li class="flex items-start">
                                            <svg class="w-4 h-4 mr-2 mt-0.5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                            </svg>
                                            Applied once per transaction
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Example Scenarios (Desktop) -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-xl font-semibold text-gray-900 mb-4">Example Scenarios</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Small Purchase -->
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                            <span class="inline-block w-6 h-6 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">1</span>
                                            Small Purchase
                                        </h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div class="flex justify-between">
                                                <span>Original:</span>
                                                <span class="font-medium">₱{{ calculateExample(100).original.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Discount:</span>
                                                <span class="font-medium text-red-600">-₱{{ calculateExample(100).discount.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                                <span>Final:</span>
                                                <span>₱{{ calculateExample(100).final.toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Medium Purchase -->
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                            <span class="inline-block w-6 h-6 bg-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">2</span>
                                            Medium Purchase
                                        </h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div class="flex justify-between">
                                                <span>Original:</span>
                                                <span class="font-medium">₱{{ calculateExample(500).original.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Discount:</span>
                                                <span class="font-medium text-red-600">-₱{{ calculateExample(500).discount.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                                <span>Final:</span>
                                                <span>₱{{ calculateExample(500).final.toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Large Purchase -->
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                            <span class="inline-block w-6 h-6 bg-purple-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">3</span>
                                            Large Purchase
                                        </h4>
                                        <div class="text-sm text-gray-600 space-y-1">
                                            <div class="flex justify-between">
                                                <span>Original:</span>
                                                <span class="font-medium">₱{{ calculateExample(1000).original.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Discount:</span>
                                                <span class="font-medium text-red-600">-₱{{ calculateExample(1000).discount.toFixed(2) }}</span>
                                            </div>
                                            <div class="flex justify-between font-semibold text-green-600 pt-1 border-t border-gray-300">
                                                <span>Final:</span>
                                                <span>₱{{ calculateExample(1000).final.toFixed(2) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Calculator & Actions (Desktop) -->
                        <div class="hidden lg:block space-y-6">
                            <!-- Interactive Calculator -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Discount Calculator</h3>
                                
                                <div class="space-y-4">
                                    <!-- Test Amount Input -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Test Amount (₱)
                                        </label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-lg">₱</span>
                                            </div>
                                            <input
                                                v-model.number="previewAmount"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-medium"
                                                placeholder="0.00"
                                            >
                                        </div>
                                    </div>

                                    <!-- Quick Amount Buttons -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-3">Quick Test Amounts</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button
                                                v-for="amount in [100, 500, 1000, 2000]"
                                                :key="amount"
                                                @click="previewAmount = amount"
                                                class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                                                :class="{ 'bg-blue-100 text-blue-700 ring-2 ring-blue-500': previewAmount === amount }"
                                            >
                                                ₱{{ amount.toLocaleString() }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Calculation Results -->
                                    <div v-if="discountPreview" class="space-y-3 p-5 bg-gradient-to-br from-blue-50 via-green-50 to-purple-50 rounded-lg border-2 border-blue-200">
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                            <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                            <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                        </div>
                                        <hr class="border-gray-300">
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                            <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                        </div>
                                        <div class="text-center pt-3 border-t border-gray-200">
                                            <div class="space-y-2">
                                                <span class="inline-block px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                                    {{ discountPreview.savingsPercentage }}% Total Savings
                                                </span>
                                                <div class="text-xs text-gray-600">
                                                    Saves {{ formatCurrency(discountPreview.discountAmount) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                                
                                <div class="space-y-3">
                                    <Link
                                        v-if="isAdmin"
                                        :href="route('discountsv2.edit', discount.id)"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Edit Discount
                                    </Link>

                                    <Link
                                        :href="route('discountsv2.index')"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-gray-50 hover:bg-gray-100 text-gray-700 font-medium rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        All Discounts
                                    </Link>
                                    
                                    <button
                                        v-if="isAdmin"
                                        @click="confirmDelete"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete Discount
                                    </button>
                                </div>
                            </div>

                            <!-- Discount Information Summary -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Information</h3>
                                
                                <div class="space-y-4">
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Discount ID</div>
                                        <div class="text-lg font-semibold text-gray-900">#{{ discount.id }}</div>
                                    </div>
                                    
                                    <div class="p-3 bg-gray-50 rounded-lg">
                                        <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Status</div>
                                        <div class="flex items-center">
                                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                            <span class="text-sm font-medium text-green-700">Active</span>
                                        </div>
                                    </div>

                                    <div class="text-xs text-gray-500 pt-2 border-t border-gray-200">
                                        This discount is available for use in POS systems and online transactions.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Floating Action Menu (Only visible on mobile) -->
                <div class="lg:hidden fixed bottom-6 right-6 z-40">
                    <!-- Menu Options -->
                    <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                        <!-- Edit Discount -->
                        <Link
                            v-if="isAdmin"
                            :href="route('discountsv2.edit', discount.id)"
                            @click="closeFloatingMenu"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Discount
                        </Link>

                        <!-- Advanced Calculator -->
                        <button
                            @click="toggleCalculatorPanel"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Advanced Calculator
                        </button>

                        <!-- Example Scenarios -->
                        <button
                            @click="toggleExamplesPanel"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm8-10V9a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Example Scenarios
                        </button>

                        <!-- View All Discounts -->
                        <Link
                            :href="route('discountsv2.index')"
                            @click="closeFloatingMenu"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            All Discounts
                        </Link>

                        <!-- Delete (Admin Only) -->
                        <button
                            v-if="isAdmin"
                            @click="confirmDelete"
                            class="w-full px-4 py-3 text-left text-sm text-red-600 hover:bg-red-50 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete Discount
                        </button>

                        <!-- Divider and Info -->
                        <div class="border-t border-gray-200 my-2"></div>
                        <div class="px-4 py-2">
                            <p class="text-xs text-gray-500 mb-1">Discount ID: #{{ discount.id }}</p>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                This discount is available for use in POS systems and online transactions.
                            </p>
                        </div>
                    </div>

                    <!-- Main Floating Button -->
                    <button
                        @click="toggleFloatingMenu"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105"
                        :class="{ 'rotate-45': showFloatingMenu }"
                    >
                        <svg v-if="!showFloatingMenu" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Modals (Unchanged) -->
                <!-- Advanced Calculator Panel -->
                <div v-if="showCalculatorPanel" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
                    <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Advanced Calculator</h3>
                                <button
                                    @click="showCalculatorPanel = false"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-6">
                            <!-- Test Amount Input -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Test Amount (₱)
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-lg">₱</span>
                                    </div>
                                    <input
                                        v-model.number="previewAmount"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-lg font-medium"
                                        placeholder="0.00"
                                    >
                                </div>
                            </div>

                            <!-- Calculation Results -->
                            <div v-if="discountPreview" class="space-y-3 p-5 bg-gradient-to-br from-blue-50 via-green-50 to-purple-50 rounded-lg border-2 border-blue-200">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                    <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                    <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                </div>
                                <hr class="border-gray-300">
                                <div class="flex justify-between items-center py-2">
                                    <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                    <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                </div>
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

                            <!-- Quick Amount Buttons -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Quick Test Amounts</label>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="amount in [100, 500, 1000, 1500, 2000, 5000]"
                                        :key="amount"
                                        @click="previewAmount = amount"
                                        class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors"
                                        :class="{ 'bg-blue-100 text-blue-700': previewAmount === amount }"
                                    >
                                        ₱{{ amount.toLocaleString() }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Examples Panel -->
                <div v-if="showExamplesPanel" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
                    <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Example Scenarios</h3>
                                <button
                                    @click="showExamplesPanel = false"
                                    class="text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="p-6 space-y-4">
                            <!-- Example 1: Small Purchase -->
                            <div class="p-4 bg-gray-50 rounded-lg border">
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <span class="inline-block w-6 h-6 bg-blue-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">1</span>
                                    Small Purchase: ₱100.00
                                </h4>
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
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <span class="inline-block w-6 h-6 bg-green-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">2</span>
                                    Medium Purchase: ₱500.00
                                </h4>
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
                                <h4 class="font-medium text-gray-900 mb-3 flex items-center">
                                    <span class="inline-block w-6 h-6 bg-purple-500 text-white text-xs font-bold rounded-full flex items-center justify-center mr-2">3</span>
                                    Large Purchase: ₱1,000.00
                                </h4>
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

                            <!-- Use Case Buttons -->
                            <div class="pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-700 mb-3">Test these scenarios:</p>
                                <div class="grid grid-cols-3 gap-2">
                                    <button
                                        v-for="amount in [100, 500, 1000]"
                                        :key="amount"
                                        @click="previewAmount = amount; showExamplesPanel = false; showCalculatorPanel = true"
                                        class="px-3 py-2 text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors"
                                    >
                                        Test ₱{{ amount }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl w-full max-w-md p-6">
                        <div class="text-center">
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
                                
                                <div class="p-3 bg-gray-50 rounded-lg border text-left">
                                    <p class="font-medium text-gray-900">{{ discount.DISCOFFERNAME }}</p>
                                    <p class="text-sm text-gray-600">{{ discountTypeLabel }} - {{ formatDiscountValue }}</p>
                                </div>
                                
                                <p class="text-xs text-red-600 mt-3">
                                    <strong>Warning:</strong> This action cannot be undone and may affect existing transactions.
                                </p>
                            </div>
                            
                            <div class="flex space-x-3">
                                <button
                                    @click="showDeleteModal = false"
                                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    @click="deleteDiscount"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                >
                                    Delete Discount
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Overlay to close floating menu -->
                <div v-if="showFloatingMenu" @click="closeFloatingMenu" class="lg:hidden fixed inset-0 bg-black bg-opacity-25 z-30"></div>
            </div>
        </template>
    </component>
</template>