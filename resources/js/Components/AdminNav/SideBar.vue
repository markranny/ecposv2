<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Categories from "@/Components/Svgs/Categories.vue";
import Announcement from "@/Components/Svgs/Announcement.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Manage from "@/Components/Svgs/Manage.vue";
import Order from "@/Components/Svgs/Order.vue";
import Register from "@/Components/Svgs/Register.vue";
import Store from "@/Components/Svgs/Store.vue";
import Opic from "@/Components/Svgs/Opic.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Stock from "@/Components/Svgs/Stock.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import Receipt from "@/Components/Svgs/Picklist.vue";
import List from "@/Components/Nav/List.vue";
import Customers from "@/Components/Svgs/Customers.vue";
import Attendance from "@/Components/Svgs/Attendance.vue";

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

const emit = defineEmits();

const logout = () => {
    emit('logout');
};
</script>

<template>
    <!-- Overlay for mobile to close sidebar when clicking outside -->
    <div 
        v-if="isSidebarOpen" 
        class="fixed inset-0 bg-black bg-opacity-50 z-10 md:hidden"
        @click="$emit('toggle-sidebar')"
    ></div>

    <div :class="[
        'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2',
        (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
    ]" v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'DISPATCH' || $page.props.auth.user.role === 'ADMIN' || $page.props.auth.user.role === 'STORE' || $page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" >
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary z-40 " data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin">
                    <Dashboard class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary z-40" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items">
                    <RetailItems class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'STORE' && $page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary" data-tip="Create Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order">
                    <Order class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'DISPATCH' || $page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="FINISHED GOODS">
                <List :active-tab="activeTab" tabName="FG" url="/dispatch-inventory">
                    <Manage class="h-6 lg:h-8"/> 
                </List>
            </li>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Attendance">
                <List :active-tab="activeTab" tabName="ATTENDANCE" url="/attendance"><Attendance class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Customer">
                <List :active-tab="activeTab" tabName="CUSTOMERS" url="/customers"><Customers class="h-6 lg:h-8"/> </List>
            </div>

            <li v-if="$page.props.auth.user.role != 'DISPATCH'" class="tooltip tooltip-right tooltip-primary" data-tip="REPORTS">
                <List :active-tab="activeTab" tabName="REPORTS" url="/reports">
                    <Reports class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="Process">
                <List :active-tab="activeTab" tabName="FGCOUNT" url="/mgcount">
                    <Opic class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="FINAL DR">
                    <List :active-tab="activeTab" tabName="FINALDR" url="/fdr-daterange?EndDate=1997-08-23&STORE=MRPA"> 
                    <Receipt class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN' || $page.props.auth.user.role === 'OPIC'" class="tooltip tooltip-right tooltip-primary" data-tip="Store">
                <List :active-tab="activeTab" tabName="STORE" url="/store">
                    <Store class="h-6 lg:h-8" />
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="Register">
                <List :active-tab="activeTab" tabName="REGISTER" url="/signup">
                    <Register class="h-6 lg:h-8"/>
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </li>
        </ul>
    </div>
</template>