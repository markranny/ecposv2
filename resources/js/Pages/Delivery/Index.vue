<script setup>
import Create from "@/Components/ReceivedOrders/Create.vue";
import Update from "@/Components/ReceivedOrders/Update.vue";
import Post from "@/Components/ReceivedOrderlist/Post.vue";

import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import Main from "@/Layouts/Main.vue";
import Send from "@/Components/Svgs/Send.vue";

import Add from "@/Components/Svgs/Add.vue";    

import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, onMounted, onUnmounted } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const journalid = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showSendModal = ref(false);
const showModalMore = ref(false);
const showPostModal = ref(false);

const props = defineProps({
    receivedordertables: {
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
    { data: 'description', title: 'TO #' },
    { data: 'qty', title: 'QTY' },
    { data: 'createddatetime', title: 'DATE' },
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

const toggleCreateModal = () => {
    showCreateModal.value = true;
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

const navigateToOrder = (journalid) => {
  console.log('Redirecting to Item Order Entries for account:', journalid);
  window.location.href = `/ReceivedItems/${journalid}`;
};


const post = async (journalId) => {
  console.log('Starting post operation for journalId:', journalId);
  
  try {
    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    console.log('CSRF Token found:', !!csrfToken);
    
    if (!csrfToken) {
      throw new Error('CSRF token not found');
    }

    console.log('Sending POST request to /post-receivedorder');
    const postResponse = await fetch('/post-receivedorder', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken.getAttribute('content')
      },
      body: JSON.stringify({ journalid: journalId })
    });

    const responseData = await postResponse.json();
    console.log('Server response:', responseData);

    if (!postResponse.ok) {
      throw new Error(`Server responded with status: ${postResponse.status}, message: ${responseData.message || 'Unknown error'}`);
    }

    alert('Receive Order has been successfully posted!');
    console.log('Reloading page after successful post');
    location.reload();
    
  } catch (error) {
    console.error('Post operation failed:', {
      error: error.message,
      stack: error.stack
    });
    alert('Error posting and sending order: ' + error.message);
  }
};


async function generateAndDownloadTextFile() {
    if (!props.orders || props.orders.length === 0) {
        alert("No orders available to generate file.");
        return;
    }

    const storeID = props.orders[0].STOREID;
    if (!storeID) {
        alert("Store ID is missing from the order data.");
        return;
    }

    const postedDate = new Date(props.orders[0].POSTEDDATETIME);
    if (isNaN(postedDate.getTime())) {
        alert("Invalid posted date in the order data.");
        return;
    }

    const formattedPostedDate = `${postedDate.getFullYear()}${(postedDate.getMonth() + 1).toString().padStart(2, '0')}${postedDate.getDate().toString().padStart(2, '0')}`;
    const filename = `${storeID}${formattedPostedDate}.txt`;
    const header = `${storeID}|${postedDate.toLocaleDateString()}`;
    
    const dataRows = props.orders
        .filter(order => order.COUNTED > 0)
        .map(order => `${order.ITEMID}|${Math.floor(order.COUNTED)}`);

    if (dataRows.length === 0) {
        alert("No items with non-zero counts to report.");
        return;
    }

    const content = [header, ...dataRows].join('\n');

    const folderName = formattedPostedDate;

    try {
        const response = await fetch('/save-file', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ content, filename, folderName })
        });

        const responseText = await response.text();
        console.log('Full server response:', responseText);

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (error) {
            console.error('Error parsing JSON:', error);
            alert('Error: Server returned an invalid response. Please clear your cache!');
            return;
        }

        if (data.success) {
            alert(`Your order has been successfully sent to the head office!`);
            location.reload();
        } else {
            alert('Error saving file: ' + (data.message || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving file: ' + error.message);
    }
}

const showWarning = ref(false);
const currentTime = ref('');

let audio;

const startWarningSound = () => {
  console.log('Starting warning sound');
  if (!audio) {
    audio = new Audio('/audio/warning.ogg');
    audio.loop = true;
  }
  audio.play().then(() => {
    console.log('Audio started playing');
  }).catch(error => {
    console.error('Error playing audio:', error);
  });
};

const stopWarningSound = () => {
  if (audio) {
    audio.pause();
    audio.currentTime = 0;
  }
};


const checkTime = () => {
  const now = new Date();
  const hours = now.getHours();
  const minutes = now.getMinutes();
  
  console.log(`Checking time: ${hours}:${minutes}`);
  
  if ((hours === 13 && minutes === 32) || (hours === 16 && minutes === 40)) {
    console.log('Warning condition met!');
    showWarning.value = true;
    currentTime.value = now.toLocaleTimeString();
    startWarningSound();
  }
};

const closeWarning = () => {
  showWarning.value = false;
  stopWarningSound();
};

let interval;

onMounted(() => {
  audio = new Audio('/audio/warning.ogg');
  audio.loop = true;
  interval = setInterval(checkTime, 60000);
  checkTime();
});

onUnmounted(() => {
  clearInterval(interval);
  stopWarningSound();
});
</script>

<template>
    <div v-if="showWarning" class="fixed inset-0 flex items-center justify-center z-50">
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg">
        <p class="font-bold">Warning</p>
        <p>It's {{ currentTime }}!</p>
        <button @click="closeWarning" class="mt-2 bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">
          Close
        </button>
      </div>
    </div>
    <Main active-tab="RECEIVE">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate"  :journalid="journalid" :description="description"  @toggle-active="updateModalHandler"  />
            <SendModal :show-modal="showSendModal" item-name="inventjournaltables" :journalid="journalid" @toggle-active="sendModalHandler"  />
            <Post :show-modal="showPostModal" item-name="inventjournaltrans" :journalid="journalid" @toggle-active="postModalHandler"  />


            <More
            :show-modal="showModalMore"
            :accountnum="accountnum"
            @toggle-active="MoreModalHandler"
            />
        </template>

        <template v-slot:main>

            <TableContainer>
                
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">

                        <PrimaryButton
                        type="button"
                        @click="toggleCreateModal"
                        class="m-6 bg-navy"
                        >
                        <Add class="h-4" />
                        </PrimaryButton>

                        <PrimaryButton
                                class="ml-5 cursor-pointer bg-navy hidden"
                                @click="generateAndDownloadTextFile"
                                >
                                <Send class="h-4"></Send>
                        </PrimaryButton>

                        <label class="inline-flex items-center font-bold">
                            STOCK TRANSFER
                        </label>

                        <button @click="startWarningSound" hidden>Start Warning Sound</button>

                    </div>
                </div>

                <DataTable :data="receivedordertables" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">

                        <div class="flex justify-start">
                                <PrimaryButton
                                class="cursor-pointer bg-navy"
                                @click="navigateToOrder(data.cellData.journalid)"
                                >
                                 Received Items
                                </PrimaryButton>

                                <PrimaryButton
                                    class="ml-5 cursor-pointer bg-red-900"
                                    @click="post(data.cellData.journalid)"
                                >
                                    POST
                                </PrimaryButton>
                            </div>

                    </template>
                </DataTable>

            </TableContainer>



                <TableContainer class="hidden">
                    <div class="absolute adjust">

                        <div class="flex justify-start items-center">
                        

                            &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<button @click="generateAndDownloadTextFile">Generate and Download Text File</button>
                                

                            <SuccessButton
                                type="button"
                                @click="exportToExcel"
                                class="m-6 bg-green"
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
        </template>
    </Main>

</template>




