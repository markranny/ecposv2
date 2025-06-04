<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Categories from "@/Components/Svgs/Categories.vue";
import Announcement from "@/Components/Svgs/Announcement.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Order from "@/Components/Svgs/Order.vue";
import Register from "@/Components/Svgs/Register.vue";
import Store from "@/Components/Svgs/Store.vue";
import Opic from "@/Components/Svgs/Opic.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Finish from "@/Components/Svgs/Finish.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import Receipt from "@/Components/Svgs/Picklist.vue";
import List from "@/Components/Nav/List.vue";
import Cart from "@/Components/Svgs/Cart.vue";

const props = defineProps({
    username: {
        type: String
    },
    announcements: {
    type: Array,
    required: true,
  },
});
</script>

<template>
<div class="flex flex-col min-h-screen">
<header class="sticky top-0 bg-navy shadow-md z-10 mb-5">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <h1 class="text-sm font-bold text-white">ECORDER</h1>
        <h1 class="text-sm font-bold text-white">{{ $page.props.auth.user.name }}</h1>
    </div>
</header>


  <!-- Main content area -->
  <main class="flex-grow">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <template v-if="!announcements || announcements.length === 0">
            <div class="col-span-full text-center mt-8">
                <div class="bg-white rounded-lg shadow-md p-4 sm:p-8 max-w-sm mx-auto">
                    <p class="text-gray-600 text-base sm:text-lg">No announcements available.</p>
                </div>
            </div>
        </template>

        <div v-for="announcement in announcements" :key="announcement.id" class="w-full">
            <!-- <div class="mockup-window border-base-300 border bg-white h-full"> -->
                <div class="flex flex-col p-4 sm:p-6 lg:p-10 h-full">
                    <div class="card bg-base-200 rounded-box grid h-full place-items-center p-4">
                        <h3 class="text-black font-bold mb-2 text-sm sm:text-base lg:text-lg">{{ announcement.SUBJECT }}</h3>
                        <p class="text-black mb-4 text-xs sm:text-sm lg:text-base">{{ announcement.DESCRIPTION }}</p>
                        <div v-if="announcement.file_path" class="mt-2">
                            <a :href="route('announcements.download', announcement.id)" class="btn btn-primary btn-xs sm:btn-sm">
                                Download Attachment
                            </a>
                        </div>
                    </div>
                    <!-- <div class="divider mt-auto"></div> -->
                </div>
            <!-- </div> -->
        </div>
    </div>
  </main>

  <!-- Bottom navigation -->
  <nav class="fixed bottom-0 left-0 right-0 bg-navy shadow-lg">
    <div class="flex justify-around items-center h-16 relative">
      <a href="/dashboard" class="flex flex-col items-center text-blue-400">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
        </svg>
        <span class="text-xs mt-1">Home</span>
      </a>
      <a href="/m-retail" class="flex flex-col items-center text-white">
        <RetailItems class="w-5"></RetailItems>
        <span class="text-xs mt-1">Retail</span>
      </a>
      <button onclick="window.location.href='/m-order'"  class="absolute left-1/2 top-0 transform -translate-x-1/2 -translate-y-1/2 w-14 h-14 bg-blue-700 rounded-full shadow-lg flex items-center justify-center border-2 border-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
        <!-- <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg> --><span class="text-xs mt-1 font-bold text-white">EC</span>
      </button>

      <a href="/m-partycakes" class="flex flex-col items-center text-white">
        <PartyCake class="w-5"></PartyCake>
        <span class="text-xs mt-1">Cakes</span>
      </a>
      <a href="/m-orders" class="flex flex-col items-center text-white">
        <Cart class="w-5"></Cart>
        <span class="text-xs mt-1">Orders</span>
      </a>
    </div>
  </nav>
</div>
</template>

<style >
</style>
