<script setup>

/* ICON */
import Store from "@/Components/Svgs/Store.vue";
import Peso from "@/Components/Svgs/Peso.vue";
import Receipt from "@/Components/Svgs/Receipt.vue";
import Income from "@/Components/Svgs/Income.vue";
import Discounts from "@/Components/Svgs/Discounts.vue";

import CarouselSlides from '@/Components/Carousel/CarouselSlides.vue';
import Window from '@/Components/Home/Window.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import AdminPanel from "@/Layouts/AdminPanel.vue";
import Title from "@/Components/Titles/Title.vue";
import { ref } from 'vue';

const title = ref('Purchase');
const showThis = ref('purchase');

const props = defineProps({
    username: {
        type: String
    }
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

const showTab = (showThisParam, titleParam) => {
    showThis.value = showThisParam;
    title.value = titleParam;
};

</script>

<template>
     <AdminPanel active-tab="DASHBOARD" prop-class="p-2">
        <template v-slot:main>
            <!-- <div class="flex gap-3 flex-wrap flex-column justify-center lg:justify-start"> -->
            <div class="flex gap-3 flex-column justify-center">
                <!-- <div v-for="items in 5" class="h-[180px] w-[300px] bg-white shadow-sm shadow-gray-500 rounded-md p-3"> -->


                <!-- <div class="h-[130px] w-[300px] bg-white shadow-sm shadow-gray-100 rounded-md p-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                        <Peso /> 
                    </div>
                    <h3 class="sm:text-xs lg:text-lg text-black my-5 font-bold">NET SALES</h3>
                </div>


                <div class="h-[130px] w-[300px] bg-white shadow-sm shadow-gray-100 rounded-md p-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                        <Income />
                    </div>
                    <h3 class="sm:text-xs lg:text-lg text-black my-5 font-bold">GROSS SALES</h3>
                </div>

                <div class="h-[130px] w-[300px] bg-white shadow-sm shadow-gray-100 rounded-md p-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                        <Discounts />
                    </div>
                    <h3 class="sm:text-xs lg:text-lg text-black my-5 font-bold">DISCOUNT</h3>
                </div>

                <div class="h-[130px] w-[300px] bg-white shadow-sm shadow-gray-100 rounded-md p-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                        <Store />
                    </div>
                    <h3 class="sm:text-xs lg:text-lg text-black my-5 font-bold">TOTAL BRANCHES</h3>
                </div>

                <div class="h-[130px] w-[300px] bg-white shadow-sm shadow-gray-100 rounded-md p-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-gray-200 rounded-full">
                        <Receipt />
                    </div>
                    <h3 class="sm:text-xs lg:text-lg text-black my-5 font-bold">TOTAL TRANSACTIONS</h3>
                </div> -->

                
            </div>

            <!-- <div class="hero h-5/6 w-full mt-16" style="background-image: url(https://bwsuperbakeshop.ph/images/slider/slider-2-1920x870.webp?v=18);"> -->
                    <!-- <div class="hero-overlay bg-opacity-60"></div>
                        <div class="hero-content text-center text-neutral-content">
                            <div class="max-w-md">
                                <h1 class="mb-5 text-5xl font-bold">Hello there</h1>
                                <p class="mb-5">Provident cupiditate voluptatem et in. Quaerat fugiat ut assumenda excepturi exercitationem quasi. In deleniti eaque aut repudiandae et a id nisi.</p>
                                <button class="btn btn-primary">Get Started</button>
                            </div>
                        </div> -->
                    <!-- </div> -->

                    <!-- <iframe src="https://lottie.host/embed/43c1b491-658d-43f3-8dd5-854446819251/2igOT6RrNk.json" class="w-full h-full"></iframe> -->
                    <iframe src="https://lottie.host/embed/f69dee81-7a79-4de1-9199-0ed5f959549c/BiVHosiGkN.json" class="w-full h-full"></iframe>
        </template>

    </AdminPanel>
</template>

<style >
.vue-slide {
  transition: transform 0.3s ease; /* Adjust duration and easing as needed */
}
</style>
