<script setup>
import SideBar from "@/Components/AdminNav/SideBar.vue";
import CloseSideBarButton from "@/Components/Nav/CloseSideBarButton.vue";
import FullScreenIcon from "@/Components/Nav/FullScreenIcon.vue";
import FlashMessage from "@/Components/Alerts/FlashMessage.vue";
import Logout from "@/Components/Globals/Logout.vue";

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

</script>

<template>

    <Head title="RETAIL">
        <meta name="theme-color" content="#000000" />
        <link rel="manifest" href="/manifest.json" />
        <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
        <meta name="apple-mobile-web-app-status-bar" content="#000000" />
        <meta name="apple-mobile-web-app-capable" content="yes" />
        <meta name="mobile-web-app-capable" content="yes" />
    </Head>

    
    <div class="relative inset-0 h-screen w-screen">
        <!-- <section class="bg-gray-200 text-black inset-0 w-screen h-screen absolute overflow-x-hidden">
            <aside class="bg-gray-200 text-black h-screen w-28 relative">
                <CloseSideBarButton :is-sidebar-open="isSidebarOpen" @toggle-sidebar="toggleSidebar"  />
                <SideBar :is-sidebar-open="isSidebarOpen" :active-tab="activeTab" @logout="logout" />
            </aside>
            <nav class="top-0 right-0 left-0 w-screen h-10 bg-white text-xs lg:text-sm text-black font-bold absolute z-10 pr-2 flex justify-end gap-2 items-center">
                {{ $page.props.auth.user.name }} || {{ $page.props.auth.user.storeid }}
                <FullScreenIcon />
            </nav>
        </section> -->

        <section class="bg-gray-200 text-black inset-0 w-screen h-screen absolute overflow-x-hidden">
            <aside class="bg-gray-200 text-black h-screen w-28 relative">
                <CloseSideBarButton :is-sidebar-open="isSidebarOpen" @toggle-sidebar="toggleSidebar" />
                <SideBar :is-sidebar-open="isSidebarOpen" :active-tab="activeTab" @logout="logout" />
            </aside>

            <nav class="pl-48 top-0 right-0 left-0 w-screen h-10 bg-white text-xs lg:text-sm text-navy font-bold absolute z-10 pr-2 flex justify-between items-center">
                <div>{{ $page.props.auth.user.storeid }}</div>
                <div class="flex-grow text-right pr-10">
                    {{ $page.props.auth.user.name }}
                </div>
                <FullScreenIcon />
            </nav>
            
        </section>
        <section :class="['bg-blue-50 text-black inset-0 absolute overflow-x-hidden transition-all duration-500 ease-in-out']">
            <FlashMessage />
            <Logout :show-modal="showModalLogout" @toggle-active="logoutModalHandler" />
            <slot name="modals"></slot>

            <main :class="['top-10 absolute right-0 bottom-0 trnasition-all ease-in-out duration-500', propClass, isSidebarOpen ? 'left-28' : 'left-0']">
                <slot name="main"></slot>

            </main>
        </section>
    </div>
</template>
