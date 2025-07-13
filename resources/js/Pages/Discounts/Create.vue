<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";

const props = defineProps({
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
    DISCOFFERNAME: '',
    PARAMETER: '',
    DISCOUNTTYPE: ''
});

const discountTypes = [
    { value: 'FIXED', label: 'Fixed Amount', description: 'Fixed amount off per item (cannot exceed item price)' },
    { value: 'FIXEDTOTAL', label: 'Fixed Total', description: 'Fixed amount off the total bill' },
    { value: 'PERCENTAGE', label: 'Percentage', description: 'Percentage off the total amount' }
];

// Reactive state
const previewAmount = ref(100);

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

// Watch for form changes to update preview
watch(() => form.DISCOUNTTYPE, () => {
    form.PARAMETER = '';
});

// Methods
const submitForm = () => {
    form.post(route('discountsv2.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handled by redirect
        }
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
};
</script>

<template>
    <Head title="Create New Discount" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <div class="min-h-screen bg-gray-50 p-2 sm:p-4 lg:p-6">
                <!-- Mobile Header -->
                <div class="mb-4 lg:hidden">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Create Discount</h1>
                            <p class="text-sm text-gray-600">Add a new discount offer</p>
                        </div>
                        <Link
                            :href="route('discountsv2.index')"
                            class="inline-flex items-center px-3 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-md transition-colors"
                        >
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Back
                        </Link>
                    </div>
                </div>

                <!-- Desktop Header -->
                <div class="hidden lg:block mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Create New Discount</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Add a new discount offer for your store
                            </p>
                        </div>
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

                <!-- Flash Messages -->
                <div v-if="flash.message" 
                     :class="[
                         'mb-4 lg:mb-6 px-4 py-2 rounded-md',
                         flash.isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                     ]">
                    {{ flash.message }}
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-8">
                    <!-- Form Section -->
                    <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
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
                                    placeholder="Enter discount name (e.g., SENIOR CITIZEN, STUDENT DISCOUNT)"
                                    class="w-full px-3 py-2 text-sm lg:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                    class="w-full px-3 py-2 text-sm lg:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                        class="w-full px-3 py-2 text-sm lg:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 lg:pt-6">
                                <Link
                                    :href="route('discountsv2.index')"
                                    class="w-full sm:w-auto px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-md transition-colors text-center"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing || parameterError || !form.DISCOFFERNAME || !form.DISCOUNTTYPE || !form.PARAMETER"
                                    class="w-full sm:w-auto px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    {{ form.processing ? 'Creating...' : 'Create Discount' }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="space-y-4 lg:space-y-6">
                        <!-- Discount Preview -->
                        <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Preview</h2>
                            
                            <div v-if="!form.DISCOUNTTYPE" class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm lg:text-base">Select a discount type to see preview</p>
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
                                        class="w-full px-3 py-2 text-sm lg:text-base border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
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

                        <!-- Discount Type Guide -->
                        <div class="bg-white rounded-lg shadow-sm p-4 lg:p-6">
                            <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Types Guide</h2>
                            
                            <div class="space-y-4">
                                <div v-for="type in discountTypes" :key="type.value" 
                                     class="p-3 lg:p-4 border rounded-md"
                                     :class="form.DISCOUNTTYPE === type.value ? 'border-blue-500 bg-blue-50' : 'border-gray-200'">
                                    <h3 class="font-medium text-gray-900 mb-2">{{ type.label }}</h3>
                                    <p class="text-sm text-gray-600 mb-3">{{ type.description }}</p>
                                    
                                    <!-- Examples -->
                                    <div class="text-xs text-gray-500">
                                        <strong>Example:</strong>
                                        <div v-if="type.value === 'FIXED'" class="mt-1">
                                            ₱10 off per item → Item costs ₱50, customer pays ₱40
                                        </div>
                                        <div v-else-if="type.value === 'FIXEDTOTAL'" class="mt-1">
                                            ₱100 off total → Bill is ₱500, customer pays ₱400
                                        </div>
                                        <div v-else-if="type.value === 'PERCENTAGE'" class="mt-1">
                                            20% off → Bill is ₱500, customer pays ₱400
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Preview Summary -->
                        <div class="lg:hidden bg-white rounded-lg shadow-sm p-4" v-if="form.DISCOUNTTYPE && form.PARAMETER">
                            <h3 class="font-medium text-gray-900 mb-3">Quick Summary</h3>
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Name:</span>
                                    <span class="font-medium">{{ form.DISCOFFERNAME || 'Not set' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Type:</span>
                                    <span class="font-medium">{{ form.DISCOUNTTYPE }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Value:</span>
                                    <span class="font-medium text-blue-600">
                                        {{ form.DISCOUNTTYPE === 'PERCENTAGE' ? form.PARAMETER + '%' : '₱' + Number(form.PARAMETER).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </component>
</template>