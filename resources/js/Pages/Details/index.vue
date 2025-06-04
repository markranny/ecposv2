<script setup>
import Create from "@/Components/AddDetails/Create.vue";
import Update from "@/Components/AddDetails/Update.vue";
import GetBWP from "@/Components/AddDetails/GetBWP.vue";
import SYNC from "@/Components/AddDetails/Sync.vue";
import Post from "@/Components/AddDetails/Post.vue";
import CopyFrom from "@/Components/AddDetails/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Trash from "@/Components/Svgs/Trash.vue";
import Generate from "@/Components/Svgs/Generate.vue";
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed, reactive } from "vue";
import axios from 'axios';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const JOURNALID = ref('');
const FGENCODER = ref('');
const PLENCODER = ref('');
const DISPATCHER = ref('');
const LOGISTICS = ref('');
const ROUTES = ref('');
const DELIVERYDATE = ref('');
const CREATEDDATE = ref('');

const showModalUpdate = ref(false);
const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);
const showSYNCModal = ref(false);

const props = defineProps({
    details: {
        type: Array,
        required: true,
    },
    journalid: {
        type: [String, Number],
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const handleSelectedStore = (rbostoretables) => {
  console.log('Selected rbostoretables:', rbostoretables);
};

const columns = [
    { data: 'FGENCODER', title: 'FGENCODER' },
    { data: 'PLENCODER', title: 'PLENCODER' },
    { data: 'DISPATCHER', title: 'DISPATCHER' },
    { data: 'LOGISTICS', title: 'LOGISTICS' },
    { data: 'ROUTES', title: 'ROUTES' },
    { data: 'CREATEDDATE', title: 'CREATEDDATE' },
    { data: 'DELIVERYDATE', title: 'DELIVERYDATE' },
    {
        data: null,
        render: '#action',
        title: 'ACTIONS'
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

const toggleUpdateModal = (newFGENCODER, newPLENCODER, newDISPATCHER, newLOGISTICS, newROUTES, newCREATEDDATE, newDELIVERYDATE) => {
    FGENCODER.value = newFGENCODER;
    PLENCODER.value = newPLENCODER;
    DISPATCHER.value = newDISPATCHER;
    LOGISTICS.value = newLOGISTICS;
    ROUTES.value = newROUTES;
    CREATEDDATE.value = newCREATEDDATE;
    DELIVERYDATE.value = newDELIVERYDATE;
    showModalUpdate.value = true;
};

const toggleGetSYNCModal = (newFGENCODER, newPLENCODER, newDISPATCHER, newLOGISTICS, newROUTES, newCREATEDDATE, newDELIVERYDATE) => {
    FGENCODER.value = newFGENCODER;
    PLENCODER.value = newPLENCODER;
    DISPATCHER.value = newDISPATCHER;
    LOGISTICS.value = newLOGISTICS;
    ROUTES.value = newROUTES;
    CREATEDDATE.value = newCREATEDDATE;
    DELIVERYDATE.value = newDELIVERYDATE;
    showSYNCModal.value = true;
};

const updateModalHandler = () => {
    showModalUpdate.value = false;
};

const syncModalHandler = () => {
    showSYNCModal.value = false;
};

const toggleCreateModal = (journalid, newLINENUM) => {
    JOURNALID.value = journalid;
    LINENUM.value = newLINENUM;
    showCreateModal.value = true;
    console.log(JOURNALID.value);
};

const toggleGetBWModal = (journalid) => {
    JOURNALID.value = "CREATE DETAILS";
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
    window.location.href = '/mgcount';
};


const SYNCDR = () => {
    const userConfirmed = window.confirm('Are you sure you want to sync the details on the delivery receipt?');

    if (userConfirmed) {
        window.location.href = '/details/sync';
    } else {
        console.log('User cancelled the post operation.');
    }
};


const ViewOrders = () => {
    window.location.href = `/specialorders/vieworders`;
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
        
        const response = await axios.post('/api/sp-update-all-counted-values', {
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
    <Main active-tab="FGCOUNT">
        <template v-slot:modals>
            <Create
                :show-modal="showCreateModal"
                :JOURNALID="JOURNALID"
                :items="props.items" 
                @toggle-active="createModalHandler"
            />
            <GetBWP
                :show-modal="showGetBWModal"
                :JOURNALID="JOURNALID"
                :rbostoretables="props.rbostoretables" 
                @toggle-active="GetBWModalHandler"
                @select-item="handleSelectedStore"  
            />
            <CopyFrom
                :show-modal="showGetCFModal"
                :JOURNALID="JOURNALID"
                :rbostoretables="props.rbostoretables"  
                @select-item="handleSelectedStore"
                @toggle-active="GetCFModalHandler"
            />
            <Update 
                :show-modal="showModalUpdate" 
                :FGENCODER="FGENCODER" 
                :PLENCODER="PLENCODER" 
                :DISPATCHER="DISPATCHER" 
                :LOGISTICS="LOGISTICS" 
                :ROUTES="ROUTES" 
                :CREATEDDATE="CREATEDDATE" 
                :DELIVERYDATE="DELIVERYDATE" 
                @toggle-active="updateModalHandler"
            />
            <SYNC 
                :show-modal="showSYNCModal" 
                :FGENCODER="FGENCODER" 
                :PLENCODER="PLENCODER" 
                :DISPATCHER="DISPATCHER" 
                :LOGISTICS="LOGISTICS" 
                :ROUTES="ROUTES" 
                :CREATEDDATE="CREATEDDATE" 
                :DELIVERYDATE="DELIVERYDATE" 
                @toggle-active="syncModalHandler"
            />
        </template>

        <template v-slot:main>
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
                    ADD DETAILS
                </PrimaryButton>
            
            </div>
        </div>

        <DataTable 
            :data="details" 
            :columns="columns" 
            class="w-full relative display" 
            :options="options"
        >
            <template #action="data">
                <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.FGENCODER, data.cellData.PLENCODER, data.cellData.DISPATCHER, data.cellData.LOGISTICS, data.cellData.ROUTES, data.cellData.CREATEDDATE, data.cellData.DELIVERYDATE)" class="me-1">
                            Update
                </PrimaryButton>
                <PrimaryButton
                    type="button"
                    @click="toggleGetSYNCModal(data.cellData.FGENCODER, data.cellData.PLENCODER, data.cellData.DISPATCHER, data.cellData.LOGISTICS, data.cellData.ROUTES, data.cellData.CREATEDDATE, data.cellData.DELIVERYDATE)"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    SYNC DR
                </PrimaryButton>
            </template>
        </DataTable>
    </TableContainer>
</template>
    </Main>
</template>