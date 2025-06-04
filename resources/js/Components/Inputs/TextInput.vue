<script setup>
import { onMounted, ref } from 'vue';

defineProps({
    modelValue: [String, Number],
    isError: {
        type: Boolean,
        default: false
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        ref="input"
        :class="[' rounded-md shadow-sm text-sm ',
        isError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500']"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>
