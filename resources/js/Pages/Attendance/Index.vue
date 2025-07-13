<template>
  <Head title="Attendance">
    <meta name="description" content="Attendance Management System" />
  </Head>

  <AdminPanel :active-tab="'ATTENDANCE'" :user="$page.props.auth.user">
    <template #main>
      <div class="container mx-auto px-3 sm:px-4 lg:px-8 py-4">
        
        <!-- Header Section -->
        <div class="mb-6">
          <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <div>
              <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Attendance Management</h1>
              <p class="mt-1 text-sm text-gray-600">Track staff attendance and view records</p>
            </div>
            
            <!-- Stats Cards (Mobile: stacked, Desktop: inline) -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
              <div class="bg-blue-50 rounded-lg px-3 py-2 text-center sm:text-left">
                <div class="text-lg sm:text-xl font-semibold text-blue-600">{{ attendances.length }}</div>
                <div class="text-xs text-blue-600">Total Records</div>
              </div>
              <div class="bg-green-50 rounded-lg px-3 py-2 text-center sm:text-left">
                <div class="text-lg sm:text-xl font-semibold text-green-600">
                  {{ attendances.filter(a => a.status === 'ACTIVE').length }}
                </div>
                <div class="text-xs text-green-600">Active Today</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="mb-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex flex-col sm:flex-row gap-4">
              
              <!-- Search Bar -->
              <div class="flex-1">
                <label for="search" class="sr-only">Search records</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                  </div>
                  <input
                    id="search"
                    type="text"
                    placeholder="Search by staff ID, store, or date..."
                    v-model="search"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 text-sm"
                  />
                  <!-- Clear search button -->
                  <button
                    v-if="search"
                    @click="search = ''"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                  >
                    <svg class="h-4 w-4 text-gray-400 hover:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>

              <!-- Filter Buttons (Mobile: full width, Desktop: auto) -->
              <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                <select 
                  v-model="statusFilter"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="">All Status</option>
                  <option value="ACTIVE">Active</option>
                  <option value="INACTIVE">Inactive</option>
                </select>
                
                <input
                  v-model="dateFilter"
                  type="date"
                  class="px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>

            <!-- Quick stats bar (mobile only) -->
            <div class="mt-4 sm:hidden border-t pt-4">
              <div class="flex justify-around text-center">
                <div>
                  <div class="text-sm font-medium text-gray-900">{{ filteredAttendances.length }}</div>
                  <div class="text-xs text-gray-500">Showing</div>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ filteredAttendances.filter(a => a.timeIn && !a.timeOut).length }}
                  </div>
                  <div class="text-xs text-gray-500">Checked In</div>
                </div>
                <div>
                  <div class="text-sm font-medium text-gray-900">
                    {{ filteredAttendances.filter(a => a.timeIn && a.timeOut).length }}
                  </div>
                  <div class="text-xs text-gray-500">Completed</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Results Info -->
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
          <div class="text-sm text-gray-700">
            <span class="font-medium">{{ filteredAttendances.length }}</span>
            <span class="hidden sm:inline"> records found</span>
            <span class="sm:hidden"> results</span>
            <span v-if="search || statusFilter || dateFilter" class="text-gray-500">
              (filtered)
            </span>
          </div>
          
          <!-- View toggle (Desktop only) -->
          <div class="hidden lg:flex items-center gap-2">
            <span class="text-sm text-gray-500">View:</span>
            <button
              @click="viewMode = 'table'"
              :class="[
                'px-3 py-1 text-xs rounded-md transition-colors',
                viewMode === 'table' 
                  ? 'bg-blue-100 text-blue-700' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Table
            </button>
            <button
              @click="viewMode = 'card'"
              :class="[
                'px-3 py-1 text-xs rounded-md transition-colors',
                viewMode === 'card' 
                  ? 'bg-blue-100 text-blue-700' 
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Cards
            </button>
          </div>
        </div>

        <!-- Attendance Records -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
          <AttendanceTable
            :attendances="filteredAttendances"
            @edit="openEditModal"
            @delete="handleDelete"
          />
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-40">
          <div class="bg-white rounded-lg p-6 shadow-xl">
            <div class="flex items-center gap-3">
              <svg class="animate-spin h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="text-sm text-gray-600">Loading...</span>
            </div>
          </div>
        </div>

        <!-- Modal Components -->
        <AttendanceForm
          v-if="showModal"
          :show="showModal"
          :form="form"
          :is-editing="isEditing"
          @close="closeModal"
          @submit="handleSubmit"
        />
      </div>
    </template>
  </AdminPanel>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AdminPanel from '@/Layouts/AdminPanel.vue';
import AttendanceForm from '@/Pages/Attendance/AttendanceForm.vue';
import AttendanceTable from '@/Pages/Attendance/AttendanceTable.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    attendances: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
        }),
    },
});

// Reactive state
const search = ref(props.filters.search || '');
const statusFilter = ref('');
const dateFilter = ref('');
const viewMode = ref('table');
const loading = ref(false);

// Modal state
const showModal = ref(false);
const isEditing = ref(false);
const selectedAttendance = ref(null);

// Form
const form = useForm({
    staffId: '',
    storeId: '',
    date: '',
    timeIn: '',
    timeInPhoto: null,
    breakIn: '',
    breakInPhoto: null,
    breakOut: '',
    breakOutPhoto: null,
    timeOut: '',
    timeOutPhoto: null,
    status: 'ACTIVE'
});

// Computed filtered attendances
const filteredAttendances = computed(() => {
    let filtered = props.attendances;

    // Filter by status
    if (statusFilter.value) {
        filtered = filtered.filter(attendance => 
            attendance.status === statusFilter.value
        );
    }

    // Filter by date
    if (dateFilter.value) {
        filtered = filtered.filter(attendance => 
            attendance.date === dateFilter.value
        );
    }

    // Client-side search filter (for immediate feedback)
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(attendance => 
            attendance.staffId.toLowerCase().includes(searchLower) ||
            attendance.storeId.toLowerCase().includes(searchLower) ||
            attendance.date.includes(searchLower)
        );
    }

    return filtered;
});

// Debounced search function
const debouncedSearch = debounce((value) => {
    loading.value = true;
    router.get(
        '/attendance',
        { 
            search: value,
            status: statusFilter.value,
            date: dateFilter.value
        },
        { 
            preserveState: true,
            preserveScroll: true,
            replace: true,
            onFinish: () => {
                loading.value = false;
            }
        }
    );
}, 300);

// Watch for changes in filters
watch([search, statusFilter, dateFilter], ([newSearch, newStatus, newDate]) => {
    // Only make server request for search, handle others client-side for better UX
    if (newSearch !== props.filters.search) {
        debouncedSearch(newSearch);
    }
});

// Modal functions
const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    showModal.value = true;
};

const openEditModal = (attendance) => {
    isEditing.value = true;
    selectedAttendance.value = attendance;
    form.staffId = attendance.staffId;
    form.storeId = attendance.storeId;
    form.date = attendance.date;
    form.timeIn = attendance.timeIn;
    form.breakIn = attendance.breakIn;
    form.breakOut = attendance.breakOut;
    form.timeOut = attendance.timeOut;
    form.status = attendance.status;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    form.reset();
    form.clearErrors();
};

const handleSubmit = () => {
    if (isEditing.value) {
        form.put(route('attendance.update', selectedAttendance.value.id), {
            onSuccess: () => closeModal()
        });
    } else {
        form.post(route('attendance.store'), {
            onSuccess: () => closeModal()
        });
    }
};

const handleDelete = (id) => {
    if (confirm('Are you sure you want to delete this record?')) {
        form.delete(route('attendance.destroy', id));
    }
};

</script>


<style scoped>
/* Custom scrollbar for mobile */
@media (max-width: 640px) {
  .overflow-x-auto::-webkit-scrollbar {
    height: 4px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
  }
  
  .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
  }
}

/* Smooth transitions */
.transition-colors {
  transition: color 0.2s ease-in-out, border-color 0.2s ease-in-out, background-color 0.2s ease-in-out;
}

/* Loading spinner animation */
@keyframes spin {
  to { transform: rotate(360deg); }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

/* Focus states for better accessibility */
input:focus,
select:focus,
button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Mobile-first responsive design helpers */
@media (max-width: 640px) {
  .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }
}

/* Card hover effects */
.bg-white:hover {
  transition: box-shadow 0.2s ease-in-out;
}

/* Status badge animations */
.bg-blue-50,
.bg-green-50 {
  transition: all 0.2s ease-in-out;
}

/* Search input enhancements */
.relative input {
  transition: all 0.2s ease-in-out;
}

.relative input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Mobile optimization for touch targets */
@media (max-width: 640px) {
  button,
  input,
  select {
    min-height: 44px; /* Minimum touch target size */
  }
  
  /* Ensure clickable areas are large enough */
  .cursor-pointer {
    min-height: 44px;
    display: flex;
    align-items: center;
  }
}

/* Loading overlay */
.fixed.inset-0 {
  backdrop-filter: blur(2px);
  -webkit-backdrop-filter: blur(2px);
}

/* Responsive text sizes */
@media (max-width: 640px) {
  h1 {
    font-size: 1.25rem; /* 20px */
    line-height: 1.75rem; /* 28px */
  }
}

/* Animation for filter changes */
.bg-white.shadow-sm {
  transition: all 0.3s ease-in-out;
}

/* Custom select styling for mobile */
@media (max-width: 640px) {
  select {
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
    background-position: right 8px center;
    background-repeat: no-repeat;
    background-size: 16px 16px;
    padding-right: 32px;
  }
}
</style>