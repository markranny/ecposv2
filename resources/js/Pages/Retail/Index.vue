<script setup>
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import Create from "@/Components/Items/Create.vue";
import Update from "@/Components/Items/Update.vue";
import Delete from "@/Components/Items/Delete.vue";
import More from "@/Components/Items/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import SuccessButton from "@/Components/Buttons/SuccessButton.vue";
import PurpleButton from "@/Components/Buttons/PurpleButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import DangerButton from "@/Components/Buttons/DangerButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/RetailPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import Import from "@/Components/Svgs/Import.vue";
import ExcelIcon from "@/Components/Svgs/Excel.vue";

import { ref } from "vue";

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);


const itemid = ref('');
const itemname = ref('');
const cost = ref('');
const itemgroup = ref('');
const specialgroup = ref('');
const price = ref('');
const barcode = ref('');


const showModalUpdate = ref(false);
const showCreateModal = ref(false);
/* const showDeleteModal = ref(false); */
const showModalMore = ref(false);



const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const columns = [
    { data: 'itemid', title: 'PRODUCTCODE' },
    { data: 'itemname', title: 'DESCRIPTION' },
    { data: 'barcode', title: 'BARCODE' },
    { data: 'itemgroup', title: 'CATEGORY' },
    /* { data: 'specialgroup', title: 'SPECIALGROUP' }, */
    {
    data: 'cost',
    title: 'COST',
    render: (data, type, row) => {
      if (type === 'display') {
        return row.cost.toFixed(2);
      }
      return data;
    },
    },

    {
    data: 'price',
    title: 'PRICEINCLTAX',
    render: (data, type, row) => {
      if (type === 'display') {
        return row.price.toFixed(2);
      }
      return data;
    },
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


const toggleUpdateModal = (newID, newItemName, newItemGroup, newPrice, newCost) => {
  itemid.value = newID;
  itemname.value = newItemName;
  itemgroup.value = newItemGroup;
  price.value = newPrice;
  cost.value = newCost;
  showModalUpdate.value = true;
};
/* const toggleDeleteModal = (newID) => {
    itemid.value = newID;
    showDeleteModal.value = true;
}; */

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const toggleMoreModal = (newID) => {
    itemid.value = newID;
    showModalMore.value = true;
};


const updateModalHandler = () => {
    showModalUpdate.value = false;
};
const createModalHandler = () => {
    showCreateModal.value = false;
};
/* const deleteModalHandler = () => {
    showDeleteModal.value = false;
}; */
const MoreModalHandler = () => {
    showModalMore.value = false;
};

const form = useForm({
    title: '',
    content: '',
});

const submitForm = () => {
    form.post('/ImportProducts', {
        onSuccess: () => {
        },
        onError: (errors) => {
        },
    });
  }

</script>

<template>
    <Main active-tab="RETAILITEMS">

      <template v-slot:main>
        <!-- <div className="card w-full">
              <div className="card-body">
                <div v-for="item in items" :key="item.itemid">
                <p>{{ item.itemid }} - {{ item.itemname }}</p>
              </div>
              </div>
          </div> -->
          <div class="flex justify-center">
            <div class="w-[95%] mt-5">
              <div role="alert" class="alert shadow-lg p-5 skeleton">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="stroke-current shrink-0 w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <div v-for="item in items" :key="item.itemid">
                  <span>{{ item.itemid }} - {{ item.itemname }}</span>
                </div>
              </div>
            </div>
          </div>
          
      </template>

    </Main>
  </template>

