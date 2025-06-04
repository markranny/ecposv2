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
    receivedordertrans: {
        type: Array,
        required: false,
    },
    JOURNALID: String,
    items: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    JOURNALID: '',
    itemname: '',
    qty: '',
    unitid: '',
});

const searchQuery = ref('')
const isOpen = ref(false)
const selectedItem = ref(null)
const isLoading = ref(false)

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

const submitForm = () => {
    console.log('Submitting form...');
    isLoading.value = true;
    form.patch("/StockCountingLine/getbwproducts", {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Form submission successful.');
            isLoading.value = false;
            toggleActive();
        },
        onError: (error) => {
            console.error('Form submission error:', error);
            isLoading.value = false;
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
    <Modal title="GET BW PRODUCTS" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-75 z-50">
                    <div class="spinner"></div>
                </div>
                <div class="grid grid-cols-3 gap-4 h-full">
                    <div class="col-span-3">
                        <InputLabel for="INFO" value="Generate ID NO:" />
                        <TextInput
                            id="JOURNALID"
                            v-model="form.JOURNALID"
                            type="text"
                            class="mt-1 block w-full input !bg-gray-100 !text-black"
                            :is-error="!!form.errors.JOURNALID"
                            autofocus
                            disabled
                        />
                        <InputError :message="form.errors.JOURNALID" class="mt-2" />
                    </div>  
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing || isLoading" :class="{ 'opacity-25': form.processing || isLoading }">
                {{ isLoading ? 'Loading...' : 'GET ITEMS' }}
            </PrimaryButton>
        </template>
    </Modal>
</template>

<style scoped>
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>