<script setup>
import Create from "@/Components/Inventtransreason/Create.vue";
import Update from "@/Components/Inventtransreason/Update.vue";
import Delete from "@/Components/Inventtransreason/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const REASONID = ref('');
const REASONTEXT = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    inventtransreasons: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'REASONID', title: 'REASONID' },
    { data: 'REASONTEXT', title: 'REASONTEXT' },
   
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


const toggleUpdateModal = (newREASONID, newREASONTEXT) => {
    REASONID.value = newREASONID;
    REASONTEXT.value = newREASONTEXT;
  
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newREASONID) => {
    REASONID.value = newREASONID;
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
            <Update :show-modal="showModalUpdate" :REASONID="REASONID" :REASONTEXT="REASONTEXT" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-REASONTEXT="inventtransreasons" :REASONID ="REASONID " @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="inventtransreasons" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.REASONID, data.cellData.REASONTEXT)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.REASONID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
