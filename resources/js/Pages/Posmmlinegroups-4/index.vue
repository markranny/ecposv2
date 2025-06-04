<script setup>
import Create from "@/Components/POSline/Create.vue";
import Update from "@/Components/POSline/Update.vue";
import Delete from "@/Components/POSline/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Back from "@/Components/Svgs/Back.vue";
import Group from "@/Components/Svgs/Group.vue";
import Refresh from "@/Components/Svgs/Refresh.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const OFFERID  = ref('');
const LINEGROUP = ref('');
const NOOFITEMSNEEDED = ref('');
const DESCRIPTION = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    posperiodicdiscountlines: {
        type: Array,
        required: true,
    },
    offerid: {
        type: [String, Number],
        required: true,
    },
});

const columns = [
    { data: 'OFFERID', title: 'OFFERID' },
    { data: 'LINEID', title: 'LINEID' },
    { data: 'LINEGROUP', title: 'LINEGROUP' },
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

const toggleUpdateModal = (newOFFERID, newLINEID, newPRODUCTTYPE, newID, newDEALPRICEORDISCPCT, newLINEGROUP, newDISCTYPE) => {
    OFFERID.value = newOFFERID;
    LINEID.value = newLINEID;
    PRODUCTTYPE.value = newPRODUCTTYPE;
    ID.value = newID;
    DEALPRICEORDISCPCT.value = newDEALPRICEORDISCPCT;
    LINEGROUP.value = newLINEGROUP;
    DISCTYPE.value = newDISCTYPE;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newLINEID) => {
    LINEID.value = newLINEID;
    showDeleteModal.value = true;
};

const toggleCreateModal = (offerid) => {
    if (offerid && typeof offerid === 'object' && 'offerid' in offerid) {
        OFFERID.value = offerid.offerid;
    } else if (offerid) {
        OFFERID.value = offerid;
    } else {
        OFFERID.value = null;
    }

    showCreateModal.value = true;
    console.log(OFFERID.value); 
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

/* const navigateToposmmlinegroups = () => {
  window.location.href = '/posmmlinegroups';
}; */

const navigateToposmmlinegroups = (offerid) => {
  console.log('Redirecting to Line Group Entries:', offerid);
  window.location.href = `/POSMMMLINEGROUPS/${offerid}`;
};

const reload = (offerid) => {
  /* console.log('Redirecting to Line Group Entries:', offerid); */
  window.location.href = `/MNM/${offerid}`;
};


</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" :OFFERID="OFFERID" @toggle-active="createModalHandler" />
            <Update :show-modal="showModalUpdate" :OFFERID="OFFERID" :LINEID="LINEID" :PRODUCTTYPE="PRODUCTTYPE" :ID="ID" :DEALPRICEORDISCPCT="DEALPRICEORDISCPCT" :LINEGROUP="LINEGROUP" :DISCTYPE="DISCTYPE" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posperiodicdiscountlines" :LINEID="LINEID" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
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
                        @click="navigateToPosDiscount('/posperiodicdiscounts')"
                        class="m-1 bg-navy p-10"
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="navigateToposmmlinegroups(offerid)"
                        class="m-1 ml-6 bg-navy p-10"
                        >
                        <Group class="h-6" />
                        </PrimaryButton>

                        <DangerButton
                        type="button"
                        @click="reload(offerid)"
                        class="m-1 bg-navy p-10 ml-6"
                        >
                        <Refresh class="h-6" />
                        </DangerButton>

                        <!-- <h3 class="text-blue-900 ml-6">Need to execute reload trigger to get offerid</h3> -->
                        

                    </div>
                </div>
                <DataTable :data="posperiodicdiscountlines" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.OFFERID, data.cellData.LINEID, data.cellData.PRODUCTTYPE, data.cellData.ID , data.cellData.DEALPRICEORDISCPCT , data.cellData.LINEGROUP , data.cellData.DISCTYPE)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.LINEID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
