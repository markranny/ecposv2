<script setup>
import CarouselSlides from '@/Components/Carousel/CarouselSlides.vue';
import Window from '@/Components/Home/Window.vue';
import 'vue3-carousel/dist/carousel.css'
import { Carousel, Slide, Pagination, Navigation } from 'vue3-carousel'
import Main from "@/Layouts/Main.vue";
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
     <!-- <Main active-tab="HOME" prop-class="p-10"> -->
    <Main active-tab="HOME" prop-class="p-1">
        <template v-slot:main>
            <!-- <Title>HOME</Title> -->

            <carousel :settings="carouselSettings" :breakpoints="breakpoints" class="mt-2 w-full pl-5 justify-center">
                <slide key="purchase-btn">
                    <carousel-slides :show-this="showThis == 'purchase'" @click="showTab('purchase', 'Purchase')">
                        Purchase
                    </carousel-slides>
                </slide>
                <slide key="party-cake-btn">
                    <carousel-slides :show-this="showThis == 'party-cake'" @click="showTab('party-cake', 'Party Cake')">
                        Party Cake
                    </carousel-slides>
                </slide>
                <slide key="reseller-btn">
                    <carousel-slides :show-this="showThis == 'reseller'" @click="showTab('reseller', 'Reseller')">
                        Reseller
                    </carousel-slides>
                </slide>
                <slide key="tie-up-btn">
                    <carousel-slides :show-this="showThis == 'tie-up'" @click="showTab('tie-up', 'Tie Up')">
                        Tie Up
                    </carousel-slides>
                </slide>
                <slide key="advance-order-btn">
                    <carousel-slides :show-this="showThis == 'advance-order'" @click="showTab('advance-order', 'Advance Order')">
                        Advance Order
                    </carousel-slides>
                </slide>
                <template #addons>
                    <Navigation />
                    <!-- <Pagination /> -->
                </template>

            </carousel>

            <section class="mt-5">
                <div >
                    <h5 class="font-bold text-gray-900">{{ title }}</h5>
                    <small class="font-medium text-gray-900 ms-2">Results</small>
                </div>

                <div class="mt-2 grid grid-cols-12 gap-2">
                    <window :show-this="showThis == 'purchase'" url="/purchase" >
                        Purchase Window 1
                    </window>

                    <window :show-this="showThis == 'purchase'" url="/purchase">
                        Purchase Window 2
                    </window>
                    <window :show-this="showThis == 'purchase'" url="/purchase">
                        Purchase Window 3
                    </window>
                    <window :show-this="showThis == 'purchase'" url="/purchase">
                        Purchase Window 4
                    </window>


                    <window :show-this="showThis == 'party-cake'" url="/party_cake" >
                        Party Cake Window 1
                    </window>
                    <window :show-this="showThis == 'party-cake'" url="/party_cake">
                        Party Cake Window 2
                    </window>


                    <window :show-this="showThis == 'reseller'" url="/reseller" >
                        Reseller Window 1
                    </window>
                    <window :show-this="showThis == 'reseller'" url="/reseller">
                        Reseller Window 2
                    </window>


                    <window :show-this="showThis == 'tie-up'" url="/tie_up" >
                        Tie up Window 1
                    </window>
                    <window :show-this="showThis == 'tie-up'" url="/tie_up" >
                        Tie up Window 2
                    </window>
                    <window :show-this="showThis == 'tie-up'" url="/tie_up">
                        Tie up Window 3
                    </window>


                    <window :show-this="showThis == 'advance-order'" url="/advance_order">
                        Advance Order Window 2
                    </window>
                </div>
            </section>
        </template>

    </Main>
</template>

<style >
.vue-slide {
  transition: transform 0.3s ease; /* Adjust duration and easing as needed */
}
</style>
