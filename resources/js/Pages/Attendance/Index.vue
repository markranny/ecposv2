<template>
  <Head title="Attendance">
    <meta name="description" content="Attendance Management System" />
  </Head>

  <AdminPanel :active-tab="'ATTENDANCE'" :user="$page.props.auth.user">
    <template #main>
      <div class="container mx-auto px-4 mt-10 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-col sm:flex-row justify-between items-center gap-4">
          <h1 class="text-2xl font-bold">Attendance Management</h1>
          <div class="flex gap-4 items-center w-full sm:w-auto">
            <!-- Search Bar -->
            <div class="flex-grow sm:flex-grow-0 sm:min-w-[300px]">
              <input
                type="text"
                placeholder="Search records..."
                v-model="search"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
              />
            </div>
            <!-- <button 
              @click="openCreateModal"
              class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 whitespace-nowrap"
            >
              Add New Record
            </button> -->
          </div>
        </div>

        <!-- Attendance Records List -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <AttendanceTable
              :attendances="attendances"
              @edit="openEditModal"
              @delete="handleDelete"
            />
          </div>
        </div>

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
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Head } from '@inertiajs/vue3';
import AdminPanel from '@/Layouts/Main.vue';
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

// Search functionality
const search = ref(props.filters.search);

// Debounced search function
const debouncedSearch = debounce((value) => {
    router.get(
        '/attendance',
        { search: value },
        { 
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    );
}, 300);

// Watch for changes in search input
watch(search, (value) => {
    debouncedSearch(value);
});

const showModal = ref(false);
const isEditing = ref(false);
const selectedAttendance = ref(null);

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
/* Add any component-specific styles here */
.modal-overlay {
  background-color: rgba(0, 0, 0, 0.5);
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .container {
    padding: 1rem;
  }
}
</style>