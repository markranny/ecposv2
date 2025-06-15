<script setup>
import { useForm } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import Create from "@/Components/Items/Create.vue";
import Enable from "@/Components/Items/Enable.vue";
import Update from "@/Components/Items/Update.vue";
import UpdateMOQ from "@/Components/Items/UpdateMOQ.vue";
import More from "@/Components/Items/More.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import TransparentButton from "@/Components/Buttons/TransparentButton.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import Main from "@/Layouts/AdminPanel.vue";
import StorePanel from "@/Layouts/Main.vue";
import Excel from "@/Components/Exports/Excel.vue";

import Add from "@/Components/Svgs/Add.vue";
import Enabled from "@/Components/Svgs/Enabled.vue";
import editblue from "@/Components/Svgs/editblue.vue";
import Import from "@/Components/Svgs/Import.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import Link from "@/Components/Svgs/Link.vue";

import { ref, computed, toRefs, onMounted, nextTick } from 'vue';
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
// Added missing price fields
const manilaprice = ref('');
const foodpandaprice = ref('');
const grabfoodprice = ref('');
const mallprice = ref('');
const production = ref('');

const allSelected = ref(false);
const selectedItems = ref([]);
const showImportModal = ref(false);

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
  return props.auth === 'STORE' ? StorePanel : Main;
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

const navigateToLinks = (itemid) => {
    router.visit(route('item-links.index', itemid));
};

const columns = computed(() => {
  const baseColumns = [
    { data: 'Activeondelivery', title: 'ENABLEORDER' },
    { data: 'itemid', title: 'PRODUCTCODE' },
    { data: 'itemname', title: 'DESCRIPTION' },
    { data: 'barcode', title: 'BARCODE' },
    { data: 'itemgroup', title: 'CATEGORY' },
    { data: 'specialgroup', title: 'RETAILGROUP' },
    { data: 'production', title: 'PRODUCTION' },
    { data: 'moq', title: 'MOQ' },
    {
      data: 'cost',
      title: 'COST',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.cost != null ? Number(row.cost).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    {
      data: 'price',
      title: 'SRP',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.price != null ? Number(row.price).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    {
      data: 'manilaprice',
      title: 'MANILA',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.manilaprice != null ? Number(row.manilaprice).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    {
      data: 'mallprice',
      title: 'MALL',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.mallprice != null ? Number(row.mallprice).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    {
      data: 'grabfoodprice',
      title: 'GRABFOOD',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.grabfoodprice != null ? Number(row.grabfoodprice).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    {
      data: 'foodpandaprice',
      title: 'FOODPANDA',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.foodpandaprice != null ? Number(row.foodpandaprice).toFixed(2) : '0.00';
        }
        return data;
      },
    },
    // Added default fields to columns
    {
      data: 'default1',
      title: 'DEFAULT1',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.default1 ? 'Yes' : 'No';
        }
        return data;
      },
    },
    {
      data: 'default2',
      title: 'DEFAULT2',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.default2 ? 'Yes' : 'No';
        }
        return data;
      },
    },
    {
      data: 'default3',
      title: 'DEFAULT3',
      render: (data, type, row) => {
        if (type === 'display') {
          return row.default3 ? 'Yes' : 'No';
        }
        return data;
      },
    },
  ];

  if (isOpic.value || isAdmin.value || isRso.value) {
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

// Updated to accept all required parameters including price fields
const toggleUpdateModal = (newID, newItemName, newItemGroup, newPrice, newCost, newMoq, newManilaPrice, newFoodPandaPrice, newGrabFoodPrice, newMallPrice, newProduction) => {
    itemid.value = newID;
    itemname.value = newItemName;
    itemgroup.value = newItemGroup;
    price.value = newPrice;
    cost.value = newCost;
    moq.value = newMoq;
    manilaprice.value = newManilaPrice || 0;
    foodpandaprice.value = newFoodPandaPrice || 0;
    grabfoodprice.value = newGrabFoodPrice || 0;
    mallprice.value = newMallPrice || 0;
    production.value = newProduction || '';
    showModalUpdate.value = true;
};

const toggleMoreModal = (newID) => {
    itemid.value = newID;
    showModalMore.value = true;
};

const toggleUpdateMOQModal = (newID, newItemName, newItemGroup, newPrice, newCost, newMoq, newProduction) => {
    itemid.value = newID;
    itemname.value = newItemName;
    itemgroup.value = newItemGroup;
    price.value = newPrice;
    cost.value = newCost;
    moq.value = newMoq;
    production.value = newProduction || '';
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

// Import form
const importForm = useForm({
    file: null,
});

const handleFileUpload = (event) => {
    importForm.file = event.target.files[0];
};

const submitImportForm = () => {
    if (!importForm.file) {
        alert('Please select a file to import.');
        return;
    }

    importForm.post('/ImportProducts', {
        preserveScroll: true,
        onSuccess: () => {
            importForm.reset();
            const fileInput = document.getElementById('fileInput');
            if (fileInput) fileInput.value = '';
            showImportModal.value = false;
            // Reload the page to show imported items
            window.location.reload();
        },
        onError: (errors) => {
            console.error('Import failed:', errors);
        },
    });
};

const downloadTemplate = () => {
    window.location.href = '/download-import-template';
};

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

// Enhanced export data with all fields
const getExportData = () => {
  const data = getFilteredData();
  return data.map(item => ({
    itemid: item.itemid,
    itemname: item.itemname,
    barcode: item.barcode,
    itemgroup: item.itemgroup,
    specialgroup: item.specialgroup,
    production: item.production,
    moq: item.moq,
    cost: item.cost ? Number(item.cost).toFixed(2) : '0.00',
    price: item.price ? Number(item.price).toFixed(2) : '0.00',
    manilaprice: item.manilaprice ? Number(item.manilaprice).toFixed(2) : '0.00',
    mallprice: item.mallprice ? Number(item.mallprice).toFixed(2) : '0.00',
    grabfoodprice: item.grabfoodprice ? Number(item.grabfoodprice).toFixed(2) : '0.00',
    foodpandaprice: item.foodpandaprice ? Number(item.foodpandaprice).toFixed(2) : '0.00',
    default1: item.default1 ? 'Yes' : 'No',
    default2: item.default2 ? 'Yes' : 'No',
    default3: item.default3 ? 'Yes' : 'No',
    Activeondelivery: item.Activeondelivery
  }));
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

    <component :is="layoutComponent" active-tab="RETAILITEMS">
      <template v-slot:modals>
        <Create :show-modal="showCreateModal" @toggle-active="createModalHandler" :rboinventitemretailgroups="props.rboinventitemretailgroups"  @select-item="handleSelectedCategory"/>

        <!-- Updated Update component with all required props -->
        <Update
          :show-modal="showModalUpdate"
          :itemid="itemid"
          :itemname="itemname"
          :itemgroup="itemgroup"
          :price="price"
          :cost="cost"
          :moq="moq"
          :manilaprice="manilaprice"
          :foodpandaprice="foodpandaprice"
          :grabfoodprice="grabfoodprice"
          :mallprice="mallprice"
          :production="production"
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
          :production="production"
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

        <!-- Import Modal -->
        <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
          <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
              <h3 class="text-lg font-medium text-gray-900 mb-4">Import Items</h3>
              
              <div class="mb-4">
                <button
                  @click="downloadTemplate"
                  class="w-full bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-3"
                >
                  Download Template
                </button>
              </div>

              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Select CSV File
                </label>
                <input
                  type="file"
                  id="fileInput"
                  @change="handleFileUpload"
                  accept=".csv,.txt"
                  class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
              </div>

              <div class="flex justify-end space-x-3">
                <button
                  @click="showImportModal = false"
                  class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded"
                >
                  Cancel
                </button>
                <button
                  @click="submitImportForm"
                  :disabled="importForm.processing || !importForm.file"
                  class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded disabled:opacity-50"
                >
                  {{ importForm.processing ? 'Importing...' : 'Import' }}
                </button>
              </div>
            </div>
          </div>
        </div>

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
                  
                  <!-- Export Button -->
                  <Excel
                    :data="getExportData()"
                    :headers="['ITEMID', 'ITEMNAME', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 'PRODUCTION', 'MOQ', 'COST', 'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'DEFAULT1', 'DEFAULT2', 'DEFAULT3', 'ENABLEORDER']"
                    :row-name-props="['itemid', 'itemname', 'barcode', 'itemgroup', 'specialgroup', 'production', 'moq', 'cost', 'price', 'manilaprice', 'mallprice', 'grabfoodprice', 'foodpandaprice', 'default1', 'default2', 'default3', 'Activeondelivery']"
                    class="m-2 bg-green-500"
                    v-if="isAdmin || isOpic"
                  />
                  
                  <!-- Import Button -->
                  <PrimaryButton 
                    class="m-2 bg-blue-500 hover:bg-blue-700" 
                    @click="showImportModal = true" 
                    v-if="isAdmin || isOpic"
                  >
                    <Import class="h-4" />
                  </PrimaryButton>
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

                  <!-- Export Button -->
                  <Excel
                    :data="getExportData()"
                    :headers="['ITEMID', 'ITEMNAME', 'BARCODE', 'CATEGORY', 'RETAILGROUP', 'PRODUCTION', 'MOQ', 'COST', 'SRP', 'MANILA', 'MALL', 'GRABFOOD', 'FOODPANDA', 'DEFAULT1', 'DEFAULT2', 'DEFAULT3', 'ENABLEORDER']"
                    :row-name-props="['itemid', 'itemname', 'barcode', 'itemgroup', 'specialgroup', 'production', 'moq', 'cost', 'price', 'manilaprice', 'mallprice', 'grabfoodprice', 'foodpandaprice', 'default1', 'default2', 'default3', 'Activeondelivery']"
                    class="m-2 sm:m-6 bg-green-500"
                    v-if="isAdmin || isOpic"
                  />
                  
                  <!-- Import Button -->
                  <PrimaryButton 
                    class="m-2 sm:m-6 bg-blue-500 hover:bg-blue-700" 
                    @click="showImportModal = true" 
                    v-if="isOpic"
                  >
                    <Import class="h-4" />
                  </PrimaryButton>
                </div>

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
                        <!-- Updated to pass all required data including price fields -->
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
                                    data.cellData.moq,
                                    data.cellData.manilaprice,
                                    data.cellData.foodpandaprice,
                                    data.cellData.grabfoodprice,
                                    data.cellData.mallprice,
                                    data.cellData.production
                                )
                            "
                            class="me-1"
                        >
                            <editblue class="h-6"></editblue>
                        </TransparentButton>

                        <!-- New Links Button -->
                        <TransparentButton
                            type="button"
                            @click="navigateToLinks(data.cellData.itemid)"
                            class="me-1"
                            v-if="isAdmin || isOpic"
                            title="Manage Item Links"
                        >
                            <Link class="h-6"></Link>
                        </TransparentButton>

                        <TransparentButton
                            type="button"
                            @click="toggleMoreModal(data.cellData.itemid)"
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
                                    data.cellData.moq,
                                    data.cellData.production
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