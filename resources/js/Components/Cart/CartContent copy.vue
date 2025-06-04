<script setup>
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import Create from "@/Components/Items/Create.vue";
import Update from "@/Components/Items/Update.vue";
import Delete from "@/Components/Items/Delete.vue";
import More from "@/Components/Items/More.vue";
import Items from "@/Components/Cart/Items.vue";
import PrimaryButton from "@/Components/Buttons/PrimaryButton.vue";
import PurpleButton from "@/Components/Buttons/PurpleButton.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";
import Shopping from "@/Components/Svgs/Shopping.vue";
import Cart from "@/Components/Svgs/Cart.vue";
import Setting from "@/Components/Svgs/Setting.vue";
import Promo from "@/Components/Svgs/Promo.vue";
import MenuDot from "@/Components/Svgs/MenuDot.vue";
import ClearCart from "@/Components/Svgs/ClearCart.vue";
import ModalDaisy from "@/Components/DaisyUI/Modal.vue";




</script>


<template>
    <div class="relative bg-white border-s border-gray-400 shadow-md shadow-gray-600 w-full h-full lg:w-full lg:h-full max-w-sm lg:max-w-full">
        <!-- <div class="absolute left-0 right-0 top-0 h-14 bg-white text-black flex justify-between items-center text-xl font-bold p-3">

            <p class="tracking-widest">BILLS</p>

            <SecondaryButton type="button">
                <a class="text-xs">CLEAR ALL</a>
            </SecondaryButton>
        </div> -->

        <div class="absolute left-0 right-0 top-0 h-10 bg-white text-black flex justify-center items-center text-xl font-bold p-3">
            <p class="tracking-widest ">ORDER | CART</p>
                <div class="ml-auto">
                    <a class="text-xs"><ClearCart class="w-7 h-7"></ClearCart></a>
                </div>
        </div>

        <div class="absolute top-7 bottom-12 left-0 right-0 my-2 px-2 overflow-y-auto">

            <section class="h-5/6 px-1 py-3">
                <div class="h-full w-full overflow-y-auto bg-white p-3 top-0 right:0 left: 0 m-0">
                    

                    <ul class=" grid grid-cols-3 text-sm font-bold lg:grid-cols-3">
                        <li class="p-2 flex justify-center"><a>Item</a></li>
                        <li class="p-2 flex justify-center"><a>Price</a></li>
                        <li class="p-2 flex justify-center"><a>Qty</a></li>
                        <li class="w-full"><hr></li>
                        <li class="w-full"><hr></li>
                        <li class="w-full"><hr></li>
                    </ul>

                    <ul class=" grid grid-cols-3 text-xs lg:grid-cols-3">
                        <li class="p-2 overflow-hidden">
                            <a class="whitespace-no-wrap">Customized</a>
                        </li>
                        <li class="p-2 flex justify-center"><a><span class="font-bold text-xs">20.00</span></a></li>
                        <li class="p-2 flex justify-center"><a><span class="font-bold text-xs">2 PCS</span></a></li>
                        <!-- <li class="p-2 w-10 right-0 mr-28 lg:relative lg:w-full">
                            <div class="inline-flex ">
                                <button @click="decrement" class="rounded-full w-10 h-7 m-1 px-2 py-1 bg-red-900 text-white">-</button>
                                    <input type="text" v-model="quantity" class="input input-bordered w-full max-w-xs h-10 text-center" >
                                <button @click="increment" class="rounded-full w-10 h-7 m-1 px-2 py-1 bg-green-900 text-white">+</button>
                            </div>
                        </li> -->
                    </ul>

                    <div class="flex items-center">
                        <p class="flex justify-start text-xs text-blue-900">20% Discount | VAT 2.00</p>
                        <div class="ml-auto">
                            <button class="" onclick="my_modal_1.showModal()">
                                <MenuDot class="w-10 h-10 cursor-pointer"></MenuDot>
                                <ModalDaisy></ModalDaisy>
                            </button>
                        </div>
                    </div>
                    <hr class="h-px-10 bg-gray-100">
                                       

                    <!-- <details class="collapse collapse-plus bg-gray-100 rounded-none">
                    <summary class="collapse-title text-sm font-medium">Actions</summary>
                    <div class="collapse-content"> 
                        <ul class="w-full flex justify-center">
                            <div class="inline-flex p-1 gap-5">
                                <button class="rounded-full w-8 h-8 m-1 px-2 py-1 bg-red-900 text-white"><Setting></Setting></button>
                                <button class="rounded-full w-8 h-8 m-1 px-2 py-1 bg-green-900 text-white"><promo></promo></button>
                                <button class="rounded-full w-8 h-8 m-1 px-2 py-1 bg-yellow-300 text-white"><Setting></Setting></button>
                                <button class="rounded-full w-8 h-8 m-1 px-2 py-1 bg-green-900 text-white"><promo></promo></button>
                            </div>
                        </ul>
                        <hr>
                    </div>
                    </details> -->
                    

                    <!-- <Items v-for="items in 10" /> -->
                </div>
            </section>

            <section class="p-2">
                <div class="flex text-sm font-bold justify-between items-center px-1">
                    <p>VAT</p>
                    <p>121.22</p>
                </div>
                <p class="border-b border-b-gray-300 pt-3"></p>
            </section>

            <section class="mt-3 p-2 font-bold text-xl bg-white overflow-y-auto">
                <p class="text-sm">PAYMENT METHODS</p>
                <div class="flex flex-wrap gap-2 pt-2">
                    <PurpleButton type="button" v-for="item in 6">
                        PAYMENT {{ item }}
                    </PurpleButton>
                </div>
            </section>

            <!-- <section class="mt-3 mx-2 p-3 font-bold text-xl bg-white overflow-y-auto rounded">
                <p class="text-sm">PAYMENT METHODS</p>
                <div class="flex flex-wrap gap-1 pt-2 justify-center ">
                    <PurpleButton type="button" class="w-2/5 justify-center">
                        <a>Home</a> 
                    </PurpleButton>

                    <PurpleButton type="button" class="w-2/5 justify-center">
                        <a>Payment</a> 
                    </PurpleButton>

                    <PurpleButton type="button" class="w-2/5 justify-center">
                        <a>Adjust</a> 
                    </PurpleButton>

                    <PurpleButton type="button" class="w-2/5 justify-center">
                        <a>Discount</a> 
                    </PurpleButton>

                    <PurpleButton type="button" class="w-2/5 justify-center">
                        <a>Xread</a> 
                    </PurpleButton>

                    <PurpleButton type="button" class="w-2/5 justify-center">
                      Zread 
                    </PurpleButton>

                </div>
            </section> -->

        </div>

        <div class="absolute left-0 right-0 bottom-0 h-14 text-white bg-navy flex justify-between items-center border-t border-gray-900 shadow-sm shadow-gray-900">
            <div class="flex flex-col justify-start ps-1 tracking-wider">
                <p class="font-thin text-xs text-white ml-2">TOTAL</p>
                <p class="font-extrabold text-lg ml-2">PHP 121.22</p>
            </div>
            <div class="skeleton m-5 w-1/3 h-10 bg-blue-900 flex justify-center items-center cursor-pointer font-bold text-white tracking-wider  text-xs md:text-sm lg:text-base">
                <Cart class="w-10 p-2 lg:p-1"></Cart>
            </div>
        </div>
    </div>
</template>

<script>
export default {
  data() {
    return {
      quantity: 1
    };
  },
  methods: {
    increment() {
      this.quantity++;
    },
    decrement() {
      if (this.quantity > 1) {
        this.quantity--;
      }
    }
  }
};
</script>
