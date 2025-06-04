# resources/js/Pages/Items/Links/Index.vue

<template>
    <Head :title="'Links - ' + mainItem.itemname" />
    
    <Main active-tab="RETAILITEMS">
        <template v-slot:modals>
            <CreateLink
                v-if="showCreateModal"
                :show-modal="showCreateModal"
                :main-item="mainItem"
                :available-items="availableItems"
                @close="toggleCreateModal"
                @created="onLinkCreated"
            />

            <UpdateLink
                v-if="showUpdateModal && selectedLink"
                :show-modal="showUpdateModal"
                :link="selectedLink"
                @close="toggleUpdateModal"
                @updated="onLinkUpdated"
            />
        </template>

        <template v-slot:main>
            <TableContainer>
                <!-- Flash Messages -->
                <div v-if="flash.success" class="mb-4 px-4 py-2 bg-green-100 text-green-700 rounded-md">
                    {{ flash.success }}
                </div>
                <div v-if="flash.error" class="mb-4 px-4 py-2 bg-red-100 text-red-700 rounded-md">
                    {{ flash.error }}
                </div>

                <!-- Header Section -->
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">
                                Item Links
                            </h2>
                            <p class="mt-1 text-sm text-gray-600">
                                Managing links for: {{ mainItem.itemname }} 
                                <span class="text-gray-400">({{ mainItem.itemid }})</span>
                            </p>
                        </div>
                        
                        <Link 
                            :href="route('items.index')" 
                            class="text-blue-600 hover:text-blue-800 text-sm mr-4"
                        >
                            Back to Items
                        </Link>
                    </div>

                    <div class="mt-4 flex justify-between items-center">
                        <div class="flex space-x-2">
                            <PrimaryButton
                                @click="toggleCreateModal"
                                class="bg-blue-600 hover:bg-blue-700 transition-colors"
                            >
                                Add New Link
                            </PrimaryButton>
                        </div>

                        <!-- Stats Overview -->
                        <div class="flex space-x-4 text-sm">
                            <div class="px-4 py-2 bg-gray-50 rounded-md">
                                <span class="text-gray-600">Total Links:</span>
                                <span class="ml-1 font-medium">{{ linkedItems.length }}</span>
                            </div>
                            <div class="px-4 py-2 bg-gray-50 rounded-md">
                                <span class="text-gray-600">Active:</span>
                                <span class="ml-1 font-medium">{{ activeLinksCount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Links Table -->
                    <div class="mt-6 bg-white rounded-lg shadow">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Linked Item
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Link Type
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Last Updated
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-if="linkedItems.length === 0">
                                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">
                                            No linked items found. Click "Add New Link" to create one.
                                        </td>
                                    </tr>

                                    <tr v-for="link in linkedItems" 
                                        :key="link.id" 
                                        class="hover:bg-gray-50 transition-colors"
                                    >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ getRelatedItemName(link) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            ID: {{ link.is_parent ? link.child_item?.itemid : link.parent_item?.itemid }}
                                        </div>
                                    </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                :class="{
                                                    'bg-blue-100 text-blue-800': link.link_type === 'bundle',
                                                    'bg-green-100 text-green-800': link.link_type === 'recipe'
                                                }"
                                            >
                                                {{ formatLinkType(link.link_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatQuantity(link.quantity) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full"
                                                :class="{
                                                    'bg-green-100 text-green-800': link.active,
                                                    'bg-red-100 text-red-800': !link.active
                                                }"
                                            >
                                                {{ link.active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(link.updated_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button
                                                @click="toggleUpdateModal(link)"
                                                class="text-blue-600 hover:text-blue-900 transition-colors"
                                            >
                                                Edit
                                            </button>
                                            <Link
                                                :href="route('item-links.destroy', link.id)"
                                                method="delete"
                                                as="button"
                                                class="text-red-600 hover:text-red-900 transition-colors"
                                                @click.prevent="confirmDelete(link)"
                                            >
                                                Delete
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </TableContainer>
        </template>
    </Main>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import TableContainer from "@/Components/Tables/TableContainer.vue";
import CreateLink from "@/Components/Items/Links/Create.vue";
import UpdateLink from "@/Components/Items/Links/Update.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";

const props = defineProps({
    mainItem: {
        type: Object,
        required: true,
        validator: (value) => {
            return value && 
                   typeof value === 'object' && 
                   'itemid' in value && 
                   'itemname' in value;
        }
    },
    linkedItems: {
        type: Array,
        required: true,
        default: () => []
    },
    availableItems: {
        type: Array,
        required: true,
        default: () => []
    }
});

const { flash } = usePage().props;
const showCreateModal = ref(false);
const showUpdateModal = ref(false);
const selectedLink = ref(null);

// Computed
const activeLinksCount = computed(() => {
    return props.linkedItems.filter(link => link.active).length;
});

// Methods
const toggleCreateModal = () => {
    showCreateModal.value = !showCreateModal.value;
};

const toggleUpdateModal = (link = null) => {
    selectedLink.value = link;
    showUpdateModal.value = !showUpdateModal.value;
};

const getRelatedItemName = (link) => {
    const relatedItem = link.is_parent ? link.child_item : link.parent_item;
    return relatedItem?.itemname || 'Unknown Item';
};

/* const formatQuantity = (quantity) => {
    return Number(quantity).toFixed(2);
}; */

const formatQuantity = (quantity) => {
    return Math.floor(Number(quantity));
};


const formatLinkType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1);
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const confirmDelete = (link) => {
    if (!confirm(`Are you sure you want to delete this link?`)) return;
    
    router.delete(route('item-links.destroy', link.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handling is automatic through flash messages
        }
    });
};

const onLinkCreated = () => {
    showCreateModal.value = false;
    router.reload({ only: ['linkedItems', 'availableItems'] });
};

const onLinkUpdated = () => {
    showUpdateModal.value = false;
    router.reload({ only: ['linkedItems'] });
};
</script>