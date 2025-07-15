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
    DISCOUNTTYPE: props.discount.DISCOUNTTYPE || '',
    GRABFOOD_PARAMETER: props.discount.GRABFOOD_PARAMETER || '',
    FOODPANDA_PARAMETER: props.discount.FOODPANDA_PARAMETER || '',
    FOODPANDAMALL_PARAMETER: props.discount.FOODPANDAMALL_PARAMETER || '',
    GRABFOODMALL_PARAMETER: props.discount.GRABFOODMALL_PARAMETER || '',
    MANILAPRICE_PARAMETER: props.discount.MANILAPRICE_PARAMETER || '',
    MALLPRICE_PARAMETER: props.discount.MALLPRICE_PARAMETER || ''
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
const showDeleteModal = ref(false);
const showChangesPanel = ref(false);
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

// Check if form has changes
const formHasChanges = computed(() => {
    return form.DISCOFFERNAME !== props.discount.DISCOFFERNAME ||
           form.PARAMETER != props.discount.PARAMETER ||
           form.DISCOUNTTYPE !== props.discount.DISCOUNTTYPE ||
           form.GRABFOOD_PARAMETER != props.discount.GRABFOOD_PARAMETER ||
           form.FOODPANDA_PARAMETER != props.discount.FOODPANDA_PARAMETER ||
           form.FOODPANDAMALL_PARAMETER != props.discount.FOODPANDAMALL_PARAMETER ||
           form.GRABFOODMALL_PARAMETER != props.discount.GRABFOODMALL_PARAMETER ||
           form.MANILAPRICE_PARAMETER != props.discount.MANILAPRICE_PARAMETER ||
           form.MALLPRICE_PARAMETER != props.discount.MALLPRICE_PARAMETER;
});

const hasPlatformSpecificValues = computed(() => {
    return platformFields.some(field => form[field.key] && form[field.key] !== '');
});

const originalHasPlatformValues = computed(() => {
    return platformFields.some(field => props.discount[field.key] && props.discount[field.key] !== '');
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
    form.GRABFOOD_PARAMETER = props.discount.GRABFOOD_PARAMETER;
    form.FOODPANDA_PARAMETER = props.discount.FOODPANDA_PARAMETER;
    form.FOODPANDAMALL_PARAMETER = props.discount.FOODPANDAMALL_PARAMETER;
    form.GRABFOODMALL_PARAMETER = props.discount.GRABFOODMALL_PARAMETER;
    form.MANILAPRICE_PARAMETER = props.discount.MANILAPRICE_PARAMETER;
    form.MALLPRICE_PARAMETER = props.discount.MALLPRICE_PARAMETER;
    form.clearErrors();
    closeFloatingMenu();
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(value);
};

const formatDiscountValue = (discount, platform = null) => {
    if (!discount) return '';
    
    let parameter;
    if (platform) {
        const platformKey = platform.toUpperCase() + '_PARAMETER';
        parameter = discount[platformKey] || discount.PARAMETER;
    } else {
        parameter = discount.PARAMETER;
    }
    
    switch (discount.DISCOUNTTYPE) {
        case 'PERCENTAGE':
            return `${parameter}%`;
        case 'FIXED':
        case 'FIXEDTOTAL':
            return `₱${Number(parameter).toFixed(2)}`;
        default:
            return parameter;
    }
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

const togglePlatformSettings = () => {
    showPlatformSettings.value = !showPlatformSettings.value;
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

