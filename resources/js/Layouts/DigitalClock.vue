<template>
    <div class="digital-clock">
        {{ currentTime }}
    </div>
</template>

<script>
export default {
    data() {
        return {
            currentTime: this.formatTime(new Date())
        };
    },
    mounted() {
        this.interval = setInterval(() => {
            this.currentTime = this.formatTime(new Date());
        }, 1000);
    },
    beforeUnmount() {
        clearInterval(this.interval);
    },
    methods: {
        formatTime(date) {
            return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        }
    }
};
</script>

<style scoped>
.digital-clock {
    font-size: 1rem; /* Adjust size as needed */
    font-weight: bold;
    margin-left: 200px;
    color: rgb(25, 0, 168);
}
</style>
