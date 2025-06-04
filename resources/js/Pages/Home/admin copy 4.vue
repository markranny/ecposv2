<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';
import AdminPanel from "@/Layouts/AdminPanel.vue";
import Store from "@/Components/Svgs/Store.vue";
import Peso from "@/Components/Svgs/Peso.vue";
import Receipt from "@/Components/Svgs/Receipt.vue";
import Income from "@/Components/Svgs/Income.vue";
import Discounts from "@/Components/Svgs/Discounts.vue";
import { CubeIcon } from '@heroicons/vue/24/outline';
import MultiSelectDropdown from "@/Components/MultiSelect/MultiSelectDropdown.vue";

// Register Chart.js components
Chart.register(...registerables);

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const props = defineProps({
    username: {
        type: String,
        default: 'User'
    },
    announcements: {
        type: Array,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
        default: () => ({
            totalGross: 0,
            totalNetsales: 0,
            totalDiscount: 0,
            totalCost: 0,
            totalVat: 0,
            totalVatableSales: 0,
            paymentBreakdown: {
                cash: 0,
                gcash: 0,
                paymaya: 0,
                card: 0,
                loyaltyCard: 0,
                foodPanda: 0,
                grabFood: 0
            }
        })
    }
});

const localMetrics = ref(props.initialMetrics);
const dateRangeError = ref('');

const topProductsRef = ref(null);
const bottomProductsRef = ref(null);
let topProductsChart = null;
let bottomProductsChart = null;

const stores = ref([]);
const selectedStores = ref([]);

const fetchStores = async () => {
  try {
    const response = await axios.get(route('get.stores'));
    stores.value = response.data;
    selectedStores.value = response.data;  // By default, all stores are selected
  } catch (error) {
    console.error('Error fetching stores:', error);
  }
};

// State variables
const hoveredCard = ref(null);
const isVisible = ref(false);
const chartRef = ref(null);
const topBottomProductsRef = ref(null);
let paymentChart = null;
let topBottomProductsChart = null;

const selectedDateRange = ref({
    start_date: '2024-11-25',  // Default start date
    end_date: new Date().toISOString().split('T')[0]  // Today's date
});

// New: Date Range Validation Computed Property
const isValidDateRange = computed(() => {
    const startDate = new Date(selectedDateRange.value.start_date);
    const endDate = new Date(selectedDateRange.value.end_date);
    const today = new Date();

    return (
        startDate <= endDate && 
        startDate <= today &&
        endDate <= today
    );
});


// Currency formatting utility
const formatCurrency = (value) => {
    const numValue = Number(value);
    return new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    }).format(isNaN(numValue) ? 0 : numValue);
};

// Metrics cards computation (continued)
const metricsCards = computed(() => {
    /* const metrics = props.metrics || {}; */
    const metrics = localMetrics.value || {};
    return [
        {
            title: "TOTAL GROSS",
            value: formatCurrency(metrics.totalGross),
            icon: Discounts,
            color: "text-blue-600",
            bgColor: "bg-blue-100",
            description: "Total revenue before deductions"
        },
        {
            title: "TOTAL NETSALES",
            value: formatCurrency(metrics.totalNetsales),
            icon: Income,
            color: "text-green-600",
            bgColor: "bg-green-100",
            description: "Revenue after all deductions"
        },
        {
            title: "TOTAL DISCOUNT",
            value: formatCurrency(metrics.totalDiscount),
            icon: Discounts,
            color: "text-amber-600",
            bgColor: "bg-amber-100",
            description: "Sum of all applied discounts"
        },
        {
            title: "TOTAL COST",
            value: formatCurrency(metrics.totalCost),
            icon: CubeIcon,
            color: "text-purple-600",
            bgColor: "bg-purple-100",
            description: "Aggregate cost of goods sold"
        },
        {
            title: "TOTAL VAT",
            value: formatCurrency(metrics.totalVat),
            icon: Receipt,
            color: "text-pink-600",
            bgColor: "bg-pink-100",
            description: "Value Added Tax collected"
        },
        {
            title: "VATABLE SALES",
            value: formatCurrency(metrics.totalVatableSales),
            icon: Peso,
            color: "text-indigo-600",
            bgColor: "bg-indigo-100",
            description: "Sales subject to VAT"
        }
    ];
});

const initializePaymentChart = () => {
    if (!chartRef.value) return;
    
    const ctx = chartRef.value.getContext('2d');
    
    if (paymentChart) {
        paymentChart.destroy();
    }

    // Use localMetrics instead of props.metrics
    const paymentBreakdown = localMetrics.value?.paymentBreakdown || {};
    const labels = Object.keys(paymentBreakdown).map(label => 
        label.replace(/([A-Z])/g, ' $1').trim()
    );
    const data = Object.values(paymentBreakdown).map(value => 
        Number(value) || 0
    );

    paymentChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels,
            datasets: [{
                data,
                backgroundColor: [
                    '#3B82F6', '#10B981', '#F43F5E', 
                    '#6366F1', '#8B5CF6', '#EC4899', 
                    '#F97316'
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            // Changed to show actual value instead of percentage
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.parsed;
                            const percentage = ((value / total) * 100).toFixed(2);
                            return `${context.label}: ${value.toFixed(2)} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
};

const fetchTopBottomProducts = async () => {
  try {
    const startDate = selectedDateRange.value.start_date || '2000-01-01';
    
    const payload = {
      start_date: startDate,
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
          store.NAME || (typeof store === 'object' ? store.NAME : store)
      )
    };

    const response = await axios.post(route('get.top.bottom.products'), payload);

    const topProducts = Array.isArray(response?.data?.topProducts) 
      ? response.data.topProducts 
      : [];
    const bottomProducts = Array.isArray(response?.data?.bottomProducts) 
      ? response.data.bottomProducts 
      : [];

    initializeTopBottomProductsChart(topProducts, bottomProducts);
  } catch (error) {
    console.error('Error fetching top/bottom products:', error);
    initializeTopBottomProductsChart([], []);
  }
};

// Initialize top and bottom products chart
const initializeTopBottomProductsChart = (topProducts, bottomProducts) => {
    if (!topBottomProductsRef.value) return;
    
    const ctx = topBottomProductsRef.value.getContext('2d');
    
    if (topBottomProductsChart) {
        topBottomProductsChart.destroy();
    }

    // Prepare data for chart
    const topProductNames = topProducts.slice(0, 10).map(p => p.itemname);
    const bottomProductNames = bottomProducts.slice(0, 10).map(p => p.itemname);
    const topProductQuantities = topProducts.slice(0, 10).map(p => p.total_quantity);
    const bottomProductQuantities = bottomProducts.slice(0, 10).map(p => p.total_quantity);

    topBottomProductsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [...topProductNames, ...bottomProductNames],
            datasets: [
                {
                    label: 'Top 10 Products (Quantity)',
                    data: topProductQuantities,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Bottom 10 Products (Quantity)',
                    data: [
                        ...Array(10).fill(0),
                        ...bottomProductQuantities
                    ],
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed.y;
                            const datasetLabel = context.dataset.label;
                            return `${datasetLabel}: ${value.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Quantity Sold'
                    }
                }
            }
        }
    });
};

const fetchMetrics = async () => {
  try {
    console.log('Selected Stores:', selectedStores.value);
    
    const payload = {
      start_date: selectedDateRange.value.start_date || '2000-01-01',
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    console.log('Payload for metrics:', payload);

    const response = await axios.post(route('get.metrics'), payload);
    
    console.log('Metrics response:', response.data);
    
    localMetrics.value = response.data || props.initialMetrics;
    initializePaymentChart();
  } catch (error) {
    console.error('Error fetching metrics:', error);
  }
};

watch([
  () => selectedDateRange.value.start_date, 
  () => selectedDateRange.value.end_date, 
  () => JSON.stringify(selectedStores.value)  // Use JSON stringify to detect array changes
], () => {
  if (isValidDateRange.value) {
    dateRangeError.value = '';
    fetchMetrics();
    fetchTopBottomProducts();
  } else {
    dateRangeError.value = 'Invalid date range. Please check your selections.';
  }
});

onMounted(() => {
  fetchStores();
  fetchMetrics();
  fetchTopBottomProducts();
});
</script>

<template>
    <AdminPanel active-tab="DASHBOARD">
        <template #main>
            <div class="min-h-screen bg-gray-50 p-6">
                <!-- Enhanced Filtering Section -->
                <div class="mb-6 bg-white rounded-xl shadow-md p-4">
                    <div class="flex items-center space-x-4">
                        <!-- Date Range Inputs with Validation -->
                        <div class="flex flex-col">
                            <div class="flex items-center space-x-2">
                                <label class="text-gray-700">Start Date:</label>
                                <input 
                                    type="date" 
                                    v-model="selectedDateRange.start_date" 
                                    class="border rounded px-2 py-1"
                                    :class="{'border-red-500': dateRangeError}"
                                >
                                <label class="text-gray-700">End Date:</label>
                                <input 
                                    type="date" 
                                    v-model="selectedDateRange.end_date" 
                                    class="border rounded px-2 py-1"
                                    :class="{'border-red-500': dateRangeError}"
                                >
                            </div>
                            <!-- Date Range Error Message -->
                            <p 
                                v-if="dateRangeError" 
                                class="text-red-500 text-sm mt-1"
                            >
                                {{ dateRangeError }}
                            </p>
                        </div>

                        <!-- Store Multi-Select -->
                        <div class="flex items-center space-x-2">
                            <label class="text-gray-700">Stores:</label>
                            <MultiSelectDropdown
                                :modelValue="selectedStores"
                                @update:modelValue="selectedStores = $event"
                                :options="stores"
                                :multiple="true"
                            />
                        </div>
                    </div>
                </div>
                <!-- Welcome Section -->
                <div 
                    class="mb-8 transform transition-all duration-500"
                    :class="[isVisible ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0']"
                >
                    <h1 class="text-3xl font-bold text-gray-800">
                        Welcome back, {{ username }}! 👋
                    </h1>
                    <p class="text-gray-600 mt-2">
                        Here's your business overview for today
                    </p>
                </div>

                <!-- Metrics Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                    <TransitionGroup
                        enter-active-class="transition-all duration-500 ease-out"
                        enter-from-class="opacity-0 transform translate-y-4"
                        enter-to-class="opacity-100 transform translate-y-0"
                        move-class="transition-all duration-300"
                    >
                        <div
                            v-for="(card, index) in metricsCards"
                            :key="card.title"
                            class="metric-card"
                            :style="{ transitionDelay: `${index * 100}ms` }"
                            @mouseenter="hoveredCard = index"
                            @mouseleave="hoveredCard = null"
                        >
                            <div 
                                class="bg-white rounded-xl shadow-md transition-all duration-300 transform hover:scale-105"
                                :class="{ 'shadow-lg': hoveredCard === index }"
                            >
                                <div class="p-6">
                                    <div class="flex items-center justify-between">
                                        <div :class="[card.bgColor, 'p-3 rounded-lg']">
                                            <component 
                                                :is="card.icon" 
                                                :class="[card.color, 'w-6 h-6']"
                                            />
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <p class="text-sm font-medium text-gray-600">
                                            {{ card.title }}
                                        </p>
                                        <p :class="[card.color, 'text-2xl font-bold mt-2']">
                                            {{ card.value }}
                                        </p>
                                        <p class="text-sm text-gray-500 mt-2">
                                            {{ card.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>

                <!-- Charts and Announcements Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Payment Methods Chart -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Payment Methods Distribution
                        </h3>
                        <div class="h-[400px]">
                            <canvas ref="chartRef"></canvas>
                        </div>
                    </div>

                    <!-- Announcements Section -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Recent Announcements
                        </h3>
                        <div class="space-y-4 max-h-[400px] overflow-y-auto">
                            <template v-if="announcements.length">
                                <div
                                    v-for="(announcement, index) in announcements"
                                    :key="index"
                                    class="p-4 border border-gray-100 rounded-lg hover:border-blue-500 transition-all duration-300"
                                >
                                    <h4 class="font-semibold text-gray-800">
                                        {{ announcement.title }}
                                    </h4>
                                    <p class="text-gray-600 mt-2">
                                        {{ announcement.description }}
                                    </p>
                                </div>
                            </template>
                            <p v-else class="text-gray-500 text-center py-4">
                                No announcements available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Top/Bottom Products Section -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Top & Bottom Products Analysis
                        </h3>
                        <div class="flex space-x-4">
                            <input 
                                type="date" 
                                v-model="selectedDateRange.start_date" 
                                class="border rounded px-2 py-1"
                            >
                            <input 
                                type="date" 
                                v-model="selectedDateRange.end_date" 
                                class="border rounded px-2 py-1"
                            >
                        </div>
                    </div>
                    <div class="h-[500px]">
                        <canvas ref="topBottomProductsRef"></canvas>
                    </div>
                </div>
            </div>
        </template>
    </AdminPanel>
</template>

<style scoped>
.metric-card {
    @apply transform transition-all duration-300;
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.metric-card {
    animation: slideUp 0.5s ease-out forwards;
}

.metric-card:nth-child(1) { animation-delay: 0.1s; }
.metric-card:nth-child(2) { animation-delay: 0.2s; }
.metric-card:nth-child(3) { animation-delay: 0.3s; }
.metric-card:nth-child(4) { animation-delay: 0.4s; }
.metric-card:nth-child(5) { animation-delay: 0.5s; }
.metric-card:nth-child(6) { animation-delay: 0.6s; }
</style>