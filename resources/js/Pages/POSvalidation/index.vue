<script setup>
import Create from "@/Components/POSvalid/Create.vue";
import Update from "@/Components/POSvalid/Update.vue";
import Delete from "@/Components/POSvalid/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Back from "@/Components/Svgs/Back.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const ID  = ref('');
const DESCRIPTION = ref('');
const STARTINGDATE = ref('');
const ENDINGDATE = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    posdiscvalidationperiods: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'ID', title: 'ID' },
    { data: 'DESCRIPTION', title: 'DESCRIPTION' },
    { data: 'STARTINGDATE', title: 'STARTINGDATE' },
    { data: 'ENDINGDATE', title: 'ENDINGDATE' },
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


const toggleUpdateModal = (newID, newDESCRIPTION, newSTARTINGDATE, newENDINGDATE) => {
    ID.value = newID;
    DESCRIPTION.value = newDESCRIPTION;
    STARTINGDATE.value = newSTARTINGDATE;
    ENDINGDATE.value = newENDINGDATE;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newID) => {
    ID.value = newID;
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

const navigateToPosDiscount = () => {
  window.location.href = '/posperiodicdiscounts';
};

</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :ID="ID" :DESCRIPTION="DESCRIPTION" :STARTINGDATE="STARTINGDATE" :ENDINGDATE="ENDINGDATE" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posdiscvalidationperiods" :ID="ID" @toggle-active="deleteModalHandler"  />
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
                        <Add class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="navigateToPosDiscount('/posperiodicdiscounts')"
                        class="m-1 bg-navy p-10"
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                    </div>
                </div>
                <DataTable :data="posdiscvalidationperiods" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.ID, data.cellData.DESCRIPTION, data.cellData.STARTINGDATE, data.cellData.ENDINGDATE)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.ID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
