<template>
    <Main active-tab="PICKLIST">
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
              @click="printPackingList"
              class="m-6 bg-navy "
            >
              PRINT PL
            </PrimaryButton>

            <PrimaryButton
              type="button"
              @click="printPackingList"
              class="bg-navy "
            >
              PRINT DR
            </PrimaryButton>
  
            <!-- <button @click="printPackingList" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded mt-4">
              Print Packing List
            </button> -->
          </div>
        </div>
  
        <div role="tablist" class="tabs tabs-lifted mt-10 p-5">
          <input type="radio" name="my_tabs_2" role="tab" class="tab bg-base-200 border-base-300" aria-label="PICK LIST" checked />
          <!-- <div role="tabpanel" class="tab-content bg-base-200 border-base-300 p-6"> -->
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
  
                <template v-else-if="!picklist || picklist.length === 0">
                  <div class="col-span-full text-center mt-8">
                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                      <p class="text-gray-600 text-base sm:text-lg">No Pick List Available</p>
                    </div>
                  </div>
                </template>
                
                <template v-else>
                  <div class="max-w-xl mx-auto bg-white shadow-lg" ref="printableContent">
                    <div class="bg-blue-800 text-white text-center py-2 font-bold">
                      ELJIN CORPORATION
                    </div>
  
                    <div class="bg-blue-600 text-white text-center py-1 font-semibold">
                      PACKING LIST
                    </div>
  
                    <div class="bg-blue-400 text-white text-center py-1">
                      DELIVERY DATE: {{ getCurrentDate() }}
                    </div>
  
                    <div class="w-full px-4 mb-8">
                      <div class="flex bg-gray-200 font-semibold">
                        <div class="w-1/2 p-2 border-r border-gray-400">PRODUCT</div>
                        <div class="w-1/4 p-2 text-center border-r border-gray-400">{{ picklist[0]?.STORENAME || 'STORE' }}</div>
                        <div class="w-1/4 p-2 text-center">ACTUAL</div>
                      </div>
  
                      <div class="divide-y divide-gray-300">
                        <div v-for="item in picklist" :key="item.ITEMID" class="flex">
                          <div class="w-1/2 p-2 border-r border-gray-300">{{ item.ITEMNAME }}</div>
                          <div class="w-1/4 p-2 text-center border-r border-gray-300">{{ formatNumber(item.COUNTED) }}</div>
                          <div class="w-1/4 p-2 text-center">
                            <!-- <input 
                              v-model="item.actual"
                              type="number" 
                              step="0.01"
                              class="w-full text-center border border-gray-300 rounded"
                              @input="formatActualInput(item)"
                            > -->
                            <input 
                              v-model="item.actual"
                              type="number"
                              class="w-full text-center border border-gray-300 rounded"
                              @input="formatActualInput(item)"
                            >
                          </div>
                        </div>
                        
                        <div class="flex bg-red-200">
                          <div class="w-1/2 p-2 border-r border-gray-300">TOTAL</div>
                          <div class="w-1/4 p-2 text-center border-r border-gray-300">{{ formatNumber(calculateTotal) }}</div>
                          <div class="w-1/4 p-2 text-center">{{ formatNumber(calculateActualTotal) }}</div>
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
                          <td class="border-b border-l border-gray-300 p-2 text-sm text-right">{{ getCurrentDate() }}</td>
                        </tr>
                        <tr>
                          <td class="border-r border-gray-300 p-2 font-semibold">LOGISTICS:</td>
                          <td class="p-2">
                            <div>SIGN OVER PRINTED NAME</div>
                            <div class="border-b border-gray-300 mt-4"></div>
                          </td>
                          <td class="border-l border-gray-300 p-2 text-sm text-right">{{ getCurrentDate() }}</td>
                        </tr>
                      </table>
                    </div>
                  </div>
                </template>
              </div>
            </div>
          </div>
  
          <input type="radio" name="my_tabs_2" role="tab" class="tab" aria-label="DR" />
          <div role="tabpanel" class="tab-content bg-base-100 border-base-200 p-6">
            DR
          </div>
        </div>
      </template>
    </Main>
  </template>
  
  <script setup>
  import { ref, computed } from "vue";
  import { usePage } from '@inertiajs/vue3';
  import Create from "@/Components/Partycakes/Create.vue";
  import Update from "@/Components/Partycakes/Update.vue";
  import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
  import Main from "@/Layouts/AdminPanel.vue";
  import PrintColored from "@/Components/Svgs/PrintColored.vue";
  
  const page = usePage();
  const picklist = ref(page.props.picklist.map(item => ({
    ...item,
    actual: item.COUNTED 
  })));
  
  const id = ref('');
  const subject = ref('');
  const description = ref('');
  
  const showModalUpdate = ref(false);
  const showCreateModal = ref(false);
  
  const isLoading = ref(false);
  const error = ref(null);
  
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
  
  const calculateTotal = computed(() => {
    return picklist.value
      .reduce((sum, item) => sum + parseFloat(item.COUNTED || 0), 0);
  });
  
  const calculateActualTotal = computed(() => {
    return picklist.value
      .reduce((sum, item) => sum + parseFloat(item.actual || 0), 0);
  });
  
  const getCurrentDate = () => {
    const date = new Date();
    return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear().toString().substr(-2)}`;
  };
  
  /* const formatNumber = (value) => {
    const num = parseFloat(value);
    return Number.isInteger(num) ? num.toFixed(0) : num.toFixed(2);
  }; */

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


  
  const saveList = () => {
    console.log('Save functionality to be implemented');
  };
  
  const printableContent = ref(null);
  
  const printPackingList = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  const tableContent = picklist.value.map(item => `
    <tr>
      <td class="border p-2">${item.ITEMNAME}</td>
      <td class="border p-2 text-center">${formatNumber(item.COUNTED)}</td>
    </tr>
  `).join('');

  windowPrint.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page { size: A4 portrait; }
          body { font-family: Arial, sans-serif; }
          .container { width: 50%; float: left; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid black; padding: 4px; font-size: 12px; }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 4px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST</div>
          <div class="bg-blue-400">DELIVERY DATE: ${getCurrentDate()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-2">PRODUCT</th>
                <th class="border p-2">${picklist.value[0]?.STORENAME || 'STORE'}</th>
              </tr>
            </thead>
            <tbody>
              ${tableContent}
              <tr class="bg-red-200">
                <td class="border p-2 font-bold">TOTAL</td>
                <td class="border p-2 text-center font-bold">${formatNumber(calculateTotal.value)}</td>
              </tr>
              <tr>
                <td>
                  <div>DISPATCHER: SIGN OVER PRINTED NAME</div>
                  <div style="border-bottom: 1px solid #ccc; margin-top: 16px;"></div>
                </td>
                <td class="text-sm text-right">${getCurrentDate()}</td>
              </tr>
              <tr>
                <td>
                  <div>LOGISTICS: SIGN OVER PRINTED NAME</div>
                  <div style="border-bottom: 1px solid #ccc; margin-top: 16px;"></div>
                </td>
                <td class="text-sm text-right">${getCurrentDate()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </body>
    </html>
  `);
  windowPrint.document.close();
  windowPrint.focus();
  windowPrint.print();
  windowPrint.close();
};

/* const printPackingList = () => {
  const windowPrint = window.open('', '', 'left=0,top=0,width=800,height=600,toolbar=0,scrollbars=0,status=0');
  
  const tableContent = picklist.value.map(item => `
    <tr>
      <td class="border p-2">${item.ITEMNAME}</td>
      <td class="border p-2 text-center">${formatNumber(item.COUNTED)}</td>
    </tr>
  `).join('');

  windowPrint.document.write(`
    <html>
      <head>
        <title>Packing List</title>
        <style>
          @page { size: A4 portrait; }
          body { font-family: Arial, sans-serif; }
          .container { width: 100%; max-width: 800px; margin: 0 auto; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid black; padding: 4px; font-size: 12px; }
          .text-center { text-align: center; }
          .bg-blue-800 { background-color: #2b6cb0; color: white; text-align: center; padding: 4px 0; font-weight: bold; }
          .bg-blue-600 { background-color: #3182ce; color: white; text-align: center; padding: 2px 0; font-weight: 600; }
          .bg-blue-400 { background-color: #4299e1; color: white; text-align: center; padding: 2px 0; }
          .bg-red-200 { background-color: #fed7d7; }
          .signature-line { border-bottom: 1px solid #ccc; margin-top: 16px; }
          .text-right { text-align: right; }
          .text-red { color: red; }
          .font-semibold { font-weight: 600; }
          .font-bold { font-weight: bold; }
        </style>
      </head>
      <body>
        <div class="container">
          <div class="bg-blue-800">ELJIN CORPORATION</div>
          <div class="bg-blue-600">PACKING LIST</div>
          <div class="bg-blue-400">DELIVERY DATE: ${getCurrentDate()}</div>
          <table>
            <thead>
              <tr>
                <th class="border p-2">PRODUCT</th>
                <th class="border p-2">${picklist.value[0]?.STORENAME || 'STORE'}</th>
              </tr>
            </thead>
            <tbody>
              ${tableContent}
              <tr class="bg-red-200">
                <td class="border p-2 font-bold">TOTAL</td>
                <td class="border p-2 text-center font-bold">${formatNumber(calculateTotal.value)}</td>
              </tr>
            </tbody>
          </table>
          <table style="margin-top: 20px;">
            <tbody>
              <tr>
                <td class="text-red font-semibold" style="width: 20%;">DISPATCHER:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${getCurrentDate()}</td>
              </tr>
              <tr>
                <td class="font-semibold" style="width: 20%;">LOGISTICS:</td>
                <td style="width: 60%;">
                  <div>SIGN OVER PRINTED NAME</div>
                  <div class="signature-line"></div>
                </td>
                <td class="text-right" style="width: 20%;">${getCurrentDate()}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </body>
    </html>
  `);
  windowPrint.document.close();
  windowPrint.focus();
  windowPrint.print();
  windowPrint.close();
}; */
  </script>