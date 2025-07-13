<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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

// Form setup
const form = useForm({
    DISCOFFERNAME: props.discount.DISCOFFERNAME || '',
    PARAMETER: props.discount.PARAMETER || '',
    DISCOUNTTYPE: props.discount.DISCOUNTTYPE || ''
});

const discountTypes = [
    { value: 'FIXED', label: 'Fixed Amount', description: 'Fixed amount off per item (cannot exceed item price)' },
    { value: 'FIXEDTOTAL', label: 'Fixed Total', description: 'Fixed amount off the total bill' },
    { value: 'PERCENTAGE', label: 'Percentage', description: 'Percentage off the total amount' }
];

// Reactive state
const previewAmount = ref(100);
const showFloatingMenu = ref(false);
const showPreviewPanel = ref(false);
const showDeleteModal = ref(false);
const showChangesPanel = ref(false);

// Computed properties for form validation and preview
const isPercentage = computed(() => form.DISCOUNTTYPE === 'PERCENTAGE');
const parameterLabel = computed(() => {
    switch (form.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return 'Percentage (%)';
        case 'FIXED':
        case 'FIXEDTOTAL':
            return 'Amount (₱)';
        default:
            return 'Value';
    }
});

const parameterPlaceholder = computed(() => {
    switch (form.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return 'Enter percentage (e.g., 10 for 10%)';
        case 'FIXED':
            return 'Enter fixed amount per item (e.g., 50.00)';
        case 'FIXEDTOTAL':
            return 'Enter fixed amount off total (e.g., 100.00)';
        default:
            return 'Enter value';
    }
});

const discountPreview = computed(() => {
    if (!form.PARAMETER || !form.DISCOUNTTYPE || !previewAmount.value) {
        return null;
    }

    const originalAmount = previewAmount.value;
    const parameter = parseFloat(form.PARAMETER);
    let discountAmount = 0;
    let finalAmount = originalAmount;

    switch (form.DISCOUNTTYPE) {
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
        finalAmount: finalAmount.toFixed(2)
    };
});

// Check if form has changes
const formHasChanges = computed(() => {
    return form.DISCOFFERNAME !== props.discount.DISCOFFERNAME ||
           form.PARAMETER != props.discount.PARAMETER ||
           form.DISCOUNTTYPE !== props.discount.DISCOUNTTYPE;
});

// Validation rules
const parameterError = computed(() => {
    if (!form.PARAMETER) return null;
    
    const value = parseFloat(form.PARAMETER);
    if (isNaN(value) || value < 0) {
        return 'Value must be a positive number';
    }
    
    if (form.DISCOUNTTYPE === 'PERCENTAGE' && value > 100) {
        return 'Percentage cannot exceed 100%';
    }
    
    return null;
});

// Methods
const submitForm = () => {
    form.put(route('discountsv2.update', props.discount.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by redirect
        }
    });
};

const resetForm = () => {
    form.DISCOFFERNAME = props.discount.DISCOFFERNAME;
    form.PARAMETER = props.discount.PARAMETER;
    form.DISCOUNTTYPE = props.discount.DISCOUNTTYPE;
    form.clearErrors();
    closeFloatingMenu();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
};

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

const toggleFloatingMenu = () => {
    showFloatingMenu.value = !showFloatingMenu.value;
};

const closeFloatingMenu = () => {
    showFloatingMenu.value = false;
};

const togglePreviewPanel = () => {
    showPreviewPanel.value = !showPreviewPanel.value;
    closeFloatingMenu();
};

const toggleChangesPanel = () => {
    showChangesPanel.value = !showChangesPanel.value;
    closeFloatingMenu();
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
</script>

<template>
    <Head :title="`Edit ${discount.DISCOFFERNAME}`" />

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
                            <div>
                                <h1 class="text-lg font-semibold text-gray-900">Edit Discount</h1>
                                <p class="text-xs text-gray-600 truncate max-w-[200px]">{{ discount.DISCOFFERNAME }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Changes Indicator -->
                            <div v-if="formHasChanges" class="w-3 h-3 bg-orange-500 rounded-full animate-pulse"></div>
                            <!-- View Button -->
                            <Link
                                :href="route('discountsv2.show', discount.id)"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
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
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h1 class="text-2xl font-bold text-gray-900">Edit Discount</h1>
                                    <div v-if="formHasChanges" class="flex items-center space-x-2 px-3 py-1 bg-orange-100 border border-orange-200 rounded-full">
                                        <div class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></div>
                                        <span class="text-xs font-medium text-orange-700">Unsaved Changes</span>
                                    </div>
                                </div>
                                <p class="text-gray-600 mb-1">{{ discount.DISCOFFERNAME }}</p>
                                <div class="flex items-center space-x-2 text-sm text-gray-500">
                                    <span>ID: #{{ discount.id }}</span>
                                    <span>•</span>
                                    <span>{{ formatDiscountValue(discount) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <Link
                                :href="route('discountsv2.show', discount.id)"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View Details
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

                <!-- Changes Warning (Mobile Only) -->
                <div v-if="formHasChanges" class="lg:hidden mx-4 mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <svg class="h-5 w-5 text-yellow-400 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Unsaved Changes</h3>
                            <p class="text-sm text-yellow-700 mt-1">You have unsaved changes. Don't forget to save your updates.</p>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="p-4 lg:p-0">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8">
                        <!-- Form Section -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden lg:order-1">
                            <div class="p-4 lg:p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4 lg:mb-6">Discount Information</h2>
                                
                                <form @submit.prevent="submitForm" class="space-y-4 lg:space-y-6">
                                    <!-- Discount Name -->
                                    <div>
                                        <label for="discountName" class="block text-sm font-medium text-gray-700 mb-2">
                                            Discount Name *
                                        </label>
                                        <input
                                            id="discountName"
                                            v-model="form.DISCOFFERNAME"
                                            type="text"
                                            placeholder="e.g., SENIOR CITIZEN, STUDENT DISCOUNT"
                                            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :class="{ 'border-red-500 ring-red-500': form.errors.DISCOFFERNAME }"
                                            required
                                        >
                                        <p v-if="form.errors.DISCOFFERNAME" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.DISCOFFERNAME }}
                                        </p>
                                    </div>

                                    <!-- Discount Type -->
                                    <div>
                                        <label for="discountType" class="block text-sm font-medium text-gray-700 mb-2">
                                            Discount Type *
                                        </label>
                                        <select
                                            id="discountType"
                                            v-model="form.DISCOUNTTYPE"
                                            class="w-full px-4 py-3 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                            :class="{ 'border-red-500 ring-red-500': form.errors.DISCOUNTTYPE }"
                                            required
                                        >
                                            <option value="">Select discount type</option>
                                            <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                                {{ type.label }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.DISCOUNTTYPE" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.DISCOUNTTYPE }}
                                        </p>
                                        
                                        <!-- Type Description -->
                                        <div v-if="form.DISCOUNTTYPE" class="mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                            <p class="text-sm text-blue-800">
                                                {{ discountTypes.find(t => t.value === form.DISCOUNTTYPE)?.description }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Discount Value -->
                                    <div v-if="form.DISCOUNTTYPE">
                                        <label for="parameter" class="block text-sm font-medium text-gray-700 mb-2">
                                            {{ parameterLabel }} *
                                        </label>
                                        <div class="relative">
                                            <input
                                                id="parameter"
                                                v-model="form.PARAMETER"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                :max="isPercentage ? 100 : undefined"
                                                :placeholder="parameterPlaceholder"
                                                class="w-full px-4 py-3 pr-12 text-base border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                                :class="{ 'border-red-500 ring-red-500': form.errors.PARAMETER || parameterError }"
                                                required
                                            >
                                            <div v-if="isPercentage" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-base font-medium">%</span>
                                            </div>
                                            <div v-else-if="form.DISCOUNTTYPE" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                                <span class="text-gray-500 text-base font-medium">₱</span>
                                            </div>
                                        </div>
                                        <p v-if="form.errors.PARAMETER" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.PARAMETER }}
                                        </p>
                                        <p v-else-if="parameterError" class="mt-2 text-sm text-red-600">
                                            {{ parameterError }}
                                        </p>
                                    </div>

                                    <!-- Preview Summary (Mobile Inline) -->
                                    <div v-if="form.DISCOUNTTYPE && form.PARAMETER && discountPreview" 
                                         class="lg:hidden p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                                        <h3 class="text-sm font-medium text-gray-900 mb-3">Quick Preview</h3>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Test Amount:</span>
                                                <span class="font-medium">{{ formatCurrency(previewAmount) }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Discount:</span>
                                                <span class="font-medium text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                            </div>
                                            <div class="flex justify-between border-t pt-2">
                                                <span class="font-medium text-gray-900">Final Amount:</span>
                                                <span class="font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Form Actions -->
                            <div class="bg-gray-50 px-4 py-4 lg:px-6 border-t border-gray-200">
                                <div class="flex flex-col-reverse sm:flex-row space-y-reverse space-y-3 sm:space-y-0 sm:space-x-3">
                                    <button
                                        v-if="formHasChanges"
                                        @click="resetForm"
                                        type="button"
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Reset
                                    </button>
                                    <Link
                                        :href="route('discountsv2.index')"
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        @click="submitForm"
                                        :disabled="form.processing || parameterError || !form.DISCOFFERNAME || !form.DISCOUNTTYPE || !form.PARAMETER || !formHasChanges"
                                        class="w-full sm:w-auto inline-flex justify-center items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ form.processing ? 'Updating...' : 'Update Discount' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Preview Section (Only visible on desktop) -->
                        <div class="hidden lg:block space-y-6 lg:order-2">
                            <!-- Live Preview Panel -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Live Preview</h2>
                                
                                <div v-if="!form.DISCOUNTTYPE" class="text-center py-8 text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <p class="text-base">Configure discount to see preview</p>
                                </div>

                                <div v-else class="space-y-4">
                                    <!-- Preview Amount Input -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Test Amount (₱)
                                        </label>
                                        <input
                                            v-model.number="previewAmount"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            placeholder="Enter amount to test discount"
                                        >
                                    </div>

                                    <!-- Preview Results -->
                                    <div v-if="discountPreview" class="space-y-3 p-4 bg-gradient-to-br from-blue-50 via-green-50 to-purple-50 rounded-lg border-2 border-blue-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                            <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                            <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                        </div>
                                        <hr class="border-gray-300">
                                        <div class="flex justify-between items-center">
                                            <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                            <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Changes Comparison (Desktop Only) -->
                            <div v-if="formHasChanges" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Changes Summary</h2>
                                
                                <div class="space-y-4">
                                    <!-- Original Values -->
                                    <div class="p-4 bg-gray-50 rounded-lg border">
                                        <h4 class="text-sm font-medium text-gray-700 mb-3">Original Values</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Name:</span>
                                                <span class="font-medium">{{ discount.DISCOFFERNAME }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Type:</span>
                                                <span class="font-medium">{{ discount.DISCOUNTTYPE }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-gray-600">Value:</span>
                                                <span class="font-medium">{{ formatDiscountValue(discount) }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- New Values -->
                                    <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                        <h4 class="text-sm font-medium text-blue-700 mb-3">New Values</h4>
                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between">
                                                <span class="text-blue-600">Name:</span>
                                                <span class="font-medium text-blue-900">{{ form.DISCOFFERNAME }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-blue-600">Type:</span>
                                                <span class="font-medium text-blue-900">{{ form.DISCOUNTTYPE }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span class="text-blue-600">Value:</span>
                                                <span class="font-medium text-blue-900">
                                                    {{ form.DISCOUNTTYPE === 'PERCENTAGE' ? form.PARAMETER + '%' : '₱' + Number(form.PARAMETER).toFixed(2) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions (Desktop Only) -->
                            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h2>
                                
                                <div class="space-y-3">
                                    <Link
                                        :href="route('discountsv2.show', discount.id)"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View Discount Details
                                    </Link>
                                    
                                    <button
                                        v-if="isAdmin"
                                        @click="confirmDelete"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-700 font-medium rounded-lg transition-colors"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                        Delete Discount
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Floating Action Menu (Only visible on mobile) -->
                <div class="lg:hidden fixed bottom-6 right-6 z-40">
                    <!-- Menu Options -->
                    <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                        <!-- Preview Calculator -->
                        <button
                            v-if="form.DISCOUNTTYPE"
                            @click="togglePreviewPanel"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Preview Calculator
                        </button>

                        <!-- Changes Summary -->
                        <button
                            v-if="formHasChanges"
                            @click="toggleChangesPanel"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            View Changes
                            <span class="ml-auto w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                        </button>

                        <!-- Reset Form -->
                        <button
                            v-if="formHasChanges"
                            @click="resetForm"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset Changes
                        </button>

                        <!-- View Discount -->
                        <Link
                            :href="route('discountsv2.show', discount.id)"
                            @click="closeFloatingMenu"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 616 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            View Details
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

                        <!-- Divider and Help -->
                        <div class="border-t border-gray-200 my-2"></div>
                        <div class="px-4 py-2">
                            <p class="text-xs text-gray-500 mb-2">Discount ID: #{{ discount.id }}</p>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Make sure to save your changes before leaving this page.
                            </p>
                        </div>
                    </div>

                    <!-- Main Floating Button -->
                    <button
                        @click="toggleFloatingMenu"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105 relative"
                        :class="{ 'rotate-45': showFloatingMenu }"
                    >
                        <!-- Changes indicator -->
                        <div v-if="formHasChanges && !showFloatingMenu" class="absolute -top-1 -right-1 w-4 h-4 bg-orange-500 rounded-full animate-pulse border-2 border-white"></div>
                        
                        <svg v-if="!showFloatingMenu" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Modals (Unchanged) -->
                <!-- Preview Panel Modal -->
                <div v-if="showPreviewPanel" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
                    <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Discount Calculator</h3>
                                <button
                                    @click="showPreviewPanel = false"
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
                                <input
                                    v-model.number="previewAmount"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Enter amount to test discount"
                                >
                            </div>

                            <!-- Calculation Results -->
                            <div v-if="discountPreview" class="space-y-3 p-5 bg-gradient-to-br from-blue-50 via-green-50 to-purple-50 rounded-lg border-2 border-blue-200">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                    <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                    <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                </div>
                                <hr class="border-gray-300">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                    <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Changes Panel Modal -->
                <div v-if="showChangesPanel" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end sm:items-center justify-center p-4">
                    <div class="bg-white rounded-t-xl sm:rounded-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Changes Summary</h3>
                                <button
                                    @click="showChangesPanel = false"
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
                            <!-- Original Values -->
                            <div class="p-4 bg-gray-50 rounded-lg border">
                                <h4 class="text-sm font-medium text-gray-700 mb-3">Original Values</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Name:</span>
                                        <span class="font-medium">{{ discount.DISCOFFERNAME }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Type:</span>
                                        <span class="font-medium">{{ discount.DISCOUNTTYPE }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Value:</span>
                                        <span class="font-medium">{{ formatDiscountValue(discount) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- New Values -->
                            <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                <h4 class="text-sm font-medium text-blue-700 mb-3">New Values</h4>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Name:</span>
                                        <span class="font-medium text-blue-900">{{ form.DISCOFFERNAME }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Type:</span>
                                        <span class="font-medium text-blue-900">{{ form.DISCOUNTTYPE }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-blue-600">Value:</span>
                                        <span class="font-medium text-blue-900">
                                            {{ form.DISCOUNTTYPE === 'PERCENTAGE' ? form.PARAMETER + '%' : '₱' + Number(form.PARAMETER).toFixed(2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex space-x-3">
                                <button
                                    @click="resetForm"
                                    class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Reset Changes
                                </button>
                                <button
                                    @click="submitForm"
                                    class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                                >
                                    Save Changes
                                </button>
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
                                    <p class="text-sm text-gray-600">{{ formatDiscountValue(discount) }}</p>
                                </div>
                                
                                <p class="text-xs text-red-600 mt-3">
                                    <strong>Warning:</strong> This action cannot be undone.
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
                                    Delete
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