<script setup>
import Create from "@/Components/ReceivedOrderlist/Create.vue";
import GetBWP from "@/Components/ReceivedOrderlist/GetBWP.vue";
import CopyFrom from "@/Components/ReceivedOrderlist/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Add from "@/Components/Svgs/Add.vue";
import Cart from "@/Components/Svgs/Cart.vue";
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

const isLoading = ref(false);

const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    receivedordertrans: {
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
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'itemname', title: 'ITEMNAME' },
    { data: 'itemgroup', title: 'CATEGORY' },
    /* { data: 'stocks', title: 'STOCKS' },
    { data: 'moq', title: 'MOQ' }, */
    {
        data: 'COUNTED',
        title: 'COUNTED',
        render: function(data, type, row) {
            if (type === 'display') {
                const count = Number(data);
                let backgroundColor, textColor;
                
                if (count === 0) {
                    backgroundColor = '#f3f3f3';
                    textColor = 'black';
                } else if (count < Number(row.moq)) {
                    backgroundColor = 'red';
                    textColor = 'white';
                } else {
                    backgroundColor = 'white';
                    textColor = 'black';
                }
                
                return `<input type="number" class="counted-input form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="${count.toFixed(0)}" min="0" step="1" style="background-color: ${backgroundColor}; color: ${textColor};">`;
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
                // Set initial background color and text color
                const count = Number(input.value);
                let backgroundColor, textColor;
                
                if (count === 0) {
                    backgroundColor = '#f3f3f3';
                    textColor = 'black';
                } else if (count < Number(rowData.moq)) {
                    backgroundColor = 'red';
                    textColor = 'white';
                } else {
                    backgroundColor = 'white';
                    textColor = 'black';
                }
                
                input.style.backgroundColor = backgroundColor;
                input.style.color = textColor;
                
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

const Received = () => {
    window.location.href = '/Received';
};

const DeleteOrders = () => {
    window.location.href = '/DeleteOrders';
};

const ViewOrders = (journalid) => {
    window.location.href = `/ReceivedViewOrders/${journalid}`;
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
/* const handleCountedChange = (event, item) => {
    const newValue = event.target.value;
    updatedValues[item.ITEMID] = newValue;
    const isLessThanMOQ = Number(newValue) < Number(item.moq);
    const backgroundColor = isLessThanMOQ ? 'red' : 'white';
    const textColor = isLessThanMOQ ? 'white' : 'black';
    event.target.style.backgroundColor = backgroundColor;
    event.target.style.color = textColor;
}; */

const handleCountedChange = (event, item) => {
    const newValue = event.target.value;
    updatedValues[item.ITEMID] = newValue;
    
    const count = Number(newValue);
    let backgroundColor, textColor;
    
    if (count === 0) {
        backgroundColor = '#f3f3f3';
        textColor = 'black';
    } else if (count < Number(item.moq)) {
        backgroundColor = 'red';
        textColor = 'white';
    } else {
        backgroundColor = 'white';
        textColor = 'black';
    }
    
    event.target.style.backgroundColor = backgroundColor;
    event.target.style.color = textColor;
};

/* const updateAllCountedValues = async () => {
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
}; */


/* const updateAllCountedValues = async () => {
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
            
            message.text = response.data.message;
            message.type = 'success';
            location.reload();
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        console.error('Error updating values:', error);
        message.text = error.response?.data?.message || error.message || "You don't have any changes!";
        message.type = 'error';
        
        if (error.response?.data?.errors) {
            error.response.data.errors.forEach(err => {
                console.error(err);
            });
        }
    }
    clearMessage();
}; */

const updateAllCountedValues = async () => {
    try {
        isLoading.value = true;
        message.text = 'Updating counted values...';
        message.type = 'info';
        
        const response = await axios.post('/api/received-update-all-counted-values', {
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
            
            message.text = response.data.message;
            message.type = 'success';
            
            // Use setTimeout to delay the page reload
            setTimeout(() => {
                location.reload();
            }, 1000); // 1 second delay
        } else {
            throw new Error(response.data.message);
        }
    } catch (error) {
        console.error('Error updating values:', error);
        message.text = error.response?.data?.message || error.message || "You don't have any changes!";
        message.type = 'error';
        
        if (error.response?.data?.errors) {
            error.response.data.errors.forEach(err => {
                console.error(err);
            });
        }
    } finally {
        clearMessage();
        // Keep the loading state for a moment before reloading
        setTimeout(() => {
            isLoading.value = false;
        }, 500);
    }
};



const clearMessage = () => {
    setTimeout(() => {
        message.text = '';
        message.type = '';
    }, 5000); // Clear after 5 seconds
};

const navigateToOrder = (journalid) => {
  window.location.href = `/Receivedwarehouse/orders/${journalid}`;
};

const SYNCFG = () => {
    const userConfirmed = window.confirm('View Current Stocks');

    if (userConfirmed) {
        window.location.href = '/getcurrentstocks';
    } else {
        console.log('User cancelled the post operation.');
    }
};
</script>

<template>
    <Main active-tab="RECEIVE">
        <template v-slot:modals>
            <Create
                :show-modal="showCreateModal"
                :JOURNALID="JOURNALID"
                :items="props.items" 
                @toggle-active="createModalHandler"
                @select-item="handleSelectedItem"
            />
            <GetBWP
                :show-modal="showGetBWModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetBWModalHandler"
            />
            <CopyFrom
                :show-modal="showGetCFModal"
                :JOURNALID="JOURNALID"
                @toggle-active="GetCFModalHandler"
            />
        </template>

        <template v-slot:main>
    <TableContainer>
        <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 backdrop-filter backdrop-blur-sm">
            <div class="text-white text-2xl">Loading...</div>
        </div>

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
                    @click="Received"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Back class="h-5" />
                </PrimaryButton>

                <!-- <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(journalid)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-5" />
                        </PrimaryButton> -->

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

                <!-- <PrimaryButton
                type="button"
                @click="SYNCFG"
                class="sm:bg-red-500"
                >
                    Current Stocks
                </PrimaryButton> -->

                <PrimaryButton
                  type="button"
                  @click="navigateToOrder(journalid)"
                  class="m-1 bg-red-900"
                >
                  WAREHOUSE
                </PrimaryButton>
            </div>
        </div>

        <DataTable 
            :data="receivedordertrans" 
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
</template>
    </Main>
</template>