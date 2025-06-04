<script setup>

const props = defineProps({
    showThis: {
        type: Boolean,
        default: false,
    },
});

</script>

<!-- rounded-md border-b border-blue-400 -->

<template>
    <button :class="[
        'font-bold p-2 my-2 shadow-sm shadow-gray-100 h-70vh hover:bg-gray-200 hover:text-black rounded-md  min-w-12 text-sm tracking-widest lg:min-w-64',
        (showThis ? 'bg-transparent text-black' : 'bg-transparent text-gray-800')]">
        <slot></slot>
    </button>   
</template>
