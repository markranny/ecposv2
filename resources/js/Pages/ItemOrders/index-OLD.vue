<script setup>
import Create from "@/Components/ItemOrders/Create.vue";
import Update from "@/Components/Orders/Update.vue";
import Delete from "@/Components/Orders/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";

import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";


import Main from "@/Layouts/RetailPanel.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const journalid = ref('');
const ITEMNAME = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    inventjournaltrans: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'journalid', title: 'Journal ID' },
    { data: 'itemname', title: 'Item Name' },
    {
        data: null,
        render: '#action',
        title: 'Actions'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
};


const toggleUpdateModal = (newEntryno, newPostingdate, newCustomer, newType, newDocumentno, newDescription, newReasoncode, newCurrency, newCurrencyamount, newAmount, newRemainingamount, newUserid) => {
    entryno.value = newEntryno;
    postingdate.value = newPostingdate;
    customer.value = newCustomer;
    type.value = newType;
    documentno.value = newDocumentno;
    description.value = newDescription;
    reasoncode.value = newReasoncode;
    currency.value = newCurrency;
    currencyamount.value = newCurrencyamount;
    amount.value = newAmount;
    remainingamount.value = newRemainingamount;
    userid.value = newUserid;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newEntryno) => {
    entryno.value = newEntryno;
    showDeleteModal.value = true;
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
const deleteModalHandler = () => {
    showDeleteModal.value = false;
};

</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :entryno="entryno" :postingdate="postingdate" :customer="customer" :type="type" :documentno="documentno" :description="description" :reasoncode="reasoncode" :currency="currency" :currencyamount="currencyamount" :amount="amount" :remainingamount="remainingamount" :userid="userid" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="customerledgerentries" :entryno="entryno" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <!-- <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton> -->

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

                    </div>
                </div>

                <!-- <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                    <div v-for="entry in inventjournaltrans" :key="entry.inventjournaltables" class="font-bold p-5 text-blue-500">
                        <h6>{{ entry.itemname }}</h6>
                    </div>
                    </div>
                </div> -->

                <DataTable :data="inventjournaltrans" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.entryno, data.cellData.postingdate, data.cellData.customer, data.cellData.type , data.cellData.documentno , data.cellData.description , data.cellData.reasoncode , data.cellData.currency , data.cellData.currencyamount , data.cellData.amount , data.cellData.remainingamount , data.cellData.userid)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.entryno)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
