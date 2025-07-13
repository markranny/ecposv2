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
const failedImages = ref(new Set());
const expandedRecord = ref(null);

const openImageModal = (imageUrl) => {
  selectedImage.value = imageUrl;
};

const closeImageModal = () => {
  selectedImage.value = null;
};

const toggleExpanded = (attendanceId) => {
  expandedRecord.value = expandedRecord.value === attendanceId ? null : attendanceId;
};

// Function to handle image loading errors
const handleImageError = (event, photoType, attendance) => {
  console.error(`${photoType} image failed to load for attendance ${attendance.id}:`, event.target.src);
  
  // Add to failed images set
  failedImages.value.add(`${attendance.id}-${photoType}`);
  
  // Hide the image element
  event.target.style.display = 'none';
  
  // Show fallback text
  const parent = event.target.parentElement;
  if (parent && !parent.querySelector('.fallback-text')) {
    const fallback = document.createElement('span');
    fallback.className = 'fallback-text text-red-400 text-xs';
    fallback.textContent = 'Image not found';
    parent.appendChild(fallback);
  }
};

// Function to handle successful image loading
const handleImageLoad = (event, photoType, attendance) => {
  console.log(`${photoType} image loaded successfully for attendance ${attendance.id}:`, event.target.src);
  
  // Remove from failed images set if it was there
  failedImages.value.delete(`${attendance.id}-${photoType}`);
  
  // Remove any fallback text
  const parent = event.target.parentElement;
  const fallbackText = parent?.querySelector('.fallback-text');
  if (fallbackText) {
    fallbackText.remove();
  }
};

// Check if image failed to load
const hasImageFailed = (attendanceId, photoType) => {
  return failedImages.value.has(`${attendanceId}-${photoType}`);
};

// Format time display
const formatTime = (time) => {
  if (!time) return '-';
  return time.length > 5 ? time.substring(0, 5) : time;
};

// Get status badge color
const getStatusColor = (status) => {
  return status === 'ACTIVE' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800';
};

</script>

<template>
  <div class="w-full">
    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Store</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time In</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break In</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Break Out</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Out</th>
            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="attendance in attendances" :key="attendance.id" class="hover:bg-gray-50">
            <td class="px-3 py-2 text-sm">{{ attendance.staffId }}</td>
            <td class="px-3 py-2 text-sm">{{ attendance.storeId }}</td>
            <td class="px-3 py-2 text-sm">{{ attendance.date }}</td>
            <td class="px-3 py-2 text-sm">{{ formatTime(attendance.timeIn) }}</td>
            
            <!-- Time In Photo -->
            <td class="px-3 py-2">
              <div v-if="attendance.timeInPhoto" class="relative">
                <img 
                  :src="attendance.timeInPhoto"
                  class="h-12 w-12 object-cover rounded cursor-pointer border border-gray-200"
                  @click="openImageModal(attendance.timeInPhoto)"
                  @error="(e) => handleImageError(e, 'timeInPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'timeInPhoto', attendance)"
                  alt="Time In"
                  loading="lazy"
                />
              </div>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            
            <td class="px-3 py-2 text-sm">{{ formatTime(attendance.breakIn) }}</td>
            
            <!-- Break In Photo -->
            <td class="px-3 py-2">
              <div v-if="attendance.breakInPhoto" class="relative">
                <img 
                  :src="attendance.breakInPhoto"
                  class="h-12 w-12 object-cover rounded cursor-pointer border border-gray-200"
                  @click="openImageModal(attendance.breakInPhoto)"
                  @error="(e) => handleImageError(e, 'breakInPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'breakInPhoto', attendance)"
                  alt="Break In"
                  loading="lazy"
                />
              </div>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            
            <td class="px-3 py-2 text-sm">{{ formatTime(attendance.breakOut) }}</td>
            
            <!-- Break Out Photo -->
            <td class="px-3 py-2">
              <div v-if="attendance.breakOutPhoto" class="relative">
                <img 
                  :src="attendance.breakOutPhoto"
                  class="h-12 w-12 object-cover rounded cursor-pointer border border-gray-200"
                  @click="openImageModal(attendance.breakOutPhoto)"
                  @error="(e) => handleImageError(e, 'breakOutPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'breakOutPhoto', attendance)"
                  alt="Break Out"
                  loading="lazy"
                />
              </div>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
            
            <td class="px-3 py-2 text-sm">{{ formatTime(attendance.timeOut) }}</td>
            
            <!-- Time Out Photo -->
            <td class="px-3 py-2">
              <div v-if="attendance.timeOutPhoto" class="relative">
                <img 
                  :src="attendance.timeOutPhoto"
                  class="h-12 w-12 object-cover rounded cursor-pointer border border-gray-200"
                  @click="openImageModal(attendance.timeOutPhoto)"
                  @error="(e) => handleImageError(e, 'timeOutPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'timeOutPhoto', attendance)"
                  alt="Time Out"
                  loading="lazy"
                />
              </div>
              <span v-else class="text-gray-400 text-xs">-</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden space-y-4">
      <div v-for="attendance in attendances" :key="attendance.id" 
           class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
        
        <!-- Card Header (Always Visible) -->
        <div class="p-4 border-b border-gray-200 cursor-pointer" 
             @click="toggleExpanded(attendance.id)">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2 mb-1">
                <h3 class="text-sm font-medium text-gray-900 truncate">
                  {{ attendance.staffId }}
                </h3>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusColor(attendance.status)">
                  {{ attendance.status }}
                </span>
              </div>
              <div class="text-xs text-gray-500 space-y-1">
                <div class="flex items-center gap-4">
                  <span>🏪 {{ attendance.storeId }}</span>
                  <span>📅 {{ attendance.date }}</span>
                </div>
                <div class="flex items-center gap-4">
                  <span>🕐 In: {{ formatTime(attendance.timeIn) }}</span>
                  <span v-if="attendance.timeOut">🕕 Out: {{ formatTime(attendance.timeOut) }}</span>
                </div>
              </div>
            </div>
            <div class="ml-2 flex-shrink-0">
              <svg class="h-5 w-5 text-gray-400 transition-transform duration-200"
                   :class="{ 'rotate-180': expandedRecord === attendance.id }"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Expanded Details -->
        <div v-show="expandedRecord === attendance.id" class="p-4 bg-gray-50">
          
          <!-- Time Entries Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            
            <!-- Time In -->
            <div class="bg-white rounded-lg p-3 border">
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-900">Time In</h4>
                <span class="text-sm text-gray-600">{{ formatTime(attendance.timeIn) }}</span>
              </div>
              <div v-if="attendance.timeInPhoto" class="flex justify-center">
                <img 
                  :src="attendance.timeInPhoto"
                  class="h-20 w-20 object-cover rounded-lg cursor-pointer border-2 border-gray-200 hover:border-blue-300 transition-colors"
                  @click="openImageModal(attendance.timeInPhoto)"
                  @error="(e) => handleImageError(e, 'timeInPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'timeInPhoto', attendance)"
                  alt="Time In Photo"
                  loading="lazy"
                />
              </div>
              <div v-else class="flex justify-center">
                <div class="h-20 w-20 bg-gray-100 rounded-lg flex items-center justify-center">
                  <span class="text-gray-400 text-xs">No Photo</span>
                </div>
              </div>
            </div>

            <!-- Time Out -->
            <div class="bg-white rounded-lg p-3 border">
              <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-900">Time Out</h4>
                <span class="text-sm text-gray-600">{{ formatTime(attendance.timeOut) }}</span>
              </div>
              <div v-if="attendance.timeOutPhoto" class="flex justify-center">
                <img 
                  :src="attendance.timeOutPhoto"
                  class="h-20 w-20 object-cover rounded-lg cursor-pointer border-2 border-gray-200 hover:border-blue-300 transition-colors"
                  @click="openImageModal(attendance.timeOutPhoto)"
                  @error="(e) => handleImageError(e, 'timeOutPhoto', attendance)"
                  @load="(e) => handleImageLoad(e, 'timeOutPhoto', attendance)"
                  alt="Time Out Photo"
                  loading="lazy"
                />
              </div>
              <div v-else class="flex justify-center">
                <div class="h-20 w-20 bg-gray-100 rounded-lg flex items-center justify-center">
                  <span class="text-gray-400 text-xs">No Photo</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Break Times (if any) -->
          <div v-if="attendance.breakIn || attendance.breakOut" 
               class="border-t pt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Break Times</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              
              <!-- Break In -->
              <div v-if="attendance.breakIn" class="bg-white rounded-lg p-3 border">
                <div class="flex items-center justify-between mb-2">
                  <h5 class="text-sm font-medium text-gray-700">Break In</h5>
                  <span class="text-sm text-gray-600">{{ formatTime(attendance.breakIn) }}</span>
                </div>
                <div v-if="attendance.breakInPhoto" class="flex justify-center">
                  <img 
                    :src="attendance.breakInPhoto"
                    class="h-16 w-16 object-cover rounded-lg cursor-pointer border-2 border-gray-200 hover:border-blue-300 transition-colors"
                    @click="openImageModal(attendance.breakInPhoto)"
                    @error="(e) => handleImageError(e, 'breakInPhoto', attendance)"
                    @load="(e) => handleImageLoad(e, 'breakInPhoto', attendance)"
                    alt="Break In Photo"
                    loading="lazy"
                  />
                </div>
                <div v-else class="flex justify-center">
                  <div class="h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No Photo</span>
                  </div>
                </div>
              </div>

              <!-- Break Out -->
              <div v-if="attendance.breakOut" class="bg-white rounded-lg p-3 border">
                <div class="flex items-center justify-between mb-2">
                  <h5 class="text-sm font-medium text-gray-700">Break Out</h5>
                  <span class="text-sm text-gray-600">{{ formatTime(attendance.breakOut) }}</span>
                </div>
                <div v-if="attendance.breakOutPhoto" class="flex justify-center">
                  <img 
                    :src="attendance.breakOutPhoto"
                    class="h-16 w-16 object-cover rounded-lg cursor-pointer border-2 border-gray-200 hover:border-blue-300 transition-colors"
                    @click="openImageModal(attendance.breakOutPhoto)"
                    @error="(e) => handleImageError(e, 'breakOutPhoto', attendance)"
                    @load="(e) => handleImageLoad(e, 'breakOutPhoto', attendance)"
                    alt="Break Out Photo"
                    loading="lazy"
                  />
                </div>
                <div v-else class="flex justify-center">
                  <div class="h-16 w-16 bg-gray-100 rounded-lg flex items-center justify-center">
                    <span class="text-gray-400 text-xs">No Photo</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- No records message -->
    <div v-if="attendances.length === 0" class="text-center py-12 text-gray-500">
      <div class="text-gray-400 text-6xl mb-4">📋</div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No attendance records found</h3>
      <p class="text-gray-500">Records will appear here once staff check in.</p>
    </div>

    <!-- Image Modal -->
    <div v-if="selectedImage" 
         class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
         @click="closeImageModal">
      <div class="relative bg-white rounded-lg max-w-full max-h-full overflow-auto"
           @click.stop>
        <button 
          @click="closeImageModal"
          class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-2xl font-bold bg-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg z-10 hover:bg-gray-100 transition-colors"
        >
          ×
        </button>
        <div class="p-4">
          <img 
            :src="selectedImage" 
            class="max-w-full max-h-[calc(100vh-8rem)] h-auto rounded-lg" 
            alt="Full size image"
            @error="() => console.error('Modal image failed to load:', selectedImage)"
          />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Smooth transitions */
.transition-transform {
  transition: transform 0.2s ease-in-out;
}

.transition-colors {
  transition: color 0.2s ease-in-out, border-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

/* Mobile optimizations */
@media (max-width: 640px) {
  .grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
  }
}

/* Image hover effects */
img:hover {
  transform: scale(1.02);
  transition: transform 0.2s ease-in-out;
}

/* Modal backdrop blur */
.fixed {
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
}

/* Card shadow on hover */
.bg-white.rounded-lg.shadow:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  transition: box-shadow 0.2s ease-in-out;
}

/* Fallback text styling */
.fallback-text {
  display: block;
  margin-top: 4px;
  font-size: 0.75rem;
}

/* Loading skeleton animation */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>