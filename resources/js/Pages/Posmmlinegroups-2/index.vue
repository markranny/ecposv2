<script setup>
import Create from "@/Components/POSmmlinegroups/Create.vue";
import Update from "@/Components/POSmmlinegroups/Update.vue";
import Delete from "@/Components/POSmmlinegroups/Delete.vue";

import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Back from "@/Components/Svgs/Back.vue";
import Date from "@/Components/Svgs/Date.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const OFFERID  = ref('');
const LINEGROUP = ref('');
const NOOFITEMSNEEDED = ref('');
const DESCRIPTION = ref('');

const props = defineProps({
    posmmlinegroups: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'OFFERID', title: 'OFFERID' },
    { data: 'LINEGROUP', title: 'LINEGROUP' },
    { data: 'NOOFITEMSNEEDED', title: 'NOOFITEMSNEEDED' },
    { data: 'DESCRIPTION', title: 'DESCRIPTION' },
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
};


const toggleUpdateModal = (newOFFERID, newLINEGROUP, newNOOFITEMSNEEDED, newDESCRIPTION) => {
    OFFERID.value = newOFFERID;
    LINEGROUP.value = newLINEGROUP;
    NOOFITEMSNEEDED.value = newNOOFITEMSNEEDED;
    DESCRIPTION.value = newDESCRIPTION;
    showModalUpdate.value = true;
};


const toggleDeleteModal = (newOFFERID) => {
    OFFERID.value = newOFFERID;
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

const posposmmlinegroups = () => {
  window.location.href = '/posperiodicdiscounts';
};

</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :OFFERID="OFFERID" :DESCRIPTION="DESCRIPTION" :LINEGROUP="LINEGROUP" :NOOFITEMSNEEDED="NOOFITEMSNEEDED" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posperiodicdiscounts" :OFFERID="OFFERID" @toggle-active="deleteModalHandler"  />
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
                        <Add class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="posposmmlinegroups('/posposmmlinegroups')"
                        class="m-1 bg-navy p-10"
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                    </div>
                </div>
                <DataTable :data="posperiodicdiscounts" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.OFFERID, data.cellData.DESCRIPTION, data.cellData.LINEGROUP, data.cellData.NOOFITEMSNEEDED)" class="me-1">
                            Update
                        </PrimaryButton>
                        <!-- <DangerButton type="button" @click="toggleDeleteModal(data.cellData.OFFERID)">
                            Delete
                        </DangerButton> -->
                        <DangerButton type="button" @click="navigateToPosDiscount(data.cellData.OFFERID)">
                            MIX AND MATCH
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
