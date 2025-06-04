<script setup>
import Create from "@/Components/Rbotransdis/Create.vue";
import Update from "@/Components/Rbotransdis/Update.vue";
import Delete from "@/Components/Rbotransdis/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const TRANSACTIONID = ref('');
const LINENUM = ref('');
const DISCLINENUM = ref('');
const STORE = ref('');
const DISCOUNTTYPE = ref('');
const DISCOUNTPCT = ref('');
const DISCOUNTAMT = ref('');
const DISCOUNTAMTWITHTAX = ref('');
const PERIODICDISCTYPE = ref('');
const DISCOFFERID = ref('');
const DISCOFFERNAME = ref('');
const QTYDISCOUNTED = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    rbotransactiondiscounttrans: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'TRANSACTIONID', title: 'TRANSACTIONID' },
    { data: 'LINENUM', title: 'LINENUM' },
    { data: 'DISCLINENUM', title: 'DISCLINENUM' },
    { data: 'STORE', title: 'STORE' },
    { data: 'DISCOUNTTYPE', title: 'DISCOUNTTYPE' },
    { data: 'DISCOUNTPCT', title: 'DISCOUNTPCT' },
    { data: 'DISCOUNTAMT', title: 'DISCOUNTAMT' },
    { data: 'DISCOUNTAMTWITHTAX', title: 'DISCOUNTAMTWITHTAX' },
    { data: 'PERIODICDISCTYPE', title: 'PERIODICDISCTYPE' },
    { data: 'DISCOFFERID', title: 'DISCOFFERID' },
    { data: 'DISCOFFERNAME', title: 'DISCOFFERNAME' },
    { data: 'QTYDISCOUNTED', title: 'QTYDISCOUNTED' },
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


const toggleUpdateModal = (newTRANSACTIONID, newLINENUM, newDISCLINENUM, newSTORE, newDISCOUNTTYPE, newDISCOUNTPCT, newDISCOUNTAMT, newDISCOUNTAMTWITHTAX, newPERIODICDISCTYPE, newDISCOFFERID, newDISCOFFERNAME, newQTYDISCOUNTED) => {
    TRANSACTIONID.value = newTRANSACTIONID;
    LINENUM.value = newLINENUM;
    DISCLINENUM.value = newDISCLINENUM;
    STORE.value = newSTORE;
    DISCOUNTTYPE.value = newDISCOUNTTYPE;
    DISCOUNTPCT.value = newDISCOUNTPCT;
    DISCOUNTAMT.value = newDISCOUNTAMT;
    DISCOUNTAMTWITHTAX.value = newDISCOUNTAMTWITHTAX;
    PERIODICDISCTYPE.value = newPERIODICDISCTYPE;
    DISCOFFERID.value = newDISCOFFERID;
    DISCOFFERNAME.value = newDISCOFFERNAME;
    QTYDISCOUNTED.value = newQTYDISCOUNTED;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newTRANSACTIONID) => {
    TRANSACTIONID.value = newTRANSACTIONID;
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
            <Update :show-modal="showModalUpdate" :TRANSACTIONID="TRANSACTIONID" :LINENUM="LINENUM" :DISCLINENUM="DISCLINENUM" :STORE="STORE" :DISCOUNTTYPE="DISCOUNTTYPE" :DISCOUNTPCT="DISCOUNTPCT" :DISCOUNTAMT="DISCOUNTAMT" :DISCOUNTAMTWITHTAX="DISCOUNTAMTWITHTAX" :PERIODICDISCTYPE="PERIODICDISCTYPE" :DISCOFFERID="DISCOFFERID" :DISCOFFERNAME="DISCOFFERNAME" :QTYDISCOUNTED="QTYDISCOUNTED" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="rbotransactiondiscounttrans" :TRANSACTIONID="TRANSACTIONID" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="rbotransactiondiscounttrans" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.TRANSACTIONID, data.cellData.LINENUM, data.cellData.DISCLINENUM, data.cellData.STORE, data.cellData.DISCOUNTTYPE, data.cellData.DISCOUNTPCT, data.cellData.DISCOUNTAMT, data.cellData.DISCOUNTAMTWITHTAX, data.cellData.PERIODICDISCTYPE, data.cellData.DISCOFFERID, data.cellData.DISCOFFERNAME , data.cellData.QTYDISCOUNTED)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.TRANSACTIONID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
