<script setup>
import { useRouter } from 'vue-router';
import Create from "@/Components/Partycakes/Create.vue";
import Update from "@/Components/Partycakes/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Main from "@/Layouts/AdminPanel.vue";
import Excel from "@/Components/Exports/Excel.vue";
import Add from "@/Components/Svgs/Add.vue";    
import editblue from "@/Components/Svgs/editblue.vue";
import moreblue from "@/Components/Svgs/moreblue.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import { ref, onMounted } from "vue";
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
DataTable.use(DataTablesCore);

const id = ref('');
const subject = ref('');
const description = ref('');

const showModalUpdate = ref(false);
const showCreateModal = ref(false);

const props = defineProps({
    partycakes: {
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

const router = useRouter();

const fetchPartyCakes = async () => {
  try {
    const response = await axios.get('/api/partycakes');
    partycakes.value = response.data;
  } catch (error) {
    console.error('Error fetching party cakes:', error);
  }
};

const updateCake = async (cake) => {
  try {
    await axios.put(`/api/partycakes/${cake.id}`, cake);
    await fetchPartyCakes();
    alert('Cake updated successfully');
  } catch (error) {
    console.error('Error updating cake:', error);
    alert('Failed to update cake');
  }
};

const postCake = async (cake) => {
  try {
    await axios.post('/api/partycakes', cake);
    await fetchPartyCakes();
    alert('Cake posted successfully');
  } catch (error) {
    console.error('Error posting cake:', error);
    alert('Failed to post cake');
  }
};

const deleteCake = async (cake) => {
  if (confirm('Are you sure you want to delete this cake?')) {
    try {
      await axios.delete(`/api/partycakes/${cake.id}`);
      partycakes.value = partycakes.value.filter(c => c.id !== cake.id);
      alert('Cake deleted successfully');
    } catch (error) {
      console.error('Error deleting cake:', error);
      alert('Failed to delete cake');
    }
  }
};

onMounted(fetchPartyCakes);
</script>

<template>
    <Main active-tab="PARTYCAKES">
        <template v-slot:modals>
            <Create :show-modal="showCreateModal" @toggle-active="createModalHandler"  />
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
                    <input type="radio" name="my_tabs_2" role="tab" className="tab bg-base-200 border-base-300" aria-label="COS" defaultChecked />
                    <div role="tabpanel" className="tab-content bg-base-200 border-base-300 p-6">
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
                        <div v-for="cake in partycakes" :key="cake.id" class="w-full md:w-1/2 px-4 mb-8">
                            <div class="card bg-base-100 shadow-xl h-full">
                            <div class="card-body">
                                <div class="card-actions justify-end">
                                    <button type="button" @click="updateCake(cake)" class="btn btn-square btn-sm" id="UPDATE">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        </svg>
                                    </button>

                                    <button type="button" @click="postCake(cake)" class="btn btn-square btn-sm" id="POST">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        </svg>
                                    </button>
                                    <!-- <button type="button" @click="deleteCake(cake)" class="btn btn-square btn-sm" id="DELETE">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        </svg>
                                    </button> -->
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
                                    <input type="text" id="datepickedup" v-model="cake.DATEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="timepickedup" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timepickedup" v-model="cake.TIMEPICKEDUP" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="delivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Delivered</label>
                                    <input type="text" id="delivered" v-model="cake.DELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="timedelivered" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Time</label>
                                    <input type="text" id="timedelivered" v-model="cake.TIMEDELIVERED" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                </div>

                                <div class="grid gap-6 mb-6 md:grid-cols-4">
                                    <div>
                                    <label for="bdaycodeno" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">B-day Code No.</label>
                                    <input type="text" id="bdaycodeno" v-model="cake.BDAYCODENO" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="flavor" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Flavor</label>
                                    <input type="text" id="flavor" v-model="cake.FLAVOR" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="motif" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Motif</label>
                                    <input type="text" id="motif" v-model="cake.MOTIF" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                    <div>
                                    <label for="icing" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Icing</label>
                                    <input type="text" id="icing" v-model="cake.ICING" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="dedication" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dedication</label>
                                    <input type="text" id="dedication" v-model="cake.DEDICATION" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required />
                                </div> 

                                <div>
                                    <label for="others" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Others</label>
                                    <textarea id="others" v-model="cake.OTHERS" rows="4" class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" required></textarea>
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
                        className="tab"
                        aria-label="CS"
                        />
                    <div role="tabpanel" className="tab-content bg-base-200 border-base-300 p-6">
                        CAKELAB STATION
                    </div>

                    <input type="radio" name="my_tabs_2" role="tab" className="tab" aria-label="FD" />
                    <div role="tabpanel" className="tab-content bg-base-100 border-base-200 p-6">
                        FOR DELIVERY
                    </div>

                    <input type="radio" name="my_tabs_2" role="tab" className="tab" aria-label="RS" />
                    <div role="tabpanel" className="tab-content bg-base-100 border-base-200 p-6">
                        RECEIVED STORE
                    </div>

                    </div>

                    
                

        </template>
    </Main>
</template>
