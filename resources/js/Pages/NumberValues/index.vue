<script setup>
import Create from "@/Components/NumberValue/Create.vue";
import Update from "@/Components/NumberValue/Update.vue";
import Delete from "@/Components/NumberValue/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const NUMBERSEQUENCE = ref('');
const NEXTREC = ref('');
const STOREID = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    nubersequencevalues: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'NUMBERSEQUENCE', title: 'NUMBERSEQUENCE' },
    { data: 'NEXTREC', title: 'NEXTREC' },
    { data: 'STOREID', title: 'STOREID' },
   
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


const toggleUpdateModal = (newNUMBERSEQUENCE, newNEXTREC, newSTOREID) => {
    NUMBERSEQUENCE.value = newNUMBERSEQUENCE;
    NEXTREC.value = newNEXTREC;
    STOREID.value = newSTOREID;
  
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
            <Update :show-modal="showModalUpdate" :NUMBERSEQUENCE ="NUMBERSEQUENCE" :NEXTREC="NEXTREC" :STOREID="STOREID" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="nubersequencevalues" :NUMBERSEQUENCE ="NUMBERSEQUENCE" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="nubersequencevalues" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.NUMBERSEQUENCE, data.cellData.NEXTREC, data.cellData.STOREID)" class="me-1">
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
