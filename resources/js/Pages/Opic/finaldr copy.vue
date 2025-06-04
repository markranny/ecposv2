<script setup>
import { ref, computed } from "vue";
import { usePage } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Create from "@/Components/Partycakes/Create.vue";
import Update from "@/Components/Partycakes/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import Search from "@/Components/Svgs/SearchColored.vue";
import Back from "@/Components/Svgs/Back.vue";
import Main from "@/Layouts/AdminPanel.vue";

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const page = usePage();
const groupedPicklist = ref(Object.entries(page.props.groupedPicklist).reduce((acc, [storeName, items]) => {
  acc[storeName] = items.map(item => ({
    ...item,
    actual: item.ACTUAL
  }));
  return acc;
}, {}));

const picklist = computed(() => {
  const firstStore = Object.values(groupedPicklist.value)[0];
  return firstStore ? firstStore : [];
});

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
};

const calculateTotalTarget = (items) => {
  return items.reduce((sum, item) => sum + Number(item.TARGET || 0), 0);
};

const calculateTotalAlloc = (items) => {
  return items.reduce((sum, item) => sum + Number(item.CHECKINGCOUNT || 0), 0);
};

const calculateTotalVariance = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.CHECKINGCOUNT || 0) - Number(item.TARGET || 0)) , 0);
};

const calculateTotalReceived = (items) => {
  return 0;
};

const calculateTotalTransferCost = (items) => {
  return items.reduce((sum, item) => sum + Number(item.COST || 0), 0);
};

const calculateTotalAmount = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.CHECKINGCOUNT || 0) * Number(item.COST || 0)), 0);
};

const form = useForm({
  StartDate: '2024-07-22',
  StoreName: 'Urdaneta2',
});

const submitForm = () => {
  form.get(route('picklist.getrange'), {
    preserveState: true,
    preserveScroll: true,
  });
};

const formattedDate1 = computed(() => {
  const startDate = form.StartDate;
  if (startDate) {
    const date = new Date(startDate);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  return '';
});

const totalCost = computed(() => {
  return picklist.value.reduce((sum, item) => sum + (Number(item.COST || 0) * Number(item.CHECKINGCOUNT || 0)), 0);
});

const hasSpecialOrder = computed(() => {
  return picklist.value.some(item => Number(item.SPECIALORDER || 0) > 0);
});

const specialOrder = computed(() => {
  return picklist.value.find(item => Number(item.SPECIALORDER || 0) > 0) || {};
});

const specialOrderTotalCost = computed(() => {
  return Number(specialOrder.value.COST || 0) * Number(specialOrder.value.SPECIALORDER || 0);
});

const formatNumber = (value) => {
  const num = parseFloat(value);
  return Number.isInteger(num) ? num.toString() : Math.round(num).toString();
};

const updateActual = async (storeName, itemName, itemId, value) => {
  try {
    const store = groupedPicklist.value[storeName];
    const item = store.find(i => i.ITEMID === itemId);

    if (!item) {
      console.error('Item not found');
      return;
    }

    if (!item.JOURNALID) {
      console.error('JOURNALID is missing for this item');
      return;
    }

    const response = await axios.post('/api/update-actual', {
      journal_id: item.JOURNALID,
      store_name: storeName,
      item_name: itemName,
      item_id: itemId,
      actual: value
    });

    if (response.data.success) {
      item.ACTUAL = value;
    } else {
      console.error('Failed to update ACTUAL value', response.data);
    }
  } catch (error) {
    console.error('Error updating ACTUAL value:', error.message);
  }
};

const printDeliveryReceipt = () => {
  // ... (keep your existing printDeliveryReceipt function)
};

const BackPre = () => {
  window.location.href = '/mgcount';
};

const picklistreload = () => {
  window.location.href = '/picklist';
};
</script>


<template>
  <Main active-tab="FINALDR">
    <template v-slot:modals>
      <Create v-if="showCreateModal" @toggle-active="createModalHandler" />
      <Update 
        v-if="showModalUpdate"  
        :ID="id" 
        :SUBJECT="subject"  
        :DESCRIPTION="description" 
        @toggle-active="updateModalHandler"
      />
    </template>

    <template v-slot:main>
      <div class="absolute adjust">
        <div class="flex justify-start items-center">
          <PrimaryButton
                  type="button"
                  @click="BackPre"
                  class="m-1 ml-2 bg-navy p-10"
                >
                  <Back class="h-5" />
            </PrimaryButton>
          <!-- <PrimaryButton
            type="button"
            @click="printPackingList"
            class="m-6 bg-navy"
          >
            PRINT PL
          </PrimaryButton> -->

          <PrimaryButton
            type="button"
            @click="printDeliveryReceipt"
            class="bg-navy"
          >
            PRINT DR
          </PrimaryButton>

          <!-- <PrimaryButton
            type="button"
            @click="getPickListInputData"
            class="ml-2 bg-navy"
          >
            GET
          </PrimaryButton> -->

          <!-- <PrimaryButton
            type="button"
            class="bg-navy ml-2"
          >
            POST ALL PL
          </PrimaryButton> -->

          <!-- <PrimaryButton
            type="button"
            @click="picklistreload"
            class="ml-2 bg-navy"
          >
            CALCULATE
          </PrimaryButton> -->

          <form @submit.prevent="submitForm"   class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <input type="hidden" name="_token" :value="$page.props.csrf_token">
                <div date-rangepicker  class="flex items-center">
                <div class="relative ml-5 ">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                    </div>

                    <input
                    id="StartDate"
                    type="date"
                    v-model="form.StartDate"
                    @input="formattedDate1"
                    :placeholder="formattedDate1"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date start"
                    required
                    />
                    <InputError :message="form.errors.StartDate" class="mt-2" />

                    <!-- <InputLabel for="RETAILGROUP" value="RETAILGROUP" />
                      <SelectOption 
                          id="itemdepartment"
                          v-model="form.itemdepartment" 
                          :is-error="form.errors.itemdepartment ? true : false"
                          class="mt-1 block w-full"
                          >
                          <option disabled value="">Select an option</option>
                          <option>PRODUCT</option>
                          <option>NON PRODUCT</option>
                      </SelectOption>
                      <InputError :message="form.errors.itemdepartment" class="mt-2" /> -->
                    </div>

                <!-- <span class="mx-4 text-gray-500">to</span>

                <div class="relative">
                    <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                    </div>

                    <input
                    id="EndDate"
                    type="date"
                    v-model="form.EndDate"
                    @input="formattedDate2"
                    :placeholder="formattedDate2"
                    class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end"
                    required
                    />
                    <InputError :message="form.errors.EndDate" class="mt-2" />
                </div> -->
            </div>
            </form>

            <TransparentButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
            <Search class="h-8" />
            </TransparentButton>

          
        </div>
      </div>

      <div role="tablist" class="tabs tabs-lifted mt-10 p-5">
        <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="FINAL DR" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-200 p-6 h-[85vh] overflow-y-auto">
              <template v-if="isLoading">
                <div class="col-span-full text-center mt-8">
                  <p class="text-gray-600 text-lg">Loading...</p>
                </div>
              </template>
              
              <template v-else-if="error">  
                <div class="col-span-full text-center mt-8">
                  <p class="text-red-600 text-lg">{{ error }}</p>
                </div>
              </template>

              <template v-else-if="!groupedPicklist || Object.keys(groupedPicklist).length === 0">
                <div class="col-span-full text-center mt-8">
                  <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                    <p class="text-gray-600 text-base sm:text-lg">No DR2 List Available</p>
                  </div>
                </div>
              </template>
              
            <template v-else>
                <div class="max-w-4xl mx-auto p-4 bg-white shadow-lg text-xs">
                <table class="w-full border-collapse border border-gray-400">
                  <tr>
                    <td colspan="6" class="text-center font-bold text-lg border border-gray-400 p-1">
                      MALIWALO<br>
                      TARLAC CITY
                    </td>
                  </tr>
                  <tr>
                    <td colspan="4" class="font-bold border border-gray-400 p-1">DELIVERY GOODS RECEIPT<br>BW PRODUCT</td>
                    <td colspan="2" class="border border-gray-400 p-1">
                      DR #: <span class="font-bold">{{ picklist[0].JOURNALID }}</span><br>
                      DELIVERY DATE: {{ formatDate(picklist[0].DELIVERYDATE) }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="border border-gray-400 p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
                    <td colspan="3" class="border border-gray-400 p-1">DELIVERED TO:<br><span class="font-bold">{{ picklist[0].STORENAME }}</span></td>
                  </tr>
                  <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-400 p-1">PRODUCT DESCRIPTION</td>
                    <td class="border border-gray-400 p-1 text-center">DELIVERED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">RECEIVED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">VARIANCE</td>
                    <td class="border border-gray-400 p-1 text-right">UNIT COST</td>
                    <td class="border border-gray-400 p-1 text-right">TOTAL</td>
                  </tr>
                  <!-- Product rows -->
                  <tr v-for="item in picklist" :key="item.ITEMID">
                    <td class="border border-gray-400 p-1">{{ item.ITEMNAME }}</td>
                    <td class="border border-gray-400 p-1 text-center">{{ item.CHECKINGCOUNT }}</td>
                    <td class="border border-gray-400 p-1 text-center">0</td>
                    <td class="border border-gray-400 p-1 text-center">{{ item.ACTUAL - item.CHECKINGCOUNT }}</td>
                    <td class="border border-gray-400 p-1 text-right">{{ item.COST }}</td>
                    <td class="border border-gray-400 p-1 text-right">{{ item.COST * item.CHECKINGCOUNT }}</td>
                  </tr>
                  <tr>
                    <td colspan="5" class="border border-gray-400 p-1 text-right font-bold">TOTAL</td>
                    <td class="border border-gray-400 p-1 text-right font-bold">{{ totalCost }}</td>
                  </tr>

                  <!-- SPECIAL ORDER ONLY -->
                  <tr v-if="hasSpecialOrder">
                    <!-- <td rowspan="2" class="border border-gray-400 p-1">PROMO</td> -->
                    <td class="border border-gray-400 p-1">PROMO</td>
                    <td class="border border-gray-400 p-1 text-center">DELIVERED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">RECEIVED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">VARIANCE</td>
                    <td class="border border-gray-400 p-1 text-right">UNIT COST</td>
                    <td class="border border-gray-400 p-1 text-right">AMOUNT</td>
                  </tr>
                  <tr v-if="hasSpecialOrder">
                    <td class="border border-gray-400 p-1 text-center">{{ specialOrder.ITEMNAME }}</td>
                    <td class="border border-gray-400 p-1 text-center">{{ specialOrder.COUNTED }}</td>
                    <td class="border border-gray-400 p-1 text-center">0</td>
                    <td class="border border-gray-400 p-1">0</td>
                    <td class="border border-gray-400 p-1 text-right">{{ specialOrder.COST }}</td>
                    <td class="border border-gray-400 p-1 text-right">{{ specialOrder.COST * specialOrder.SPECIALORDER }}</td>
                  </tr>
                  <tr v-if="hasSpecialOrder">
                    <td colspan="5" class="border border-gray-400 p-1 text-right font-bold">TOTAL</td>
                    <td class="border border-gray-400 p-1 text-right font-bold">{{ specialOrderTotalCost }}</td>
                  </tr>

                  <!-- Omitting Party Cake section as it's not clear from the controller data -->

                  <tr>
                    <td colspan="3" class="border border-gray-400 p-1">
                      ENDORSED BY:DISPATCHING<br>
                      <span class="font-bold">{{ picklist[0].DISPATCHER }}</span><br>
                      BREADS/CAKES<br>
                      NAME & SIGNATURE/ DATE
                    </td>
                    <td colspan="3" class="border border-gray-400 p-1">
                      <span class="font-bold">{{ picklist[0].LOGISTICS }}</span><br>
                      DELIVERED BY:LOGISTICS<br>
                      NAME & SIGNATURE/ DATE
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="border border-gray-400 p-1 font-bold">CRATES QUANTITY DELIVERED</td>
                  </tr>
                  <tr>
                    <td colspan="2" class="border border-gray-400 p-1">ORANGE CRATES</td>
                    <td colspan="2" class="border border-gray-400 p-1">BLUE CRATES</td>
                    <td colspan="2" class="border border-gray-400 p-1">EMPANADA CRATES</td>
                  </tr>
                </table>
              </div>
              
          </template>

        </div>
      </div>


      
    </template>
  </Main>
</template>

