<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    show: Boolean,
    form: Object,
    isEditing: Boolean
});

const emit = defineEmits(['close', 'submit']);

const handleSubmit = () => {
    emit('submit');
};
</script>

<template>
    <Modal :show="show" @close="$emit('close')">
        <div class="p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">
                {{ isEditing ? 'Edit Attendance Record' : 'Create New Attendance Record' }}
            </h3>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Staff ID</label>
                    <input
                        v-model="form.staffId"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.staffId" class="text-red-500 text-sm mt-1">
                        {{ form.errors.staffId }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Store ID</label>
                    <input
                        v-model="form.storeId"
                        type="text"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.storeId" class="text-red-500 text-sm mt-1">
                        {{ form.errors.storeId }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Date</label>
                    <input
                        v-model="form.date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.date" class="text-red-500 text-sm mt-1">
                        {{ form.errors.date }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Time In</label>
                    <input
                        v-model="form.timeIn"
                        type="time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.timeIn" class="text-red-500 text-sm mt-1">
                        {{ form.errors.timeIn }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Time Out</label>
                    <input
                        v-model="form.timeOut"
                        type="time"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    />
                    <div v-if="form.errors.timeOut" class="text-red-500 text-sm mt-1">
                        {{ form.errors.timeOut }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    >
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                    <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">
                        {{ form.errors.status }}
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update' : 'Create') }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template>