<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import Import from "@/Components/Svgs/Import.vue";

const fileInput = ref(null);
const isLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const form = useForm({
  file: null
});

const handleFileChange = (event) => {
  const file = event.target.files[0];
  form.file = file;
  
  // Reset messages
  errorMessage.value = '';
  successMessage.value = '';
};

const submitForm = () => {
  if (!form.file) {
    errorMessage.value = 'Please select a file to import';
    return;
  }
  
  isLoading.value = true;
  
  form.post(route('products.import'), {
    preserveScroll: true,
    onSuccess: (response) => {
      isLoading.value = false;
      successMessage.value = response?.props?.flash?.message || 'Import successful';
      form.reset();
      if (fileInput.value) {
        fileInput.value.value = '';
      }
    },
    onError: (errors) => {
      isLoading.value = false;
      errorMessage.value = errors.file || 'An error occurred during import';
    }
  });
};
</script>

<template>
  <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4">
    <div class="w-full md:w-auto">
      <input
        type="file"
        ref="fileInput"
        class="file-input file-input-bordered file-input-primary w-full max-w-xs"
        @input="handleFileChange"
        accept=".xlsx,.xls,.csv"
      />
      <p class="text-xs text-gray-500 mt-1">
        Supported formats: .xlsx, .xls, .csv
      </p>
    </div>
    
    <PrimaryButton 
      @click="submitForm" 
      class="bg-navy" 
      :disabled="isLoading || !form.file"
    >
      <Import v-if="!isLoading" class="h-4 mr-2" />
      <span v-if="isLoading" class="spinner-border spinner-border-sm mr-2" role="status"></span>
      {{ isLoading ? 'Importing...' : 'Import Products' }}
    </PrimaryButton>
  </div>
  
  <div v-if="errorMessage" class="mt-4 p-3 bg-red-100 text-red-800 rounded">
    {{ errorMessage }}
  </div>
  
  <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-800 rounded">
    {{ successMessage }}
  </div>
</template>