<script setup>
import Create from "@/Components/Transactions/InventoryTransactions/Create.vue";
import Update from "@/Components/Transactions/InventoryTransactions/Update.vue";
import Delete from "@/Components/Transactions/InventoryTransactions/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const POSTINGDATE = ref('');
const ITEMID = ref('');
const STOREID = ref('');
const ADJUSTMENT = ref('');
const TYPE = ref('');
const COSTPRICEPERITEM = ref('');
const SALESPRICEWITHOUTTAXPERITEM = ref('');
const SALESPRICEWITHTAXPERITEM = ref('');
const REASONCODE = ref('');
const DISCOUNTAMOUNTPERITEM = ref('');
const UNITID = ref('');
const ADJUSTMENTININVENTORYUNIT = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    inventtrans: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'POSTINGDATE', title: 'POSTINGDATE' },
    { data: 'ITEMID', title: 'ITEMID' },
    { data: 'STOREID', title: 'STOREID' },
    { data: 'ADJUSTMENT', title: 'ADJUSTMENT' },
    { data: 'TYPE', title: 'TYPE' },
    { data: 'COSTPRICEPERITEM', title: 'COSTPRICEPERITEM' },
    { data: 'SALESPRICEWITHOUTTAXPERITEM', title: 'SALESPRICEWITHOUTTAXPERITEM' },
    { data: 'SALESPRICEWITHTAXPERITEM', title: 'SALESPRICEWITHTAXPERITEM' },
    { data: 'REASONCODE', title: 'REASONCODE' },
    { data: 'DISCOUNTAMOUNTPERITEM', title: 'DISCOUNTAMOUNTPERITEM' },
    { data: 'UNITID', title: 'UNITID' },
    { data: 'ADJUSTMENTININVENTORYUNIT', title: 'ADJUSTMENTININVENTORYUNIT' },
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


const toggleUpdateModal = (newPOSTINGDATE, newITEMID, newSTOREID, newADJUSTMENT, newTYPE, newCOSTPRICEPERITEM, newSALESPRICEWITHOUTTAXPERITEM, newSALESPRICEWITHTAXPERITEM, newREASONCODE, newDISCOUNTAMOUNTPERITEM, newUNITID, newADJUSTMENTININVENTORYUNIT) => {
    POSTINGDATE.value = newPOSTINGDATE;
    ITEMID.value = newITEMID;
    STOREID.value = newSTOREID;
    ADJUSTMENT.value = newADJUSTMENT;
    TYPE.value = newTYPE;
    COSTPRICEPERITEM.value = newCOSTPRICEPERITEM;
    SALESPRICEWITHOUTTAXPERITEM.value = newSALESPRICEWITHOUTTAXPERITEM;
    SALESPRICEWITHTAXPERITEM.value = newSALESPRICEWITHTAXPERITEM;
    REASONCODE.value = newREASONCODE;
    DISCOUNTAMOUNTPERITEM.value = newDISCOUNTAMOUNTPERITEM;
    UNITID.value = newUNITID;
    ADJUSTMENTININVENTORYUNIT.value = newADJUSTMENTININVENTORYUNIT;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newPOSTINGDATE) => {
    POSTINGDATE.value = newPOSTINGDATE;
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
            <Update :show-modal="showModalUpdate" :POSTINGDATE="POSTINGDATE" :ITEMID="ITEMID" :STOREID="STOREID" :ADJUSTMENT="ADJUSTMENT" :TYPE="TYPE" :COSTPRICEPERITEM="COSTPRICEPERITEM" :SALESPRICEWITHOUTTAXPERITEM="SALESPRICEWITHOUTTAXPERITEM" :SALESPRICEWITHTAXPERITEM="SALESPRICEWITHTAXPERITEM" :REASONCODE="REASONCODE" :DISCOUNTAMOUNTPERITEM="DISCOUNTAMOUNTPERITEM" :UNITID="UNITID" :ADJUSTMENTININVENTORYUNIT="ADJUSTMENTININVENTORYUNIT" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="inventtrans" :POSTINGDATE="POSTINGDATE" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="inventtrans" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.POSTINGDATE, data.cellData.ITEMID, data.cellData.STOREID, data.cellData.ADJUSTMENT , data.cellData.TYPE , data.cellData.COSTPRICEPERITEM , data.cellData.SALESPRICEWITHOUTTAXPERITEM , data.cellData.SALESPRICEWITHTAXPERITEM , data.cellData.REASONCODE , data.cellData.DISCOUNTAMOUNTPERITEM , data.cellData.UNITID , data.cellData.ADJUSTMENTININVENTORYUNIT)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.POSTINGDATE)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
