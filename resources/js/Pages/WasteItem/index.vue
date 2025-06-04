<script setup>
import Create from "@/Components/WasteItem/Create.vue";
import GetBWP from "@/Components/WasteItem/GetBWP.vue";
import CopyFrom from "@/Components/WasteItem/CopyFrom.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import Save from "@/Components/Svgs/Save.vue";
import Back from "@/Components/Svgs/Back.vue";
import Add from "@/Components/Svgs/Add.vue";
import Printer from "@/Components/Svgs/PrinterColored.vue";
import Cart from "@/Components/Svgs/BO.vue";
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
    wastedeclarationtrans: {
        type: Array,
        required: true,
    },
    wastedeclarations: {
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

const selectedItemId = ref('');

const selectedItem = computed(() => {
  return props.items.find(item => item.itemid === selectedItemId.value);
});

const columns = [
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'itemname', title: 'ITEMNAME' },
    { data: 'itemgroup', title: 'CATEGORY' },
    { data: 'REASON', title: 'DECLARATION' },
    {
        data: 'COUNTED',
        title: 'COUNTED',
        render: function (data, type, row) {
            return Math.floor(data); 
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

const WasteItem = () => {
    window.location.href = '/waste';
};

const DeleteOrders = () => {
    window.location.href = '/DeleteOrders';
};

const ViewOrders = (journalid) => {
    window.location.href = `/ViewWasteItem/${journalid}`;
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

/* const handleCountedChange = (event, item) => {
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
        
        const response = await axios.post('waste-update-all-counted-values', {
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
  window.location.href = `/warehouse/orders/${journalid}`;
};

const SYNCFG = () => {
    const userConfirmed = window.confirm('View Current Stocks');

    if (userConfirmed) {
        window.location.href = '/getcurrentstocks';
    } else {
        console.log('User cancelled the post operation.');
    }
};

const handlePrint = async () => {
    try {
        const response = await fetch(route('waste.print', { journalid: props.journalid }), {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
        });

        if (!response.ok) {
            const errorResponse = await response.json();
            console.error('Printing failed:', errorResponse.message);
            return; // Stop execution if the response isn't okay
        }

        const result = await response.json();
        if (result.success) {
            console.log('Print successful');
        } else {
            console.error('Print failed:', result.message);
        }
    } catch (error) {
        console.error('Print error:', error);
    }
};
</script>

<template>
    <Main active-tab="BO">
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
                    @click="WasteItem"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Back class="h-5" />
                </PrimaryButton>

                <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(journalid, items)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-5" />
                        </PrimaryButton>

                <!-- <PrimaryButton
                    type="button"
                    @click="toggleGetBWModal(journalid)"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    GENERATE
                </PrimaryButton> -->

                <PrimaryButton
                    type="button"
                    @click="handlePrint"
                    class="m-1 ml-2 bg-navy p-10"
                >
                    <Printer class="h-5" />
                </PrimaryButton>
                <!-- <PrimaryButton
                    type="button"
                    @click="ViewOrders(journalid)"
                    class="m-1 ml-2 bg-red-900 p-10"
                >
                    <Cart class="h-5" />
                </PrimaryButton> -->

                <!-- <PrimaryButton
                  type="button"
                  @click="navigateToOrder(journalid)"
                  class="m-1 bg-red-900"
                >
                  WAREHOUSE
                </PrimaryButton> -->
            </div>
        </div>

        <DataTable 
            :data="wastedeclarationtrans" 
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