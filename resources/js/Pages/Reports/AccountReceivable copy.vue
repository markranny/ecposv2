<script setup>
import Create from "@/Components/Orders/Create.vue";
import Update from "@/Components/Orders/Update.vue";
import Post from "@/Components/ItemOrders/Post.vue";
import SendModal from "@/Components/Orders/Send.vue";
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import Main from "@/Layouts/Main.vue";  
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, computed, onMounted, onUnmounted } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const journalid = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showSendModal = ref(false);
const showModalMore = ref(false);
const showPostModal = ref(false);

const props = defineProps({
    ar: {
        type: Array,
        required: true,
    },
});


const columns = [
    { data: 'receiptid', title: 'RECEIPT' },
    { 
        data: 'createddate', 
        title: 'CREATED DATE', 
        render: function(data, type, row) {
            const date = new Date(data);
            return date.toLocaleDateString();  
        }
    },
    { data: 'charge', title: 'CHARGE' },
    { data: 'gcash', title: 'GCASH' },
    { data: 'paymaya', title: 'PAYMAYA' },
    { data: 'card', title: 'CARD' },
    { data: 'loyaltycard', title: 'LOYALTY CARD' },
    { data: 'foodpanda', title: 'FOODPANDA' },
    { data: 'grabfood', title: 'GRABFOOD' },
    { data: 'representation', title: 'REPRESENTATION' }
];


const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
};

</script>

<template>
    <Main active-tab="ORDER">
        <template v-slot:modals>
        </template>

        <template v-slot:main>

            <TableContainer>

                <DataTable :data="ar" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                    </template>
                </DataTable>

            </TableContainer>

        </template>
    </Main>

</template>




