<script setup>
import Create from "@/Components/POSline/Create.vue";
import Update from "@/Components/POSline/Update.vue";
import Delete from "@/Components/POSline/Delete.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Add from "@/Components/Svgs/Add.vue";
import Back from "@/Components/Svgs/Back.vue";
import Group from "@/Components/Svgs/Group.vue";
import Refresh from "@/Components/Svgs/Refresh.vue";
import { ref, computed } from 'vue';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const offerid = ref('');
const discounttype = ref('');
const items = ref([]);
const producttype = ref('');
const id = ref('');
const dealpriceordiscpct = ref('');
const linegroup = ref('');
const disctype = ref('');

/* const ITEMS = ref([]);   */

const showModalUpdate = ref(false);
const showCreateModal = ref(false);
const showDeleteModal = ref(false);

const props = defineProps({
    posperiodicdiscountlines: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    offerid: {
        type: [String, Number],
        required: true,
    },
    discounttype: {
        type: [String, Number],
        required: true,
    },
});
const formatPrice = (value) => {
    return Number(value).toFixed(2);
};

const columns = [
    { data: 'offerid', title: 'OFFERID' },
    /* { data: 'lineid', title: 'LINEID' }, */
    { data: 'linegroup', title: 'LINEGROUP' },
    { data: 'itemid', title: 'ITEMID' },
    { 
        data: 'dealpriceordiscpct', 
        title: 'DISCOUNTPERITEM',
        render: function(data, type) {
            if (type === 'display') {
                return formatPrice(data);
            }
            return data;
        }
    },
    /* { data: 'disctype', title: 'DISCTYPE' }, */
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

const toggleUpdateModal = (newOFFERID, newLINEID, newPRODUCTTYPE, newID, newDEALPRICEORDISCPCT, newLINEGROUP, newDISCTYPE) => {
    offerid.value = newOFFERID;
    lineid.value = newLINEID;
    producttype.value = newPRODUCTTYPE;
    id.value = newID;
    dealpriceordiscpct.value = newDEALPRICEORDISCPCT;
    linegroup.value = newLINEGROUP;
    discounttype.value = newDISCTYPE;
    showModalUpdate.value = true;
};
const toggleDeleteModal = (newLINEID) => {
    lineid.value = newLINEID;
    showDeleteModal.value = true;
};

const toggleCreateModal = (newOfferid, newDiscountType, newItems) => {
    offerid.value = newOfferid;
    discounttype.value = newDiscountType;
    items.value = newItems;
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

const navigateToPosDiscount = () => {
  window.location.href = '/posperiodicdiscounts';
};

/* const navigateToposmmlinegroups = () => {
  window.location.href = '/posmmlinegroups';
}; */

const navigateToposmmlinegroups = (offerid) => {
  console.log('Redirecting to Line Group Entries:', offerid);
  window.location.href = `/POSMMMLINEGROUPS/${offerid}`;
};

const reload = (offerid) => {
  /* console.log('Redirecting to Line Group Entries:', offerid); */
  window.location.href = `/MNM/${offerid}`;
};


/* const selectedPrice = ref('');

const uniquePrices = computed(() => {
  const prices = new Set();
  props.items.forEach(item => prices.add(item.price));
  return Array.from(prices);
}); */

const selectedItemId = ref('');

const selectedItem = computed(() => {
  return props.items.find(item => item.itemid === selectedItemId.value);
});

console.log(props.items);

</script>

<template>
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <Create 
                :show-modal="showCreateModal"
                :offerid="props.offerid"
                :discounttype="props.discounttype"
                :items="props.items"
                @toggle-active="createModalHandler"
            />
            <Update :show-modal="showModalUpdate" :offerid="offerid" :lineid="lineid" :producttype="producttype" :id="id" :dealpriceordiscpct="dealpriceordiscpct" :linegroup="linegroup" :disctype="disctype" @toggle-active="updateModalHandler"  />
            <Delete :show-modal="showDeleteModal" item-name="posperiodicdiscountlines" :lineid="lineid" @toggle-active="deleteModalHandler"  />
        </template>

        <template v-slot:main>
            <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">


                        <!-- <div>
                            <label for="item-select">Select an item:</label>
                            <select id="item-select" v-model="selectedItemId">
                            <option value="">-- Select an item --</option>
                            <option v-for="item in items" :key="item.itemid" :value="item.itemid">
                                {{ item.itemname }}
                            </option>
                            </select>
                            <p v-if="selectedItem" class="hidden">Selected Item: {{ selectedItem.itemname }} (ID: {{ selectedItemId }})</p>
                        </div> -->


                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal(offerid, DISCOUNTTYPE, items)"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="navigateToPosDiscount('/posperiodicdiscounts')"
                        class="m-1 bg-navy p-10"
                        >
                        <Back class="h-6" />
                        </PrimaryButton>

                        <PrimaryButton
                        type="button"
                        @click="navigateToposmmlinegroups(offerid)"
                        class="m-1 ml-6 bg-navy p-10"
                        >
                        <Group class="h-6" />
                        </PrimaryButton>

                        <DangerButton
                        type="button"
                        @click="reload(offerid)"
                        class="m-1 bg-navy p-10 ml-6"
                        >
                        <Refresh class="h-6" />
                        </DangerButton>

                        <!-- <h3 class="text-blue-900 ml-6">Need to execute reload trigger to get offerid</h3> -->
                        

                    </div>
                </div>
                <DataTable :data="posperiodicdiscountlines" :columns="columns" class="w-full relative display" :options="options" >
                    <template #action="data">
                        <PrimaryButton type="button" @click="toggleUpdateModal(data.cellData.OFFERID, data.cellData.LINEID, data.cellData.PRODUCTTYPE, data.cellData.ID , data.cellData.DEALPRICEORDISCPCT , data.cellData.LINEGROUP , data.cellData.DISCTYPE)" class="me-1">
                            Update
                        </PrimaryButton>
                        <DangerButton type="button" @click="toggleDeleteModal(data.cellData.LINEID)">
                            Delete
                        </DangerButton>
                    </template>
                </DataTable>
            </TableContainer>
        </template>
    </Main>
</template>
