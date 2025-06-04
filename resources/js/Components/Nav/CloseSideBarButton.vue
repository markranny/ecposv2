<script setup>
import { ref } from "vue";

const emit = defineEmits();

const toggleSidebar = () => {
    emit('toggleSidebar');
};

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    }
});

const src = ref('arrow_to_left.svg');
</script>

<template>
    <button
        :class="[
            'h-8 w-8 absolute top-0 p-2  bg-navy text-gray-900 z-50 transition-all duration-500 ease-in-out rounded-full',
            (isSidebarOpen ? 'left-[6rem] ' : '-left-4 -rotate-180')
        ]"
        @click="toggleSidebar"
    >
    <svg class="" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg" transform="matrix(-1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M12 2C6.49 2 2 6.49 2 12C2 17.51 6.49 22 12 22C17.51 22 22 17.51 22 12C22 6.49 17.51 2 12 2ZM14.79 12.53L11.26 16.06C11.11 16.21 10.92 16.28 10.73 16.28C10.54 16.28 10.35 16.21 10.2 16.06C9.91 15.77 9.91 15.29 10.2 15L13.2 12L10.2 9C9.91 8.71 9.91 8.23 10.2 7.94C10.49 7.65 10.97 7.65 11.26 7.94L14.79 11.47C15.09 11.76 15.09 12.24 14.79 12.53Z"></path> </g></svg>

    </button>
</template>
