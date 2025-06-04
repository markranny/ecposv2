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


import { useRouter } from 'vue-router'
import Create from "@/Components/Orders/Create.vue";
import Update from "@/Components/Orders/Update.vue";
import Post from "@/Components/ItemOrders/Post.vue";
import SendModal from "@/Components/Orders/Send.vue";

import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";
import TxtFile from "@/Components/Svgs/TxtFile.vue";
import Send from "@/Components/Svgs/Send.vue";

import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import PostIcon from "@/Components/Svgs/Post.vue";

import TableContainer from "@/Components/Tables/TableContainer.vue";
import { openDB, addOrder, getAllOrders } from '@/IndexDB/OrderDB';
import { ref, computed } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const journalid = ref('');
const description = ref('');
const createddatetime = ref('');
const storeid = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showSendModal = ref(false);
const showModalMore = ref(false);
const showPostModal = ref(false);

const props = defineProps({
    inventjournaltables: {
        type: Array,
        required: true,
    },
    orders: {
        type: Array,
        required: true,
    },
});
const txtfilecolumns = [
    { data: 'STOREID', title: 'STOREID' },
    { 
        data: 'POSTEDDATETIME', 
        title: 'POSTEDDATETIME',
        render: function(data, type, row) {
            const date = new Date(data);
            return date.toLocaleDateString(); 
        }
    },
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'COUNTED', title: 'COUNTED' },
];

const columns = [
    { data: 'posted', title: 'POSTED' },
    {
        data: 'createddatetime',
        title: 'DATE',
        render: function (data, type, row) {
            // Format the date to YYYY-MM-DD (or any format you need)
            const date = new Date(data);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
    },
    {
        data: null,
        render: '#action',
        title: 'ACTIONS'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
};

const togglePostModal = (newJOURNALID,) => {
    journalid.value = newJOURNALID;
    showPostModal.value = true;
};


const toggleUpdateModal = (newJOURNALID, newDESCRIPTION) => {
    
    journalid.value = newJOURNALID;
    description.value = newDESCRIPTION;
    showModalUpdate.value = true;
};
const toggleSendModal = (newJOURNALID) => {
    journalid.value = newJOURNALID;
    showSendModal.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const toggleMoreModal = (newJOURNALID) => {
    journalid.value = newJOURNALID;
    showModalMore.value = true;
};


const updateModalHandler = () => {
    showModalUpdate.value = false;
};
const createModalHandler = () => {
    showCreateModal.value = false;
};
const sendModalHandler = () => {
    showSendModal.value = false;  
};
const MoreModalHandler = () => {
    showModalMore.value = false;
};
const postModalHandler = () => {
    showPostModal.value = false;
};

const router = useRouter()

const navigateToOrder = (journalid) => {
  console.log('Redirecting to Item Order Entries for account:', journalid);
  window.location.href = `/m-ItemOrders/${journalid}`;
};








const currentDate = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}${month}${day}`;
});

function generateTextFileContent(orders, columns) {
    const dataRows = orders.map(order => {
        const rowData = columns.map(column => {
            if (column.data === 'POSTEDDATETIME') {
                const date = new Date(order[column.data]);
                return date.toLocaleDateString();
            } else if (column.data === 'COUNTED') {
                return Math.floor(order[column.data]) || ''; 
            } else {
                return order[column.data] || '';
            }
        });
        return rowData.join('|');
    });

    return dataRows.join('\n');
}

function downloadTextFile(filename, content) {
  const blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

/* function generateAndDownloadTextFile() {
    const storeID = props.orders[0].STOREID;
    const postedDate = new Date(props.orders[0].POSTEDDATETIME); 
    const formattedDate = `${postedDate.getFullYear()}${(postedDate.getMonth() + 1).toString().padStart(2, '0')}${postedDate.getDate().toString().padStart(2, '0')}`;
    const filename = `${storeID}${formattedDate}.txt`;
    const header = `${storeID}|${postedDate.toLocaleDateString()}`;
    const dataRows = props.orders.map(order => `${order.ITEMID}|${Math.floor(order.COUNTED)}`);
    const content = [header, ...dataRows].join('\n');

    downloadTextFile(filename, content);
} */

async function generateAndDownloadTextFile() {
    const storeID = props.orders[0].STOREID;
    const postedDate = new Date(props.orders[0].POSTEDDATETIME);
    const formattedDate = `${postedDate.getFullYear()}${(postedDate.getMonth() + 1).toString().padStart(2, '0')}${postedDate.getDate().toString().padStart(2, '0')}`;
    const filename = `${storeID}${formattedDate}.txt`;
    const header = `${storeID}|${postedDate.toLocaleDateString()}`;
    const dataRows = props.orders.map(order => `${order.ITEMID}|${Math.floor(order.COUNTED)}`);
    const content = [header, ...dataRows].join('\n');

    try {
        const response = await fetch('/save-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ content, filename })
        });

        const data = await response.json();

        if (data.success) {
            /* alert(`File saved successfully. You can access it at: ${data.path}`); */
            alert(`Your order has been successfully sent to the head office!`);
        } else {
            alert('Error saving file');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving file');
    }
}

function saveFileToPublicDirectory(filePath, content) {
    const url = `${window.location.origin}/${filePath}`;
    const link = document.createElement('a');
    link.href = url;
    link.download = filePath.split('/').pop();
    link.style.display = 'none';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>

<template>
<div class="flex flex-col min-h-screen">
  <!-- Sticky header -->
  <header class="sticky top-0 bg-navy shadow-md z-10 mb-5">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-sm font-bold text-white">ECORDER</h1>
        <h1 class="text-sm font-bold text-white">{{ $page.props.auth.user.name }}</h1>
    </div>
</header>


  <!-- Main content area -->
  <main class="flex-grow px-4 pb-20">
    <div class="">
        <!-- <div class="flex justify-start items-center ">
            <PrimaryButton
                type="button"
                @click="toggleCreateModal"
                class="bg-red-900 w-full flex justify-center items-center"
            >
                <Add class="h-4 " />
            </PrimaryButton>
            </div>
        </div> -->

        <div class="flex justify-start items-center ">
            <PrimaryButton
                type="button" 
                @click="toggleCreateModal"
                class="bg-red-900 ml-2  "
            >
                <Add class="h-4 " />
            </PrimaryButton>
            </div>
        </div>
    <TableContainer class="overflow-x-auto">
        

      <DataTable :data="inventjournaltables" :columns="columns" class="w-full relative display" :options="options">
        <template #action="data">
          <div class="flex flex-col sm:flex-row justify-start">
            <PrimaryButton
            class="cursor-pointer bg-navy mb-2 sm:mb-0 sm:mr-2 text-center flex items-center justify-center"
            @click="navigateToOrder(data.cellData.journalid)"
            >
            ORDER
            </PrimaryButton>

            <PrimaryButton
              class="cursor-pointer bg-red-900 flex items-center justify-center"
              @click="togglePostModal(data.cellData.journalid)"
            >
              POST
            </PrimaryButton>
          </div>
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
