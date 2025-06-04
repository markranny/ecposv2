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
import Refresh from "@/Components/Svgs/Refresh.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const offerid = ref('');
const linegroup = ref('');
const noofitemsneeded = ref('');
const description = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    posmmlinegroups: {
        type: Array,
        required: true,
    },
    offerid: {
        type: [String, Number],
        required: true,
    },
});

const columns = [
    { data: 'offerid', title: 'OFFERID' },
    { data: 'linegroup', title: 'LINEGROUP' },
    { data: 'noofitemsneeded', title: 'NO OF ITEMS' },
    { data: 'description', title: 'DESCRIPTION' },
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
    offerid.value = newOFFERID;
    linegroup.value = newLINEGROUP;
    noofitemsneeded.value = newNOOFITEMSNEEDED;
    description.value = newDESCRIPTION;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newLINEGROUP) => {
    LINEGROUP.value = newLINEGROUP;
    showDeleteModal.value = true;
};

const toggleCreateModal = (offerid) => {
    if (offerid && typeof offerid === 'object' && 'offerid' in offerid) {
        offerid.value = offerid.offerid;
    } else if (offerid) {
        offerid.value = offerid;
    } else {
        offerid.value = null;
    }

    showCreateModal.value = true;
    console.log(offerid.value); 
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

/* const navigateToposmmlinegroups = (offerid) => {
  console.log('Redirecting to Line Group Entries:', offerid);
  window.location.href = `/POSMMMLINEGROUPS/${offerid}`;
}; */

const reload = (offerid) => {
  /* console.log('Redirecting to Line Group Entries:', offerid); */
  window.location.href = `/POSMMMLINEGROUPS/${offerid}`;
};

</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" :offerid="offerid" @toggle-active="createModalHandler" />
            <Update :show-modal="showModalUpdate" :offerid="offerid" :description="description" :linegroup="linegroup" :noofitemsneeded="noofitemsneeded"
            @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posmmlinegroups" :linegroup="linegroup" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <!-- <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton> -->
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">

                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(offerid)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="posmmlinegroups"
                        class="m-1 ml-6 bg-navy p-10"
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                        <DangerButton
                        type="button"
                        @click="reload(offerid)"
                        class="m-1 bg-navy p-10 ml-6"
                        >
                        <Refresh class="h-6" />
                        </DangerButton>

                    </div>
                </div>
                <DataTable :data="posmmlinegroups" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.ID, data.cellData.DESCRIPTION, data.cellData.STARTINGDATE, data.cellData.ENDINGDATE)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.LINEGROUP)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
