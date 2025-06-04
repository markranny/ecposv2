
<script setup>


const props = defineProps({
    title: {
        type: String,
        default: "Modal Title"
    }
});


</script>

<template>
        <div class="text-base font-light text-gray-700 py-2">
            {{ title }}
        </div>
</template>
