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

<!-- <template>


        <div :class="[
            'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28  text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 ',
            (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
        ]">
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
 -->


 <!-- <template>
    <div :class="[
        'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 overflow-y-auto h-full',
        (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
    ]" >
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <div class="tooltip tooltip-right tooltip-primary z-40" data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin">
                    <Dashboard class="h-6 lg:h-8"/> 
                </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary z-40" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items">
                    <RetailItems class="h-6 lg:h-8"/> 
                </List>
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
                <List :active-tab="activeTab" tabName="REPORTS" url="/orderingconso">
                    <Reports class="h-6 lg:h-8"/> 
                </List>
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
</template> -->


<template>
    <div :class="[
        'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 overflow-y-auto h-full',
        (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
    ]" v-if="$page.props.auth.user.role === 'SUPERADMIN'">
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <li class="tooltip tooltip-right tooltip-primary z-40">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin">
                    <Dashboard class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary z-40" >
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items">
                    <RetailItems class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN'" class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="CATEGORY" url="/rboinventitemretailgroups">
                    <Categories class="h-6 lg:h-8"/>
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'STORE'" class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="ORDER" url="/order">
                    <Order class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="REPORTS" url="/orderingconso">
                    <Cart class="h-6 lg:h-8"/> 
                </List>
            </li>

            <!-- <li class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="PARTYCAKES" url="/partycakes">
                    <PartyCake class="h-6 lg:h-8" />
                </List>
            </li> -->

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="ANNOUNCEMENT" url="/announcement">
                    <Announcement class="h-6 lg:h-8" />
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="STORE" url="/store">
                    <Store class="h-6 lg:h-8" />
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="REGISTER" url="/signup">
                    <Register class="h-6 lg:h-8"/>
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" >
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </li>
        </ul>
    </div>

    <div :class="[
        'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 ',
        (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
    ]" v-if="$page.props.auth.user.role === 'ADMIN' || $page.props.auth.user.role === 'STORE' || $page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" >
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <li class="tooltip tooltip-right tooltip-primary z-40" data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin">
                    <Dashboard class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary z-40" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items">
                    <RetailItems class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="Retail Group">
                <List :active-tab="activeTab" tabName="CATEGORY" url="/rboinventitemretailgroups">
                    <Categories class="h-6 lg:h-8"/>
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'STORE'" class="tooltip tooltip-right tooltip-primary" data-tip="Create Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order">
                    <Order class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" data-tip="Orders">
                <List :active-tab="activeTab" tabName="REPORTS" url="/orderingconso">
                    <Cart class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="Process">
                <List :active-tab="activeTab" tabName="FGCOUNT" url="/mgcount">
                    <Opic class="h-6 lg:h-8"/> 
                </List>
            </li>

            <!-- <li v-if="$page.props.auth.user.role === 'OPIC'" class="tooltip tooltip-right tooltip-primary" data-tip="INVENTORY RECORDS">
                <List :active-tab="activeTab" tabName="INVENTORY" url="/f-mgcount">
                    <Finish class="h-6 lg:h-8"/> 
                </List>
            </li> -->

            <!-- <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="PICK LIST">
                <List :active-tab="activeTab" tabName="PICKLIST" url="/picklist">
                    <Receipt class="h-6 lg:h-8"/> 
                </List>
            </li> -->

            <li v-if="$page.props.auth.user.role === 'OPIC' || $page.props.auth.user.role === 'PLANNING'" class="tooltip tooltip-right tooltip-primary" data-tip="FINAL DR">
                <List :active-tab="activeTab" tabName="FINALDR" url="/finalDR">
                    <Receipt class="h-6 lg:h-8"/> 
                </List>
            </li>

            <li class="tooltip tooltip-right tooltip-primary" data-tip="Party Cakes">
                <List :active-tab="activeTab" tabName="PARTYCAKES" url="/partycakes">
                    <PartyCake class="h-6 lg:h-8" />
                </List>
            </li>

            <li v-if="$page.props.auth.user.role === 'SUPERADMIN' || $page.props.auth.user.role === 'ADMIN'" class="tooltip tooltip-right tooltip-primary" data-tip="Announcement">
                <List :active-tab="activeTab" tabName="ANNOUNCEMENT" url="/announcement">
                    <Announcement class="h-6 lg:h-8" />
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

