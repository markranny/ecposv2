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
import { ref, computed } from 'vue';

const showThis = ref('Purchase');
const selectedCategory = ref('');
const searchQuery = ref('');
const selectedAR = ref('CASH'); 
const selectedCustomer = ref('000000'); 
const cartMessage = ref('');

const props = defineProps({
    category: {
        type: Array,
        required: true,
    },
    items: {
        type: Array,
        required: true,
    },
    ar: {
        type: Array,
        required: true,
    },
    customers: {
        type: Array,
        required: true,
    },
    windowId: {
        type: String,
        required: true,
    },
    windowDesc: {
        type: String,
        required: true,
    }
});

const filteredItems = computed(() => {
    let filtered = props.items;
    
    if (selectedCategory.value) {
        filtered = filtered.filter(item => item.itemgroup === selectedCategory.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(item => 
            item.itemname.toLowerCase().includes(query) || 
            item.barcode.toLowerCase().includes(query)
        );
    }
    
    return filtered.map(item => ({
        ...item,
        category: item.itemgroup || selectedCategory.value || ''
    }));
});

const selectCategory = (name) => {
    selectedCategory.value = name === selectedCategory.value ? null : name;
};

const addToCart = async (itemId) => {
    try {
        const response = await axios.post(`/windows/menu/addtocart/${itemId}/${props.windowId}/${selectedAR.value}/${selectedCustomer.value}`);
        if (response.data.success) {
            cartMessage.value = response.data.message;
        }
    } catch (error) {
        console.error('Error adding item to cart:', error);
        cartMessage.value = 'Failed to add item to cart. Please try again.';
    }
};

/* const getItemLink = computed(() => {
    return (itemId) => `addtocart/${itemId}/${props.windowId}/${selectedAR.value || ''}/${selectedCustomer.value || ''}`;
}); */
</script>

<template>
    <Main active-tab="HOME">
        <template v-slot:main>
            <div class="relative w-full h-full">
                <div class="absolute top-0 bottom-0 left-0 w-4/6 lg:w-4/6 p-2">
                    <div class="flex justify-between items-center gap-2">
                        <div class="flex items-center">
                            <Title class="text-sm font-bold lg:text-md">{{ windowDesc }}</Title>
                            <span class="mx-2">|</span> 
                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0">
                                <select v-model="selectedAR" id="payment-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                    <option value="" disabled>Payment Method</option>
                                    <option value="CASH">CASH</option>
                                    <option v-for="data in ar" :key="data.ID" :value="data.ar">{{ data.ar }}</option>
                                </select>
                            </div>

                            <div class="w-full lg:w-48 xl:w-64 mb-2 lg:mb-0 lg:ml-2">
                                <select v-model="selectedCustomer" id="customer-options" class="block w-full p-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring focus:ring-blue-500">
                                    <option value="" disabled>CUSTOMER</option>
                                    <option value="000000">WALK-IN</option>
                                    <option v-for="result in customers" :key="result.ID" :value="result.accountnum">{{ result.name }}</option>
                                </select>
                            </div>
                        </div>

                        <label class="input input-bordered flex items-center gap-2 w-3/5 lg:w-2/5">
                            <input v-model="searchQuery" type="text" class="grow text-sm" placeholder="Barcode / Item" />
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4 opacity-70"><path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd" /></svg>
                        </label>
                    </div>

                    <div class="relative px-10 mt-4">
                        <div class="flex overflow-x-auto">
                            <div class="flex flex-nowrap ml-10 text-xs w-[80%] lg:text-sm">
                                <div v-for="cat in category" :key="cat.name">
                                    <div class="flex-none p-2 w-48">
                                        <div 
                                            @click="selectCategory(cat.name)"
                                            :class="{'bg-blue-500 text-white': selectedCategory === cat.name, 'bg-gray-200': selectedCategory !== cat.name}"
                                            class="flex items-center justify-center rounded-lg p-2 font-bold italic cursor-pointer transition-colors duration-300 h-full"
                                        >
                                            {{ cat.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <SwipeLeft class="w-6 h-6 absolute left-0 top-1/2 -translate-y-1/2 transform"></SwipeLeft>
                        <SwipeRight class="w-6 h-6 absolute right-0 top-1/2 transform -translate-y-1/2"></SwipeRight>
                    </div>
                </div>

                <div class="absolute top-[20vh] bottom-0 left-0 w-9/12 ml-[-50px] lg:w-4/6 lg:top-35 px-2">
                    <div class="relative w-full h-full">
                        <div class="left-0 top-5 overflow-y-auto h-[70vh] w-full ml-10 justify-center gap-x-5 gap-y-4 p-2 lg:gap-x-20 lg:ml-20 ">
                            <div class="flex flex-wrap">
                                <div v-for="item in filteredItems" :key="item.itemid" class="relative flex flex-col justify-center w-[20%] min-h-20 bg-white shadow-md shadow-gray-200 py-4 px-2 rounded-lg cursor-pointer lg:w-2/12 m-2 hover:shadow-lg transition-shadow duration-300">
                                    <span class="font-bold text-base text-nowrap overflow-hidden">
                                        <!-- <a :href="getItemLink(item.itemid)"><figure><img src="../../../../public/images/Product.webp" alt="Product" /></figure></a> -->
                                        <figure @click="addToCart(item.itemid)"><img src="../../../../public/images/Product.webp" alt="Product" /></figure>
                                    </span>
                                    <span class="text-xs text-gray-400 font-thin">{{ item.barcode }}</span>
                                    <span class="text-xs text-gray-600 font-bold">{{ item.itemname }}</span>
                                    <span class="text-end text-sm text-gray-600 font-bold">Php {{ item.price }}.00</span>
                                    <span class="absolute top-0 right-0 h-9 w-9 bg-blue-400 rounded-full flex items-center justify-center">
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

            <div v-if="cartMessage" class="fixed bottom-4 right-4 bg-green-500 text-white p-2 rounded">
                {{ cartMessage }}
            </div>
        </template>
    </Main>
</template>

<style scoped>
.vue-slide {
  transition: transform 0.3s ease;
}
</style>