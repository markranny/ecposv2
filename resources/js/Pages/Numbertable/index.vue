<script setup>
import Create from "@/Components/Numbersequence/Create.vue";
import Update from "@/Components/Numbersequence/Update.vue";
import Delete from "@/Components/Numbersequence/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const NUMBERSEQUENCE  = ref('');
const TXT = ref('');
const LOWEST = ref('');
const HIGHEST = ref('');
const BLOCKED = ref('');
const STOREID = ref('');
const CANBEDELETED = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    nubersequencetables: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'NUMBERSEQUENCE', title: 'NUMBERSEQUENCE' },
    { data: 'TXT', title: 'TXT' },
    { data: 'LOWEST', title: 'LOWEST' },
    { data: 'HIGHEST', title: 'HIGHEST' },
    { data: 'BLOCKED', title: 'BLOCKED' },
    { data: 'STOREID', title: 'STOREID' },
    { data: 'CANBEDELETED', title: 'CANBEDELETED' },
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


const toggleUpdateModal = (newNUMBERSEQUENCE, newTXT, newLOWEST, newHIGHEST, newBLOCKED, newSTOREID, newCANBEDELETED) => {
    NUMBERSEQUENCE.value = newNUMBERSEQUENCE;
    TXT.value = newTXT;
    LOWEST.value = newLOWEST;
    HIGHEST.value = newHIGHEST;
    BLOCKED.value = newBLOCKED;
    STOREID.value = newSTOREID;
    CANBEDELETED.value = newCANBEDELETED;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newNUMBERSEQUENCE) => {
    NUMBERSEQUENCE.value = newNUMBERSEQUENCE;
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
            <Update :show-modal="showModalUpdate" :NUMBERSEQUENCE="NUMBERSEQUENCE" :TXT="TXT" :LOWEST="LOWEST" :HIGHEST="HIGHEST" :BLOCKED="BLOCKED" :STOREID="STOREID" :CANBEDELETED="CANBEDELETED" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="nubersequencetables" :NUMBERSEQUENCE="NUMBERSEQUENCE" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="nubersequencetables" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.NUMBERSEQUENCE, data.cellData.TXT, data.cellData.LOWEST, data.cellData.HIGHEST , data.cellData.BLOCKED , data.cellData.STOREID , data.cellData.CANBEDELETED)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.NUMBERSEQUENCE)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
