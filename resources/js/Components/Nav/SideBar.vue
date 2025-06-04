<script setup>
import Home from "@/Components/Svgs/Home.vue";
import Order from "@/Components/Svgs/Order.vue";
import Transactions from "@/Components/Svgs/Transactions.vue";
import Wallet from "@/Components/Svgs/Wallet.vue";
import Promo from "@/Components/Svgs/Promo.vue";
import Dashboard from "@/Components/Svgs/Dashboard.vue";
import Received from "@/Components/Svgs/Received.vue";
import Logout from "@/Components/Svgs/Logout.vue";
import Truck from "@/Components/Svgs/Truck.vue";
import POS from "@/Components/Svgs/Pos.vue";
import Reports from "@/Components/Svgs/Reports.vue";
import Stock from "@/Components/Svgs/Stock.vue";
import Register from "@/Components/Svgs/Register.vue";
import BO from "@/Components/Svgs/BO.vue";
import Customers from "@/Components/Svgs/Customers.vue";
import PartyCake from "@/Components/Svgs/PartyCake143.vue";
import Transfer from "@/Components/Svgs/Transfer.vue";
import LoyaltyCard from "@/Components/Svgs/Loyaltycard.vue";
import Attendance from "@/Components/Svgs/Attendance.vue";

import List from "@/Components/Nav/List.vue";

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
            'rounded-tr-3xl rounded-br-3xl p-4 bg-navy top-0 bottom-0 left-0 text-black font-bold absolute z-20 pt-12 pb-2 flex flex-col justify-between transition-all duration-500 ease-in-out px-2 lg:w-28',
            (isSidebarOpen ? 'translate-x-0' : 'translate-x-[-100%]')
        ]">
        <!-- <ul class="flex justify-center flex-col items-center gap-3 text-xs max-h-96 overflow-y-auto">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full max-w-5x"> -->
        <ul class="flex justify-center flex-col items-center gap-3 text-xs">
            <li class="flex justify-center flex-col items-center mb-5 p-3 text-white text-lg font-sans font-extrabold rounded-md w-full">
                ECPOS
            </li>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Master">
                <List :active-tab="activeTab" tabName="DASHBOARD" url="/admin" ><Dashboard class="h-6 lg:h-8"/> </List>
            </div>

            <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="POS">
                <List :active-tab="activeTab" tabName="POS" url="/pos"><POS class="h-6 lg:h-8"/> </List>
            </div> -->

            <!-- <div class="tooltip tooltip-right tooltip-primary" data-tip="Create Order">
                <List :active-tab="activeTab" tabName="ORDER" url="/order"><Received class="h-6 lg:h-8"/> </List>
            </div> -->

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Stock Transfer">
                <List :active-tab="activeTab" tabName="STOCKTRANSFER" url="/StockTransfer"><Transfer class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Batch Counting">
                <List :active-tab="activeTab" tabName="STOCK" url="/StockCounting"><Stock class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Customers">
                <List :active-tab="activeTab" tabName="CUSTOMERS" url="/customers"><Customers class="h-6 lg:h-8"/> </List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Reports">
                <List :active-tab="activeTab" tabName="REPORTS" url="/reports"><Reports class="h-6 lg:h-8"/> </List>
            </div>

            <li class="tooltip tooltip-right tooltip-primary" data-tip="Party Cakes">
                <List :active-tab="activeTab" tabName="PARTYCAKES" url="/partycakes">
                    <PartyCake class="h-6 lg:h-8" />
                </List>
            </li>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Loyalty Card">
                <List :active-tab="activeTab" tabName="LOYALTY" url="/loyalty-cards"><LoyaltyCard class="h-6 lg:h-8"/></List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Attendance">
                <List :active-tab="activeTab" tabName="ATTENDANCE" url="/attendance"><Attendance class="h-6 lg:h-8"/></List>
            </div>

            <div class="tooltip tooltip-right tooltip-primary" data-tip="Logout">
                <List :active-tab="activeTab" tabName="SETTINGS">
                    <button @click="logout"><Logout class="h-6 lg:h-8"/></button>
                </List>
            </div>


        </ul>
        
    </div>
</template>

