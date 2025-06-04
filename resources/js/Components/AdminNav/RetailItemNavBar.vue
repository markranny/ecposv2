<script setup>
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import RetailItems from "@/Components/Svgs/RetailItems.vue";
import Customers from "@/Components/Svgs/Customers.vue";
import Transactions from "@/Components/Svgs/Transactions.vue";
import Discount from "@/Components/Svgs/Discount.vue";
import GiftCards from "@/Components/Svgs/GiftCards.vue";
import Pos from "@/Components/Svgs/Pos.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Setting from "@/Components/Svgs/Setting.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import List from "@/Components/Nav/List.vue";
/* import RetailItems from "../Svgs/RetailItems.vue";
import Customers from "../Svgs/Customers.vue"; */

const props = defineProps({
    isSidebarOpen: {
        type: Boolean,
        default: true
    },
    activeTab: {
        type: String,
        default: "HOME"
    }
});

const emit = defineEmits();

const logout = () => {
    emit('logout');
};
</script>

<template>
    <div :class="[
            'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 w-28  text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2',
            (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
        ]">
        <!-- <ul class="flex justify-center flex-col items-center gap-3 text-xs max-h-96 overflow-y-auto">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full max-w-5x"> -->
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ADMIN
            </li>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin" ><Dashboard class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Retails">
                <List :active-tab="activeTab" tabName="RETAILITEMS" url="/items"><RetailItems class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order" v-if="isStore"><Order class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Reports">
                <List :active-tab="activeTab" tabName="REPORTS" url="/orderingconso"><Reports class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Store">
                <List :active-tab="activeTab" tabName="STORE" url="/store" v-if="isAdmin"><Store class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Register">
                <List :active-tab="activeTab" tabName="REGISTER" url="/signup"><Register class="h-6 lg:h-8"/></List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </div>


        </ul>
        <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
            <button class="flex justify-center flex-col items-center p-3 text-black hover:bg-navy rounded-md w-full max-w-5x font-light text-xs hover:text-white gap-1" @click="logout">
            <Logout />
        </button>
        </div> -->
        
    </div>
</template>
