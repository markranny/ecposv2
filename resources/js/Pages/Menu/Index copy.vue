<script setup>
import CarouselSlides from '@/Components/Carousel/MenuCarouselSlides.vue';
import CartContent from '@/Components/Cart/CartContent.vue';
import 'vue3-carousel/dist/carousel.css'
import InputLabel from '@/Components/Inputs/InputLabel.vue';
import TextInput from '@/Components/Inputs/TextInput.vue';
import Card from '@/Components/Menu/Card.vue';
import SwipeLeft from '@/Components/Svgs/SwipeLeft.vue';
import SwipeRight from '@/Components/Svgs/SwipeRight.vue';
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import Main from "@/Layouts/Main.vue";
import Title from "@/Components/Titles/Title.vue";
import { ref } from 'vue';

const showThis = ref('Purchase');

const props = defineProps({
    username: {
        type: String
    },

    category: {
        type: Array,
        required: true,
    },

    items: {
        type: Array,
        required: true,
    },

});

const breakpoints = ref({
    300: {
        itemsToShow: 1,
        snapAlign: 'start',
    },
    600: {
        itemsToShow: 1.5,
        snapAlign: 'start',
    },
    800: {
        itemsToShow: 2,
        snapAlign: 'start',
    },
    900: {
        itemsToShow: 2.5,
        snapAlign: 'start',
    },
    1000: {
        itemsToShow: 3,
        snapAlign: 'start',
    },
    1100: {
        itemsToShow: 4,
        snapAlign: 'start',
    },
    1200: {
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
};

</script>

<template>
     <Main active-tab="HOME">
        <template v-slot:main>
            <div class="relative w-full h-full">
                <div class="absolute top-0 bottom-0 left-0 w-4/6 lg:w-4/6 p-2">
                    <div class="flex justify-between items-center gap-2">
                        <Title class="text-sm font-bold lg:text-md">{{ showThis }} | AR: FOODPANDA</Title> 

                        <label class="input input-bordered flex items-center gap-2 w-3/5 lg:w-4/5">
                            <input type="text" class="grow text-sm" placeholder="Barcode / Item" />
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg>
                        </label>

                    </div>

                        <template>
                            
                        </template>

                    <div class="relative px-10">
                        <div class="flex overflow-x-auto">
                            <div class="flex flex-nowrap ml-10 text-xs w-[80%] lg:text-sm">

                            <div v-for="cat in category" :key="cat.groupid">
                                <div class="flex-none p-2 w-40">
                                    <div class="bg-transparent rounded-lg p-2 font-bold italic">{{ cat.name }}</div>
                                </div>
                            </div>

                            </div>
                        </div>

                        <SwipeLeft class="w-6 h-6 absolute left-0 top-1/2 -translate-y-1/2 transform"></SwipeLeft>

                        <SwipeRight class="w-6 h-6 absolute right-0 top-1/2 transform -translate-y-1/2"></SwipeRight>
                    </div>
 
                </div>


                <div class="absolute top-[20vh] bottom-0 left-0 w-9/12 ml-[-50px] lg:w-4/6 lg:top-35 px-2">
                    <div class="relative w-full h-full ">
                        <div class="absolute top-0 left-0 right-0 py-2">
                        </div>

                        <div class="left-0 top-5 overflow-y-auto h-[70vh] w-full flex flex-wrap ml-10 justify-center gap-x-5 gap-y-4 p-2 lg:gap-x-20 lg:ml-20 lg:h-[80vh]">

                            <div class="flex flex-wrap">
                            <div v-for="item in items" :key="item.itemid" class="relative flex flex-col justify-center w-[20%] min-h-20 bg-white shadow-md shadow-gray-200 py-4 px-2 rounded-lg cursor-pointer lg:w-2/12 m-2">
                                <span class="font-bold text-base text-nowrap overflow-hidden">
                                <figure><img src="../../../../public/images/Product.webp" alt="Coffee" /></figure>
                                </span>
                                <span class="text-xs text-gray-400 font-thin">{{ item.barcode }}</span>
                                <span class="text-xs text-gray-600 font-bold">{{ item.itemname }}</span>
                                <span class="text-end text-sm text-gray-600 font-bold">Php {{ item.price }}</span>
                                <span class="absolute top-0 right-0 h-9 w-9 bg-blue-400 rounded-full flex items-center justify-center skeleton">
                                <div class="text-sm text-white">{{ item.quantity }}</div>
                                </span>
                            </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="absolute top-0 bottom-0 right-0 w-2/6 lg:w-2/6">
                    <CartContent />
                </div>

            </div>
        </template>
    </Main>


</template>

<style >
.vue-slide {
  transition: transform 0.3s ease;
}
</style>
