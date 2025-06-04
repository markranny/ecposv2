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
    form.patch("/Dispatch/getbwproducts", {
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
    <Modal title="GET BW PRODUCTS" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 h-full">
                    <div class="col-span-3">
                        <InputLabel for="INFO" value="Generate ID NO: " />
                        <TextInput
                            id="JOURNALID"
                            v-model="form.JOURNALID"
                            type="text"
                            class="mt-1 block w-full input"
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
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                GET ITEMS
            </PrimaryButton>
        </template>
    </Modal>
</template>