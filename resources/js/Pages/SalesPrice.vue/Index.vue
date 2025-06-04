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
const itembarcode = ref('');
const itemgroup = ref('');
const itemdepartment = ref('');
const priceinltax = ref('');
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
    { data: 'itemid', title: 'ITEMID' },
    { data: 'itemname', title: 'ITEMNAME' },
    { data: 'barcode', title: 'BARCODE' },
    { data: 'itemgroup', title: 'CATEGORY' },
    /* { data: 'specialgroup', title: 'SPECIALGROUP' }, */
    { data: 'price', title: 'PRICE' },

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


const toggleUpdateModal = (newID) => {
    itemid.value = newID;
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


      <template v-slot:modals>
        <Create :show-modal="showCreateModal" @toggle-active="createModalHandler" />

        <Update
          :show-modal="showModalUpdate"
          :itemid="itemid"
          @toggle-active="updateModalHandler"
        />

        <!-- <Delete
          :show-modal="showDeleteModal"
          item-name="Item"
          :itemid="itemid"
          @toggle-active="deleteModalHandler"
        /> -->

        <More
          :show-modal="showModalMore"
          :itemid="itemid"
          @toggle-active="MoreModalHandler"
        />

      </template>


      <template v-slot:main>
        



        <TableContainer>

          <div class="absolute adjust">
            <div class="flex justify-start items-center" id="panel" v-show="showPanel">
              <PrimaryButton
                type="button"
                @click="toggleCreateModal"
                class="m-6 bg-navy"
              >
                <Add class="h-4" />
              </PrimaryButton>

              <form @submit.prevent="submitForm" id="importproduct" class="flex items-center">
                <input
                    type="file"
                    id="fileInput"
                    class="file-input file-input-bordered file-input-primary file-input-sm w-full max-w-xs"
                    @input.prevent="form.file = $event.target.files[0]"
                />

                <PrimaryButton class="m-6 bg-navy" @click.prevent="submitForm">
                    <Import class="h-4" />
                </PrimaryButton>
              </form>

              <Excel
                :data="items"
                :headers="['ITEMID', 'ITEMNAME', 'BARCODE', 'CATEGORY', 'PRICE']"
                :row-name-props="['itemid', 'itemname', 'barcode', 'category', 'price']"
                class="ml-4 relative display"
              />
            </div>

          </div>
          
          <DataTable
            :data="items"
            :columns="columns"
            class="w-full relative display"
            :options="options"
          >
            <template #action="data">
              <TransparentButton
                type="button"
                  @click="
                  toggleUpdateModal(
                    data.cellData.itemid
                  )
                "
                class="me-1"
              >
                <editblue class="h-6"></editblue>
                
              </TransparentButton>

              <TransparentButton
                type="button"
                  @click="
                  toggleMoreModal(
                    data.cellData.itemid
                  )
                "
                class="me-1"
              >
                <moreblue class="h-6"></moreblue>
              </TransparentButton>

            </template>
          </DataTable>
        </TableContainer>
      </template>
    </Main>
  </template>


<script>
import RetailPanel from "@/Layouts/RetailPanel.vue";

export default {
  components: {
    RetailPanel
  },
  data() {
    return {
      showPanel: true // Initially show the panel
    };
  },

  /* created() {
    // Listen for the 'hide-panel' event emitted by RetailPanel.vue
    this.$root.$on('hide-panel', this.hidePanel);
  }, */

  methods: {
    hidePanel() {
      // Method to hide the panel
      this.showPanel = false;
    }
  }
};
</script>

