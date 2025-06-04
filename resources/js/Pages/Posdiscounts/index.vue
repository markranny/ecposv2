<script setup>
import Create from "@/Components/POSdiscount/Create.vue";
import Update from "@/Components/POSdiscount/Update.vue";
import Delete from "@/Components/POSdiscount/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Date from "@/Components/Svgs/Date.vue";
import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const offerid = ref('');
const description = ref('');
const status = ref('');
const pdtype = ref('');
const priority = ref('');
const discvalidperiodid = ref('');
const discounttype = ref('');
const nooflinestotrigger = ref('');
const dealpricevalue = ref('');
const discountpctvalue = ref('');
const discountamountvalue = ref('');
const pricegroup = ref('');
const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    posperiodicdiscounts: {
        type: Array,
        required: true,
    },
    discvalidperiodid: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'offerid', title: 'OFFERID' },
    { data: 'discvalidperiodid', title: 'DISCVALIDPERIODID' },
    { data: 'description', title: 'DESCRIPTION' },
    { data: 'status', title: 'STATUS' },
    { data: 'discounttype', title: 'DISCOUNTTYPE' },
    { data: 'dealpricevalue', title: 'DEALPRICEVALUE' },
    { data: 'discountpctvalue', title: 'DISCOUNTPCTVALUE' },
    { data: 'discountamountvalue', title: 'DISCOUNTAMOUNTVALUE' },
    {
        data: null,
        render: '#action',
        title: 'ACTIONS'
    },
];


const options = {
    paging: false,
    scrollX: true,
    scrollY: "70vh",
    scrollCollapse: true,
};


const toggleUpdateModal = (newOFFERID, newDESCRIPTION, newSTATUS, newDISCOUNTTYPE, newDEALPRICEVALUE, newDISCOUNTPCTVALUE, newDISCOUNTAMOUNTVALUE, newDISCVALIDPERIODID) => {
    offerid.value = newOFFERID;
    description.value = newDESCRIPTION;
    status.value = newSTATUS;
    discounttype.value = newDISCOUNTTYPE;
    dealpricevalue.value = newDEALPRICEVALUE;
    discountpctvalue.value = newDISCOUNTPCTVALUE;
    discountamountvalue.value = newDISCOUNTAMOUNTVALUE;
    discvalidperiodid.value = newDISCVALIDPERIODID;
    showModalUpdate.value = true;
};

const toggleDeleteModal = (newOFFERID) => {
    offerid.value = newOFFERID;
    showDeleteModal.value = true;
};

/* const toggleCreateModal = () => {
    showCreateModal.value = true;
}; */

const toggleCreateModal = (discvalidperiodid) => {
    if (discvalidperiodid && typeof discvalidperiodid === 'object' && 'discvalidperiodid' in discvalidperiodid) {
        discvalidperiodid.value = discvalidperiodid.discvalidperiodid;
    } else if (discvalidperiodid) {
        discvalidperiodid.value = discvalidperiodid;
    } else {
        discvalidperiodid.value = null;
    }

    showCreateModal.value = true;
    console.log(discvalidperiodid.value);
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

const navigateToPosDiscount = (offerid, discounttype) => {
  /* console.log('Redirecting to Mix And Match Entries for account:', offerid / DISCOUNTTYPE); */
  /* window.alert('You are Redirecting to Discount Validation Period Entries', offerid / DISCOUNTTYPE); */
  window.location.href = `/MNM/${offerid}/${discounttype}`;
};

const posdiscvalidationperiods = () => {
  window.alert('You are Redirecting to Discount Validation Period Entries');
  window.location.href = '/posdiscvalidationperiods';
};

</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" :discvalidperiodid="props.discvalidperiodid" @toggle-active="createModalHandler"  />
            <Update :show-modal="showModalUpdate" :offerid="offerid" :description="description" :status="status" :discounttype="discounttype" :discvalidperiodid="props.discvalidperiodid"
            @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posperiodicdiscounts" :offerid="offerid" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <!-- <PrimaryButton type="button" @click="toggleCreateModal" >CREATE</PrimaryButton> -->
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">

                        <PrimaryButton
                        type="button"
                        @click="toggleCreateModal({ discvalidperiodid: props.discvalidperiodid })"
                        class="m-6 bg-navy"
                        >
                        <Add class="h-4" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="posdiscvalidationperiods('/posdiscvalidationperiods')"
                        class="m-6 bg-navy"
                        >
                        <Date class="h-4" />
                        </PrimaryButton>

                    </div>
                </div>
                <DataTable :data="posperiodicdiscounts" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton 
                            type="button" 
                            @click="toggleUpdateModal(
                                data.cellData.offerid, 
                                data.cellData.description, 
                                data.cellData.status, 
                                data.cellData.discounttype,
                                data.cellData.dealpricevalue,
                                data.cellData.discountpctvalue,
                                data.cellData.discountamountvalue,
                                data.cellData.discvalidperiodid
                            )" 
                            class="me-1"
                        >
                            Update
                        </PrimaryButton>
                        <!-- <DangerButton type="button" @click="toggleDeleteModal(data.cellData.OFFERID)">
                            Delete
                        </DangerButton> -->
                        <DangerButton type="button" @click="navigateToPosDiscount(data.cellData.offerid, data.cellData.discounttype)">
                            MIX AND MATCH
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
