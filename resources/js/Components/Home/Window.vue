<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    showThis: {
        type: Boolean,
        default: false,
    },
    url: {
        type: String,
    }
});

const goToOtherRoutes = (url) => {
    router.visit(url);
}

</script>

<template>
    <div v-if="showThis" class="cursor-pointer py-20 text-center rounded-md shadow-md shadow-gray-400 hover:opacity-90 bg-blue-600 col-span-12 md:col-span-6 px-1 lg:col-span-6 lg:py-40" @click="goToOtherRoutes('/menu')">
        <span class="text-white font-bold">
            <slot></slot>
        </span>
    </div>
    
</template>
