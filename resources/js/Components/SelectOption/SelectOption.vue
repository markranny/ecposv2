<script setup>
import { onMounted, ref } from 'vue';

defineProps({
  modelValue: [String, Number],
  isError: { type: Boolean, default: false }
});

defineEmits(['update:modelValue']);

const select = ref(null);

onMounted(() => {
  if (select.value) {
    if (select.value.hasAttribute('autofocus')) {
      select.value.focus();
    }
  }
});

defineExpose({ focus: () => select.value.focus() });
</script>

<template>
  <select
    ref="select"
    :class="[
      'select select-bordered w-full',
      isError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500'
    ]"
    :value="modelValue"
    @change="$emit('update:modelValue', $event.target.value)"
  >
    <slot></slot>
  </select>
</template>