# WasteAnalysis.vue
<script setup>
import { ref, onMounted, watch } from 'vue';
import { Chart } from 'chart.js';
import axios from 'axios';

const props = defineProps({
  selectedDateRange: {
    type: Object,
    required: true
  },
  selectedStores: {
    type: Array,
    required: true
  }
});

const wasteChartRef = ref(null);
let wasteChart = null;
const wasteAnalytics = ref({
  totalWasteCost: 0,
  totalWasteQuantity: 0,
  uniqueStores: 0
});

const formatCurrency = (value) => {
  const numValue = Number(value);
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(isNaN(numValue) ? 0 : numValue);
};

const initializeWasteChart = (wastesData) => {
  if (!wasteChartRef.value) return;
  
  const ctx = wasteChartRef.value.getContext('2d');
  
  if (wasteChart) {
    wasteChart.destroy();
  }

  // Extract data from the response
  const labels = wastesData.map(item => item.itemname);
  const quantities = wastesData.map(item => Math.abs(Number(item.total_waste_quantity)));
  const costs = wastesData.map(item => Math.abs(Number(item.total_waste_cost)));

  wasteChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Waste Quantity',
          data: quantities,
          backgroundColor: 'rgba(239, 68, 68, 0.5)',
          borderColor: 'rgba(239, 68, 68, 1)',
          borderWidth: 1,
          yAxisID: 'quantity'
        },
        {
          label: 'Waste Cost (PHP)',
          data: costs,
          backgroundColor: 'rgba(59, 130, 246, 0.5)',
          borderColor: 'rgba(59, 130, 246, 1)',
          borderWidth: 1,
          yAxisID: 'cost'
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
      },
      plugins: {
        legend: {
          position: 'top',
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const value = context.raw;
              if (context.datasetIndex === 1) {
                return `Cost: ${formatCurrency(value)}`;
              }
              return `Quantity: ${Math.round(value).toLocaleString()} units`;
            }
          }
        }
      },
      scales: {
        quantity: {
          type: 'linear',
          position: 'left',
          title: {
            display: true,
            text: 'Waste Quantity (Units)'
          },
          ticks: {
            callback: value => Math.round(value).toLocaleString()
          },
          grid: {
            drawOnChartArea: true
          }
        },
        cost: {
          type: 'linear',
          position: 'right',
          title: {
            display: true,
            text: 'Waste Cost (PHP)'
          },
          ticks: {
            callback: value => formatCurrency(value)
          },
          grid: {
            drawOnChartArea: false
          }
        }
      }
    }
  });
};

const fetchWasteData = async () => {
  try {
    const payload = {
      start_date: props.selectedDateRange.start_date,
      end_date: props.selectedDateRange.end_date,
      stores: props.selectedStores.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    const response = await axios.post(route('get.top.wastes'), payload);
    
    if (response.data && response.data.data) {
      const wastesData = response.data.data;
      wasteAnalytics.value = {
        totalWasteCost: response.data.summary.totalWasteCost || 0,
        totalWasteQuantity: response.data.summary.totalWasteQuantity || 0,
        uniqueStores: response.data.summary.uniqueStores || 0
      };

      initializeWasteChart(wastesData);
    }
  } catch (error) {
    console.error('Error fetching waste data:', error);
    wasteAnalytics.value = {
      totalWasteCost: 0,
      totalWasteQuantity: 0,
      uniqueStores: 0
    };
    initializeWasteChart([]);
  }
};

// Watch for changes in props
watch(
  [
    () => props.selectedDateRange.start_date,
    () => props.selectedDateRange.end_date,
    () => JSON.stringify(props.selectedStores)
  ],
  () => {
    fetchWasteData();
  }
);

onMounted(() => {
  fetchWasteData();
});
</script>

<template>
  <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
    <div class="absolute inset-0 opacity-10 bg-red-100"></div>
    
    <div class="relative z-10 space-y-6">
      <div class="flex items-center justify-between">
        <h3 class="text-xl font-bold text-gray-800 flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          Top Waste Analysis
        </h3>
      </div>

      <!-- Analytics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-red-50 rounded-xl p-4 border border-red-100 transform transition-all duration-300 hover:scale-105">
          <p class="text-sm text-red-600 font-medium">Total Waste Cost</p>
          <p class="text-2xl font-bold text-red-700">{{ formatCurrency(wasteAnalytics.totalWasteCost) }}</p>
        </div>
        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100 transform transition-all duration-300 hover:scale-105">
          <p class="text-sm text-blue-600 font-medium">Total Waste Quantity</p>
          <p class="text-2xl font-bold text-blue-700">{{ Math.round(wasteAnalytics.totalWasteQuantity).toLocaleString() }}</p>
        </div>
        <div class="bg-purple-50 rounded-xl p-4 border border-purple-100 transform transition-all duration-300 hover:scale-105">
          <p class="text-sm text-purple-600 font-medium">Affected Stores</p>
          <p class="text-2xl font-bold text-purple-700">{{ wasteAnalytics.uniqueStores }}</p>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="h-[400px] relative z-10">
        <canvas ref="wasteChartRef"></canvas>
      </div>
    </div>
  </div>
</template>