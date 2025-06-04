<script setup>
import Create from "@/Components/Inventory/rboinventitemretailgroups/Create.vue";
import Update from "@/Components/Inventory/rboinventitemretailgroups/Update.vue";
import Delete from "@/Components/Inventory/rboinventitemretailgroups/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import { ref } from "vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";


import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const GROUPID = ref('');
const NAME = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    rboinventitemretailgroups: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'GROUPID', title: 'GROUPID' },
    { data: 'NAME', title: 'NAME' },
   
    {
        data: null,
        render: '#action',
        title: 'Action'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
};


const toggleUpdateModal = (newGROUPID, newNAME) => {
    GROUPID.value = newGROUPID;
    NAME.value = newNAME;
  
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newGROUPID) => {
    GROUPID.value = newGROUPID;
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
    <Main active-tab="RETAILGROUP">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :GROUPID="GROUPID" :NAME="NAME" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="rboinventitemretailgroups" :GROUPID="GROUPID" @toggle-active="deleteModalHandler"  />
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

                        <!-- <Excel
                            :data="customers"
                            :headers="['ACCOUNTNUM', 'NAME', 'ADDRESS', 'PHONE', 'EMAIL', 'GENDER']"
                            :row-name-props="['accountnum', 'name', 'address', 'phone', 'email', 'gender']"
                            class="ml-4 relative display"
                        /> -->

                    </div>
                </div>

                <DataTable :data="rboinventitemretailgroups" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.GROUPID, data.cellData.NAME)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.GROUPID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
