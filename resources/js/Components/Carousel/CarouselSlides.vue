<script setup>

const props = defineProps({
    showThis: {
        type: Boolean,
        default: false,
    },
});

</script>

<template>
    <button :class="[
        'mr-4 font-bold p-2 my-2 text-nowrap shadow-sm shadow-gray-400 rounded-md hover:bg-blue-500 hover:text-white min-w-32 text-sm tracking-widest',
        (showThis ? 'bg-blue-700 text-white' : 'bg-white text-gray-900')]">
        <slot></slot>
    </button>   
</template>
