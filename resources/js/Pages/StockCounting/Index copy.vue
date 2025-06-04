<script setup>
import { useRouter } from 'vue-router'
import Create from "@/Components/Orders/Create.vue";
import Update from "@/Components/Orders/Update.vue";
import Post from "@/Components/ItemOrders/Post.vue";
/* import Delete from "@/Components/Orders/Delete.vue";
import More from "@/Components/Orders/More.vue"; */

import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import PostIcon from "@/Components/Svgs/Post.vue";

import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed } from "vue";

import txtfile from "@/Pages/Reports/TxtFile.vue";


import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

/* const JOURNALID = ref('');
const DESCRIPTION = ref('');
const CREATEDDATETIME = ref('');
const STOREID = ref(''); */

const journalid = ref('');
const description = ref('');
const createddatetime = ref('');
const storeid = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);
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

const columns = [
    { data: 'journalid', title: 'ID' },
    { data: 'posted', title: 'Posted' },
    { data: 'description', title: 'Description' },
    { data: 'createddatetime', title: 'DATE' },
    {
        data: null,
        render: '#action',
        title: 'Actions'
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
const toggleDeleteModal = (newJOURNALID) => {
    journalid.value = newJOURNALID;
    showDeleteModal.value = true;
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
const deleteModalHandler = () => {
    showDeleteModal.value = false;  
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
  window.location.href = `/ItemOrders/${journalid}`;
};








const currentDate = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day = String(now.getDate()).padStart(2, '0');
    return `${year}${month}${day}`;
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

function generateTextFileContent(orders, columns) {
    const dataRows = orders.map(order => {
        const rowData = columns.map(column => {
            if (column.data === 'POSTEDDATETIME') {
                const date = new Date(order[column.data]);
                return date.toLocaleDateString();
            } else if (column.data === 'COUNTED') {
                // Ensure COUNTED is formatted as an integer
                return Math.floor(order[column.data]) || ''; // Use Math.floor to remove decimals
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

function generateAndDownloadTextFile() {
    const storeID = props.orders[0].STOREID;
    const postedDate = new Date(props.orders[0].POSTEDDATETIME); // Assuming all orders have the same POSTEDDATETIME
    const formattedDate = `${postedDate.getFullYear()}${(postedDate.getMonth() + 1).toString().padStart(2, '0')}${postedDate.getDate().toString().padStart(2, '0')}`;
    const filename = `${storeID}${formattedDate}.txt`;
    const header = `${storeID}|${postedDate.toLocaleDateString()}`;
    const dataRows = props.orders.map(order => `${order.ITEMID}|${Math.floor(order.COUNTED)}`);
    const content = [header, ...dataRows].join('\n');

    downloadTextFile(filename, content);
}
</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate"  :journalid="journalid" :description="description"  @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="inventjournaltables" :journalid="journalid" @toggle-active="deleteModalHandler"  />
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

                        <!-- <Excel
                            :data="customers"
                            :headers="['ACCOUNTNUM', 'NAME', 'ADDRESS', 'PHONE', 'EMAIL', 'GENDER']"
                            :row-name-props="['accountnum', 'name', 'address', 'phone', 'email', 'gender']"
                            class="ml-4 relative display"
                        /> -->

                    </div>
                </div>

                <DataTable :data="inventjournaltables" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">

                        <div class="flex justify-start">
                            <TransparentButton type="button" @click="toggleUpdateModal(
                                data.cellData.journalid, data.cellData.description)" class="me-1">
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>

                            <!-- <TransparentButton :url="route('customer.index', { accountnum: form.accountnum })">
                                <moreblue class="h-6 ml-5 cursor-pointer"></moreblue>
                            </TransparentButton> -->
                                <!-- <TransparentButton @click="redirectToLedger(accountNumber)">
                                    <moreblue class="h-6 ml-5 cursor-pointer" />
                                </TransparentButton> -->
                                <!-- <TransparentButton
                                    type="button"
                                    @click="
                                    toggleMoreModal(
                                        data.cellData.accountnum
                                    )
                                    "
                                    class="ml-5 cursor-pointer"
                                    >
                                    <moreblue class="h-6"></moreblue>
                                </TransparentButton> -->

                                <TransparentButton
                                class="ml-5 cursor-pointer"
                                @click="navigateToOrder(data.cellData.journalid)"
                                >
                                <moreblue class="h-6"></moreblue>
                                </TransparentButton>

                                <TransparentButton
                                class="ml-5 cursor-pointer"
                                @click="togglePostModal(data.cellData.journalid)"
                                >
                                <PostIcon class="h-6 w-6"></PostIcon>
                                </TransparentButton>
                            </div>


                        <!-- <DangerButton type="button" @click="toggleDeleteModal(data.cellData.accountnum)">
                            Delete
                        </DangerButton> -->

                        <!-- <button class=" ms-2 p-10px inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"  @click="navigateToCustomerLedger('/customerledgerentries/', { accountnum: data.cellData.accountnum })">MORE</button> -->

                    </template>
                </DataTable>

            </TableContainer>



            <TableContainer>
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

    <!-- <txtfile class=""></txtfile> -->
</template>




