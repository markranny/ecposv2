<script setup>
import SideBar from "@/Components/AdminNav/SideBar.vue";
import CloseSideBarButton from "@/Components/Nav/CloseSideBarButton.vue";
import FullScreenIcon from "@/Components/Nav/FullScreenIcon.vue";
import FlashMessage from "@/Components/Alerts/FlashMessage.vue";
import Logout from "@/Components/Globals/Logout.vue";

import Barcodes from "@/Components/Svgs/Barcodes.vue";
import Shopping from "@/Components/Svgs/Shopping.vue";
import Products from "@/Components/Svgs/Products.vue";
import Promo from "@/Components/Svgs/Promo.vue";
import Notifications from "@/Components/Svgs/Notifications.vue";
import ShowEye from "@/Components/Svgs/ShowEye.vue";
import HideEye from "@/Components/Svgs/HideEye.vue";
import Transactions from "@/Components/Svgs/Transactions.vue";
import Discount from "@/Components/Svgs/Discount.vue";
import Headset from "@/Components/Svgs/Headset.vue";
import Inventory from "@/Components/Svgs/Inventory.vue";
import CustomerGroup from "@/Components/Svgs/CustomerGroup.vue";
import RetailGroup from "@/Components/Svgs/RetailGroup.vue";
import SpecialGroup from "@/Components/Svgs/SpecialGroup.vue";


import { ref, onMounted, onUnmounted } from 'vue';



const isSidebarOpen = ref(true);
const showModalLogout = ref(false);

const props = defineProps({
    activeTab: {
        type: String,
        default: "HOME"
    },
    propClass: {
        type: String,
        default: null
    }
});
const toggleSidebar = () => {
    isSidebarOpen.value = !isSidebarOpen.value;
}

const logout = () => {
    showModalLogout.value = true;
};

const logoutModalHandler = () => {
    showModalLogout.value = false;
};

const rboinventitemretailgroups = () => {
  window.alert('You are Redirecting to Category Entries');
  window.location.href = '/rboinventitemretailgroups';
};

const rbospecialgroups = () => {
  window.alert('You are Redirecting to Special Category Entries');
  window.location.href = '/rbospecialgroups';
};

const posperiodicdiscounts = () => {
  window.alert('You are Redirecting to Discount Entries');
  window.location.href = '/posperiodicdiscounts';
};

</script>

<template>
    <div class="relative inset-0 h-screen w-screen">
        <!-- <section class="bg-gray-200 text-black inset-0 w-screen h-screen absolute overflow-x-hidden">
            <aside class="bg-gray-200 text-black h-screen w-28 relative">
                <CloseSideBarButton :is-sidebar-open="isSidebarOpen" @toggle-sidebar="toggleSidebar" />
                <SideBar :is-sidebar-open="isSidebarOpen" :active-tab="activeTab" @logout="logout" />
            </aside>
            <nav class="pl-48 top-0 right-0 left-0 w-screen h-10 bg-white text-xs lg:text-sm text-navy font-bold absolute z-10 pr-2 flex justify-between items-center">
                <div>{{ $page.props.auth.user.storeid }}</div>
                <div class="flex-grow text-right pr-10">
                    
                    <div class="tooltip tooltip-bottom tooltip-primary cursor-pointer" data-tip="Retail Group">
                        <RetailGroup class="h-5 mr-10" @click="rboinventitemretailgroups('/rboinventitemretailgroups')"></RetailGroup>
                    </div>

                    {{ $page.props.auth.user.name }}
                </div>
                <FullScreenIcon />
            </nav>
        </section> -->

        <section class="bg-gray-200 text-black inset-0 w-screen h-screen absolute overflow-x-hidden">
            <aside class="bg-gray-200 text-black h-screen w-28 relative">
                <CloseSideBarButton :is-sidebar-open="isSidebarOpen" @toggle-sidebar="toggleSidebar"  />
                <SideBar :is-sidebar-open="isSidebarOpen" :active-tab="activeTab" @logout="logout" />
            </aside>
            <nav class="top-0 right-0 left-0 w-screen h-10 bg-white text-xs lg:text-sm text-black font-bold absolute z-10 pr-2 flex justify-end gap-2 items-center">
                <!-- <div class="tooltip tooltip-bottom tooltip-primary cursor-pointer" data-tip="Retail Group">
                    <RetailGroup class="h-5 mr-10" @click="rboinventitemretailgroups('/rboinventitemretailgroups')"></RetailGroup>
                </div> -->
                {{ $page.props.auth.user.name }}
                <FullScreenIcon />
            </nav>
        </section>

        <section :class="['text-black inset-0 absolute overflow-x-hidden transition-all duration-500 ease-in-out']">
            <FlashMessage />
            <Logout :show-modal="showModalLogout" @toggle-active="logoutModalHandler" />
            <slot name="modals"></slot>

            <main :class="['top-10 absolute right-0 bottom-0 trnasition-all ease-in-out duration-500', propClass, isSidebarOpen ? 'left-28' : 'left-0']">
                <slot name="main"></slot>
            </main>
        </section>
    </div>
</template>


<script>
export default {
  methods: {
    hidePanel() {
      this.$emit('hide-panel');
    }
  }
};
</script>

