<script setup>
import Create from "@/Components/DiscblankOP/Create.vue";
import Update from "@/Components/DiscblankOP/Update.vue";
import Delete from "@/Components/DiscblankOP/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/Main.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const ID = ref('');
const DISCTYPE = ref('');
const ISPRECENTAGE = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    isdiscblankoperations: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'ID', title: 'ID' },
    { data: 'DISCTYPE', title: 'DISCTYPE' },
    { data: 'ISPRECENTAGE', title: 'ISPRECENTAGE' },
   
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


const toggleUpdateModal = (newID, newDISCTYPE, newISPRECENTAGE) => {
    ID.value = newID;
    DISCTYPE.value = newDISCTYPE;
    ISPRECENTAGE.value = newISPRECENTAGE;
  
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

</script>

<template>
    <Main >
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :ID="ID" :DISCTYPE="DISCTYPE" :ISPRECENTAGE="ISPRECENTAGE" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="isdiscblankoperations" :ID="ID" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton>
            <TableContainer>
                <DataTable :data="isdiscblankoperations" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.ID, data.cellData.DISCTYPE, data.cellData.ISPRECENTAGE)" class="me-1">
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
