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

// Function to handle image loading errors
const handleImageError = (event, photoType, attendance) => {
  console.error(`${photoType} image failed to load for attendance ${attendance.id}:`, event.target.src);
  console.log('Raw photo data:', attendance[photoType]);
  event.target.style.display = 'none';
};

// Function to handle successful image loading
const handleImageLoad = (event, photoType, attendance) => {
  console.log(`${photoType} image loaded successfully for attendance ${attendance.id}:`, event.target.src);
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
            <div v-if="attendance.timeInPhoto" class="relative">
              <img 
                :src="attendance.timeInPhoto"
                class="h-16 w-16 object-cover rounded cursor-pointer border border-gray-200"
                @click="openImageModal(attendance.timeInPhoto)"
                @error="(e) => handleImageError(e, 'timeInPhoto', attendance)"
                @load="(e) => handleImageLoad(e, 'timeInPhoto', attendance)"
                alt="Time In Photo"
                loading="lazy"
              />
              <!-- Fallback text if image fails -->
              <span v-if="!attendance.timeInPhoto" class="text-gray-400 text-sm">No image</span>
            </div>
            <span v-else class="text-gray-400 text-sm">-</span>
          </td>
          <td class="px-4 py-2">{{ attendance.breakIn || '-' }}</td>
          <td class="px-4 py-2">
            <div v-if="attendance.breakInPhoto" class="relative">
              <img 
                :src="attendance.breakInPhoto"
                class="h-16 w-16 object-cover rounded cursor-pointer border border-gray-200"
                @click="openImageModal(attendance.breakInPhoto)"
                @error="(e) => handleImageError(e, 'breakInPhoto', attendance)"
                @load="(e) => handleImageLoad(e, 'breakInPhoto', attendance)"
                alt="Break In Photo"
                loading="lazy"
              />
            </div>
            <span v-else class="text-gray-400 text-sm">-</span>
          </td>
          <td class="px-4 py-2">{{ attendance.breakOut || '-' }}</td>
          <td class="px-4 py-2">
            <div v-if="attendance.breakOutPhoto" class="relative">
              <img 
                :src="attendance.breakOutPhoto"
                class="h-16 w-16 object-cover rounded cursor-pointer border border-gray-200"
                @click="openImageModal(attendance.breakOutPhoto)"
                @error="(e) => handleImageError(e, 'breakOutPhoto', attendance)"
                @load="(e) => handleImageLoad(e, 'breakOutPhoto', attendance)"
                alt="Break Out Photo"
                loading="lazy"
              />
            </div>
            <span v-else class="text-gray-400 text-sm">-</span>
          </td>
          <td class="px-4 py-2">{{ attendance.timeOut || '-' }}</td>
          <td class="px-4 py-2">
            <div v-if="attendance.timeOutPhoto" class="relative">
              <img 
                :src="attendance.timeOutPhoto"
                class="h-16 w-16 object-cover rounded cursor-pointer border border-gray-200"
                @click="openImageModal(attendance.timeOutPhoto)"
                @error="(e) => handleImageError(e, 'timeOutPhoto', attendance)"
                @load="(e) => handleImageLoad(e, 'timeOutPhoto', attendance)"
                alt="Time Out Photo"
                loading="lazy"
              />
            </div>
            <span v-else class="text-gray-400 text-sm">-</span>
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
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50"
         @click="closeImageModal">
      <div class="relative bg-white p-4 rounded-lg max-w-4xl max-h-[90vh] overflow-auto"
           @click.stop>
        <button 
          @click="closeImageModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl font-bold bg-white rounded-full w-8 h-8 flex items-center justify-center shadow-lg z-10"
        >
          ×
        </button>
        <img 
          :src="selectedImage" 
          class="max-w-full h-auto rounded" 
          alt="Full size image"
          @error="handleImageError"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Add loading state for images */
img {
  transition: opacity 0.3s ease;
}

img:not([src]) {
  opacity: 0;
}

/* Improved modal styling */
.fixed {
  backdrop-filter: blur(4px);
}
</style>