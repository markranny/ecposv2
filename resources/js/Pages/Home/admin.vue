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
import WasteAnalysis from '../Home/wasteanalysis.vue';
import ProductAnalysis from '../Home/ProductAnalysis.vue';

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

const monthlySalesRef = ref(null);
let monthlySalesChart = null;

const topWasteRef = ref(null);
let topWasteChart = null;

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
    start_date: new Date().toISOString().split('T')[0],  // Default start date
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

/* const initializePaymentChart = () => {
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
}; */

const initializePaymentChart = () => {
    if (!chartRef.value) return;
    
    const ctx = chartRef.value.getContext('2d');
    
    if (paymentChart) {
        paymentChart.destroy();
    }

    // Use localMetrics instead of props.metrics
    const paymentBreakdown = localMetrics.value?.paymentBreakdown || {};
    const labels = Object.keys(paymentBreakdown).map(label => {
        const value = paymentBreakdown[label];
        const formattedLabel = label.replace(/([A-Z])/g, ' $1').trim();
        const formattedValue = formatCurrency(value);
        return `${formattedLabel} (${formattedValue})`;
    });
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

const fetchMonthlySales = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date || '2000-01-01',
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.monthly.sales'), payload);
    initializeMonthlySalesChart(response.data);
  } catch (error) {
    console.error('Error fetching monthly sales:', error);
  }
};

const initializeMonthlySalesChart = (monthlySalesData) => {
  if (!monthlySalesRef.value) return;
  
  const ctx = monthlySalesRef.value.getContext('2d');
  
  if (monthlySalesChart) {
    monthlySalesChart.destroy();
  }

  const labels = monthlySalesData.map(item => item.label);
  const salesData = monthlySalesData.map(item => item.total_sales);

  monthlySalesChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels,
      datasets: [{
        label: 'Monthly Sales',
        data: salesData,
        borderColor: 'rgba(75, 192, 192, 1)',
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        tension: 0.1,
        fill: true
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              return formatCurrency(context.parsed.y);
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Sales Amount'
          },
          ticks: {
            callback: function(value) {
              return formatCurrency(value);
            }
          }
        }
      }
    }
  });
};

const fetchTopWastes = async () => {
  try {
    const payload = {
      start_date: selectedDateRange.value.start_date || '2000-01-01',
      end_date: selectedDateRange.value.end_date,
      stores: selectedStores.value.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.top.wastes'), payload);

    const topWastes = Array.isArray(response?.data) 
      ? response.data 
      : [];

    initializeTopWasteChart(topWastes);
  } catch (error) {
    console.error('Error fetching top wastes:', error);
    initializeTopWasteChart([]);
  }
};

const initializeTopWasteChart = (topWastes) => {
    if (!topWasteRef.value) return;
    
    const ctx = topWasteRef.value.getContext('2d');
    
    if (topWasteChart) {
        topWasteChart.destroy();
    }

    // Prepare data for chart
    const wasteItemNames = topWastes.slice(0, 10).map(w => w.itemname || w.ITEMID);
    const wasteQuantities = topWastes.slice(0, 10).map(w => Math.abs(Number(w.ADJUSTMENT || 0)));
    const wasteCosts = topWastes.slice(0, 10).map(w => Math.abs(Number(w.SALESAMOUNT || 0)));

    topWasteChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: wasteItemNames,
            datasets: [
                {
                    label: 'Waste Quantity',
                    data: wasteQuantities,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Waste Cost',
                    data: wasteCosts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
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
                            return `${datasetLabel}: ${context.dataset.label === 'Waste Cost' ? formatCurrency(value) : value.toFixed(2)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Waste Quantity / Cost'
                    },
                    ticks: {
                        callback: function(value, index, ticks) {
                            return this.getLabelForValue(value);
                        }
                    }
                }
            }
        }
    });
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
    fetchMonthlySales();
    fetchTopWastes();
  } else {
    dateRangeError.value = 'Invalid date range. Please check your selections.';
  }
});

onMounted(() => {
  fetchStores();
  fetchMetrics();
  fetchTopBottomProducts();
  fetchMonthlySales();
  fetchTopWastes();
});
</script>

<template>
    <AdminPanel active-tab="DASHBOARD">
        <template #main>
            <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 p-6 space-y-6 relative overflow-hidden">
                <!-- Subtle Background Particles/Overlay -->
                <div class="absolute inset-0 opacity-10 bg-pattern z-0"></div>

                <!-- Enhanced Filtering Section with Elegant Design -->
                <div 
                    class="relative z-10 bg-white/80 rounded-3xl shadow-2xl border border-blue-100/50 p-6 transform transition-all duration-500 hover:scale-[1.02]"
                >
                    <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                        <!-- Date Range and Welcome Section -->
                        <div class="flex flex-col w-full md:w-auto">
                            <div class="flex items-center space-x-4">
                                <h1 
                                    class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-3 animate-pulse-slow"
                                >
                                    Welcome back, {{ username }}! 👋
                                </h1>
                                <div class="flex space-x-4">
                                    <div class="flex flex-col">
                                        <label class="text-sm font-medium text-gray-600 mb-1">Start Date</label>
                                        <input 
                                            type="date" 
                                            v-model="selectedDateRange.start_date" 
                                            class="border-2 border-blue-200 rounded-xl px-4 py-2 focus:ring-4 focus:ring-blue-300/50 transition-all duration-300"
                                            :class="{'border-red-500': dateRangeError}"
                                        >
                                    </div>
                                    <div class="flex flex-col">
                                        <label class="text-sm font-medium text-gray-600 mb-1">End Date</label>
                                        <input 
                                            type="date" 
                                            v-model="selectedDateRange.end_date" 
                                            class="border-2 border-blue-200 rounded-xl px-4 py-2 focus:ring-4 focus:ring-blue-300/50 transition-all duration-300"
                                            :class="{'border-red-500': dateRangeError}"
                                        >
                                    </div>
                                </div>
                            </div>
                            <!-- Error Message with Animation -->
                            <div 
                                v-if="dateRangeError" 
                                class="text-red-500 text-xs mt-2 flex items-center space-x-2 animate-bounce"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <span>{{ dateRangeError }}</span>
                            </div>
                        </div>

                        <!-- Store Multi-Select with Enhanced Design -->
                        <div class="w-full md:w-auto">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Stores</label>
                            <MultiSelectDropdown
                                :modelValue="selectedStores"
                                @update:modelValue="selectedStores = $event"
                                :options="stores"
                                :multiple="true"
                                class="w-full md:w-64"
                            />
                        </div>
                    </div>
                </div>

                <!-- Metrics Grid with Enhanced Hover and Transition Effects -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    <TransitionGroup enter-active-class="transition-all duration-700 ease-out" enter-from-class="opacity-0 translate-y-10" enter-to-class="opacity-100 translate-y-0">
                        <div v-for="(card, index) in metricsCards" :key="card.title" class="transform transition-all duration-300 hover:-translate-y-2 hover:scale-105 metric-card">
                            <div class="bg-white/90 rounded-3xl shadow-xl border border-blue-100/50 p-6 space-y-4 relative overflow-hidden group">
                                <!-- Gradient Background Overlay -->
                                <div class="absolute inset-0 opacity-20 group-hover:opacity-30 transition-all duration-300" :style="{background: `linear-gradient(135deg, ${card.color}, transparent)`}"></div>
                                
                                <div class="flex justify-between items-center relative z-10">
                                    <div :class="[card.bgColor, 'p-3 rounded-xl shadow-md transform transition-all group-hover:rotate-12']">
                                        <component :is="card.icon" :class="[card.color, 'w-7 h-7']" />
                                    </div>
                                </div>
                                <div class="relative z-10">
                                    <p class="text-xs uppercase tracking-wider text-gray-500 mb-2">
                                        {{ card.title }}
                                    </p>
                                    <p :class="[card.color, 'text-3xl font-bold']">
                                        {{ card.value }}
                                    </p>
                                    <p class="text-sm text-gray-400 mt-2">
                                        {{ card.description }}
                                    </p>
                                </div>
                                <!-- Bubble Hover Effect -->
                                <div class="absolute top-0 left-0 w-24 h-24 bg-opacity-20 rounded-full bg-blue-300 opacity-0 group-hover:opacity-100 transition-all duration-300"></div>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>

                <!-- Charts and Announcements Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Payment Methods Chart with Modern Design -->
                    <div class="bg-white/80  rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-blue-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm4 5a.5.5 0 11-1 0 .5.5 0 011 0z" />
                            </svg>
                            Payment Methods Distribution
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="chartRef" class="w-full h-full"></canvas>
                        </div>
                    </div>

                    <!-- Announcements Section with Enhanced Design -->
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-green-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            Recent Announcements
                        </h3>
                        <div class="max-h-[400px] overflow-y-auto custom-scrollbar relative z-10">
                            <template v-if="announcements.length">
                                <div
                                    v-for="(announcement, index) in announcements"
                                    :key="index"
                                    class="bg-white/60 p-4 rounded-xl border border-blue-100 mb-4 hover:border-blue-300 transition-all duration-300 hover:shadow-lg"
                                >
                                    <h4 class="font-semibold text-gray-800 mb-2">
                                        {{ announcement.title }}
                                    </h4>
                                    <p class="text-gray-600 text-sm">
                                        {{ announcement.description }}
                                    </p>
                                </div>
                            </template>
                            <p v-else class="text-gray-500 text-center py-4 italic">
                                No announcements available
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Remaining sections would follow the same design pattern -->
                <!-- Top/Bottom Products Section -->
                <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.01] transition-all duration-300">
                    <div class="absolute inset-0 opacity-10 bg-purple-100"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                        Popular Products & Products Under Monitoring
                    </h3>
                    <div class="h-[500px] relative z-10">
                        <canvas ref="topBottomProductsRef" class="w-full h-full"></canvas>
                    </div>
                </div>

                <!-- Monthly Sales and Wastes Sections would follow similar design -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
                        <div class="absolute inset-0 opacity-10 bg-teal-100"></div>
                        <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center relative z-10">
                            Monthly Sales Trend
                        </h3>
                        <div class="h-[400px] relative z-10">
                            <canvas ref="monthlySalesRef" class="w-full h-full"></canvas>
                        </div>
                    </div>
                    
                    <WasteAnalysis
                    :selectedDateRange="selectedDateRange"
                    :selectedStores="selectedStores"
                    />
                </div>
            </div>
        </template>
    </AdminPanel>
</template>

<style scoped>
.bg-pattern {
    background-image: linear-gradient(45deg, rgba(59, 130, 246, 0.1) 0%, rgba(99, 102, 241, 0.1) 100%);
    background-size: 20px 20px;
}

.animate-pulse-slow {
    animation: pulse 3s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.metric-card {
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

/* Bubble effect on hover */
.metric-card::after {
    content: '';
    position: absolute;
    top: 10%;
    left: 10%;
    width: 40px;
    height: 40px;
    background-color: rgba(59, 130, 246, 0.2);
    border-radius: 50%;
    opacity: 0;
    transform: scale(0.5);
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.metric-card:hover::after {
    opacity: 1;
    transform: scale(1);
}
</style>