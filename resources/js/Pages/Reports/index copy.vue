<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Reset from "@/Components/Resetter/reset.vue";
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import Refresh from "@/Components/Svgs/Refresh.vue";
import Warning from "@/Components/Svgs/Warning.vue";

// Initialize DataTable
DataTable.use(DataTablesCore);

const emit = defineEmits();

// Add missing showResetModal ref
const showResetModal = ref(false);

// Reset modal handler function
const ResetModalHandler = () => {
  showResetModal.value = !showResetModal.value;
};

const cards = ref([
  {
    id: 1,
    title: 'Card Title 1',
    description: 'This is a description for card 1. Add your content here.',
    category: 'Category A',
    animationClass: 'bounce'
  },
  {
    id: 2,
    title: 'Card Title 2',
    description: 'This is a description for card 2. Add your content here.',
    category: 'Category B',
    animationClass: 'wave'
  },
  {
    id: 3,
    title: 'Card Title 3',
    description: 'This is a description for card 3. Add your content here.',
    category: 'Category C',
    animationClass: 'spin'
  }
]);

const handleAction = (id) => {
  console.log(`Action triggered for card ${id}`);
  // Add your action handling logic here
};
</script>

<template>
  <Main active-tab="REPORTS">
    <template v-slot:modals>
      <Reset
        :show-modal="showResetModal"
        @toggle-active="ResetModalHandler"
      />
    </template>

    <template v-slot:main>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-4">
        <div v-for="card in cards" :key="card.id" 
             class="bg-white rounded-xl shadow-lg overflow-hidden transform transition-all duration-300 hover:scale-105">
          <div class="relative h-48 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-100 to-purple-100">
              <div class="character" :class="card.animationClass">
                <div class="w-20 h-20 bg-blue-500 rounded-full mx-auto mt-8 relative animate-bounce">
                  <div class="absolute w-4 h-4 bg-white rounded-full" style="left: 20%; top: 30%">
                    <div class="w-2 h-2 bg-black rounded-full absolute right-0"></div>
                  </div>
                  <div class="absolute w-4 h-4 bg-white rounded-full" style="right: 20%; top: 30%">
                    <div class="w-2 h-2 bg-black rounded-full absolute right-0"></div>
                  </div>
                  <div class="absolute w-12 h-6 border-b-4 border-white rounded-full" 
                       style="left: 20%; top: 60%"></div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ card.title }}</h3>
            <p class="text-gray-600">{{ card.description }}</p>
            
            <div class="mt-4 flex justify-between items-center">
              <span class="text-sm font-semibold text-purple-600">{{ card.category }}</span>
              <button @click="handleAction(card.id)" 
                      class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 
                             transition-colors duration-200">
                Learn More
              </button>
            </div>
          </div>
        </div>
      </div>
    </template>
  </Main>
</template>

<style scoped>
.character {
  width: 100%;
  height: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Animation classes */
.bounce {
  animation: bounce 2s infinite;
}

.wave {
  animation: wave 3s infinite;
}

.spin {
  animation: spin 4s infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}

@keyframes wave {
  0%, 100% {
    transform: rotate(0deg);
  }
  25% {
    transform: rotate(-10deg);
  }
  75% {
    transform: rotate(10deg);
  }
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>