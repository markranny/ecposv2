<script setup>
import { useForm } from '@inertiajs/vue3';
import Create from "@/Components/Items/Create.vue";
import Enable from "@/Components/Items/Enable.vue";
import Update from "@/Components/Items/Update.vue";
import UpdateMOQ from "@/Components/Items/UpdateMOQ.vue";
import More from "@/Components/Items/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
/* import StorePanel from "@/Layouts/StorePanel.vue"; */
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import Enabled from "@/Components/Svgs/Enabled.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import Import from "@/Components/Svgs/Import.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";

import { ref, computed, defineProps, toRefs, onMounted, nextTick } from 'vue';
import axios from 'axios';

import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const itemid = ref('');
const itemname = ref('');
const cost = ref('');
const itemgroup = ref('');
const specialgroup = ref('');
const price = ref('');
const moq = ref('');

const allSelected = ref(false);
const selectedItems = ref([]);

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
    itemids: Array,
    auth: {
        type: Object,
        required: true,
    },
    rboinventitemretailgroups:{
        type: Array,
        required: true,
    },
});

const layoutComponent = computed(() => {
  return props.auth === 'STORE' ? Main : Main;
});

const { user } = toRefs(props.auth);
const userRole = ref(user.value.role);
const isOpic = computed(() => userRole.value === 'SUPERADMIN');
const isAdmin = computed(() => userRole.value === 'OPIC');
const isRso = computed(() => userRole.value === 'ADMIN');

const showModalUpdate = ref(false);
const showModalUpdateMOQ = ref(false);
const showCreateModal = ref(false);
const showEnableModal = ref(false);
const showModalMore = ref(false);

const columns = computed(() => {
  const baseColumns = [
    { data: 'Activeondelivery', title: 'ENABLEORDER' },
    { data: 'itemid', title: 'PRODUCTCODE' },
    { data: 'itemname', title: 'DESCRIPTION' },
    { data: 'barcode', title: 'BARCODE' },
    { data: 'production', title: 'PRODUCTION' },
    { data: 'itemgroup', title: 'CATEGORY' },
    { data: 'specialgroup', title: 'RETAILGROUP' },
    { data: 'moq', title: 'MOQ' },
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
      title: 'SRP',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.price.toFixed(2);
        }
        return data;
      },
    },
  ];

  if (isOpic || isAdmin.value || isRso.value) {
    baseColumns.unshift({
      data: null,
      title: '<input type="checkbox" id="selectAll" class="form-checkbox h-5 w-5 text-blue-600 rounded-full">',
      orderable: false,
      render: (data, type, row) => {
        return `<input type="checkbox" class="select-item form-checkbox h-5 w-5 text-blue-600 rounded-full" data-id="${row.itemid}">`;
      }
    });
    baseColumns.push({
      data: null,
      render: '#action',
      title: 'ACTIONS'
    });
  }
  return baseColumns;
});

const options = {
    paging: false,
    scrollX: true,
    scrollY: "60vh",
    scrollCollapse: true,
};

const toggleAllSelection = () => {
    allSelected.value = !allSelected.value;
    const checkboxes = document.querySelectorAll('.select-item');
    checkboxes.forEach(checkbox => {
        checkbox.checked = allSelected.value;
    });
    updateSelectedItems();
};

const updateSelectedItems = () => {
    const checkboxes = document.querySelectorAll('.select-item:checked');
    selectedItems.value = Array.from(checkboxes).map(checkbox => checkbox.dataset.id);
};

const getSelectedItems = () => {
    return selectedItems.value;
};

const toggleUpdateModal = (newID, newItemName, newItemGroup, newPrice, newCost, newMoq) => {
    itemid.value = newID;
    itemname.value = newItemName;
    itemgroup.value = newItemGroup;
    price.value = newPrice;
    cost.value = newCost;
    moq.value = newMoq;
    showModalUpdate.value = true;
};
const toggleMoreModal = (newID) => {
    itemid.value = newID;
    showModalMore.value = true;
};
const toggleUpdateMOQModal = (newID, newItemName, newItemGroup, newPrice, newCost, newMoq) => {
    itemid.value = newID;
    itemname.value = newItemName;
    itemgroup.value = newItemGroup;
    price.value = newPrice;
    cost.value = newCost;
    moq.value = newMoq;
    showModalUpdateMOQ.value = true;
};

const toggleCreateModal = () => {
    showCreateModal.value = true;
};

const toggleEnableModal = (newID) => {
    itemid.value = newID;
    showEnableModal.value = true;
};

const updateModalHandler = () => {
    showModalUpdate.value = false;
};

const updateMOQModalHandler = () => {
    showModalUpdateMOQ.value = false;
};

const createModalHandler = () => {
    showCreateModal.value = false;
};

const enableModalHandler = () => {
    showCreateModal.value = false;
};

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

const handleSelectedCategory = (category) => {
    console.log('Selected Category:', category);
};

onMounted(() => {
  const dataTable = ref(null);
  
  nextTick(() => {
    if (isAdmin.value || isOpic.value) {
      const selectAllCheckbox = document.getElementById('selectAll');
      if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', toggleAllSelection);
      }

      const itemCheckboxes = document.querySelectorAll('.select-item');
      itemCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedItems);
      });
    }
  });
});

const ClickEnable = () => {
  if (selectedItems.value.length === 0) {
    alert('Please select at least one item.');
    return;
  }

  axios.post('/EnableOrder', {
    itemids: selectedItems.value
  })
  .then(response => {
    alert(response.data.message);
    location.reload();
  })
  
  .catch(error => {
    console.error('Error updating items:', error);
    alert('An error occurred while updating items.');
  });
};

const dataTable = ref(null);

if (dataTable.value) {
  dataTable.value.api = dataTable.value.dt;
  }

const getFilteredData = () => {
  if (dataTable.value && dataTable.value.api) {
    return dataTable.value.api.rows({ search: 'applied' }).data().toArray();
  }
  return props.items;
};

const products = () => {
  window.location.href = '/items';
};

const nonproducts = () => {
  window.location.href = '/warehouse';
};
</script>

<template>
  <Head title="RETAILITEMS">
    <meta name="theme-color" content="#000000" />
    <link rel="manifest" href="/manifest.json" />
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    <meta name="apple-mobile-web-app-status-bar" content="#000000" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="mobile-web-app-capable" content="yes" />
  </Head>

  <!-- <component :is="layoutComponent" active-tab="RETAILITEMS"> 
  </component> -->

    <component :is="layoutComponent" active-tab="RETAILITEMS">
      <template v-slot:modals>
        <Create :show-modal="showCreateModal" @toggle-active="createModalHandler" :rboinventitemretailgroups="props.rboinventitemretailgroups"  @select-item="handleSelectedCategory"/>

        <Update
          :show-modal="showModalUpdate"
          :itemid="itemid"
          :itemname="itemname"
          :itemgroup="itemgroup"
          :price="price"
          :cost="cost"
          :moq="moq"
          @toggle-active="updateModalHandler"
        />

        <UpdateMOQ
          :show-modal="showModalUpdateMOQ"
          :itemid="itemid"
          :itemname="itemname"
          :itemgroup="itemgroup"
          :price="price"
          :cost="cost"
          :moq="moq"
          @toggle-active="updateMOQModalHandler"
        />

        <More
          :show-modal="showModalMore"
          :itemid="itemid"
          @toggle-active="MoreModalHandler"
        />

        <Enable
          :show-modal="showEnableModal"
          :itemids="selectedItems"
          @click="ClickEnable"
        />

      </template>


      <template v-slot:main>

        <TableContainer>

          <div class="md:hidden">
            <div class="absolute adjust">
              <div class="flex flex-col" id="panel" v-show="showPanel">
                <div class="flex flex-wrap items-center">
                  <PrimaryButton
                    v-if="isAdmin || isOpic"
                    type="button"
                    @click="toggleCreateModal"
                    class="m-2 bg-navy"
                  >
                    <Add class="h-4" />
                  </PrimaryButton>

                  <PrimaryButton
                    v-if="isAdmin || isOpic"
                    type="button"
                    @click="ClickEnable"
                    class="m-2 bg-navy"
                  >
                    <Enabled class="h-4" />
                  </PrimaryButton>
                  

                  <form @submit.prevent="submitForm" id="importproduct" class="flex items-center">
                    <Excel
                      :data="items"
                      :headers="['ITEMID', 'ITEMNAME', 'BARCODE', 'CATEGORY', 'PRICE', 'PRODUCTION', 'MOQ']"
                      :row-name-props="['itemid', 'itemname', 'barcode', 'itemgroup', 'price', 'production', 'moq']"
                      class="relative display"
                      v-if="isAdmin || isOpic"
                    />
                    
                    <PrimaryButton class="m-2 bg-navy" @click.prevent="submitForm" v-if="isAdmin || isOpic">
                      <Import class="h-4" />
                    </PrimaryButton>
                  </form>
                </div>

                <div class="w-full mt-2">
                  <!-- <input
                    type="file"
                    id="fileInput"
                    class="file-input file-input-bordered file-input-primary file-input-sm w-full max-w-xs"
                    @input.prevent="form.file = $event.target.files[0]"
                    v-if="isAdmin"
                  /> -->
                </div>
              </div>
            </div> 
          </div>

            <div class="hidden md:block">
              <div class="absolute adjust">
              <div class="flex flex-col sm:flex-row justify-start items-center" id="panel" v-show="showPanel">
                
                <div class="flex flex-wrap justify-center sm:justify-start w-full sm:w-auto">
                  <PrimaryButton
                    v-if="isOpic "
                    type="button"
                    @click="toggleCreateModal"
                    class="m-2 sm:m-6 bg-navy"
                  >
                    <Add class="h-4" />
                  </PrimaryButton>

                  <PrimaryButton
                    v-if="isAdmin || isOpic"
                    type="button"
                    @click="ClickEnable"
                    class="m-2 sm:m-6 bg-navy"
                  >
                    <Enabled class="h-4" />
                  </PrimaryButton>
                </div>

                <form @submit.prevent="submitForm" id="importproduct" class="flex flex-col sm:flex-row items-center w-full sm:w-auto">
                  <Excel
                  :data="getFilteredData()"
                  :headers="['ITEMID', 'ITEMNAME', 'BARCODE', 'CATEGORY', 'PRICE', 'PRODUCTION']"
                  :row-name-props="['itemid', 'itemname', 'barcode', 'itemgroup', 'price', 'production']"
                  class="mt-4 sm:mt-0 sm:ml-4 relative display"
                  v-if="isAdmin || isOpic"
                  />
                  
                  <PrimaryButton class="m-2 sm:m-6 bg-navy" @click.prevent="submitForm" v-if="isOpic">
                      <Import class="h-4" />
                  </PrimaryButton>

                  <!-- <input
                      type="file"
                      id="fileInput"
                      class="file-input file-input-bordered file-input-primary file-input-sm w-full max-w-xs mb-2 sm:mb-0"
                      @input.prevent="form.file = $event.target.files[0]"
                      v-if="isAdmin"
                  /> -->
                </form>

                <PrimaryButton
                    type="button"
                    @click="products"
                    class="sm:m-2 bg-navy"
                  >
                    BW PRODUCTS
                  </PrimaryButton>

                  <PrimaryButton
                    type="button"
                    @click="nonproducts"
                    class="sm:m-2 bg-red-900"
                  >
                    WAREHOUSE
                  </PrimaryButton>

              </div>
            </div>
          </div>
          
          <DataTable
          :data="items"
          :columns="columns"
          class="w-full relative display mt-10"
          :options="options"
          ref="dataTable"
        >
          <template #action="data">
            <TransparentButton
              type="button"
              v-if="isAdmin || isOpic"
              @click="
                toggleUpdateModal(
                  data.cellData.itemid,
                  data.cellData.itemname,
                  data.cellData.itemgroup,
                  data.cellData.price,
                  data.cellData.cost,
                  data.cellData.moq
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

            <TransparentButton
              type="button"
              v-if="isRso || isOpic"
              @click="
                toggleUpdateMOQModal(
                  data.cellData.itemid,
                  data.cellData.itemname,
                  data.cellData.itemgroup,
                  data.cellData.price,
                  data.cellData.cost,
                  data.cellData.moq
                )
              "
              class="me-1"
            >
              <editblue class="h-6"></editblue>
            </TransparentButton>
          </template>
        </DataTable>
        </TableContainer>
      </template>
    </component>
  </template>


<script>
import RetailPanel from "@/Layouts/RetailPanel.vue";

export default {
  components: {
    RetailPanel
  },
  data() {
    return {
      showPanel: true 
    };
  },

  methods: {
    hidePanel() {
      this.showPanel = false;
    }
  }
};

</script>
