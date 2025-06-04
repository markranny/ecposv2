<script setup>
import Create from "@/Components/ItemOrders/Create.vue";
import Update from "@/Components/ItemOrders/Update.vue";
import Delete from "@/Components/ItemOrders/Delete.vue";
import Post from "@/Components/ItemOrders/Post.vue";

import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Back from "@/Components/Svgs/Back.vue";
import Refresh from "@/Components/Svgs/Post.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const JOURNALID  = ref('');
const COUNTED = ref('');
const LINENUM = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const showPostModal = ref(false);

const props = defineProps({
    inventjournaltrans: {
        type: Array,
        required: true,
    },
    journalid: {
        type: [String, Number],
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'LINENUM', title: 'LINE' },
    { data: 'itemid', title: 'ItemID    ' },
    { data: 'itemname', title: 'Itemname    ' },
    { data: 'COUNTED', title: 'Qty' },
    { data: 'UNITID', title: 'Unit' },
    {
        data: null,
        render: '#action',
        title: 'Actions'
    },
];

const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
};


const toggleUpdateModal = (newJOURNALID, newLINENUM, newCOUNTED) => {
    JOURNALID.value = newJOURNALID;
    LINENUM.value = newLINENUM;
    COUNTED.value = newCOUNTED;
    showModalUpdate.value = true;
};

const toggleDeleteModal = (newJOURNALID, newLINENUM,) => {
    JOURNALID.value = newJOURNALID;
    LINENUM.value = newLINENUM;
    showDeleteModal.value = true;
};

const toggleCreateModal = (journalid, newLINENUM,) => {

    JOURNALID.value = journalid;
    LINENUM.value = newLINENUM;

    showCreateModal.value = true;
    console.log(JOURNALID.value); 
};
const togglePostModal = (newJOURNALID,) => {
    JOURNALID.value = newJOURNALID;
    showPostModal.value = true;
};

const handleSelectedItem = (item) => {
  console.log('Selected Item:', item);
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
const postModalHandler = () => {
    showPostModal.value = false;
};
const ItemOrders = () => {
  window.location.href = '/order';
};

const reload = (journalid) => {
  window.location.href = `/ItemOrders/${journalid}`;
};


</script>

<template>
    
    <Main active-tab="ORDER">
        <template v-slot:modals>
            <Create
                :show-modal="showCreateModal"
                :JOURNALID="JOURNALID"
                :items="props.items" 
                @toggle-active="createModalHandler"
                @select-item="handleSelectedItem"
            />
            <Update :show-modal="showModalUpdate" :JOURNALID="JOURNALID" :LINENUM="LINENUM" :COUNTED="COUNTED" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="inventjournaltrans" :JOURNALID="JOURNALID" :LINENUM="LINENUM" @toggle-active="deleteModalHandler"  />
            <Post :show-modal="showPostModal" item-name="inventjournaltrans" :JOURNALID="JOURNALID" @toggle-active="postModalHandler"  />
        </template>

        <template v-slot:main>
            <!-- <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton> -->
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">

                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(journalid)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="ItemOrders"
                        class="m-1 ml-2 bg-navy p-10 "
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                        <!-- <DangerButton
                        type="button"
                        @click="togglePostModal(data.cellData.JOURNALID)"
                        class="m-1 bg-navy p-10 ml-6"
                        >
                        <Refresh class="h-6 w-7" /> POST ALL LINES
                        </DangerButton> -->

                        <!-- <DangerButton
                            type="button"
                            @click="togglePostModal(journalid)"
                            class="m-1 bg-navy p-10 ml-6"
                            >
                            <Refresh class="h-6 w-7" /> POST ALL LINES
                        </DangerButton> -->

                        <!-- <PrimaryButton type="button" @click="togglePostModal(data.cellData.JOURNALID)" class="me-1">
                            <Refresh class="h-6 w-7" /> POST ALL LINES
                        </PrimaryButton> -->

                    </div>
                </div>
                <DataTable :data="inventjournaltrans" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.JOURNALID, data.cellData.LINENUM, data.cellData.COUNTED)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.JOURNALID,  data.cellData.LINENUM)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
