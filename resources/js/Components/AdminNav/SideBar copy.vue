<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Categories from "@/Components/Svgs/Categories.vue";
import Announcement from "@/Components/Svgs/Announcement.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Order from "@/Components/Svgs/Order.vue";
import Register from "@/Components/Svgs/Register.vue";
import Store from "@/Components/Svgs/Store.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import List from "@/Components/Nav/List.vue";

import { ref, computed, defineProps, toRefs } from 'vue';

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    activeTab: {
        type: String,
        default: "HOME"
    },
    auth: {
        type: Object,
        required: true,
    },
});

/* const user = props.auth ? toRefs(props.auth).user : null;
const userRole = ref(user ? user.value.role : 'ADMIN');
const isAdmin = computed(() => userRole.value === 'ADMIN'); */

const emit = defineEmits();

const logout = () => {
    emit('logout');
};
</script>

<template>


        <div :class="[
            'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28  text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2',
            (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
        ]" style="overflow-y: auto; max-height: 100vh; overflow-x: hidden; z-index: 99999;">
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <div class="tooltip tooltip-right tooltip-primary z-30" data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin" ><Dashboard class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items"><RetailItems class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Retail Group">
                <List v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" :active-tab="activeTab" tabName="CATEGORY" url="/rboinventitemretailgroups">
                    <Categories class="h-6 lg:h-8"/>
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order" v-if="$page.props.auth.user.role === 'STORE'">
                    <Order class="h-6 lg:h-8"/> 
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Reports">
                <List :active-tab="activeTab" tabName="REPORTS" url="/orderingconso"><Reports class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Party Cakes">
                <List :activeTab="activeTab" tabName="PARTYCAKES" url="/partycakes">
                    <PartyCake class="h-6 lg:h-8" />
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Announcement">
                <List v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" :activeTab="activeTab" tabName="ANNOUNCEMENT" url="/announcement">
                    <Announcement class="h-6 lg:h-8" />
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Store">
                <List v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" :activeTab="activeTab" tabName="STORE" url="/store">
                    <Store class="h-6 lg:h-8" />
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Register">
                <List v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" :active-tab="activeTab" tabName="REGISTER" url="/signup">
                    <Register class="h-6 lg:h-8"/>
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </div>


        </ul>
        
    </div>
</template>
