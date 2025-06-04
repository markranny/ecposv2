<template>
    <div class="relative" ref="dropdownRef">
      <label class="block text-sm font-medium text-gray-700 mb-1">{{ label }}</label>
      <button
        @click="isOpen = !isOpen"
        type="button"
        class="relative w-full bg-white border border-gray-300 rounded-md shadow-sm pl-3 pr-10 py-2 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
      >
        <span class="block truncate">{{ displayValue }}</span>
        <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
          <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </span>
      </button>
  
      <div v-if="isOpen" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
        <div class="border-b border-gray-200">
          <div
            class="px-3 py-2 cursor-pointer hover:bg-gray-100 flex items-center"
            @click="toggleAll"
          >
            <input
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              :checked="selectedCount === options.length"
              :indeterminate="selectedCount > 0 && selectedCount < options.length"
            >
            <span class="ml-2 font-medium">Select All</span>
          </div>
        </div>
        
        <div v-for="option in options" :key="option.STOREID" 
          class="px-3 py-2 cursor-pointer hover:bg-gray-100 flex items-center"
          @click="toggleOption(option.NAME)"
        >
          <input
            type="checkbox"
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            :checked="modelValue.includes(option.NAME)"
          >
          <span class="ml-2">{{ option.NAME }}</span>
        </div>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue';
  
  const props = defineProps({
    options: {
      type: Array,
      required: true
    },
    modelValue: {
      type: Array,
      required: true
    },
    label: {
      type: String,
      default: 'Select Options'
    }
  });
  
  const emit = defineEmits(['update:modelValue']);
  
  const isOpen = ref(false);
  const dropdownRef = ref(null);
  
  const selectedCount = computed(() => props.modelValue.length);
  
  const displayValue = computed(() => {
    if (selectedCount.value === 0) return 'Select stores...';
    if (selectedCount.value === 1) return `1 store selected`;
    return `${selectedCount.value} stores selected`;
  });
  
  const handleClickOutside = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
      isOpen.value = false;
    }
  };
  
  const toggleOption = (value) => {
    const selected = [...props.modelValue];
    const index = selected.indexOf(value);
    
    if (index === -1) {
      selected.push(value);
    } else {
      selected.splice(index, 1);
    }
    
    emit('update:modelValue', selected);
  };
  
  const toggleAll = () => {
    if (props.modelValue.length === props.options.length) {
      emit('update:modelValue', []);
    } else {
      emit('update:modelValue', props.options.map(option => option.NAME));
    }
  };
  
  onMounted(() => {
    document.addEventListener('click', handleClickOutside);
  });
  
  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
  });
  </script>