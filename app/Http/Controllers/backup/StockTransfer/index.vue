<script setup>
import Create from "@/Components/StockTransferTables/Create.vue";
/* import Update from "@/Components/StockTransferTables/Update.vue";
import Post from "@/Components/StockTransferTables/Post.vue"; */
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/Main.vue";
import Add from "@/Components/Svgs/Add.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';

DataTable.use(DataTablesCore);

const journalid = ref('');
const description = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showPostModal = ref(false);

const props = defineProps({
    stocktransfertables: {
        type: Array,
        required: true,
    },
    currentStoreId: {
        type: String,
        required: true
    }
});

const columns = [
    { data: 'POSTED', title: 'SENT' },
    { data: 'SENT', title: 'RECEIVED' },
    { data: 'FROM_STOREID', title: 'FROM STORE' },
    { data: 'TO_STOREID', title: 'TO STORE' },
    { data: 'DESCRIPTION', title: 'DESCRIPTION' },
    { data: 'CREATEDDATETIME', title: 'DATE' },
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
    createdRow: function(row, data) {
    if (!data.POSTED && !data.SENT) {
        row.classList.add('unposted-row');
    }
    }
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

const postModalHandler = () => {
    showPostModal.value = false;
};

const navigateToTransfer = (journalid) => {
    window.location.href = `/StockTransfer/${journalid}`;
};

const getButtonText = (transfer) => {
    if (transfer.FROM_STOREID === props.currentStoreId) {
        return 'View Items';
    } else if (transfer.TO_STOREID === props.currentStoreId) {
        return 'View Items';
    }
    return 'View Transfer'; 
};

const shouldShowActionButton = (transfer) => {
    return !transfer.POSTED && 
           (transfer.FROM_STOREID === props.currentStoreId || 
            transfer.TO_STOREID === props.currentStoreId);
};

const getActionButtonText = (transfer) => {
    if (transfer.FROM_STOREID === props.currentStoreId) {
        return 'SEND';
    }
    if (transfer.TO_STOREID === props.currentStoreId) {
        return 'RECEIVE';
    }
    return '';
};

const getActionButtonClass = (transfer) => {
    return transfer.FROM_STOREID === props.currentStoreId ? 
        'bg-red-900' : 'bg-green-700';
};

const post = async (journalId) => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const postResponse = await fetch('/post-stocktransfer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({ journalid: journalId })
        });

        const responseData = await postResponse.json();

        if (!postResponse.ok) {
            throw new Error(`Server responded with status: ${postResponse.status}`);
        }

        alert('Stock Transfer has been successfully posted!');
        location.reload();
        
    } catch (error) {
        console.error('Post operation failed:', error);
        alert('Error posting transfer: ' + error.message);
    }
};

const receive = async (journalId) => {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            throw new Error('CSRF token not found');
        }

        const receiveResponse = await fetch('/receive-stocktransfer', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            },
            body: JSON.stringify({ journalid: journalId })
        });

        const responseData = await receiveResponse.json();

        if (!receiveResponse.ok) {
            throw new Error(`Server responded with status: ${receiveResponse.status}`);
        }

        alert('Stock Transfer has been successfully received!');
        location.reload();
        
    } catch (error) {
        console.error('Receive operation failed:', error);
        alert('Error receiving transfer: ' + error.message);
    }
};

const handleActionButton = (transfer) => {
    if (transfer.FROM_STOREID === props.currentStoreId) {
        post(transfer.JOURNALID);
    } else if (transfer.TO_STOREID === props.currentStoreId) {
        receive(transfer.JOURNALID);
    }
};
</script>

<template>
    <Main active-tab="TRANSFER">
        <template v-slot:modals>
            <Create 
                :show-modal="showCreateModal" 
                @toggle-active="createModalHandler" 
            />
            <Update 
                :show-modal="showModalUpdate" 
                :journalid="journalid" 
                :description="description" 
                @toggle-active="updateModalHandler" 
            />
            <Post 
                :show-modal="showPostModal" 
                :journalid="journalid" 
                @toggle-active="postModalHandler" 
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

                        <label class="inline-flex items-center font-bold">
                            STOCK TRANSFER
                        </label>
                    </div>
                </div>

                <DataTable 
                    :data="stocktransfertables" 
                    :columns="columns" 
                    class="w-full relative display" 
                    :options="options"
                >
                    <template #action="data">
                        <div class="flex justify-start">
                            <PrimaryButton
                                class="cursor-pointer bg-navy"
                                @click="navigateToTransfer(data.cellData.JOURNALID)"
                            >
                                {{ getButtonText(data.cellData) }}
                            </PrimaryButton>

                            <PrimaryButton
                                v-if="shouldShowActionButton(data.cellData)"
                                class="ml-5 cursor-pointer"
                                :class="getActionButtonClass(data.cellData)"
                                @click="handleActionButton(data.cellData)"
                            >
                                {{ getActionButtonText(data.cellData) }}
                            </PrimaryButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>

<style>
@keyframes purpleGradient {
    0% {
        background: linear-gradient(
            45deg,
            rgba(75, 0, 130, 0.4) 0%,     /* Darker indigo */
            rgba(48, 25, 52, 0.5) 25%,     /* Very dark purple */
            rgba(75, 0, 130, 0.4) 50%,     /* Darker indigo */
            rgba(48, 25, 52, 0.5) 75%,     /* Very dark purple */
            rgba(75, 0, 130, 0.4) 100%     /* Darker indigo */
        );
        background-size: 200% 200%;
        background-position: 0% 0%;
    }
    50% {
        background: linear-gradient(
            45deg,
            rgba(75, 0, 130, 0.5) 0%,
            rgba(48, 25, 52, 0.4) 25%,
            rgba(75, 0, 130, 0.5) 50%,
            rgba(48, 25, 52, 0.4) 75%,
            rgba(75, 0, 130, 0.5) 100%
        );
        background-size: 200% 200%;
        background-position: 100% 100%;
    }
    100% {
        background: linear-gradient(
            45deg,
            rgba(75, 0, 130, 0.4) 0%,
            rgba(48, 25, 52, 0.5) 25%,
            rgba(75, 0, 130, 0.4) 50%,
            rgba(48, 25, 52, 0.5) 75%,
            rgba(75, 0, 130, 0.4) 100%
        );
        background-size: 200% 200%;
        background-position: 0% 0%;
    }
}

.unposted-row {
    position: relative;
    animation: purpleGradient 4s ease infinite;
}

.unposted-row td {
    background-color: transparent !important;
    position: relative;
    z-index: 1;
    color: white !important;
    font-weight: bold;
}

.unposted-row::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.unposted-row:hover td {
    color: white !important;
    font-weight: bold;
}
</style>