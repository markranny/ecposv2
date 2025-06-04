<script setup>
/* import Create from "@/Components/Journaltrans/Create.vue";
import Update from "@/Components/Journaltrans/Update.vue";
import Delete from "@/Components/Journaltrans/Delete.vue"; */
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const JOURNALID = ref('');
const LINENUM = ref('');
const TRANSDATE = ref('');
const ITEMID = ref('');
const ADJUSTMENT = ref('');
const COSTPRICE = ref('');
const PRICEUNIT = ref('');
const SALESAMOUNT = ref('');
const INVENTONHAND = ref('');
const COUNTED = ref('');
const REASONREFRECID = ref('');
const VARIANTID = ref('');
const POSTED = ref('');
const POSTEDDATETIME = ref('');
const UNITID = ref('');
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
    { data: 'JOURNALID', title: 'JOURNALID' },
    { data: 'LINENUM', title: 'LINENUM' },
    { data: 'TRANSDATE', title: 'TRANSDATE' },
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'ADJUSTMENT', title: 'ADJUSTMENT' },
    { data: 'COSTPRICE', title: 'COSTPRICE' },
    { data: 'PRICEUNIT', title: 'PRICEUNIT' },
    { data: 'SALESAMOUNT', title: 'SALESAMOUNT' },
    { data: 'INVENTONHAND', title: 'INVENTONHAND' },
    { data: 'COUNTED', title: 'COUNTED' },
    { data: 'REASONREFRECID', title: 'REASONREFRECID' },
    { data: 'VARIANTID', title: 'VARIANTID' },
    { data: 'POSTED', title: 'POSTED' },
    { data: 'POSTEDDATETIME', title: 'POSTEDDATETIME' },
    { data: 'UNITID', title: 'UNITID' },
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


const toggleUpdateModal = (newJOURNALID, newLINENUM, newTRANSDATE, newITEMID, newADJUSTMENT, newCOSTPRICE, newPRICEUNIT, newSALESAMOUNT, newINVENTONHAND, newCOUNTED, newREASONREFRECID, newVARIANTID, newPOSTED, newPOSTEDDATETIME,newUNITID) => {
    JOURNALID.value = newJOURNALID;
    LINENUM.value = newLINENUM;
    TRANSDATE.value = newTRANSDATE;
    ITEMID.value = newITEMID;
    ADJUSTMENT.value = newADJUSTMENT;
    COSTPRICE.value = newCOSTPRICE;
    PRICEUNIT.value = newPRICEUNIT;
    SALESAMOUNT.value = newSALESAMOUNT;
    INVENTONHAND.value = newINVENTONHAND;
    COUNTED.value = newCOUNTED;
    REASONREFRECID.value = newREASONREFRECID;
    VARIANTID.value = newVARIANTID;
    POSTED.value = newPOSTED;
    POSTEDDATETIME.value = newPOSTEDDATETIME;
    UNITID.value = newUNITID;

    showModalUpdate.value = true;
};
const toggleDeleteModal = (newJOURNALID) => {
    JOURNALID.value = newJOURNALID;
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
    <Main >
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :JOURNALID="JOURNALID" :LINENUM="LINENUM" :TRANSDATE="TRANSDATE" :ITEMID="ITEMID" :ADJUSTMENT="ADJUSTMENT" :COSTPRICE="COSTPRICE" :PRICEUNIT="PRICEUNIT" :SALESAMOUNT="SALESAMOUNT" :INVENTONHAND="INVENTONHAND" :COUNTED="COUNTED" :REASONREFRECID="REASONREFRECID" :VARIANTID="VARIANTID" :POSTED="POSTED" :POSTEDDATETIME="POSTEDDATETIME" :UNITID="UNITID" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="inventjournaltrans" :JOURNALID="JOURNALID" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <!-- <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton> -->
            <TableContainer>
                <DataTable :data="inventjournaltrans" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.JOURNALID, data.cellData.LINENUM, data.cellData.TRANSDATE, data.cellData.ADJUSTMENT , data.cellData.TYPE , data.cellData.COSTPRICE , data.cellData.PRICEUNIT , data.cellData.SALESAMOUNT , data.cellData.INVENTONHAND , data.cellData.COUNTED , data.cellData.REASONREFRECID , data.cellData.VARIANTID , data.cellData.POSTED , data.cellData.POSTEDDATETIME , data.cellData.UNITID)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.JOURNALID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
