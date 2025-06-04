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

const props = defineProps({
    sptrans: {
        type: Array,
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
    routes: {
    type: String,
    required: true,
  },
});

const storeNames = computed(() => Object.keys(groupedPicklist.value));

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
  return items.reduce((sum, item) => sum + Number(item.actual || 0), 0);
};

const calculateTotalTransferCost = (items) => {
  return items.reduce((sum, item) => sum + Number(item.COST || 0), 0);
};

const calculateTotalAmount = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.CHECKINGCOUNT || 0) * Number(item.COST || 0)), 0);
};

const calculateSpecialOrder = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.COUNTED || 0) * Number(item.COST || 0)), 0);
};

const form = useForm({
  EndDate: '',
  StoreName: '',
});

const submitForm = () => {
  form.get(route('fdr.getrange'), {
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
  return Object.values(groupedPicklist.value).flat().reduce((sum, item) => sum + (Number(item.COST || 0) * Number(item.CHECKINGCOUNT || 0)), 0);
});


const specialOrder = computed(() => {
  return Object.values(groupedPicklist.value).flat().find(item => Number(item.SPECIALORDER || 0) > 0) || {};
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
  // Implement your print functionality here
};

const BackPre = () => {
  window.location.href = '/mgcount';
};

const picklistreload = () => {
  window.location.href = '/picklist';
};

const cratesCounts = ref({
  orangeCrates: 0,
  blueCrates: 0,
  empanadaCrates: 0,
  box: 0
});

const updateCratesCounts = async (storeName, journalId) => {
  try {
    const response = await axios.post('/api/update-crates-counts', {
      journalId,
      orangeCrates: cratesCounts.value.orangeCrates,
      blueCrates: cratesCounts.value.blueCrates,
      empanadaCrates: cratesCounts.value.empanadaCrates,
      box: cratesCounts.value.box
    });

    if (response.data.success) {
      console.log('Crates counts updated successfully');
    } else {
      console.error('Failed to update crates counts', response.data);
    }
  } catch (error) {
    console.error('Error updating crates counts:', error.message);
  }
};

const reload = () => {
 location.reload();
};



const printPackingList = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  const stores = Object.entries(groupedPicklist.value);
  let content = '';

  stores.forEach(([storeName, storeItems]) => {
    const tableContent = storeItems.map(item => `
      <tr>
        <td class="border p-1">${item.ITEMNAME}</td>
        <td class="border p-1 text-center">${formatNumber(item.CHECKINGCOUNT)}</td>
        <td class="border p-1 text-center">0</td>
        <td class="border p-1 text-center">${0 - item.CHECKINGCOUNT}</td>
        <td class="border p-1 text-right">${formatCurrency(item.COST)}</td>
        <td class="border p-1 text-right">${formatCurrency(item.COST * item.CHECKINGCOUNT)}</td>
      </tr>
    `).join('');

    const totalAmount = calculateTotalAmount(storeItems);

    let specialOrderContent = '';
    if (storeName === 'ANCHETA' && props.sptrans && props.sptrans.length > 0) {
      specialOrderContent = `
        <tr>
          <td colspan="6" class="border p-1 font-bold">SPECIAL ORDERS</td>
        </tr>
        <tr>
          <td class="border p-1">PROMO</td>
          <td class="border p-1 text-center">DELIVERED QUANTITY</td>
          <td class="border p-1 text-center">RECEIVED QUANTITY</td>
          <td class="border p-1 text-center">VARIANCE</td>
          <td class="border p-1 text-right">UNIT COST</td>
          <td class="border p-1 text-right">AMOUNT</td>
        </tr>
        ${props.sptrans.map(spItem => `
          <tr>
            <td class="border p-1 text-center">${spItem.ITEMNAME}</td>
            <td class="border p-1 text-center">${spItem.COUNTED}</td>
            <td class="border p-1 text-center">0</td>
            <td class="border p-1 text-center">0</td> 
            <td class="border p-1 text-right">${formatCurrency(spItem.COST)}</td>
            <td class="border p-1 text-right">${formatCurrency(spItem.COST * spItem.COUNTED)}</td>
          </tr>
        `).join('')}
      `;
    }

    content += `
      <div class="page-container">
        <div class="store-section">
          <table>
            <tr>
              <td colspan="6" class="text-center font-bold text-lg border p-1">
                MALIWALO<br>
                TARLAC CITY
              </td>
            </tr>
            <tr>
              <td colspan="4" class="font-bold border p-1">DELIVERY GOODS RECEIPT<br>BW PRODUCT</td>
              <td colspan="2" class="border p-1">
                DR #: <span class="font-bold">${storeItems[0].JOURNALID}</span><br>
                DELIVERY DATE: ${formatDate(storeItems[0].DELIVERYDATE)}
              </td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
              <td colspan="3" class="border p-1">DELIVERED TO:<br><span class="font-bold">${storeName}</span></td>
            </tr>
            <tr class="bg-gray-200 font-bold">
              <td class="border p-1">PRODUCT DESCRIPTION</td>
              <td class="border p-1 text-center">DELIVERED QUANTITY</td>
              <td class="border p-1 text-center">RECEIVED QUANTITY</td>
              <td class="border p-1 text-center">VARIANCE</td>
              <td class="border p-1 text-right">UNIT COST</td>
              <td class="border p-1 text-right">TOTAL</td>
            </tr>
            ${tableContent}
            ${specialOrderContent}
            <tr>
              <td colspan="5" class="border p-1 text-right font-bold">TOTAL</td>
              <td class="border p-1 text-right font-bold">${formatCurrency(totalAmount)}</td>
            </tr>
            <tr>
              <td colspan="3" class="border p-1">
                ENDORSED BY:DISPATCHING<br>
                <span class="font-bold">${storeItems[0].DISPATCHER} | ${formatDate(storeItems[0].DELIVERYDATE)}</span><br>
                BREADS/CAKES<br>
                NAME & SIGNATURE/ DATE
              </td>
              <td colspan="3" class="border p-1">
                <span class="font-bold">${storeItems[0].LOGISTICS} | ${formatDate(storeItems[0].DELIVERYDATE)}</span><br>
                DELIVERED BY:LOGISTICS<br>
                NAME & SIGNATURE/ DATE
              </td>
            </tr>
            <tr>
              <td colspan="6" class="border p-1 font-bold">CRATES QUANTITY DELIVERED</td>
            </tr>
            <tr>
              <td colspan="1" class="border p-1">ORANGE CRATES - ${storeItems[0].orangeCrates}</td>
              <td colspan="1" class="border p-1">BLUE CRATES - ${storeItems[0].blueCrates}</td>
              <td colspan="2" class="border p-1">EMPANADA CRATES - ${storeItems[0].empanadaCrates}</td>
              <td colspan="3" class="border p-1">BOX - ${storeItems[0].box}</td>
            </tr>
          </table>
        </div>
      </div>
    `;
  });

  windowPrint.document.write(`
    <html>
      <head>
        <title>Delivery Goods Receipt</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 12px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
          }
          .store-section { 
            width: 100%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 12px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .bg-gray-200 { background-color: #edf2f7; }
        </style>
      </head>
      <body>
        ${content}
      </body>
    </html>
  `);
  windowPrint.document.close();
  windowPrint.focus();
  windowPrint.print();
  windowPrint.close();
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
        <div class="flex justify-start mb-4">
          <PrimaryButton
            type="button"
            @click="printPackingList"
            class="bg-navy text-white ml-4 mt-4 rounded-md text-sm"
          >
            PRINT
          </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="reload"
            class="ml-2 bg-navy mt-4 "
            >
              RELOAD
            </PrimaryButton>

          <div class="rounded-md shadow-lg bg-blue-100 border-blue-900 ml-2 mr-2 pb-2">

            <form @submit.prevent="submitForm" class="flex items-center mt-4">
              <input type="hidden" name="_token" :value="$page.props.csrf_token">
              
              <div class="ml-2">
                <InputLabel for="STORE" value="STORE" class="sr-only" />
                <select
                  id="STORE"
                  v-model="form.STORE" 
                  class="input input-bordered w-64 !bg-gray-100"
                >
                  <option disabled value="">Select Store</option>
                  <option v-for="store in rbostoretables" :key="store.STOREID">
                    {{ store.NAME }}
                  </option>
                </select>
              </div>

              <div class="relative ml-2">
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
                </div>

              <TransparentButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                <Search class="h-8" />
              </TransparentButton>
            </form></div>

            <details className="dropdown mt-2">
                <summary className="btn m-1 !bg-navy !text-white">Select Route</summary>
                <ul className="menu dropdown-content !bg-gray-100 rounded-box z-[1] w-52 p-2 shadow">
                  <li><a href="/finalDR">ALL</a></li>
                  <li><a href="/fdr-south1">SOUTH 1</a></li>
                  <li><a href="/fdr-south2">SOUTH 2</a></li>
                  <li><a href="/fdr-south3">SOUTH 3</a></li>
                  <li><a href="/fdr-north1">NORTH 1</a></li>
                  <li><a href="/fdr-north2">NORTH 2</a></li>
                  <li><a href="/fdr-central">CENTRAL</a></li>
                  <li><a href="/fdr-east">EAST</a></li>
                </ul>
              </details>

              <h6 class="ml-2 mt-6 font-bold">{{ routes }}</h6>
        </div>

      <div role="tablist" class="tabs tabs-lifted mt-4 p-5">
        <input type="radio" name="my_tabs_2" role="tab" class="tab !bg-gray-100 !text-gray-500 !font-bold" aria-label="FINAL DR" checked />
        <div role="tabpanel" class="tab-content !bg-gray-100 !border-gray-200 p-6 h-[85vh] overflow-y-auto">
          <template v-if="form.processing">
            <div class="col-span-full text-center mt-8">
              <p class="text-gray-600 text-lg">Loading...</p>
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
            <div v-for="storeName in storeNames" :key="storeName" class="mb-8">
              <h2 class="text-xl font-bold mb-4">{{ storeName }}</h2>
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
                      DR #: <span class="font-bold">{{ groupedPicklist[storeName][0].JOURNALID }}</span><br>
                      DELIVERY DATE: {{ formatDate(groupedPicklist[storeName][0].DELIVERYDATE) }}
                    </td>
                  </tr>
                  <tr>
                    <td colspan="3" class="border border-gray-400 p-1">RECEIVED FROM:<br><span class="font-bold">HEADOFFICE</span></td>
                    <td colspan="3" class="border border-gray-400 p-1">DELIVERED TO:<br><span class="font-bold">{{ storeName }}</span></td>
                  </tr>
                  <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-400 p-1">PRODUCT DESCRIPTION</td>
                    <td class="border border-gray-400 p-1 text-center">DELIVERED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">RECEIVED QUANTITY</td>
                    <td class="border border-gray-400 p-1 text-center">VARIANCE</td>
                    <td class="border border-gray-400 p-1 text-right">UNIT COST</td>
                    <td class="border border-gray-400 p-1 text-right">TOTAL</td>
                  </tr>
                  <tr v-for="item in groupedPicklist[storeName]" :key="item.ITEMID">
                    <td class="border border-gray-400 p-1">{{ item.ITEMNAME }}</td>
                    <td class="border border-gray-400 p-1 text-center">{{ item.CHECKINGCOUNT }}</td>
                    <!-- <td class="border border-gray-400 p-1 text-center">
                      <input 
                        type="number" 
                        v-model="item.actual" 
                        @change="updateActual(storeName, item.ITEMNAME, item.ITEMID, item.actual)"
                        class="w-full text-center"
                      >
                    </td> -->
                    <!-- <td class="border border-gray-400 p-1 text-center">
                      <input 
                        type="number" 
                        :value="0" 
                        @change="updateActual(storeName, item.ITEMNAME, item.ITEMID, item.actual)"
                        class="w-full text-center"
                      >
                    </td> -->
                    <td class="border border-gray-400 p-1 text-center">0</td>
                    <td class="border border-gray-400 p-1 text-center">{{ item.actual - item.CHECKINGCOUNT }}</td>
                    <td class="border border-gray-400 p-1 text-right">{{ formatCurrency(item.COST) }}</td>
                    <td class="border border-gray-400 p-1 text-right">{{ formatCurrency(item.COST * item.CHECKINGCOUNT) }}</td>
                  </tr>
                  <tr>
                    <td colspan="5" class="border border-gray-400 p-1 text-right font-bold">TOTAL</td>
                    <td class="border border-gray-400 p-1 text-right font-bold">{{ formatCurrency(calculateTotalAmount(groupedPicklist[storeName])) }}</td>
                  </tr>



                  <!-- SPECIAL ORDER ONLY -->
                  <template v-if="storeName === 'CENTRAL' && props.sptrans && props.sptrans.length > 0">
                    <tr>
                      <td colspan="6" class="border border-gray-400 p-1 font-bold">SPECIAL ORDERS</td>
                    </tr>
                    <tr>
                      <td class="border border-gray-400 p-1">PROMO</td>
                      <td class="border border-gray-400 p-1 text-center">DELIVERED QUANTITY</td>
                      <td class="border border-gray-400 p-1 text-center">RECEIVED QUANTITY</td>
                      <td class="border border-gray-400 p-1 text-center">VARIANCE</td>
                      <td class="border border-gray-400 p-1 text-right">UNIT COST</td>
                      <td class="border border-gray-400 p-1 text-right">AMOUNT</td>
                    </tr>
                    <tr v-for="(spItem, index) in props.sptrans" :key="index">
                      <td class="border border-gray-400 p-1 text-center">{{ spItem.ITEMNAME }}</td>
                      <td class="border border-gray-400 p-1 text-center">{{ spItem.COUNTED }}</td>
                      <td class="border border-gray-400 p-1 text-center">0</td>
                      <td class="border border-gray-400 p-1 text-center">0</td> 
                      <td class="border border-gray-400 p-1 text-right">{{ formatCurrency(spItem.COST) }}</td>
                      <td class="border border-gray-400 p-1 text-right">{{ formatCurrency(spItem.COST * spItem.COUNTED) }}</td>
                    </tr>
                    <tr>
                      <td colspan="5" class="border border-gray-400 p-1 text-right font-bold">TOTAL</td>
                      <td class="border border-gray-400 p-1 text-right font-bold">{{ formatCurrency(calculateSpecialOrder(props.sptrans)) }}</td>
                    </tr>
                  </template>



                  <tr>
                    <td colspan="3" class="border border-gray-400 p-1">
                      ENDORSED BY:DISPATCHING<br>
                      <span class="font-bold">{{ groupedPicklist[storeName][0].DISPATCHER }} | {{ formatDate(groupedPicklist[storeName][0].DELIVERYDATE) }}</span><br>
                      BREADS/CAKES<br>
                      NAME & SIGNATURE/ DATE
                    </td>
                    <td colspan="3" class="border border-gray-400 p-1">
                      <span class="font-bold">{{ groupedPicklist[storeName][0].LOGISTICS }} | {{ formatDate(groupedPicklist[storeName][0].DELIVERYDATE) }}</span><br>
                      DELIVERED BY:LOGISTICS<br>
                      NAME & SIGNATURE/ DATE
                    </td>
                  </tr>
                  <tr>
                    <td colspan="6" class="border border-gray-400 p-1 font-bold">CRATES QUANTITY DELIVERED</td>
                  </tr>
                  <tr>
                  <td colspan="1" class="border border-gray-400 p-2">
                    <div class="flex justify-between items-center">
                      <span>ORANGE CRATES</span>
                      <span class="mr-2">{{ groupedPicklist[storeName][0].orangeCrates }}</span>
                      <input 
                        type="number" 
                        v-model="cratesCounts.orangeCrates"
                        @change="updateCratesCounts(storeName, groupedPicklist[storeName][0].JOURNALID)"
                        class="w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                    </div>
                  </td>
                  <td colspan="1" class="border border-gray-400 p-2">
                    <div class="flex justify-between items-center">
                      <span>BLUE CRATES</span>
                      <span class="mr-2">{{ groupedPicklist[storeName][0].blueCrates }}</span>
                      <input 
                        type="number" 
                        v-model="cratesCounts.blueCrates"
                        @change="updateCratesCounts(storeName, groupedPicklist[storeName][0].JOURNALID)"
                        class="w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                    </div>
                  </td>
                  <td colspan="2" class="border border-gray-400 p-2">
                    <div class="flex justify-between items-center">
                      <span>EMPANADA CRATES</span>
                      <span class="mr-2">{{ groupedPicklist[storeName][0].empanadaCrates }}</span>
                      <input 
                        type="number" 
                        v-model="cratesCounts.empanadaCrates"
                        @change="updateCratesCounts(storeName, groupedPicklist[storeName][0].JOURNALID)"
                        class="w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                    </div>
                  </td>
                  <td colspan="2" class="border border-gray-400 p-2">
                    <div class="flex justify-between items-center">
                      <span>BOX</span>
                      <span class="mr-2">{{ groupedPicklist[storeName][0].box }}</span>
                      <input 
                        type="number" 
                        v-model="cratesCounts.box"
                        @change="updateCratesCounts(storeName, groupedPicklist[storeName][0].JOURNALID)"
                        class="w-16 px-2 py-1 text-right border rounded focus:outline-none focus:ring-2 focus:ring-blue-500"
                      >
                    </div>
                  </td>
                </tr>
                </table>
              </div>
            </div>
          </template>
        </div>
      </div>
    </template>
  </Main>
</template>

<style scoped>
/* Add any component-specific styles here */
.adjust {
  top: 80px;
  left: 255px;
}
@media (max-width: 640px) {
  .tab-content {
    height: calc(100vh - 150px);
  }
}
</style>