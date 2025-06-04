<script setup>
import { useForm } from '@inertiajs/vue3';
import { onMounted, watch, defineProps, defineEmits, ref, computed } from 'vue';
import Modal from "@/Components/Modals/Modal.vue";
import PrimaryButton from '@/Components/Buttons/PrimaryButton.vue';
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import InputError from '@/Components/Inputs/InputError.vue';

const emit = defineEmits(['toggleActive']); 

const props = defineProps({
    showModal: Boolean,
    inventjournaltransrepos: {
        type: Array,
        required: false,
    },
    JOURNALID: String,
    items: {
        type: Array,
        required: true,
    },
    rbostoretables: {
        type: Array,
        required: true,
    },
});

const selectStore = (selectStore) => {
    form.storeid = selectStore;
};

const form = useForm({
    JOURNALID: '',
    itemname: '',
    qty: '',
    unitid: '',
    storeid: '',
    EndDate: '',
});

const searchQuery = ref('')
const isOpen = ref(false)
const selectedItem = ref(null)

const filteredItems = computed(() => {
  return props.items.filter(item =>
    item.itemname.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

function selectItem(item) {
  selectedItem.value = item
  searchQuery.value = item.itemname
  form.itemname = item.itemid
  isOpen.value = false
}

function closeDropdown() {
  setTimeout(() => {
    isOpen.value = false
  }, 100)
}

/* const submitForm = () => {
    form.patch("/ItemOrders/getbwproducts", {
        preserveScroll: true,
        onSuccess: () => {
            toggleActive();
        },
    });
}; */

const submitForm = () => {
    console.log('Submitting form...');
    form.patch("/dispatch/generate", {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Form submission successful.');
            toggleActive();
        },
        onError: (error) => {
            console.error('Form submission error:', error);
        }
    });
};

const toggleActive = () => {
    emit('toggleActive');
};

onMounted(() => {
    form.JOURNALID = props.JOURNALID;
    
    watch(() => props.JOURNALID, (newValue) => {
        form.JOURNALID = newValue;
    });
});
</script>

<template>
    <Modal title="ADD DETAILS" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 h-full">
                    <div class="col-span-3">
                        <!-- <InputLabel for="INFO" value="Generate ID NO:" /> -->
                        <TextInput
                            id="JOURNALID"
                            v-model="form.JOURNALID"
                            type="text"
                            class="mt-1 block w-full input"
                            :is-error="!!form.errors.JOURNALID"
                            autofocus
                            disabled
                            v-show="false"
                        />
                        <InputError :message="form.errors.JOURNALID" class="mt-2" />
                    </div>  

                    <div class="col-span-3">
                        <InputLabel for="StoreName" value="SELECT ROUTE" />
                        <select id="storeid" v-model="form.storeid" class="select select-bordered w-full !bg-gray-100" @change="selectStore(form.storeid)">
                            <option v-for="store in rbostoretables" :key="store.storeid" :value="store.ROUTES">
                                {{ store.ROUTES }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.storeid" />
                    </div>

                    <InputLabel for="DATE" value="DELIVERY DATE" />

                    <div class="col-span-3">
                        <div class="relative">
                            <div class="flex absolute inset-y-0 left-0 items-center pl-3 pointer-events-none">
                                <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"></path></svg>
                            </div>

                            <input
                            id="EndDate"
                            type="date"
                            v-model="form.EndDate"
                            @input="formattedDate2"
                            :placeholder="formattedDate2"
                            class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5  dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500" placeholder="Select date end"
                            required
                            />
                            <InputError :message="form.errors.EndDate" class="mt-2" />
                        </div>
                    </div> 
                    
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                GENERATE
            </PrimaryButton>
        </template>
    </Modal>
</template>