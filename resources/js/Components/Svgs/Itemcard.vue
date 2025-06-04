<script setup>
const props = defineProps({
    name:{
        type: String,
    },
    price: {
        type: Number,
    },
    itemId: {
        type: String,
    }
});

</script>

<template>
    <div class="col-10 col-sm-10 col-md-10 col-lg-5 col-xl-3 col-xxl-2 bg-light shadow-lg border-radius-15 px-2 py-3 mt-3 ms-3" role="button">
        <span class="d-block">
            <h5 class="fw-bold">{{ name }}</h5>
        </span>
        <span>
            <small class="text-secondary">{{ itemId }}</small>
        </span>
        <hr>
        <span class="d-flex justify-content-end align-items-end">
            <h5 class="fw-bold"> &#8369 {{ price }}</h5>
        </span>
    </div>
</template>
<style scoped>

.border-radius-15{
    border-radius: 15px;
}
</style>
