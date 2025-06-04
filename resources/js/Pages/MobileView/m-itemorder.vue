<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Categories from "@/Components/Svgs/Categories.vue";
import Announcement from "@/Components/Svgs/Announcement.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Order from "@/Components/Svgs/Order.vue";
import Register from "@/Components/Svgs/Register.vue";
import Store from "@/Components/Svgs/Store.vue";
import Opic from "@/Components/Svgs/Opic.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Finish from "@/Components/Svgs/Finish.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import Receipt from "@/Components/Svgs/Picklist.vue";
import List from "@/Components/Nav/List.vue";
import Cart from "@/Components/Svgs/Cart.vue";


import Create from "@/Components/ItemOrders/Create.vue";
import GetBWP from "@/Components/ItemOrders/GetBWP.vue";
import CopyFrom from "@/Components/ItemOrders/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Generate from "@/Components/Svgs/Generate.vue";
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, reactive } from "vue";
import axios from 'axios';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const JOURNALID = ref('');
const COUNTED = ref('');
const LINENUM = ref('');

const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    inventjournaltransrepos: {
        type: Array,
        required: true,
    },
    inventjournaltrans: {
        type: Array,
        required: true,
    },
    journalid: {
        type: [String, Number],
        required: true,
    },
    items: {
        type: Array,
        required: true, 
    },
});

const columns = [
    { data: 'itemname', title: 'ITEMNAME' },
    { 
        data: 'COUNTED', 
        title: 'COUNTED',
        render: function(data, type, row) {
            if (type === 'display') {
                return `<input type="number" class="counted-input form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="${Number(data).toFixed(0)}" min="0" step="1">`;
            }
            return data;
        }
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
    drawCallback: function(settings) {
        const api = this.api();
        api.rows().every(function() {
            const rowData = this.data();
            const node = this.node();
            const input = node.querySelector('.counted-input');
            if (input) {
                input.addEventListener('change', (event) => handleCountedChange(event, rowData));
            }
        });
    }
};

const toggleCreateModal = (journalid, newLINENUM) => {
    JOURNALID.value = journalid;
    LINENUM.value = newLINENUM;
    showCreateModal.value = true;
    console.log(JOURNALID.value);
};

const toggleGetBWModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetBWModal.value = true;
    console.log(JOURNALID.value);
};

const toggleGetCFModal = (journalid) => {
    JOURNALID.value = journalid;
    showGetCFModal.value = true;
    console.log(JOURNALID.value);
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const GetBWModalHandler = () => {
    showGetBWModal.value = false;
};

const GetCFModalHandler = () => {
    showGetCFModal.value = false;
};

const form = useForm({
    StartDate: '',
    EndDate: '',
});

const ItemOrders = () => {
    window.location.href = '/order';
};

const DeleteOrders = () => {
    window.location.href = '/DeleteOrders';
};

const ViewOrders = (journalid) => {
    window.location.href = `/ViewOrders/${journalid}`;
};

const handleSelectedItem = (item) => {
    console.log('Selected Item:', item);
};

// Reactive state
const tableData = ref([]);
const updatedValues = reactive({});
const message = reactive({
    text: '',
    type: '' // 'success', 'error', or 'info'
});

// Methods
const handleCountedChange = (event, item) => {
    const newValue = event.target.value;
    updatedValues[item.ITEMID] = newValue;
};

const updateAllCountedValues = async () => {
    try {
        message.text = 'Updating counted values...';
        message.type = 'info';
        
        const response = await axios.post('/api/update-all-counted-values', {
            journalId: props.journalid,
            updatedValues: updatedValues,
        });
        
        if (response.data.success) {
            console.log('All values updated successfully');
            for (const [itemId, newValue] of Object.entries(updatedValues)) {
                const item = tableData.value.find(row => row.ITEMID === itemId);
                if (item) {
                    item.COUNTED = newValue;
                }
            }
            Object.keys(updatedValues).forEach(key => delete updatedValues[key]);
            
            message.text = 'All counted values updated successfully';
            message.type = 'success';

            location.reload();
        } else {
            throw new Error('Update failed');
        }
    } catch (error) {
        console.error(`You don't have any changes!`, error);
        message.text = `You don't have any changes!`;
        message.type = 'error';
    }
    clearMessage();
};

const clearMessage = () => {
    setTimeout(() => {
        message.text = '';
        message.type = '';
    }, 5000); // Clear after 5 seconds
};
</script>

<template>
<div class="flex flex-col min-h-screen">
  <header class="sticky top-0 bg-navy shadow-md z-10 mb-5">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-sm font-bold text-white">ECORDER</h1>
        <h1 class="text-sm font-bold text-white">{{ $page.props.auth.user.name }}</h1>
    </div>
</header>

  <!-- Main content area -->
  <main class="flex-grow px-4 pb-20">

    
    <TableContainer>
        <!-- Message display area -->
        <div v-if="message.text" 
             :class="['p-4 mb-4 rounded-md', 
                      message.type === 'success' ? 'bg-green-100 text-green-700' : 
                      message.type === 'error' ? 'bg-red-100 text-red-700' : 
                      'bg-blue-100 text-blue-700']">
            {{ message.text }}
        </div>

        <div class="absolute adjust" :class="{ 'mt-20': message.text }">
            <div class="flex justify-start items-center">
                <PrimaryButton
                    type="button"
                    @click="ItemOrders"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Back class="h-5" />
                </PrimaryButton>

                <PrimaryButton
                    type="button"
                    @click="toggleGetBWModal(journalid)"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    GENERATE
                </PrimaryButton>

                <PrimaryButton
                    type="button"
                    @click="updateAllCountedValues"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Save class="h-5" />
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    @click="ViewOrders(journalid)"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Cart class="h-5" />
                </PrimaryButton>
            </div>
        </div>

        <DataTable 
            :data="inventjournaltransrepos" 
            :columns="columns" 
            class="w-full relative display" 
            :options="options"
        >
            <template #action="data">
                <label class="inline-flex items-center">
                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600 rounded-full" />
                    <span class="ml-2 text-gray-700"></span>
                </label>
            </template>
        </DataTable>
    </TableContainer>

    <TableContainer class="hidden overflow-x-auto mt-8">
      <div class="mb-4">
        <div class="flex flex-wrap justify-start items-center">
          <button @click="generateAndDownloadTextFile" class="mb-2 mr-2">Generate and Download Text File</button>
          <SuccessButton
            type="button"
            @click="exportToExcel"
            class="bg-green"
          >
            <ExcelIcon class="h-4" />
          </SuccessButton>
        </div>
      </div>
    
      <DataTable :data="orders" :columns="txtfilecolumns" class="w-full relative display" :options="options">
        <template #action="data">
          <div class="flex justify-start">
          </div>
        </template>
      </DataTable>
    </TableContainer>
  </main>

  <!-- Bottom navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-navy shadow-lg">
    <div class="flex justify-around items-center h-16 relative">
      <a href="/dashboard" class="flex flex-col items-center text-blue-400">
        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
        </svg>
        <span class="text-xs mt-1">Home</span>
      </a>
      <a href="/m-retail" class="flex flex-col items-center text-white">
        <RetailItems class="w-5 sm:w-6"></RetailItems>
        <span class="text-xs mt-1">Retail</span>
      </a>
      <button onclick="window.location.href='/m-order'"  class="absolute left-1/2 top-0 transform -translate-x-1/2 -translate-y-1/2 w-14 h-14 bg-blue-700 rounded-full shadow-lg flex items-center justify-center border-2 border-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        <!-- <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg> --><span class="text-xs mt-1 font-bold text-white">EC</span>
      </button>
      <a href="/m-partycakes" class="flex flex-col items-center text-white">
        <PartyCake class="w-5 sm:w-6"></PartyCake>
        <span class="text-xs mt-1">Cakes</span>
      </a>
      <a href="/m-orders" class="flex flex-col items-center text-white">
        <Cart class="w-5 sm:w-6"></Cart>
        <span class="text-xs mt-1">Orders</span>
      </a>
    </div>
  </nav>
</div>
</template>

<style >
</style>
