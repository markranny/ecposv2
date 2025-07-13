<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
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
const hasChanges = ref(false);

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
</script>

<template>
    <Head :title="`Edit ${discount.DISCOFFERNAME}`" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <TableContainer>
                <!-- Header -->
                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Edit Discount</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Modify discount: {{ discount.DISCOFFERNAME }}
                            </p>
                        </div>
                        <div class="flex space-x-3">
                            <Link
                                :href="route('discountsv2.show', discount.id)"
                                class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium rounded-md transition-colors"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                View
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

                <!-- Changes Warning -->
                <div v-if="formHasChanges" class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md">
                    <div class="flex">
                        <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-medium text-yellow-800">Unsaved Changes</h3>
                            <p class="text-sm text-yellow-700 mt-1">You have unsaved changes. Don't forget to save your updates.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Form Section -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-medium text-gray-900">Edit Discount Information</h2>
                            <button
                                v-if="formHasChanges"
                                @click="resetForm"
                                type="button"
                                class="text-sm text-blue-600 hover:text-blue-800 transition-colors"
                            >
                                Reset Changes
                            </button>
                        </div>
                        
                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Discount Name -->
                            <div>
                                <label for="discountName" class="block text-sm font-medium text-gray-700 mb-2">
                                    Discount Name *
                                </label>
                                <input
                                    id="discountName"
                                    v-model="form.DISCOFFERNAME"
                                    type="text"
                                    placeholder="Enter discount name (e.g., SENIOR CITIZEN, STUDENT DISCOUNT)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.DISCOFFERNAME }"
                                    required
                                >
                                <p v-if="form.errors.DISCOFFERNAME" class="mt-1 text-sm text-red-600">
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
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    :class="{ 'border-red-500': form.errors.DISCOUNTTYPE }"
                                    required
                                >
                                    <option value="">Select discount type</option>
                                    <option v-for="type in discountTypes" :key="type.value" :value="type.value">
                                        {{ type.label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.DISCOUNTTYPE" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.DISCOUNTTYPE }}
                                </p>
                                
                                <!-- Type Description -->
                                <div v-if="form.DISCOUNTTYPE" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-md">
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        :class="{ 'border-red-500': form.errors.PARAMETER || parameterError }"
                                        required
                                    >
                                    <div v-if="isPercentage" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">%</span>
                                    </div>
                                    <div v-else-if="form.DISCOUNTTYPE" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 text-sm">₱</span>
                                    </div>
                                </div>
                                <p v-if="form.errors.PARAMETER" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.PARAMETER }}
                                </p>
                                <p v-else-if="parameterError" class="mt-1 text-sm text-red-600">
                                    {{ parameterError }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-3 pt-6">
                                <button
                                    v-if="formHasChanges"
                                    @click="resetForm"
                                    type="button"
                                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors"
                                >
                                    Reset
                                </button>
                                <Link
                                    :href="route('discountsv2.index')"
                                    class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing || parameterError || !form.DISCOFFERNAME || !form.DISCOUNTTYPE || !form.PARAMETER || !formHasChanges"
                                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ form.processing ? 'Updating...' : 'Update Discount' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="space-y-6">
                        <!-- Current vs New Comparison -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Changes Summary</h2>
                            
                            <div class="space-y-4">
                                <!-- Original Values -->
                                <div class="p-4 bg-gray-50 rounded-md">
                                    <h3 class="text-sm font-medium text-gray-700 mb-3">Original Values</h3>
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

                                <!-- New Values (if changed) -->
                                <div v-if="formHasChanges" class="p-4 bg-blue-50 rounded-md border border-blue-200">
                                    <h3 class="text-sm font-medium text-blue-700 mb-3">New Values</h3>
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

                        <!-- Discount Preview -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Preview</h2>
                            
                            <div class="space-y-4">
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
                                <div v-if="discountPreview" class="space-y-3 p-4 bg-gray-50 rounded-md">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                        <span class="text-sm text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                        <span class="text-sm text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                    </div>
                                    <hr class="border-gray-300">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-bold text-gray-900">Final Amount:</span>
                                        <span class="text-lg font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Discount Info -->
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Information</h2>
                            
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created:</span>
                                    <span class="text-gray-900">{{ new Date(discount.created_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Last Updated:</span>
                                    <span class="text-gray-900">{{ new Date(discount.updated_at).toLocaleDateString() }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Discount ID:</span>
                                    <span class="text-gray-900 font-mono">#{{ discount.id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </TableContainer>
        </template>
    </component>
</template>