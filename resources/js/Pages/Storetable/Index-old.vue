<script setup>
import { useRouter } from 'vue-router';
import Create from "@/Components/Storetable/Create.vue";
import Update from "@/Components/Storetable/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const storeid = ref('');
const name = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'STOREID', title: 'StoreID' },
    { data: 'NAME', title: 'StoreName' },
    {
        data: null,
        render: '#action',
        title: 'Actions'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
};

const toggleUpdateModal = (newSTOREID, newNAME) => {
    storeid.value = newSTOREID;
    name.value = newNAME;
    showModalUpdate.value = true;
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

const router = useRouter();
</script>

<template>
    <Main active-tab="STORE">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update 
                :show-modal="showModalUpdate"  
                :STOREID="storeid" 
                :NAME="name"  
                @toggle-active="updateModalHandler"
            />
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

                <DataTable :data="rbostoretables" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton 
                                type="button" 
                                @click="toggleUpdateModal(data.cellData.STOREID, data.cellData.NAME)" 
                                class="me-1"
                            >
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
