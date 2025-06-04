<script setup>
import { useRouter } from 'vue-router';
import Create from "@/Components/Signup/Create.vue";
import Update from "@/Components/Signup/Update.vue";
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
const email = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    Users: {
        type: Array,
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'name', title: 'Name' },
    { data: 'email', title: 'Email' },
    { data: 'storeid', title: 'StoreBranch' },
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

const toggleUpdateModal = (newNAME, newEMAIL) => {
    name.value = newNAME;
    email.value = newEMAIL;
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

const handleSelectedStore = (rbostoretables) => {
  console.log('Selected rbostoretables:', rbostoretables);
};

const router = useRouter();
</script>

<template>
    <Main active-tab="REGISTER">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  :rbostoretables="props.rbostoretables"  @select-item="handleSelectedStore"/>
            <Update 
                :show-modal="showModalUpdate"  
                :name="name" 
                :email="email"  
                @toggle-active="updateModalHandler"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                        <Excel
                        :data="items"
                        :headers="['STORENAME', 'EMAIL']"
                        :row-name-props="['storeid', 'email']"
                        class="mt-4 sm:mt-0 sm:ml-4 relative display"
                        v-if="isAdmin"
                        />
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-4" />
                        </PrimaryButton>
                    </div>
                </div>

                <DataTable :data="Users" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton 
                                type="button" 
                                @click="toggleUpdateModal(data.cellData.name, data.cellData.email)" 
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
