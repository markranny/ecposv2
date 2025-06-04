<script setup>
import { useRouter } from 'vue-router';
import Create from "@/Components/Announcement/Create.vue";
import Update from "@/Components/Announcement/Update.vue";
import Delete from "@/Components/Announcement/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";    
import TrashRed from "@/Components/Svgs/TrashRed.vue";   
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const id = ref('');
const subject = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    announcements: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'id', title: 'ID' },
    { data: 'SUBJECT', title: 'SUBJECT' },
    { 
        data: 'created_at', 
        title: 'DATE',
        render: function(data, type, row) {
            if (type === 'display' || type === 'filter') {
                return data.split('T')[0]; 
            }
            return data;
        }
    },
    {
        data: null,
        render: '#action',
        title: 'ACTIONS'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
};

const toggleUpdateModal = (newID, newSUBJECT, newDESCRIPTION) => {
    id.value = newID;
    subject.value = newSUBJECT;
    description.value = newDESCRIPTION;
    showModalUpdate.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const updateModalHandler = () => {
    showModalUpdate.value = false;
};

const toggleDeleteModal = (newID) => {
    id.value = newID;
    showDeleteModal.value = true;
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const deleteModalHandler = () => {
    showDeleteModal.value = false;
};

const router = useRouter();
</script>

<template>
    <Main active-tab="ANNOUNCEMENT">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update 
                :show-modal="showModalUpdate"  
                :ID="id" 
                :SUBJECT="subject"  
                :DESCRIPTION="description" 
                @toggle-active="updateModalHandler"
            />
            <Delete :show-modal="showDeleteModal" :ID="id" @toggle-active="deleteModalHandler"  />
            
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
                            <Add class="h-4" />
                        </PrimaryButton>
                    </div>
                </div>

                <DataTable :data="announcements" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton 
                                type="button" 
                                @click="toggleUpdateModal(data.cellData.id, data.cellData.SUBJECT, data.cellData.DESCRIPTION)" 
                                class="me-1 mr-20"
                            >
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>

                            <TransparentButton type="button" @click="toggleDeleteModal(data.cellData.id)">
                                <TrashRed class="h-6 cursor-pointer"/>
                            </TransparentButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
