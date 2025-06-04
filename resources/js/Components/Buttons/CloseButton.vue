<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type"
    class="inline-flex items-center border-transparent font-extrabold text-gray-600 uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 bg-white rounded-md py-1 px-1">
        <slot />
    </button>
</template>
