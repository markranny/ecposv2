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
    DISCOUNTTYPE: '',
    GRABFOOD_PARAMETER: '',
    FOODPANDA_PARAMETER: '',
    FOODPANDAMALL_PARAMETER: '',
    GRABFOODMALL_PARAMETER: '',
    MANILAPRICE_PARAMETER: '',
    MALLPRICE_PARAMETER: ''
});

const discountTypes = [
    { value: 'FIXED', label: 'Fixed Amount', description: 'Fixed amount off per item (cannot exceed item price)' },
    { value: 'FIXEDTOTAL', label: 'Fixed Total', description: 'Fixed amount off the total bill' },
    { value: 'PERCENTAGE', label: 'Percentage', description: 'Percentage off the total amount' }
];

const platformFields = [
    { key: 'GRABFOOD_PARAMETER', label: 'GrabFood', color: 'green' },
    { key: 'FOODPANDA_PARAMETER', label: 'Foodpanda', color: 'pink' },
    { key: 'FOODPANDAMALL_PARAMETER', label: 'Foodpanda Mall', color: 'purple' },
    { key: 'GRABFOODMALL_PARAMETER', label: 'GrabFood Mall', color: 'blue' },
    { key: 'MANILAPRICE_PARAMETER', label: 'Manila Price', color: 'yellow' },
    { key: 'MALLPRICE_PARAMETER', label: 'Mall Price', color: 'indigo' }
];

// Reactive state
const previewAmount = ref(100);
const selectedPlatform = ref('default');
const showFloatingMenu = ref(false);
const showPreviewPanel = ref(false);
const showPlatformSettings = ref(false);

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

const getParameterForPlatform = (platform) => {
    switch (platform) {
        case 'grabfood':
            return form.GRABFOOD_PARAMETER || form.PARAMETER;
        case 'foodpanda':
            return form.FOODPANDA_PARAMETER || form.PARAMETER;
        case 'foodpandamall':
            return form.FOODPANDAMALL_PARAMETER || form.PARAMETER;
        case 'grabfoodmall':
            return form.GRABFOODMALL_PARAMETER || form.PARAMETER;
        case 'manila':
            return form.MANILAPRICE_PARAMETER || form.PARAMETER;
        case 'mall':
            return form.MALLPRICE_PARAMETER || form.PARAMETER;
        default:
            return form.PARAMETER;
    }
};

const discountPreview = computed(() => {
    const parameter = getParameterForPlatform(selectedPlatform.value);
    
    if (!parameter || !form.DISCOUNTTYPE || !previewAmount.value) {
        return null;
    }

    const originalAmount = previewAmount.value;
    const parameterValue = parseFloat(parameter);
    let discountAmount = 0;
    let finalAmount = originalAmount;

    switch (form.DISCOUNTTYPE) {
        case 'FIXED':
            discountAmount = Math.min(parameterValue, originalAmount);
            finalAmount = originalAmount - discountAmount;
            break;
        case 'FIXEDTOTAL':
            discountAmount = parameterValue;
            finalAmount = Math.max(0, originalAmount - discountAmount);
            break;
        case 'PERCENTAGE':
            discountAmount = (originalAmount * parameterValue) / 100;
            finalAmount = originalAmount - discountAmount;
            break;
    }

    return {
        originalAmount,
        discountAmount: discountAmount.toFixed(2),
        finalAmount: finalAmount.toFixed(2),
        platform: selectedPlatform.value,
        parameterUsed: parameterValue
    };
});

const platformOptions = computed(() => {
    const options = [{ value: 'default', label: 'Default', color: 'gray' }];
    
    platformFields.forEach(field => {
        const key = field.key.replace('_PARAMETER', '').toLowerCase();
        options.push({
            value: key,
            label: field.label,
            color: field.color
        });
    });
    
    return options;
});

// Validation rules
const getParameterError = (fieldName, value) => {
    if (!value) return null;
    
    const paramValue = parseFloat(value);
    if (isNaN(paramValue) || paramValue < 0) {
        return 'Value must be a positive number';
    }
    
    if (form.DISCOUNTTYPE === 'PERCENTAGE' && paramValue > 100) {
        return 'Percentage cannot exceed 100%';
    }
    
    return null;
};

const parameterError = computed(() => getParameterError('PARAMETER', form.PARAMETER));

const getPlatformParameterError = (fieldKey) => {
    return getParameterError(fieldKey, form[fieldKey]);
};

const hasPlatformSpecificValues = computed(() => {
    return platformFields.some(field => form[field.key] && form[field.key] !== '');
});

// Watch for form changes to update preview
watch(() => form.DISCOUNTTYPE, () => {
    form.PARAMETER = '';
    platformFields.forEach(field => {
        form[field.key] = '';
    });
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

const togglePlatformSettings = () => {
    showPlatformSettings.value = !showPlatformSettings.value;
    closeFloatingMenu();
};

const resetForm = () => {
    form.reset();
    form.clearErrors();
    closeFloatingMenu();
};

const copyDefaultToPlatforms = () => {
    if (form.PARAMETER) {
        platformFields.forEach(field => {
            form[field.key] = form.PARAMETER;
        });
    }
};

const clearPlatformValues = () => {
    platformFields.forEach(field => {
        form[field.key] = '';
    });
};

const getPlatformColorClass = (color) => {
    const colorMap = {
        green: 'bg-green-100 text-green-800 border-green-200',
        pink: 'bg-pink-100 text-pink-800 border-pink-200',
        purple: 'bg-purple-100 text-purple-800 border-purple-200',
        blue: 'bg-blue-100 text-blue-800 border-blue-200',
        yellow: 'bg-yellow-100 text-yellow-800 border-yellow-200',
        indigo: 'bg-indigo-100 text-indigo-800 border-indigo-200',
        gray: 'bg-gray-100 text-gray-800 border-gray-200'
    };
    return colorMap[color] || colorMap.gray;
};

</script>

<template>
    <Head title="Create New Discount" />

    <component :is="layoutComponent" active-tab="DISCOUNTS">
        <template v-slot:main>
            <div class="min-h-screen bg-gray-50">
                <!-- Mobile Header (Only visible on mobile) -->
                <div class="lg:hidden sticky top-0 z-30 bg-white border-b border-gray-200 px-4 py-3">
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
                                <h1 class="text-lg font-semibold text-gray-900">Create Discount</h1>
                                <p class="text-xs text-gray-600">Add new promotional offer</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <!-- Platform Settings Toggle -->
                            <button
                                v-if="form.DISCOUNTTYPE"
                                @click="togglePlatformSettings"
                                class="p-2 text-purple-600 hover:bg-purple-50 rounded-full transition-colors"
                                :class="{ 'bg-purple-100': hasPlatformSpecificValues }"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                                </svg>
                            </button>
                            <!-- Preview Toggle Button -->
                            <button
                                v-if="form.DISCOUNTTYPE"
                                @click="togglePreviewPanel"
                                class="p-2 text-blue-600 hover:bg-blue-50 rounded-full transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Desktop Header (Only visible on desktop) -->
                <div class="hidden lg:block p-6 bg-white rounded-lg shadow-sm mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Create New Discount</h1>
                            <p class="mt-1 text-sm text-gray-600">
                                Add a new discount offer for your store with platform-specific pricing
                            </p>
                            <div v-if="hasPlatformSpecificValues" class="mt-2 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                                    </svg>
                                    Platform-specific values configured
                                </span>
                            </div>
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
                         'mb-4 lg:mb-6 px-4 py-2 rounded-md mx-4 lg:mx-0',
                         flash.isSuccess ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                     ]">
                    {{ flash.message }}
                </div>

                <div class="p-4 lg:p-0">
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

                                <!-- Default Discount Value -->
                                <div v-if="form.DISCOUNTTYPE">
                                    <label for="parameter" class="block text-sm font-medium text-gray-700 mb-2">
                                        Default {{ parameterLabel }} *
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
                                    <p class="mt-1 text-xs text-gray-500">
                                        This will be used as the fallback value for all platforms
                                    </p>
                                </div>

                                <!-- Platform-Specific Values (Desktop Inline) -->
                                <div v-if="form.DISCOUNTTYPE && form.PARAMETER" class="hidden lg:block">
                                    <div class="flex items-center justify-between mb-3">
                                        <label class="block text-sm font-medium text-gray-700">
                                            Platform-Specific Values (Optional)
                                        </label>
                                        <div class="flex space-x-2">
                                            <button
                                                type="button"
                                                @click="copyDefaultToPlatforms"
                                                class="text-xs px-2 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors"
                                            >
                                                Copy Default
                                            </button>
                                            <button
                                                type="button"
                                                @click="clearPlatformValues"
                                                class="text-xs px-2 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors"
                                            >
                                                Clear All
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 gap-3">
                                        <div v-for="field in platformFields" :key="field.key" class="relative">
                                            <label :for="field.key" class="block text-xs font-medium text-gray-600 mb-1">
                                                {{ field.label }}
                                            </label>
                                            <div class="relative">
                                                <input
                                                    :id="field.key"
                                                    v-model="form[field.key]"
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    :max="isPercentage ? 100 : undefined"
                                                    :placeholder="`Override for ${field.label}`"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                    :class="{ 'border-red-500': form.errors[field.key] || getPlatformParameterError(field.key) }"
                                                >
                                                <div v-if="isPercentage" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-xs">%</span>
                                                </div>
                                                <div v-else class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 text-xs">₱</span>
                                                </div>
                                            </div>
                                            <p v-if="form.errors[field.key]" class="mt-1 text-xs text-red-600">
                                                {{ form.errors[field.key] }}
                                            </p>
                                            <p v-else-if="getPlatformParameterError(field.key)" class="mt-1 text-xs text-red-600">
                                                {{ getPlatformParameterError(field.key) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mobile Preview Summary (Only visible on mobile) -->
                                <div v-if="form.DISCOUNTTYPE && form.PARAMETER && discountPreview" 
                                     class="lg:hidden p-4 bg-gradient-to-r from-green-50 to-blue-50 rounded-lg border border-green-200">
                                    <h3 class="text-sm font-medium text-gray-900 mb-3 flex items-center justify-between">
                                        Quick Preview
                                        <span :class="[
                                            'px-2 py-1 text-xs font-medium rounded-full',
                                            getPlatformColorClass(platformOptions.find(p => p.value === selectedPlatform)?.color || 'gray')
                                        ]">
                                            {{ platformOptions.find(p => p.value === selectedPlatform)?.label || 'Default' }}
                                        </span>
                                    </h3>
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

                        <!-- Desktop Preview Section (Only visible on desktop) -->
                        <div class="hidden lg:block space-y-6">
                            <!-- Discount Preview -->
                            <div class="bg-white rounded-lg shadow-sm p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Preview</h2>
                                
                                <div v-if="!form.DISCOUNTTYPE" class="text-center py-8 text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-base">Select a discount type to see preview</p>
                                </div>

                                <div v-else class="space-y-4">
                                    <!-- Platform Selector -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">
                                            Preview Platform
                                        </label>
                                        <select
                                            v-model="selectedPlatform"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                        >
                                            <option v-for="option in platformOptions" :key="option.value" :value="option.value">
                                                {{ option.label }}
                                            </option>
                                        </select>
                                    </div>

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
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-sm font-medium text-gray-700">Platform:</span>
                                            <span :class="[
                                                'px-2 py-1 text-xs font-medium rounded-full',
                                                getPlatformColorClass(platformOptions.find(p => p.value === selectedPlatform)?.color || 'gray')
                                            ]">
                                                {{ platformOptions.find(p => p.value === selectedPlatform)?.label || 'Default' }}
                                            </span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                            <span class="text-sm text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                            <span class="text-sm text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-gray-700">Using Value:</span>
                                            <span class="text-sm text-gray-900">
                                                {{ isPercentage ? discountPreview.parameterUsed + '%' : formatCurrency(discountPreview.parameterUsed) }}
                                            </span>
                                        </div>
                                        <hr class="border-gray-300">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-bold text-gray-900">Final Amount:</span>
                                            <span class="text-lg font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Platform Configuration Guide -->
                            <div class="bg-white rounded-lg shadow-sm p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Platform Configuration</h2>
                                
                                <div class="space-y-4">
                                    <div v-for="field in platformFields" :key="field.key" 
                                         class="flex items-center justify-between p-3 border rounded-md"
                                         :class="form[field.key] ? 'border-blue-200 bg-blue-50' : 'border-gray-200'">
                                        <div class="flex items-center space-x-3">
                                            <span :class="[
                                                'inline-block w-3 h-3 rounded-full',
                                                form[field.key] ? 'bg-blue-500' : 'bg-gray-300'
                                            ]"></span>
                                            <span class="text-sm font-medium text-gray-900">{{ field.label }}</span>
                                        </div>
                                        <span class="text-sm text-gray-600">
                                            {{ form[field.key] ? 
                                                (isPercentage ? form[field.key] + '%' : '₱' + Number(form[field.key]).toFixed(2)) : 
                                                'Using default' 
                                            }}
                                        </span>
                                    </div>
                                </div>

                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                                    <p class="text-xs text-yellow-800">
                                        <strong>Note:</strong> Platform-specific values override the default value. 
                                        If not set, the default value will be used for that platform.
                                    </p>
                                </div>
                            </div>

                            <!-- Discount Type Guide -->
                            <div class="bg-white rounded-lg shadow-sm p-6">
                                <h2 class="text-lg font-medium text-gray-900 mb-4">Discount Types Guide</h2>
                                
                                <div class="space-y-4">
                                    <div v-for="type in discountTypes" :key="type.value" 
                                         class="p-4 border rounded-md"
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
                        </div>
                    </div>
                </div>

                <!-- Mobile Floating Action Menu (Only visible on mobile) -->
                <div class="lg:hidden fixed bottom-6 right-6 z-40">
                    <!-- Menu Options -->
                    <div v-if="showFloatingMenu" class="absolute bottom-16 right-0 bg-white rounded-lg shadow-lg border border-gray-200 py-2 w-56 transform transition-all duration-200 ease-out">
                        <!-- Platform Settings -->
                        <button
                            v-if="form.DISCOUNTTYPE && form.PARAMETER"
                            @click="togglePlatformSettings"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4" />
                            </svg>
                            Platform Settings
                            <span v-if="hasPlatformSpecificValues" class="ml-auto w-2 h-2 bg-purple-500 rounded-full"></span>
                        </button>

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

                        <!-- Reset Form -->
                        <button
                            @click="resetForm"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reset Form
                        </button>

                        <!-- View Discounts -->
                        <Link
                            :href="route('discountsv2.index')"
                            @click="closeFloatingMenu"
                            class="w-full px-4 py-3 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center transition-colors"
                        >
                            <svg class="h-4 w-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            View All Discounts
                        </Link>

                        <!-- Help -->
                        <div class="border-t border-gray-200 my-2"></div>
                        <div class="px-4 py-2">
                            <p class="text-xs text-gray-500 mb-2">Need Help?</p>
                            <p class="text-xs text-gray-600 leading-relaxed">
                                Configure platform-specific pricing to offer different discounts across delivery platforms.
                            </p>
                        </div>
                    </div>

                    <!-- Main Floating Button -->
                    <button
                        @click="toggleFloatingMenu"
                        class="bg-blue-600 hover:bg-blue-700 text-white rounded-full p-4 shadow-lg transition-all duration-200 ease-out transform hover:scale-105 relative"
                        :class="{ 'rotate-45': showFloatingMenu }"
                    >
                        <!-- Platform Settings indicator -->
                        <div v-if="hasPlatformSpecificValues && !showFloatingMenu" class="absolute -top-1 -right-1 w-4 h-4 bg-purple-500 rounded-full animate-pulse border-2 border-white"></div>
                        
                        <svg v-if="!showFloatingMenu" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Mobile Platform Settings Modal -->
                <div v-if="showPlatformSettings" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end justify-center p-4">
                    <div class="bg-white rounded-t-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
                        <!-- Header -->
                        <div class="sticky top-0 bg-white px-6 py-4 border-b border-gray-200 rounded-t-xl">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900">Platform Settings</h3>
                                <button
                                    @click="showPlatformSettings = false"
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
                            <!-- Quick Actions -->
                            <div class="flex space-x-2">
                                <button
                                    @click="copyDefaultToPlatforms"
                                    class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 text-sm rounded-lg hover:bg-blue-200 transition-colors"
                                >
                                    Copy Default to All
                                </button>
                                <button
                                    @click="clearPlatformValues"
                                    class="flex-1 px-3 py-2 bg-gray-100 text-gray-700 text-sm rounded-lg hover:bg-gray-200 transition-colors"
                                >
                                    Clear All
                                </button>
                            </div>

                            <!-- Platform Fields -->
                            <div class="space-y-4">
                                <div v-for="field in platformFields" :key="field.key">
                                    <label :for="`mobile-${field.key}`" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ field.label }}
                                    </label>
                                    <div class="relative">
                                        <input
                                            :id="`mobile-${field.key}`"
                                            v-model="form[field.key]"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :max="isPercentage ? 100 : undefined"
                                            :placeholder="`Override for ${field.label}`"
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            :class="{ 'border-red-500': form.errors[field.key] || getPlatformParameterError(field.key) }"
                                        >
                                        <div v-if="isPercentage" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-base">%</span>
                                        </div>
                                        <div v-else class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                            <span class="text-gray-500 text-base">₱</span>
                                        </div>
                                    </div>
                                    <p v-if="form.errors[field.key]" class="mt-1 text-sm text-red-600">
                                        {{ form.errors[field.key] }}
                                    </p>
                                    <p v-else-if="getPlatformParameterError(field.key)" class="mt-1 text-sm text-red-600">
                                        {{ getPlatformParameterError(field.key) }}
                                    </p>
                                    <p v-else class="mt-1 text-xs text-gray-500">
                                        {{ form[field.key] ? 'Custom value set' : 'Will use default value' }}
                                    </p>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-xs text-yellow-800">
                                    <strong>Note:</strong> Platform-specific values override the default value. 
                                    Leave empty to use the default value for that platform.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Preview Panel Modal -->
                <div v-if="showPreviewPanel" class="lg:hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-end justify-center p-4">
                    <div class="bg-white rounded-t-xl w-full max-w-md max-h-[80vh] overflow-y-auto">
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
                            <!-- Platform Selector -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Preview Platform
                                </label>
                                <select
                                    v-model="selectedPlatform"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option v-for="option in platformOptions" :key="option.value" :value="option.value">
                                        {{ option.label }}
                                    </option>
                                </select>
                            </div>

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
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-sm font-medium text-gray-700">Testing Platform:</span>
                                    <span :class="[
                                        'px-3 py-1 text-xs font-medium rounded-full',
                                        getPlatformColorClass(platformOptions.find(p => p.value === selectedPlatform)?.color || 'gray')
                                    ]">
                                        {{ platformOptions.find(p => p.value === selectedPlatform)?.label || 'Default' }}
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Original Amount:</span>
                                    <span class="text-lg font-semibold text-gray-900">{{ formatCurrency(discountPreview.originalAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Discount Amount:</span>
                                    <span class="text-lg font-semibold text-red-600">-{{ formatCurrency(discountPreview.discountAmount) }}</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">Using Value:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ isPercentage ? discountPreview.parameterUsed + '%' : formatCurrency(discountPreview.parameterUsed) }}
                                    </span>
                                </div>
                                <hr class="border-gray-300">
                                <div class="flex justify-between items-center">
                                    <span class="text-base font-bold text-gray-900">Customer Pays:</span>
                                    <span class="text-2xl font-bold text-green-600">{{ formatCurrency(discountPreview.finalAmount) }}</span>
                                </div>
                            </div>

                            <!-- Platform Quick Switch -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">Quick Platform Switch</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        v-for="option in platformOptions.slice(0, 6)"
                                        :key="option.value"
                                        @click="selectedPlatform = option.value"
                                        :class="[
                                            'px-3 py-2 text-xs rounded-lg transition-colors border',
                                            selectedPlatform === option.value ? 
                                                getPlatformColorClass(option.color) + ' border-current' : 
                                                'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200'
                                        ]"
                                    >
                                        {{ option.label }}
                                    </button>
                                </div>
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