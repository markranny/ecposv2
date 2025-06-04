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
    form.post("/ReceivedItems", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            location.reload();
        },
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
    <Modal title="RECEIVED ORDER ENTRIES" @toggle-active="toggleActive" :show-modal="showModal">
        <template #content>
            <form @submit.prevent="submitForm" class="px-2 py-3 max-h-[50vh] lg:max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-3 gap-4 h-full">
                    <div class="col-span-3">
                        <InputLabel for="JOURNALID" value="JOURNALID" />
                        <TextInput
                            id="JOURNALID"
                            v-model="form.JOURNALID"
                            type="text"
                            class="mt-1 block w-full input !bg-white !text-black"
                            :is-error="!!form.errors.JOURNALID"
                            autofocus
                            disabled
                        />
                        <InputError :message="form.errors.JOURNALID" class="mt-2" />
                    </div>  

                    <div class="col-span-3">
                        <InputLabel for="itemname" value="ITEMNAME" />
                        <!-- <div class="relative w-full"> -->
                            <input
                                type="text"
                                v-model="searchQuery"
                                @focus="isOpen = true"
                                @blur="closeDropdown"
                                placeholder="Search items..."
                                class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                            <div v-if="isOpen" class="absolute w-full mt-1 !bg-white border rounded-md shadow-lg max-h-60 overflow-auto z-10">
                                <div
                                    v-for="item in filteredItems"
                                    :key="item.itemid"
                                    @mousedown="selectItem(item)"
                                    class="px-4 py-2 cursor-pointer hover:bg-gray-100"
                                >
                                    {{ item.itemname }}
                                </div>
                            </div>
                        <!-- </div> -->
                        <InputError :message="form.errors.itemname" class="mt-2" />
                    </div>

                    <div class="col-span-3">
                        <InputLabel for="qty" value="QTY" />
                        <TextInput
                            id="qty"
                            v-model="form.qty"
                            type="number"
                            class="mt-1 block w-full"
                            required
                            autocomplete="qty"
                        />
                        <InputError :message="form.errors.qty" class="mt-2" />
                    </div>
                    <!-- </br></br></br></br></br></br></br></br></br> -->
                </div>
            </form>
        </template>
        <template #buttons>
            <PrimaryButton type="submit" @click="submitForm" :disabled="form.processing" :class="{ 'opacity-25': form.processing }">
                SUBMIT
            </PrimaryButton>
        </template>
    </Modal>
</template>

<style scoped>
/* Scoped styles here */
</style>