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

DataTable.use(DataTablesCore);

const emit = defineEmits();
const showResetModal = ref(false);

const ResetModalHandler = () => {
  showResetModal.value = !showResetModal.value;
};

const cards = ref([
  {
    id: 1,
    title: 'VARIANCE',
    description: '',
    animationClass: 'money-rain'
  },
  {
    id: 2,
    title: 'ACCOUNT RECEIVABLE',
    description: '',
    animationClass: 'happy-bounce'
  },
  {
    id: 3,
    title: 'EMPLOYEE CHARGE',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 4,
    title: 'BAD ORDERS',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 5,
    title: 'REGULAR DISCOUNT',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 6,
    title: 'MARKETING DISCOUNT',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 7,
    title: 'SALES',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 8,
    title: 'ORDERS',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 9,
    title: 'DELIVERY',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 10,
    title: 'STOCKS',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 11,
    title: 'SALES BY HOUR',
    description: '',
    animationClass: 'target-spin'
  },
  {
    id: 12,
    title: 'SALES COMPARISON',
    description: '',
    animationClass: 'target-spin'
  }
]);

const handleAction = (id) => {
  console.log(`Action triggered for card ${id}`);
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
                <!-- Money Rain Animation -->
                <template v-if="card.animationClass === 'money-rain'">
                  <div class="coin-container">
                    <div class="coin"></div>
                    <div class="coin" style="animation-delay: 0.5s"></div>
                    <div class="coin" style="animation-delay: 1s"></div>
                    <div class="dollar-sign">$</div>
                  </div>
                </template>
                
                <!-- Happy Customer Animation -->
                <template v-if="card.animationClass === 'happy-bounce'">
                  <div class="happy-face">
                    <div class="face">
                      <div class="eyes">
                        <div class="eye left"></div>
                        <div class="eye right"></div>
                      </div>
                      <div class="smile"></div>
                    </div>
                  </div>
                </template>
                
                <!-- Target Animation -->
                <template v-if="card.animationClass === 'target-spin'">
                  <div class="target">
                    <div class="outer-ring"></div>
                    <div class="middle-ring"></div>
                    <div class="inner-ring"></div>
                    <div class="bullseye"></div>
                    <div class="arrow"></div>
                  </div>
                </template>
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
                LINK
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
  position: relative;
}

/* Money Rain Animation */
.coin-container {
  position: relative;
  width: 100px;
  height: 100px;
}

.coin {
  position: absolute;
  width: 30px;
  height: 30px;
  background: gold;
  border-radius: 50%;
  animation: rain 2s infinite linear;
}

.dollar-sign {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 40px;
  color: #2d3748;
  font-weight: bold;
  animation: pulse 2s infinite;
}

@keyframes rain {
  0% {
    transform: translateY(-50px);
    opacity: 0;
  }
  50% {
    opacity: 1;
  }
  100% {
    transform: translateY(100px);
    opacity: 0;
  }
}

@keyframes pulse {
  0%, 100% {
    transform: translate(-50%, -50%) scale(1);
  }
  50% {
    transform: translate(-50%, -50%) scale(1.2);
  }
}

/* Happy Face Animation */
.happy-face {
  width: 100px;
  height: 100px;
  animation: bounce 2s infinite;
}

.face {
  width: 100%;
  height: 100%;
  background: #ffd700;
  border-radius: 50%;
  position: relative;
}

.eyes {
  position: absolute;
  top: 35%;
  width: 100%;
  display: flex;
  justify-content: space-around;
}

.eye {
  width: 12px;
  height: 12px;
  background: #2d3748;
  border-radius: 50%;
  animation: blink 3s infinite;
}

.smile {
  position: absolute;
  bottom: 25%;
  left: 50%;
  width: 60px;
  height: 30px;
  border-bottom: 6px solid #2d3748;
  border-radius: 50%;
  transform: translateX(-50%);
}

@keyframes blink {
  0%, 95%, 100% {
    transform: scaleY(1);
  }
  97% {
    transform: scaleY(0);
  }
}

/* Target Animation */
.target {
  width: 100px;
  height: 100px;
  position: relative;
  animation: targetPulse 3s infinite;
}

.outer-ring, .middle-ring, .inner-ring, .bullseye {
  position: absolute;
  border-radius: 50%;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.outer-ring {
  width: 100%;
  height: 100%;
  border: 8px solid #ff0000;
}

.middle-ring {
  width: 70%;
  height: 70%;
  border: 6px solid #ffffff;
}

.inner-ring {
  width: 40%;
  height: 40%;
  border: 4px solid #ff0000;
}

.bullseye {
  width: 20%;
  height: 20%;
  background: #ff0000;
}

.arrow {
  position: absolute;
  width: 40px;
  height: 4px;
  background: #2d3748;
  top: 50%;
  right: -20px;
  transform-origin: left center;
  animation: shoot 3s infinite;
}

.arrow::before {
  content: '';
  position: absolute;
  right: -8px;
  top: -8px;
  border-left: 12px solid #2d3748;
  border-top: 10px solid transparent;
  border-bottom: 10px solid transparent;
}

@keyframes targetPulse {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

@keyframes shoot {
  0%, 100% {
    transform: translateX(-100px) scaleX(1);
    opacity: 0;
  }
  50% {
    transform: translateX(0) scaleX(1);
    opacity: 1;
  }
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-20px);
  }
}
</style>