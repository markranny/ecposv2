<script setup>
import Create from "@/Components/SpecialOrders/Create.vue";
import GetBWP from "@/Components/SpecialOrders/GetBWP.vue";
import CopyFrom from "@/Components/SpecialOrders/CopyFrom.vue";
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
import Excel from "@/Components/Exports/Excel.vue";
import axios from 'axios';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const itemid = ref('');
const itemname = ref('');
const itemgroup = ref('');
const counted = ref('');

const showGetCFModal = ref(false);
const showGetBWModal = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    sptrans: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'STORENAME', title: 'STORENAME' },
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'itemname', title: 'ITEMNAME' },
    { data: 'TRANSDATE', title: 'DATE' },
    { data: 'itemgroup', title: 'CATEGORY' },
    { 
        data: 'COUNTED', 
        title: 'COUNTED',
        render: function(data, type, row) {
            if (type === 'display') {
                return `<input type="number" class="counted-input form-input block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" value="${Number(data).toFixed(0)}" min="0" step="1" disabled>`;
            }
            return data;
        }
    },
    { data: 'REMARKS', title: 'REMARKS' },
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
    JOURNALID.value = "SPECIAL ORDER";
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
    window.location.href = '/special-orders';
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

                <Excel
                  :data="sptrans"
                  :headers="['STORENAME','ITEMID', 'ITEMNAME', 'DATE', 'CATEGORY', 'COUNTED', 'REMARKS',]"
                  :row-name-props="['STORENAME','ITEMID', 'itemname', 'TRANSDATE', 'itemgroup', 'COUNTED','REMARKS']"
                  class="mt-4 sm:mt-0 sm:ml-4 relative display"
                />

                
            </div>
        </div>

        <DataTable 
            :data="sptrans" 
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