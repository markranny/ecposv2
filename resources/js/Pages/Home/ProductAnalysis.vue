<script setup>
import { ref, onMounted, watch,onUnmounted } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';

// Register Chart.js components
Chart.register(...registerables);

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

// Add refs for component state
const topProductsRef = ref(null);
const bottomProductsRef = ref(null);
const isLoading = ref(false);
const error = ref(null);

let topProductsChart = null;
let bottomProductsChart = null;

const formatCurrency = (value) => {
  const numValue = Number(value);
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(isNaN(numValue) ? 0 : numValue);
};

const truncateLabel = (label, maxLength = 20) => {
  return label.length > maxLength ? label.substring(0, maxLength) + '...' : label;
};

const createProductChart = (ctx, data, title, isTop = true) => {
  if (!ctx) {
    console.error('Canvas context not available');
    return null;
  }

  // Data validation
  if (!Array.isArray(data) || !data.length) {
    console.warn(`No data available for ${title}`);
    data = [];
  }

  // Sort data
  const sortedData = [...data].sort((a, b) => 
    isTop ? b.total_sales - a.total_sales : a.total_sales - b.total_sales
  );

  const chartData = {
    labels: sortedData.map(p => truncateLabel(p.itemname)),
    datasets: [
      {
        label: 'Quantity Sold',
        data: sortedData.map(p => Math.abs(Number(p.total_quantity) || 0)),
        backgroundColor: isTop ? 'rgba(34, 197, 94, 0.6)' : 'rgba(239, 68, 68, 0.6)',
        borderColor: isTop ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)',
        borderWidth: 1,
        barPercentage: 0.7,
        yAxisID: 'quantity'
      },
      {
        label: 'Sales Amount',
        data: sortedData.map(p => Math.abs(Number(p.total_sales) || 0)),
        backgroundColor: isTop ? 'rgba(59, 130, 246, 0.6)' : 'rgba(249, 115, 22, 0.6)',
        borderColor: isTop ? 'rgb(59, 130, 246)' : 'rgb(249, 115, 22)',
        borderWidth: 1,
        barPercentage: 0.7,
        yAxisID: 'sales'
      }
    ]
  };

  return new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false
      },
      plugins: {
        title: {
          display: true,
          text: title,
          font: { size: 16, weight: 'bold' }
        },
        legend: {
          position: 'top',
          labels: {
            boxWidth: 20,
            padding: 15
          }
        },
        tooltip: {
          callbacks: {
            label: (context) => {
              const value = context.raw;
              return context.datasetIndex === 0 
                ? `Quantity: ${Math.round(value).toLocaleString()} units`
                : `Sales: ${formatCurrency(value)}`;
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            maxRotation: 0,
            autoSkip: false,
            callback: (value) => truncateLabel(value)
          }
        },
        quantity: {
          position: 'bottom',
          beginAtZero: true,
          ticks: {
            callback: (value) => Math.round(value).toLocaleString()
          },
          grid: {
            drawOnChartArea: false
          },
          title: {
            display: true,
            text: 'Quantity Sold'
          }
        },
        sales: {
          position: 'top',
          beginAtZero: true,
          ticks: {
            callback: (value) => formatCurrency(value)
          },
          grid: {
            drawOnChartArea: false
          },
          title: {
            display: true,
            text: 'Sales Amount (PHP)'
          }
        }
      }
    }
  });
};

const fetchProductData = async () => {
  try {
    isLoading.value = true;
    error.value = null;

    const payload = {
      start_date: props.selectedDateRange.start_date,
      end_date: props.selectedDateRange.end_date,
      stores: props.selectedStores.map(store => 
        typeof store === 'object' ? store.NAME : store
      )
    };

    console.log('Fetching product data with payload:', payload);

    const response = await axios.post(route('get.top.bottom.products'), payload);
    
    if (response.data && !response.data.error) {
      // Extract data from nested structure
      const topProducts = response.data.topProducts?.['Illuminate\\Support\\Collection'] || [];
      const bottomProducts = response.data.bottomProducts?.['Illuminate\\Support\\Collection'] || [];
      
      console.log('Received top products:', topProducts);
      console.log('Received bottom products:', bottomProducts);

      initializeCharts(topProducts, bottomProducts);
    } else {
      throw new Error(response.data.error || 'Failed to fetch product data');
    }
  } catch (err) {
    console.error('Error fetching product data:', err);
    error.value = 'Failed to load product data. Please try again.';
    initializeCharts([], []);
  } finally {
    isLoading.value = false;
  }
};

const initializeCharts = (topProducts, bottomProducts) => {
  // Cleanup existing charts
  if (topProductsChart) {
    topProductsChart.destroy();
  }
  if (bottomProductsChart) {
    bottomProductsChart.destroy();
  }

  // Initialize new charts
  if (topProductsRef.value) {
    const topCtx = topProductsRef.value.getContext('2d');
    topProductsChart = createProductChart(
      topCtx, 
      topProducts, 
      'Top 10 Products by Sales',
      true
    );
  }

  if (bottomProductsRef.value) {
    const bottomCtx = bottomProductsRef.value.getContext('2d');
    bottomProductsChart = createProductChart(
      bottomCtx, 
      bottomProducts, 
      'Bottom 10 Products by Sales',
      false
    );
  }
};

// Cleanup on component unmount
onUnmounted(() => {
  if (topProductsChart) {
    topProductsChart.destroy();
  }
  if (bottomProductsChart) {
    bottomProductsChart.destroy();
  }
});

// Watch for changes in props
watch(
  [() => props.selectedDateRange, () => props.selectedStores],
  () => {
    fetchProductData();
  },
  { deep: true }
);

// Initialize on component mount
onMounted(() => {
  fetchProductData();
});
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Top Products Chart -->
    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
      <div class="absolute inset-0 opacity-10 bg-green-100"></div>
      <div class="relative z-10">
        <div class="flex items-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
          </svg>
          <h3 class="text-xl font-bold text-gray-800">Top Performing Products</h3>
        </div>
        <div v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</div>
        <div v-if="isLoading" class="flex items-center justify-center h-[500px]">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
        </div>
        <div v-else class="h-[500px] relative z-10">
          <canvas ref="topProductsRef"></canvas>
        </div>
      </div>
    </div>

    <!-- Bottom Products Chart -->
    <div class="bg-white/80 rounded-3xl shadow-xl border border-blue-100/50 p-6 relative overflow-hidden hover:scale-[1.02] transition-all duration-300">
      <div class="absolute inset-0 opacity-10 bg-red-100"></div>
      <div class="relative z-10">
        <div class="flex items-center mb-4">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
          </svg>
          <h3 class="text-xl font-bold text-gray-800">Products Under Monitoring</h3>
        </div>
        <div v-if="error" class="text-red-500 text-sm mb-4">{{ error }}</div>
        <div v-if="isLoading" class="flex items-center justify-center h-[500px]">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600"></div>
        </div>
        <div v-else class="h-[500px] relative z-10">
          <canvas ref="bottomProductsRef"></canvas>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chart-container {
  position: relative;
  height: 500px;
  width: 100%;
}
</style>