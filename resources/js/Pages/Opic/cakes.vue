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
import File from "@/Components/Svgs/File.vue";
import Main from "@/Layouts/AdminPanel.vue";

axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const page = usePage();
const rbostoretables = ref(page.props.rbostoretables);
const groupedPicklist = ref(Object.entries(page.props.groupedPicklist).reduce((acc, [storeName, items]) => {
  acc[storeName] = items.map(item => ({
    ...item,
    actual: item.ACTUAL
  }));
  return acc;
}, {})); 

const groupedDR = ref(Object.entries(page.props.groupedPicklist).reduce((acc, [storeName, items]) => {
  acc[storeName] = items.map(item => ({
    ...item,
    actual: item.ACTUAL
  }));
  return acc;
}, {}));

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value);
};


/* const calculateTotalCounted = (items) => {
  return items.reduce((sum, item) => sum + Number(item.COUNTED), 0);
};

const calculateTotalAdjustment = (items) => {
  return items.reduce((sum, item) => sum + Number(item.ADJUSTMENT), 0);
};

const calculateTotalCountedPlusAdjustment = (items) => {
  return items.reduce((sum, item) => sum + Number(item.COUNTED) + Number(item.ADJUSTMENT), 0);
};

const calculateTotalAmount = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.COST) * Number(item.COUNTED)), 0);
}; */

const calculateTotalTarget = (items) => {
  return items.reduce((sum, item) => sum + Number(item.CHECKINGCOUNT || 0), 0);
};

const calculateTotalAlloc = (items) => {
  return items.reduce((sum, item) => sum + Number(item.CHECKINGCOUNT || 0), 0);
};

const calculateTotalReceived = (items) => {
  // This should be updated if there's a specific field for received quantity
  return 0;
};

const calculateTotalTransferCost = (items) => {
  return items.reduce((sum, item) => sum + Number(item.COST || 0), 0);
};

const calculateTotalAmount = (items) => {
  return items.reduce((sum, item) => sum + (Number(item.COST || 0) * Number(item.CHECKINGCOUNT || 0)), 0);
};


const id = ref('');
const subject = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const isLoading = ref(false);
const error = ref(null);

const form = useForm({
  STORE: '',
});

const submitForm = () => {
form.get(route('cakepicklist.getstore'), {
  preserveScroll: true,
});
};

const StartDate = ref(null);
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

const EndDate = ref(null);
const formattedDate2 = computed(() => {
  const endDate = form.EndDate;
  if (endDate) {
    const date = new Date(endDate);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  }
  return '';
});

const toggleUpdateModal = (newID, newSUBJECT, newDESCRIPTION) => {
  id.value = newID;
  subject.value = newSUBJECT;
  description.value = newDESCRIPTION;
  showModalUpdate.value = true;
};

const toggleCreateModal = () => {
  showCreateModal.value = true;
};

const updateModalHandler = () => {
  showModalUpdate.value = false;
};

const createModalHandler = () => {
  showCreateModal.value = false;
};

const calculateTotalDT = (items) => {
  return items.reduce((sum, item) => sum + parseFloat(item.COUNTED || 0), 0);
};

const calculateTotal = (items, property) => {
  return items.reduce((total, item) => total + (item[property] || 0), 0);
};

const calculateActualTotal = (items) => {
  return items.reduce((sum, item) => sum + parseFloat(item.actual || 0), 0);
};


const getCurrentDate = () => {
  const date = new Date();
  return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear().toString().substr(-2)}`;
};

const formatNumber = (value) => {
  const num = parseFloat(value);
  return Number.isInteger(num) ? num.toString() : Math.round(num).toString();
};

const formatActualInput = (item) => {
  if (item.actual !== '') {
    const num = parseFloat(item.actual);
    item.actual = Math.round(num).toString();
  }
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
      journal_id: item.JOURNALID, // Use 'journal_id' instead of 'journalid'
      store_name: storeName,
      item_name: itemName,
      item_id: itemId,
      actual: value
    });

    if (response.data.success) {
      // Update the local state
      item.ACTUAL = value;
    } else {
      console.error('Failed to update ACTUAL value', response.data);
      // You might want to show an error message to the user here
    }
  } catch (error) {
    if (error.response && error.response.data) {
      console.error('Server validation errors:', error.response.data.errors);
      // Log all validation errors
      Object.entries(error.response.data.errors).forEach(([field, messages]) => {
        console.error(`${field}: ${messages.join(', ')}`);
      });
    } else {
      console.error('Error updating ACTUAL value:', error.message);
    }
    // You might want to show an error message to the user here
  }
};
  
const printableContent = ref(null);

const getPickListInputData = () => {
  window.location.href = '/PickListInputData';
};

const picklistreload = () => {
  window.location.href = '/picklist';
};

const BackPre = () => { 
    window.location.href = '/mgcount';
};




const printListForm = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  const stores = Object.entries(groupedPicklist.value);
  let content = '';

  for (let i = 0; i < stores.length; i += 2) {
    const pageContent = stores.slice(i, i + 2).map(([storeName, storeItems]) => {
      const tableContent = storeItems.map(item => `
        <tr>
          <td class="border p-1">${item.ITEMNAME}</td>
          <td class="border p-1 text-center">${formatNumber(item.COUNTED)}</td>
          <td class="border p-1 text-center"></td>
        </tr>
      `).join('');

      const totalCounted = calculateTotal(storeItems, 'COUNTED');
      const totalActual = calculateTotal(storeItems, 'ACTUAL');

      return `
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${storeName}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${storeName}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${tableContent}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${formatNumber(totalCounted)}</td>
                <td class="border p-1 text-center font-bold"></td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
            </tbody>
          </table>
        </div>
      `;
    }).join('');

    content += `
      <div class="page-container">
        ${pageContent}
      </div>
    `;
  }

  windowPrint.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 16px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
          }
          .store-section { 
            width: 48%; 
            max-width: 48%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 16px; 
          }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 3px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
          .signature-line { border-bottom: 1px solid #ccc; margin-top: 5px; }
          .text-right { text-align: right; }
          .text-red { color: red; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
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

const printPackingList = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  const stores = Object.entries(groupedPicklist.value);
  let content = '';

  for (let i = 0; i < stores.length; i += 2) {
    const pageContent = stores.slice(i, i + 2).map(([storeName, storeItems]) => {
      const tableContent = storeItems.map(item => `
        <tr>
          <td class="border p-1">${item.ITEMNAME}</td>
          <td class="border p-1 text-center">${formatNumber(item.COUNTED)}</td>
          <td class="border p-1 text-center">${formatNumber(item.CHECKINGCOUNT)}</td>
        </tr>
      `).join('');

      const totalCounted = calculateTotal(storeItems, 'COUNTED');
      const totalActual = calculateTotal(storeItems, 'ACTUAL');

      return `
        <div class="store-section">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST - ${storeName}</div>
          <div class="bg-blue-400">DELIVERY DATE: NOT POSTED</div>
          <table>
            <thead>
              <tr>
                <th class="border p-1">PRODUCT</th>
                <th class="border p-1">${storeName}</th>
                <th class="border p-1">ACTUAL</th>
              </tr>
            </thead>
            <tbody>
              ${tableContent}
              <tr class="bg-red-200">
                <td class="border p-1 font-bold">TOTAL</td>
                <td class="border p-1 text-center font-bold">${formatNumber(totalCounted)}</td>
                <td class="border p-1 text-center font-bold">${formatNumber(totalActual)}</td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 5px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">NOT POSTED</td>
              </tr>
            </tbody>
          </table>
        </div>
      `;
    }).join('');

    content += `
      <div class="page-container">
        ${pageContent}
      </div>
    `;
  }

  windowPrint.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page {
            size: legal portrait;
            margin: 0;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 16px;
            margin: 0;
            padding: 0;
          }
          .page-container {
            width: 100%;
            height: 100vh;
            page-break-after: always;
            padding: 10mm;
            box-sizing: border-box;
            display: flex;
            justify-content: space-between;
          }
          .store-section { 
            width: 48%; 
            max-width: 48%; 
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 5mm;
          }
          th, td { 
            border: 1px solid black; 
            padding: 2px; 
            font-size: 16px; 
          }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 3px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
          .signature-line { border-bottom: 1px solid #ccc; margin-top: 5px; }
          .text-right { text-align: right; }
          .text-red { color: red; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
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

const printDeliveryReceipt = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  let content = '';
  for (const [storeName, storeData] of Object.entries(groupedPicklist.value)) {
    content += `
      <div class="receipt-page">
        <div class="text-center mb-4">
          <h3 class="font-bold">ELIIN CORPORATION</h3>
          <p>MALIWALO</p>
          <p>TARLAC CITY</p>
          <h3 class="font-bold">DELIVERY GOODS RECEIPT: BW PRODUCTS</h3>
        </div>
        
        <div class="flex justify-between mb-4">
          <div>
            <p>DR #: ${storeData[0].JOURNALID}</p> 
            <p>DELIVERY DATE: NOT POSTED</p>
          </div>
          <div>
            <p>RECIEVED FROM:</p>
            <p>HEADOFFICE</p>
          </div>
          <div>
            <p>DELIVERED TO:</p>
            <p>${storeName}</p>
          </div>
        </div>
        
        <table class="w-full border-collapse border border-gray-300">
          <thead>
            <tr class="bg-gray-200">
              <th class="border border-gray-300 p-2">PRODUCT DESCRIPTION</th>
              <th class="border border-gray-300 p-2">TARGET</th>
              <th class="border border-gray-300 p-2">ALLOC</th>
              <th class="border border-gray-300 p-2">TOTAL</th>
              <th class="border border-gray-300 p-2">RECEIVE QUANTITY</th>
              <th class="border border-gray-300 p-2">TRANSFER COST</th>
              <th class="border border-gray-300 p-2">TOTAL AMOUNT</th>
            </tr>
          </thead>
          <tbody>
            ${storeData.map(item => `
              <tr>
                <td class="border border-gray-300 p-2">${item.ITEMNAME}</td>
                <td class="border border-gray-300 p-2 text-center">${formatNumber(item.CHECKINGCOUNT)}</td>
                <td class="border border-gray-300 p-2 text-center">0</td>
                <td class="border border-gray-300 p-2 text-center">${formatNumber(Number(item.CHECKINGCOUNT))}</td>
                <td class="border border-gray-300 p-2 text-center"></td>
                <td class="border border-gray-300 p-2 text-right">${formatCurrency(item.COST)}</td>
                <td class="border border-gray-300 p-2 text-right">${formatCurrency(Number(item.COST) * Number(item.CHECKINGCOUNT))}</td>
              </tr>
            `).join('')}
            <tr class="bg-gray-200 font-bold">
              <td class="border border-gray-300 p-2">TOTAL</td>
              <td class="border border-gray-300 p-2 text-center">${formatNumber(calculateTotalAlloc(storeData))}</td>
              <td class="border border-gray-300 p-2 text-center">0</td>
              <td class="border border-gray-300 p-2 text-center">${formatNumber(calculateTotalTarget(storeData))}</td>
              <td class="border border-gray-300 p-2 text-center"></td>
              <td class="border border-gray-300 p-2 text-right">${formatCurrency(calculateTotalTransferCost(storeData))}</td>
              <td class="border border-gray-300 p-2 text-right">${formatCurrency(calculateTotalAmount(storeData))}</td>
            </tr>
          </tbody>
        </table>
        
        <div class="mt-8 flex justify-between">
          <div>
            <p>ENDORSED BY: DISPATCHING</p>
            <p>_____________________________</p>
            <p>NAME & SIGNATURE / DATE</p>
          </div>
          <div>
            <p>RECEIVED BY STORE</p>
            <p>_____________________________</p>
            <p>NAME & SIGNATURE / DATE</p>
          </div>
        </div>
      </div>
      <div class="page-break"></div>
    `;
  }

  windowPrint.document.write(`
    <html>
      <head>
        <title>Delivery Goods Receipt</title>
        <style>
          @page {
            size: A4 portrait;
            margin: 1cm;
          }
          body { 
            font-family: Arial, sans-serif; 
            font-size: 16px;
            margin: 0;
            padding: 0;
          }
          .receipt-page {
            width: 100%;
            height: 100%;
            page-break-after: always;
          }
          table { 
            width: 100%; 
            border-collapse: collapse; 
          }
          th, td { 
            border: 1px solid black; 
            padding: 4px; 
            font-size: 16px; 
          }
          .text-center { text-align: center; }
          .text-right { text-align: right; }
          .font-bold { font-weight: bold; }
          .mb-4 { margin-bottom: 16px; }
          .mt-8 { margin-top: 32px; }
          .flex { display: flex; }
          .justify-between { justify-content: space-between; }
          .bg-gray-200 { background-color: #edf2f7; }
          .page-break { page-break-after: always; }
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
  <Main active-tab="INVENTORY">
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
                  class="m-1 ml-2 bg-navy p-10 mt-4 "
                >
                  <Back class="h-5" />
            </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="printListForm"
            class="bg-navy tooltip tooltip-right tooltip-primary mt-4 "
            data-tip="Packing List Template"
          >
          <File class="h-5" />
          </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="printPackingList"
            class="ml-1 mt-4 bg-navy"
          >
            PRINT PL
          </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="printDeliveryReceipt"
            class="ml-1 mt-4 bg-navy"
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

          <PrimaryButton
            type="button"
            @click="picklistreload"
            class="ml-2 bg-navy mt-4 "
          >
            CALCULATE
          </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="specialorder"
            class="ml-2 bg-navy mt-4 "
          >
            SPECIAL ORDER
          </PrimaryButton>

          <PrimaryButton
            type="button"
            @click="inputpartycakes"
            class="ml-2 bg-navy mt-4 "
          >
            PARTYCAKES
          </PrimaryButton>

            <details className="dropdown">
                <summary className="btn m-1 mt-4  bg-green-900 text-white hover:bg-navy">Select Route</summary>
                <ul className="menu dropdown-content bg-base-100 rounded-box z-[1] w-52 p-2 shadow">
                  <li><a href="/cakepicklist">ALL</a></li>
                  <li><a href="/plc-south1">SOUTH 1</a></li>
                  <li><a href="/plc-south2">SOUTH 2</a></li>
                  <li><a href="/plc-south3">SOUTH 3</a></li>
                  <li><a href="/plc-north1">NORTH 1</a></li>
                  <li><a href="/plc-north2">NORTH 2</a></li>
                  <li><a href="/plc-central">CENTRAL</a></li>
                  <li><a href="/plc-east">EAST</a></li>
                </ul>
            </details>

            <form @submit.prevent="submitForm" class="flex items-center mt-4">
              <input type="hidden" name="_token" :value="$page.props.csrf_token">
              
              <div class="mr-2">
                <InputLabel for="STORE" value="STORE" class="sr-only" />
                <select
                  id="STORE"
                  v-model="form.STORE" 
                  class="input input-bordered w-64"
                >
                  <option disabled value="">Select Store</option>
                  <option v-for="store in rbostoretables" :key="store.STOREID">
                    {{ store.NAME }}
                  </option>
                </select>
              </div>

              <TransparentButton type="submit" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                <Search class="h-8" />
              </TransparentButton>
            </form>
          
        </div>
      </div>

      <div role="tablist" class="tabs tabs-lifted mt-10 p-5">
        <input type="radio" name="my_tabs_2" role="tab" class="tab bg-base-200 border-base-300" aria-label="PICK LIST" checked />
        <div role="tabpanel" class="tab-content bg-base-200 border-base-300 p-6 h-[70vh] overflow-y-auto">
          <div class="container mx-auto px-4">
            <div class="flex flex-wrap -mx-4">
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
                    <p class="text-gray-600 text-base sm:text-lg">No Pick List Available</p>
                  </div>
                </div>
              </template>
              
              <template v-else>
                <div v-for="(storeItems, storeName) in groupedPicklist" :key="storeName" class="w-full mb-8">
                  <div class="max-w-xl mx-auto bg-white shadow-lg" ref="printableContent">
                    <div class="bg-blue-800 text-white text-center py-2 font-bold">
                      ELJIN CORPORATION
                    </div>

                    <div class="bg-blue-600 text-white text-center py-1 font-semibold">
                      PACKING LIST - {{ storeName }}
                    </div>

                    <div class="bg-blue-400 text-white text-center py-1">
                      DELIVERY DATE: NOT POSTED
                    </div>

                    <div class="w-full px-4 mb-8">
                      <div class="flex bg-gray-200 font-semibold">
                        <div class="w-1/2 p-2 border-r border-gray-400">PRODUCT</div>
                        <div class="w-1/4 p-2 text-center border-r border-gray-400">{{ storeName }}</div>
                        <div class="w-1/4 p-2 text-center">ACTUAL</div>
                      </div>

                      <div class="divide-y divide-gray-300">
                        <div v-for="item in storeItems" :key="item.ITEMID" class="flex">
                          <div class="w-1/2 p-2 border-r border-gray-300">{{ item.ITEMNAME }}</div>
                          <div class="w-1/4 p-2 text-center border-r border-gray-300">{{ formatNumber(item.COUNTED) }}</div>
                          <div class="w-1/4 p-2 text-center">
                            <input 
                              v-model="item.ACTUAL"
                              type="number"
                              class="w-full text-center border border-gray-300 rounded"
                              @input="updateActual(storeName, item.ITEMNAME, item.ITEMID, $event.target.value)"
                              :disabled="!item.JOURNALID || !item.ITEMID"
                              :title="!item.JOURNALID || !item.ITEMID ? 'Cannot update: Missing required data' : ''"
                            >
                          </div>
                        </div>
                        
                        <div class="flex bg-red-200">
                          <div class="w-1/2 p-2 border-r border-gray-300">TOTAL</div>
                          <div class="w-1/4 p-2 text-center border-r border-gray-300">{{ formatNumber(calculateTotalDT(storeItems)) }}</div>
                          <div class="w-1/4 p-2 text-center">{{ formatNumber(calculateActualTotal(storeItems)) }}</div>
                        </div>
                      </div>
                    </div>

                    <div class="max-w-md mx-auto border border-gray-300">
                      <table class="w-full">
                        <tr>
                          <td class="border-b border-r border-gray-300 p-2 text-red-600 font-semibold">DISPATCHER:</td>
                          <td class="border-b border-gray-300 p-2">
                            <div>SIGN OVER PRINTED NAME</div>
                            <div class="border-b border-gray-300 mt-4"></div>
                          </td>
                          <td class="border-b border-l border-gray-300 p-2 text-sm text-right">NOT POSTED</td>
                        </tr>
                        <tr>
                          <td class="border-r border-gray-300 p-2 font-semibold">LOGISTICS:</td>
                          <td class="p-2">
                            <div>SIGN OVER PRINTED NAME</div>
                            <div class="border-b border-gray-300 mt-4"></div>
                          </td>
                          <td class="border-l border-gray-300 p-2 text-sm text-right">NOT POSTED</td>
                        </tr>
                      </table>
                    </div>
                    <br>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="DR" />
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

              <template v-else-if="!groupedDR || Object.keys(groupedDR).length === 0">
                <div class="col-span-full text-center mt-8">
                  <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                    <p class="text-gray-600 text-base sm:text-lg">No DR List Available</p>
                  </div>
                </div>
              </template>
              
            <template v-else>
            <!-- div v-for="(storeData, storeName) in groupedDR" :key="storeName" class="max-w-3xl mx-auto bg-gray-100 p-8 mb-8"> -->
              <div v-for="(storeData, storeName) in groupedDR" :key="storeName" class="max-w-3xl mx-auto bg-gray-100 p-8 mb-8">
              <!-- <div v-for="(storeItems, storeName) in groupedPicklist" :key="storeName" class="w-full mb-8"></div> -->
              <div class="text-center mb-4">
                <h1 class="font-bold">ELIIN CORPORATION</h1>
                <p>MALIWALO</p>
                <p>TARLAC CITY</p>
                <h1 class="font-bold">DELIVERY GOODS RECEIPT: BW PRODUCTS</h1>
              </div>
              
              <div class="flex justify-between mb-4">
                <div>
                  <p>DR #: {{ storeData[0].JOURNALID }}</p> 
                  <p>DELIVERY DATE: NOT POSTED</p>
                </div>
                <div>
                  <p>RECIEVED FROM:</p>
                  <p>HEADOFFICE</p>
                </div>
                <div>
                  <p>DELIVERED TO:</p>
                  <p>{{ storeName }}</p>
                </div>
              </div>
              
              <table class="w-full border-collapse border border-gray-300">
                <thead>
                  <tr class="bg-gray-200">
                    <th class="border border-gray-300 p-2">PRODUCT DESCRIPTION</th>
                    <th class="border border-gray-300 p-2">TARGET</th>
                    <th class="border border-gray-300 p-2">ALLOC</th>
                    <th class="border border-gray-300 p-2">TOTAL</th>
                    <th class="border border-gray-300 p-2">RECEIVE QUANTITY</th>
                    <th class="border border-gray-300 p-2">TRANSFER COST</th>
                    <th class="border border-gray-300 p-2">TOTAL AMOUNT</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="item in storeData" :key="item.ITEMID">
                    <td class="border border-gray-300 p-2">{{ item.ITEMNAME }}</td>
                    <!-- <td class="border border-gray-300 p-2 text-center">{{ formatNumber(item.COUNTED) }}</td> -->
                    <td class="border border-gray-300 p-2 text-center">{{ formatNumber(item.CHECKINGCOUNT) }}</td>
                    <td class="border border-gray-300 p-2 text-center">0</td>
                    <td class="border border-gray-300 p-2 text-center">{{ formatNumber(Number(item.CHECKINGCOUNT)) }}</td>
                    <td class="border border-gray-300 p-2 text-center"></td>
                    <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(item.COST) }}</td>
                    <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(Number(item.COST) * Number(item.CHECKINGCOUNT)) }}</td>
                  </tr>

                  <tr class="bg-gray-200 font-bold">
                    <td class="border border-gray-300 p-2">TOTAL</td>
                    <td class="border border-gray-300 p-2 text-center">{{ formatNumber(calculateTotalAlloc(storeData)) }}</td>
                    <!-- <td class="border border-gray-300 p-2 text-center">{{ formatNumber(calculateTotalTarget(storeData)) }}</td> -->
                    <td class="border border-gray-300 p-2 text-center">0</td>
                    <td class="border border-gray-300 p-2 text-center">{{ formatNumber(calculateTotalTarget(storeData)) }}</td>
                    <!-- <td class="border border-gray-300 p-2 text-center">{{ formatNumber(calculateTotalReceived(storeData)) }}</td> -->
                    <td class="border border-gray-300 p-2 text-center"></td>
                    <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(calculateTotalTransferCost(storeData)) }}</td>
                    <td class="border border-gray-300 p-2 text-right">{{ formatCurrency(calculateTotalAmount(storeData)) }}</td>
                  </tr>
                </tbody>
              </table>
              
              
              
              <div class="mt-8 flex justify-between">
                <div>
                  <p>ENDORSED BY: DISPATCHING</p>
                  <p>_____________________________</p>
                  <p>NAME & SIGNATURE / DATE</p>
                </div>
                <div>
                  <p>RECEIVED BY STORE</p>
                  <p>_____________________________</p>
                  <p>NAME & SIGNATURE / DATE</p>
                </div>
              </div>
            </div>
          </template>

        </div>
      </div>
    </template>
  </Main>
</template>

<style>
.store-dropdown {
  max-height: 240px; 
  overflow-y: auto;
}
</style>


