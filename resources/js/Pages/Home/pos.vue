<script setup>
import { ref, computed } from 'vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';

import Main from "@/Layouts/Main.vue";

const props = defineProps({
    username: {
        type: String
    },
    windowtables: {
        type: Array,
        required: true,
    },
    windowtrans: {
        type: Array,
        required: true,
    },
    windows: {
        type: Array,
        required: true,
    }
});

const activeTab = ref(props.windowtables[0]?.id || null);

const setActiveTab = (tabId) => {
    activeTab.value = tabId;
};

const activeWindowData = computed(() => {
    return props.windowtrans.filter(window => parseInt(window.windownum) === activeTab.value);
});

const activeTabName = computed(() => {
    return props.windowtables.find(tab => tab.id === activeTab.value)?.DESCRIPTION || '';
});

const breakpoints = ref({
    300: {
        itemsToShow: 1.5,
        snapAlign: 'start',
    },
    500: {
        itemsToShow: 2.5,
        snapAlign: 'start',
    },
    600: {
        itemsToShow: 3.5,
        snapAlign: 'start',
    },
    800: {
        itemsToShow: 4.5,
        snapAlign: 'start',
    },
    900: {
        itemsToShow: 5,
        snapAlign: 'start',
    }
});

const carouselSettings = ref({
    touch: true,
    smooth: true,
});
</script>

<template>
    <Main active-tab="POS" prop-class="p-1">
        <template v-slot:main>
            <div class="w-full mx-auto p-4">
                <div class="flex justify-center">
                    <Carousel v-bind="carouselSettings" :breakpoints="breakpoints">
                        <Slide v-for="tab in windowtables" :key="tab.id">
                            <button 
                                @click="setActiveTab(tab.id)"
                                :class="[ 
                                    'px-10 py-2 rounded-md w-full ',
                                    tab.id === activeTab ? 'bg-blue-600 text-white' : 'bg-white text-gray-700' 
                                ]"
                            >
                                {{ tab.DESCRIPTION }}
                            </button>
                        </Slide>
                        <template #addons>
                            <!-- <Navigation /> -->
                            <!-- <Pagination /> -->
                        </template>
                    </Carousel>
                </div>
                
                <h2 class="text-lg font-semibold my-4">{{ activeTabName }}</h2>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4">
                    <div 
                        v-for="window in activeWindowData" 
                        :key="window.id"
                        class="bg-blue-600 text-white p-6 rounded-lg flex flex-col items-center justify-center 
                            sm:h-[15vh] md:h-[32vh] lg:h-[40vh]"
                    >
                        <a :href="`/windows/menu/${window.id}`">
                            <p class="text-xl font-bold mb-2">{{ window.DESCRIPTION }}</p>
                        </a>   
                    </div>
                </div>
                
            </div>
        </template>
    </Main>
</template>

<style scoped>
.carousel__slide {
    padding: 5px;
}
.vue-slide {
    transition: transform 0.3s ease;
}
</style>