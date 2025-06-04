<!-- AttendanceTable.vue -->
<script setup>
import { ref } from 'vue';

const props = defineProps({
  attendances: {
    type: Array,
    required: true
  }
});

const emit = defineEmits(['edit', 'delete']);
const selectedImage = ref(null);

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
};

const closeImageModal = () => {
  selectedImage.value = null;
};

const getStorageUrl = (path) => {
    return path || null;
};

</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full bg-white">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 text-left">Staff ID</th>
          <th class="px-4 py-2 text-left">Store ID</th>
          <th class="px-4 py-2 text-left">Date</th>
          <th class="px-4 py-2 text-left">Time In</th>
          <th class="px-4 py-2 text-left">Time In Photo</th>
          <th class="px-4 py-2 text-left">Break In</th>
          <th class="px-4 py-2 text-left">Break In Photo</th>
          <th class="px-4 py-2 text-left">Break Out</th>
          <th class="px-4 py-2 text-left">Break Out Photo</th>
          <th class="px-4 py-2 text-left">Time Out</th>
          <th class="px-4 py-2 text-left">Time Out Photo</th>
          <!-- <th class="px-4 py-2 text-left">Actions</th> -->
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        <tr v-for="attendance in attendances" :key="attendance.id">
          <td class="px-4 py-2">{{ attendance.staffId }}</td>
          <td class="px-4 py-2">{{ attendance.storeId }}</td>
          <td class="px-4 py-2">{{ attendance.date }}</td>
          <td class="px-4 py-2">{{ attendance.timeIn }}</td>
          <td class="px-4 py-2">
            <img 
              v-if="attendance.timeInPhoto" 
              :src="getStorageUrl(attendance.timeInPhoto)"
              class="h-16 w-16 object-cover rounded cursor-pointer"
              @click="openImageModal(getStorageUrl(attendance.timeInPhoto))"
              alt="Time In Photo"
            />
          </td>
          <td class="px-4 py-2">{{ attendance.breakIn }}</td>
          <td class="px-4 py-2">
            <img 
              v-if="attendance.breakInPhoto" 
              :src="getStorageUrl(attendance.breakInPhoto)"
              class="h-16 w-16 object-cover rounded cursor-pointer"
              @click="openImageModal(getStorageUrl(attendance.breakInPhoto))"
              alt="Break In Photo"
            />
          </td>
          <td class="px-4 py-2">{{ attendance.breakOut }}</td>
          <td class="px-4 py-2">
            <img 
              v-if="attendance.breakOutPhoto" 
              :src="getStorageUrl(attendance.breakOutPhoto)"
              class="h-16 w-16 object-cover rounded cursor-pointer"
              @click="openImageModal(getStorageUrl(attendance.breakOutPhoto))"
              alt="Break Out Photo"
            />
          </td>
          <td class="px-4 py-2">{{ attendance.timeOut }}</td>
          <td class="px-4 py-2">
            <img 
              v-if="attendance.timeOutPhoto" 
              :src="getStorageUrl(attendance.timeOutPhoto)"
              class="h-16 w-16 object-cover rounded cursor-pointer"
              @click="openImageModal(getStorageUrl(attendance.timeOutPhoto))"
              alt="Time Out Photo"
            />
          </td>
          <!-- <td class="px-4 py-2">
            <button
              @click="$emit('edit', attendance)"
              class="text-blue-600 hover:text-blue-800 mr-2"
            >
              Edit
            </button>
            <button
              @click="$emit('delete', attendance.id)"
              class="text-red-600 hover:text-red-800"
            >
              Delete
            </button>
          </td> -->
        </tr>
      </tbody>
    </table>

    <!-- Image Modal -->
    <div v-if="selectedImage" 
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
         @click="closeImageModal">
      <div class="relative bg-white p-2 rounded-lg max-w-4xl max-h-screen overflow-auto"
           @click.stop>
        <button 
          @click="closeImageModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl font-bold"
        >
          ×
        </button>
        <img 
          :src="selectedImage" 
          class="max-w-full h-auto" 
          alt="Full size image"
        />
      </div>
    </div>
  </div>
</template>