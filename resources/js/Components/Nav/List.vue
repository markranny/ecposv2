<script setup>
import { router } from '@inertiajs/vue3';


const props = defineProps({
    activeTab: {
        type: String,
        default: null
    },
    tabName: {
        type: String,
        required: true
    },
    url: {
        type: String,
        required: true
    },
    haveSubUrl: {
        type: Boolean,
        default: false
    }
});

const visitRoute = (url) => {
    router.visit(url);
}
</script>
<template>
    <!-- <li :class="[
        'flex justify-center flex-col items-center p-3 text-white font-light text-xs hover:bg-blue-800 rounded-md w-full max-w-5x hover:text-white cursor-pointer tracking-wider gap-1',
        activeTab === tabName ? 'bg-blue-800 text-white' : ''
    ]" @click="visitRoute(url)">
        <slot />
    </li> -->

    <li :class="[
        'main-list flex justify-center flex-col items-center p-3 text-black font-light text-xs hover:bg-purple-800 rounded-md w-full max-w-5x hover:text-white cursor-pointer tracking-wider gap-1',
        activeTab === tabName ? 'bg-blue-800 text-white' : ''
    ]" @click="visitRoute(url)" >
        <p v-show="haveSubUrl" class="relative  hidden">
            <p class="absolute -right-24 min-w-10 min-h-10 bg-black p-3">
                <ul class="flex gap-2 flex-col">
                    <li>LIST 1</li>
                    <li>LIST 2</li>
                    <li>LIST 3</li>
                </ul>
            </p>
        </p>
        <slot />
    </li>
</template>
<style scoped>
.main-list:hover > p{
    display: block;
}
</style>