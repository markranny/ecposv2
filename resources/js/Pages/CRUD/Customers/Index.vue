<script setup>
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import { useRouter } from 'vue-router'
import { computed } from 'vue'
import Create from "@/Components/CRUD/Customers/Create.vue";
import Update from "@/Components/CRUD/Customers/Update.vue";
import Delete from "@/Components/CRUD/Customers/Delete.vue";
import More from "@/Components/CRUD/Customers/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import Import from "@/Components/Svgs/Import.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";
import Back from "@/Components/Svgs/Back.vue";

import TableContainer from "@/Components/Tables/TableContainer.vue";
/* import Main from "@/Layouts/Main.vue"; */
import { ref } from "vue";


import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const accountnum = ref('');
const name = ref('');
const address = ref('');
const currency = ref('');
const phone = ref('');
const blocked = ref('');
const creditmax = ref('');
const country = ref('');
const zipcode = ref('');
const state = ref('');
const email = ref('');
const cellularphone = ref('');
const gender = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);
const showModalMore = ref(false);

const props = defineProps({
    customers: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'accountnum', title: 'ACCOUNTNUM' },
    { data: 'name', title: 'NAME' },
    { data: 'address', title: 'ADDRESS' },
    { data: 'phone', title: 'PHONE' },
    /* { data: 'currency', title: 'CURRENCY' },
    { data: 'blocked', title: 'BLOCKED' },
    { data: 'creditmax', title: 'CREDITMAX' },
    { data: 'country', title: 'COUNTRY' },
    { data: 'zipcode', title: 'ZIPCODE' },
    { data: 'county', title: 'COUNTY' }, */
    { data: 'email', title: 'EMAIL' },
    /* { data: 'cellularphone', title: 'CELLULARPHONE' }, */
    /* { data: 'dataareaid', title: 'DATAAREAID' }, */
    { data: 'gender', title: 'GENDER' },
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


const toggleUpdateModal = (newACCOUNTNUM, newNAME, newADDRESS, newPHONE, newCURRENCY, newBLOCKED, newCREDITMAX, newCOUNTRY, newZIPCODE, newSTATE, newEMAIL, newCELLULARPHONE, newGENDER) => {
    
    accountnum.value = newACCOUNTNUM;
    name.value = newNAME;
    address.value = newADDRESS;
    phone.value = newPHONE;
    currency.value = newCURRENCY;
    blocked.value = newBLOCKED;
    creditmax.value = newCREDITMAX;
    country.value = newCOUNTRY;
    zipcode.value = newZIPCODE;
    state.value = newSTATE;
    email.value = newEMAIL;
    cellularphone.value = newCELLULARPHONE;
    /* dataareaid.value = newDATAAREAID; */
    gender.value = newGENDER;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newACCOUNTNUM) => {
    accountnum.value = newACCOUNTNUM;
    showDeleteModal.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const toggleMoreModal = (newACCOUNTNUM) => {
    accountnum.value = newACCOUNTNUM;
    showModalMore.value = true;
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
const MoreModalHandler = () => {
    showModalMore.value = false;
};

const router = useRouter()

/* const accountNumber = computed(() => {
  return props.data && props.data.cellData ? props.data.cellData.accountnum : ''
})

const redirectToLedger = (accountnum) => {
  if (accountnum) {
    router.push({ name: 'ledger', params: { accountnum } })
  }
} */

/* const navigateToCustomerLedger = () => {
  window.alert('You are Redirecting to Customer Ledger Entries');
  window.location.href = '/ledger/$accountnum';
}; */

/* const navigateToCustomerLedger = (accountnum) => {
  console.log('Redirecting to Customer Ledger Entries for account:', accountnum);
  window.location.href = `/ledger/${accountnum}`;
}; */

const navigateToCustomerLedger = (accountnum) => {
  console.log('Redirecting to Customer Ledger Entries for account:', accountnum);
  window.location.href = `/ledger/${accountnum}`;
};

</script>

<template>
    <Main active-tab="CUSTOMERS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate"  :accountnum="accountnum" :name="name" :address="address" :currency="currency" :phone="phone" :blocked="blocked" :creditmax="creditmax" :country="country"
            :zipcode="zipcode" :state="state" :email="email" :cellularphone="cellularphone" :gender="gender" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="customers" :accountnum="accountnum" @toggle-active="deleteModalHandler"  />

            <More
            :show-modal="showModalMore"
            :accountnum="accountnum"
            @toggle-active="MoreModalHandler"
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

                        <Excel
                            :data="customers"
                            :headers="['ACCOUNTNUM', 'NAME', 'ADDRESS', 'PHONE', 'EMAIL', 'GENDER']"
                            :row-name-props="['accountnum', 'name', 'address', 'phone', 'email', 'gender']"
                            class="ml-4 relative display"
                        />

                    </div>
                </div>

                <DataTable :data="customers" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">

                        <div class="flex justify-start">
                            <TransparentButton type="button" @click="toggleUpdateModal(
                                data.cellData.accountnum, data.cellData.name, data.cellData.address, data.cellData.phone,
                                data.cellData.currency, data.cellData.blocked, data.cellData.creditmax, data.cellData.country,
                                data.cellData.zipcode, data.cellData.state, data.cellData.email, data.cellData.cellularphone,
                                data.cellData.gender)" class="me-1">
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>

                            <!-- <TransparentButton :url="route('customer.index', { accountnum: form.accountnum })">
                                <moreblue class="h-6 ml-5 cursor-pointer"></moreblue>
                            </TransparentButton> -->
                                <!-- <TransparentButton @click="redirectToLedger(accountNumber)">
                                    <moreblue class="h-6 ml-5 cursor-pointer" />
                                </TransparentButton> -->
                                <!-- <TransparentButton
                                    type="button"
                                    @click="
                                    toggleMoreModal(
                                        data.cellData.accountnum
                                    )
                                    "
                                    class="ml-5 cursor-pointer"
                                    >
                                    <moreblue class="h-6"></moreblue>
                                </TransparentButton> -->

                                <TransparentButton
                                class="ml-5 cursor-pointer"
                                @click="navigateToCustomerLedger(data.cellData.accountnum)"
                                >
                                <moreblue class="h-6"></moreblue>
                                </TransparentButton>
                            </div>


                        <!-- <DangerButton type="button" @click="toggleDeleteModal(data.cellData.accountnum)">
                            Delete
                        </DangerButton> -->

                        <!-- <button class=" ms-2 p-10px inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"  @click="navigateToCustomerLedger('/customerledgerentries/', { accountnum: data.cellData.accountnum })">MORE</button> -->

                    </template>
                </DataTable>
                
            </TableContainer>
        </template>
    </Main>
</template>


