<script setup>
import { useRouter, useRoute } from 'vue-router';
import Create from "@/Components/Partycakes/Create.vue";
import Update from "@/Components/Partycakes/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/Main.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, onMounted, reactive, watch } from "vue";
import { computed, defineProps, toRefs, nextTick } from 'vue';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import axios from 'axios';
DataTable.use(DataTablesCore);

const id = ref('');
const subject = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    partycakes: {
        type: Array,
        required: true,
    },
    partycakes1: {
        type: Array,
        required: true,
    },
    partycakes2: {
        type: Array,
        required: true,
    },
    partycakes3: {
        type: Array,
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

/* const partycakes = ref([]); */

const columns = [
    { data: 'id', title: 'ID' },
    { data: 'COSNO', title: 'COs No.' },
    { data: 'BRANCH', title: 'BRANCH' },
    { data: 'DATEORDER', title: 'DATEORDER' },
    { data: 'DATEPICKEDUP', title: 'DATEPICKEDUP' },
    { data: 'TIMEPICKEDUP', title: 'DATEPICKEDUP' },
    { data: 'DATEDELIVERED', title: 'DATEDELIVERED' },
    { data: 'TIMEDELIVERED', title: 'TIMEDELIVERED' },
    { 
        data: 'created_at', 
        title: 'DATE',
        render: function(data, type, row) {
            if (type === 'display' || type === 'filter') {
                return data.split('T')[0]; 
            }
            return data;
        }
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

const toggleUpdateModal = (newID, newSUBJECT, newDESCRIPTION) => {
    id.value = newID;
    subject.value = newSUBJECT;
    description.value = newDESCRIPTION;
    showModalUpdate.value = true;
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

const route = useRoute()
const router = useRouter()

const cake = reactive({
  COSNO: '',
  BRANCH: '',
  DATEORDER: '',
  DATEPICKEDUP: '',
  TIMEPICKEDUP: '',
  DELIVERED: '',
  TIMEDELIVERED: '',
  BDAYCODENO: '',
  FLAVOR: '',
  MOTIF: '',
  ICING: '',
  DEDICATION: '',
  OTHERS: '',
  file_path: null
})

const cakeId = ref(null)

onMounted(async () => {
  if (route.params && route.params.id) {
    cakeId.value = route.params.id
    try {
      const response = await axios.get(`/api/partys-cakes/${cakeId.value}`)
      Object.assign(cake, response.data)
    } catch (error) {
      console.error('Failed to fetch party cake:', error)
    }
  } else {
    console.warn('No cake ID provided in route params')
    // Handle the case where there's no ID, perhaps redirect to a list page
    // router.push('/partys-cakes')
  }
})

const updatePartyCake = async () => {
  if (!cakeId.value) {
    console.error('Cannot update: No cake ID available')
    return
  }

  try {
    await axios.put(`/api/partys-cakes/${cakeId.value}`, cake)
    console.log('Party cake updated:', cake)
    router.push('/partys-cakes') // Redirect to party cakes list after update
  } catch (error) {
    console.error('Failed to update party cake:', error)
    // Handle error (e.g., show error message to user)
  }
}



const posted = (id) => {
  window.location.href = `/pc-posted/${id}`;
};

const process = (id) => {
  window.location.href = `/pc-process/${id}`;
};

const dr = (id) => {
  window.location.href = `/pc-received/${id}`;
};

/* const posted = (id) => {
    const userConfirmed = window.confirm('Are you sure you want to post the party cakes?');

    if (userConfirmed) {
        window.location.href = '/pc-posted/${id}';
    } else {
        console.log('User cancelled the post operation.');
    }
};

const process = (id) => {
    const userConfirmed = window.confirm('Are you sure you want to process the party cakes?');

    if (userConfirmed) {
        window.location.href = '/pc-process/${id}';
    } else {
        console.log('User cancelled the post operation.');
    }
}; */

const { user } = toRefs(props.auth);
const userRole = ref(user.value.role);
const isOpic = computed(() => userRole.value === 'OPIC');
const isStore = computed(() => userRole.value === 'STORE');

const handleSelectedStore = (rbostoretables) => {
  console.log('Selected rbostoretables:', rbostoretables);
};


const getFileUrl = (filePath) => {
    return `/storage/${filePath}`;
};

const getDownloadUrl = (cakeId) => {
  return `/api/cakes/${cakeId}/download`
}

const searchQuery = ref('');
const filteredCakes = ref([]);

const searchCakes = () => {
    if (!searchQuery.value) {
        filteredCakes.value = props.partycakes2;
    } else {
        const query = searchQuery.value.toLowerCase();
        filteredCakes.value = props.partycakes2.filter(cake => 
            (cake.COSNO && cake.COSNO.toLowerCase().includes(query)) ||
            (cake.BRANCH && cake.BRANCH.toLowerCase().includes(query)) ||
            (cake.DATEORDER && cake.DATEORDER.toLowerCase().includes(query)) ||
            (cake.FLAVOR && cake.FLAVOR.toLowerCase().includes(query)) ||
            (cake.DEDICATION && cake.DEDICATION.toLowerCase().includes(query))
        );
    }
};

// Initialize filteredCakes with all cakes
filteredCakes.value = props.partycakes2;

// Watch for changes in the search query
watch(searchQuery, () => {
    searchCakes();
});

</script>


<template>
    <Main active-tab="PARTYCAKES">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  :rbostoretables="props.rbostoretables"  @select-item="handleSelectedStore"/>
            <Update 
                :show-modal="showModalUpdate"  
                :ID="id" 
                :SUBJECT="subject"  
                :DESCRIPTION="description" 
                @toggle-active="updateModalHandler"
            />
            
        </template>

        <template v-slot:main>
            <!-- <TableContainer>
                <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-4" />
                        </PrimaryButton>
                    </div>
                </div>

                <DataTable :data="announcements" :columns="columns" class="w-full relative display" :options="options">
                    <template #action="data">
                        <div class="flex justify-start">
                            <TransparentButton 
                                type="button" 
                                @click="toggleUpdateModal(data.cellData.id, data.cellData.SUBJECT, data.cellData.DESCRIPTION)" 
                                class="me-1"
                            >
                                <editblue class="h-6 cursor-pointer"></editblue>
                            </TransparentButton>
                        </div>
                    </template>
                </DataTable>
            </TableContainer> -->

                <div class="absolute adjust">
                    <div class="flex justify-start items-center">
                        <PrimaryButton
                            type="button"
                            @click="toggleCreateModal"
                            class="m-6 bg-navy"
                        >
                            <Add class="h-4" />
                        </PrimaryButton>
                    </div>
                </div>

                <div role="tablist" className="tabs tabs-lifted mt-10 p-5 ">
                    <input type="radio" v-if="isStore" name="my_tabs_2" role="tab" className="tab !bg-gray-100 !text-gray-500 !font-bold" aria-label="COS" defaultChecked />
                    <div role="tabpanel" className="tab-content !bg-gray-200 !border-gray-300 p-6">
                        ORDER STORE

                        <!-- <div className="flex flex-wrap gap-4">
                            <div className="card bg-base-100 w-full w-full sm:w-6/12 shadow-xl">
                                <div className="card-body">
                                <div className="card-actions justify-end">
                                    <button className="btn btn-square btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M17 7.25C16.5858 7.25 16.25 7.58579 16.25 8V16C16.25 16.4142 16.5858 16.75 17 16.75C17.4142 16.75 17.75 16.4142 17.75 16V8C17.75 7.58579 17.4142 7.25 17 7.25Z" fill="#1C274C"></path> <path d="M6.25 12C6.25 12.4142 6.58579 12.75 7 12.75H12.1893L10.4697 14.4697C10.1768 14.7626 10.1768 15.2374 10.4697 15.5303C10.7626 15.8232 11.2374 15.8232 11.5303 15.5303L14.5303 12.5303C14.671 12.3897 14.75 12.1989 14.75 12C14.75 11.8011 14.671 11.6103 14.5303 11.4697L11.5303 8.46967C11.2374 8.17678 10.7626 8.17678 10.4697 8.46967C10.1768 8.76256 10.1768 9.23744 10.4697 9.53033L12.1893 11.25H7C6.58579 11.25 6.25 11.5858 6.25 12Z" fill="#1C274C"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9426 1.25C9.63423 1.24999 7.82519 1.24998 6.4137 1.43975C4.96897 1.63399 3.82895 2.03933 2.93414 2.93414C2.03933 3.82895 1.63399 4.96897 1.43975 6.41371C1.24998 7.82519 1.24999 9.63423 1.25 11.9426V12.0574C1.24999 14.3658 1.24998 16.1748 1.43975 17.5863C1.63399 19.031 2.03933 20.1711 2.93414 21.0659C3.82895 21.9607 4.96897 22.366 6.4137 22.5603C7.82519 22.75 9.63423 22.75 11.9426 22.75H12.0574C14.3658 22.75 16.1748 22.75 17.5863 22.5603C19.031 22.366 20.1711 21.9607 21.0659 21.0659C21.9607 20.1711 22.366 19.031 22.5603 17.5863C22.75 16.1748 22.75 14.3658 22.75 12.0574V11.9426C22.75 9.63423 22.75 7.82519 22.5603 6.41371C22.366 4.96897 21.9607 3.82895 21.0659 2.93414C20.1711 2.03933 19.031 1.63399 17.5863 1.43975C16.1748 1.24998 14.3658 1.24999 12.0574 1.25H11.9426ZM3.9948 3.9948C4.56445 3.42514 5.33517 3.09825 6.61358 2.92637C7.91356 2.75159 9.62177 2.75 12 2.75C14.3782 2.75 16.0864 2.75159 17.3864 2.92637C18.6648 3.09825 19.4355 3.42514 20.0052 3.9948C20.5749 4.56445 20.9018 5.33517 21.0736 6.61358C21.2484 7.91356 21.25 9.62178 21.25 12C21.25 14.3782 21.2484 16.0864 21.0736 17.3864C20.9018 18.6648 20.5749 19.4355 20.0052 20.0052C19.4355 20.5749 18.6648 20.9018 17.3864 21.0736C16.0864 21.2484 14.3782 21.25 12 21.25C9.62177 21.25 7.91356 21.2484 6.61358 21.0736C5.33517 20.9018 4.56445 20.5749 3.9948 20.0052C3.42514 19.4355 3.09825 18.6648 2.92637 17.3864C2.75159 16.0864 2.75 14.3782 2.75 12C2.75 9.62178 2.75159 7.91356 2.92637 6.61358C3.09825 5.33517 3.42514 4.56445 3.9948 3.9948Z" fill="#1C274C"></path> </g></svg>
                                    </svg>
                                    </button>
                                </div>
                                <h2 class="absolute hidden sm:block md:block lg:block">Party Cake Informations</h2>


                                
                                <form>

                                    <div class="grid gap-6 mb-6 md:grid-cols-3">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>
                                    <div class="grid gap-6 mb-6 md:grid-cols-3">
                                        <div>
                                            <label for="company" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer</label>
                                            <input type="text" id="company" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500" readonly>
                                        </div>  
                                        <div>
                                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                                            <input type="tel" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500"  pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" readonly />
                                        </div>
                                        <div>
                                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone number</label>
                                            <input type="tel" id="phone" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-whitedark:focus:ring-blue-500 dark:focus:border-blue-500"  pattern="[0-9]{3}-[0-9]{2}-[0-9]{3}" readonly />
                                        </div>
                                    </div>

                                    <div class="grid gap-6 mb-6 md:grid-cols-4">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                    <div class="grid gap-6 mb-6 md:grid-cols-4">
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                            <input type="text" id="first_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                            <input type="text" id="last_name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                        <input type="email" id="email" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"  readonly />
                                    </div> 

                                    
                                    <label for="message" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="message" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"></textarea>
                                </form>


                                </div>
                            </div>
                        </div> -->

                        <!-- <div class="flex flex-wrap gap-4">
                            <div v-for="cake in partycakes" :key="cake.id" class="card bg-base-100 w-full sm:w-6/12 shadow-xl">
                            <div class="card-body">
                                <div class="card-actions justify-end">
                                <button class="btn btn-square btn-sm">
                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M17 7.25C16.5858 7.25 16.25 7.58579 16.25 8V16C16.25 16.4142 16.5858 16.75 17 16.75C17.4142 16.75 17.75 16.4142 17.75 16V8C17.75 7.58579 17.4142 7.25 17 7.25Z" fill="#1C274C"></path> <path d="M6.25 12C6.25 12.4142 6.58579 12.75 7 12.75H12.1893L10.4697 14.4697C10.1768 14.7626 10.1768 15.2374 10.4697 15.5303C10.7626 15.8232 11.2374 15.8232 11.5303 15.5303L14.5303 12.5303C14.671 12.3897 14.75 12.1989 14.75 12C14.75 11.8011 14.671 11.6103 14.5303 11.4697L11.5303 8.46967C11.2374 8.17678 10.7626 8.17678 10.4697 8.46967C10.1768 8.76256 10.1768 9.23744 10.4697 9.53033L12.1893 11.25H7C6.58579 11.25 6.25 11.5858 6.25 12Z" fill="#1C274C"></path> <path fill-rule="evenodd" clip-rule="evenodd" d="M11.9426 1.25C9.63423 1.24999 7.82519 1.24998 6.4137 1.43975C4.96897 1.63399 3.82895 2.03933 2.93414 2.93414C2.03933 3.82895 1.63399 4.96897 1.43975 6.41371C1.24998 7.82519 1.24999 9.63423 1.25 11.9426V12.0574C1.24999 14.3658 1.24998 16.1748 1.43975 17.5863C1.63399 19.031 2.03933 20.1711 2.93414 21.0659C3.82895 21.9607 4.96897 22.366 6.4137 22.5603C7.82519 22.75 9.63423 22.75 11.9426 22.75H12.0574C14.3658 22.75 16.1748 22.75 17.5863 22.5603C19.031 22.366 20.1711 21.9607 21.0659 21.0659C21.9607 20.1711 22.366 19.031 22.5603 17.5863C22.75 16.1748 22.75 14.3658 22.75 12.0574V11.9426C22.75 9.63423 22.75 7.82519 22.5603 6.41371C22.366 4.96897 21.9607 3.82895 21.0659 2.93414C20.1711 2.03933 19.031 1.63399 17.5863 1.43975C16.1748 1.24998 14.3658 1.24999 12.0574 1.25H11.9426ZM3.9948 3.9948C4.56445 3.42514 5.33517 3.09825 6.61358 2.92637C7.91356 2.75159 9.62177 2.75 12 2.75C14.3782 2.75 16.0864 2.75159 17.3864 2.92637C18.6648 3.09825 19.4355 3.42514 20.0052 3.9948C20.5749 4.56445 20.9018 5.33517 21.0736 6.61358C21.2484 7.91356 21.25 9.62178 21.25 12C21.25 14.3782 21.2484 16.0864 21.0736 17.3864C20.9018 18.6648 20.5749 19.4355 20.0052 20.0052C19.4355 20.5749 18.6648 20.9018 17.3864 21.0736C16.0864 21.2484 14.3782 21.25 12 21.25C9.62177 21.25 7.91356 21.2484 6.61358 21.0736C5.33517 20.9018 4.56445 20.5749 3.9948 20.0052C3.42514 19.4355 3.09825 18.6648 2.92637 17.3864C2.75159 16.0864 2.75 14.3782 2.75 12C2.75 9.62178 2.75159 7.91356 2.92637 6.61358C3.09825 5.33517 3.42514 4.56445 3.9948 3.9948Z" fill="#1C274C"></path> </g></svg>
                                    </svg>
                                </button>
                                </div>
                                <h2 class="absolute hidden sm:block md:block lg:block">Party Cake Information</h2>

                                <form>
                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="customer" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Customer</label>
                                    <input type="text" id="customer" v-model="cake.CUSTOMERNAME" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly>
                                    </div>  
                                    <div>
                                    <label for="address" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Address</label>
                                    <input type="text" id="address" v-model="cake.ADDRESS" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone number</label>
                                    <input type="tel" id="phone" v-model="cake.TELNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-6">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 

                                <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </form>
                            </div>
                        </div>
                    </div> -->



                    <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">

                            <template v-if="!partycakes || partycakes.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="!bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No orders</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                                <div class="card-body">
                                    <div class="card-actions justify-end">
                                        <!-- <button type="submit" class="btn btn-square btn-sm" id="UPDATE">
                                            <svg fill="#616161" viewBox="0 0 32 32" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" version="1.1" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:serif="http://www.serif.com/" xmlns:xlink="http://www.w3.org/1999/xlink"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Layer1"> <path d="M16,6l-13,0c-0.552,0 -1,0.448 -1,1l0,22c0,0.552 0.448,1 1,1l22,0c0.552,0 1,-0.448 1,-1l0,-13c0,-0.552 -0.448,-1 -1,-1c-0.552,-0 -1,0.448 -1,1l0,12c0,0 -20,0 -20,0c0,0 0,-20 0,-20c-0,0 12,0 12,0c0.552,0 1,-0.448 1,-1c0,-0.552 -0.448,-1 -1,-1Zm-9,19l14,-0c0.552,0 1,-0.448 1,-1c0,-0.552 -0.448,-1 -1,-1l-14,0c-0.552,0 -1,0.448 -1,1c0,0.552 0.448,1 1,1Zm-0,-4l4,0c0.552,-0 1,-0.448 1,-1c-0,-0.552 -0.448,-1 -1,-1l-4,0c-0.552,-0 -1,0.448 -1,1c-0,0.552 0.448,1 1,1Zm22.707,-13.293c0.391,-0.39 0.391,-1.024 0,-1.414l-4,-4c-0.39,-0.391 -1.024,-0.391 -1.414,-0l-10,10c-0.14,0.139 -0.235,0.317 -0.274,0.511l-1,5c-0.065,0.328 0.037,0.667 0.274,0.903c0.236,0.237 0.575,0.339 0.903,0.274l5,-1c0.194,-0.039 0.372,-0.134 0.511,-0.274l10,-10Zm-22.707,9.293l4,0c0.552,0 1,-0.448 1,-1c0,-0.552 -0.448,-1 -1,-1l-4,0c-0.552,0 -1,0.448 -1,1c0,0.552 0.448,1 1,1Zm0,-4l5,-0c0.552,0 1,-0.448 1,-1c0,-0.552 -0.448,-1 -1,-1l-5,-0c-0.552,0 -1,0.448 -1,1c0,0.552 0.448,1 1,1Z"></path> </g> </g></svg>
                                        </button> -->

                                        <button type="button" @click="posted(cake.id)" class="btn btn-square !bg-gray-100 btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.4" d="M3.75977 7.22005V16.7901C3.75977 18.7501 5.88975 19.98 7.58975 19L11.7397 16.61L15.8898 14.21C17.5898 13.23 17.5898 10.78 15.8898 9.80004L11.7397 7.40004L7.58975 5.01006C5.88975 4.03006 3.75977 5.25005 3.75977 7.22005Z" fill="#292D32"></path> <path d="M20.2402 18.9298C19.8302 18.9298 19.4902 18.5898 19.4902 18.1798V5.81982C19.4902 5.40982 19.8302 5.06982 20.2402 5.06982C20.6502 5.06982 20.9902 5.40982 20.9902 5.81982V18.1798C20.9902 18.5898 20.6602 18.9298 20.2402 18.9298Z" fill="#292D32"></path> </g></svg>
                                        </button>
                                    </div>
                                    <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                    <form @submit.prevent="updatePartyCake">
                                    <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                            <div>
                                            <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                            <input type="text" id="transactstore" v-model="cake.TRANSACTSTORE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Del. Address</label>
                                            <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                            </div>
                                            <div>
                                            <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                            <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>
                                

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>


                    <!-- <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                        <div v-for="cake in partycakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card bg-base-100 shadow-xl h-full">
                            <div class="card-body">
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form @submit.prevent="updateCake(cake)">
                                    <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                        <div>
                                        <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                        <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                        <div>
                                        <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                        <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                        <div>
                                        <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                        <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" />
                                        </div>
                                    </div>
                                
                                    <button type="submit" class="btn btn-primary">Update</button>
                                    <button type="button" @click="deleteCake(cake)" class="btn btn-error">Delete</button>

                                    <div class="card-actions justify-end mt-2">
                                        <button type="submit" class="btn btn-square btn-sm" id="UPDATE">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            
                                            </svg>
                                        </button>

                                        <button type="button" @click="deleteCake(cake)" class="btn btn-square btn-sm" id="DELETE">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            </svg>
                                        </button>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->


        </div>
                    <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        className="tab !bg-gray-100 !text-gray-500 !font-bold"
                        aria-label="CS"
                        />
                    <div role="tabpanel" className="tab-content !bg-gray-200 !border-gray-300 p-6 ">
                        PENDING


                        <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                            <template v-if="!partycakes1 || partycakes1.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="!bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No Pending</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes1" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <button type="button" @click="process(cake.id)" class="btn btn-square !bg-gray-100 btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.4" d="M3.75977 7.22005V16.7901C3.75977 18.7501 5.88975 19.98 7.58975 19L11.7397 16.61L15.8898 14.21C17.5898 13.23 17.5898 10.78 15.8898 9.80004L11.7397 7.40004L7.58975 5.01006C5.88975 4.03006 3.75977 5.25005 3.75977 7.22005Z" fill="#292D32"></path> <path d="M20.2402 18.9298C19.8302 18.9298 19.4902 18.5898 19.4902 18.1798V5.81982C19.4902 5.40982 19.8302 5.06982 20.2402 5.06982C20.6502 5.06982 20.9902 5.40982 20.9902 5.81982V18.1798C20.9902 18.5898 20.6602 18.9298 20.2402 18.9298Z" fill="#292D32"></path> </g></svg>
                                        </button>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3">
                                    <div v-if="cake.file_path" class="mt-2">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div>

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-4">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">STORE</label>
                                    <input type="text" id="transactstore" v-model="cake.TRANSACTSTORE" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Del. Address</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <input
                type="radio"
                name="my_tabs_2"
                role="tab"
                className="tab !bg-gray-100 !text-gray-500 !font-bold"
                aria-label="RECORDS"
            />
            <div role="tabpanel" className="tab-content !bg-gray-200 border-base-300 p-6">
                RECORDS

                <div class="container mx-auto px-4">
                    <!-- Search input -->
                    <div class="mb-4">
                        <input 
                            type="text" 
                            v-model="searchQuery" 
                            placeholder="Search Party Cakes..." 
                            class="w-full p-2 border rounded-lg"
                        >
                    </div>

                    <div class="flex flex-wrap -mx-4">
                        <template v-if="!filteredCakes || filteredCakes.length === 0">
                            <div class="col-span-full text-center mt-8">
                                <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                    <p class="text-gray-600 text-base sm:text-lg">No Records</p>
                                </div>
                            </div>
                        </template>
                        <div v-for="cake in filteredCakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card !bg-gray-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <!-- <button type="button" @click="dr(cake.id)" class="btn btn-square btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.4" d="M3.75977 7.22005V16.7901C3.75977 18.7501 5.88975 19.98 7.58975 19L11.7397 16.61L15.8898 14.21C17.5898 13.23 17.5898 10.78 15.8898 9.80004L11.7397 7.40004L7.58975 5.01006C5.88975 4.03006 3.75977 5.25005 3.75977 7.22005Z" fill="#292D32"></path> <path d="M20.2402 18.9298C19.8302 18.9298 19.4902 18.5898 19.4902 18.1798V5.81982C19.4902 5.40982 19.8302 5.06982 20.2402 5.06982C20.6502 5.06982 20.9902 5.40982 20.9902 5.81982V18.1798C20.9902 18.5898 20.6602 18.9298 20.2402 18.9298Z" fill="#292D32"></path> </g></svg>
                                        </button> -->

                                        <div v-if="cake.file_path" class="mt-2 absolute">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>

                                <!-- <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-1 lg:grid-cols-3">
                                    <div v-if="cake.file_path" class="mt-2">
                                        <a :href="getDownloadUrl(cake.id)" class="btn !btn-primary !text-white btn-xs sm:btn-sm">
                                            Download Attachment
                                        </a>
                                    </div>
                                </div> -->

                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-3">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <!-- <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div> -->
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>

                                <div class="grid gap-6 mb-6 mt-3 md:grid-cols-4">
                                        <div>
                                            <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">SRP</label>
                                            <input type="text" id="bdaycodeno" :value="Number(cake.SRP).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">DISCOUNT</label>
                                            <input type="text" id="flavor" :value="Number(cake.DISCOUNT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">PARTIAL PAYMENT</label>
                                            <input type="text" id="motif" :value="Number(cake.PARTIALPAYMENT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                        <div>
                                            <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">NETAMOUNT</label>
                                            <input type="text" id="icing" :value="Number(cake.NETAMOUNT).toFixed(2)" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                        </div>
                                    </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">BALANCE AMOUNT</label>
                                    <input type="text" id="dedication" :value="Number(cake.BALANCEAMOUNT).toFixed(2)" class="text-center text-white font-bold bg-red-500 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 
                                
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                    <!-- <input
                        type="radio"
                        name="my_tabs_2"
                        role="tab"
                        className="tab"
                        aria-label="DR PROCESS"
                        />
                    <div role="tabpanel" className="tab-content bg-base-200 border-base-300 p-6">
                        DR PROCESS


                        <div class="container mx-auto px-4">
                        <div class="flex flex-wrap -mx-4">
                            <template v-if="!partycakes3 || partycakes3.length === 0">
                                <div class="col-span-full text-center mt-8">
                                    <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                                        <p class="text-gray-600 text-base sm:text-lg">No DR Process</p>
                                    </div>
                                </div>
                            </template>
                        <div v-for="cake in partycakes3" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card bg-base-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">

                                    <button type="button" @click="dr(cake.id)" class="btn btn-square btn-sm" id="POST">
                                            <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path opacity="0.4" d="M3.75977 7.22005V16.7901C3.75977 18.7501 5.88975 19.98 7.58975 19L11.7397 16.61L15.8898 14.21C17.5898 13.23 17.5898 10.78 15.8898 9.80004L11.7397 7.40004L7.58975 5.01006C5.88975 4.03006 3.75977 5.25005 3.75977 7.22005Z" fill="#292D32"></path> <path d="M20.2402 18.9298C19.8302 18.9298 19.4902 18.5898 19.4902 18.1798V5.81982C19.4902 5.40982 19.8302 5.06982 20.2402 5.06982C20.6502 5.06982 20.9902 5.40982 20.9902 5.81982V18.1798C20.9902 18.5898 20.6602 18.9298 20.2402 18.9298Z" fill="#292D32"></path> </g></svg>
                                        </button>
                                </div>
                                <h2 class="text-xl font-semibold mb-4">Party Cake Information</h2>

                                <form>
                                <div class="grid gap-4 mb-4 sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3">
                                    <div>
                                    <label for="cosno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">COs No.</label>
                                    <input type="text" id="cosno" v-model="cake.COSNO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="branch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Branch</label>
                                    <input type="text" id="branch" v-model="cake.BRANCH" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="dateorder" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Order</label>
                                    <input type="text" id="dateorder" v-model="cake.DATEORDER" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="datepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date Picked Up</label>
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly />
                                </div> 

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" readonly></textarea>
                                </div>
                                </form>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> -->

                    </div>


                    
                

        </template>
    </Main>
</template>
