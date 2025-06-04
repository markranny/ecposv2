<script setup>
import { computed, watch, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CloseButton from '@/Components/Buttons/CloseButton.vue';
import Success from '@/Components/Svgs/Success.vue';
import Error from '@/Components/Svgs/Error.vue';

const page = usePage()
const flash = computed(() => page.props.flash)
const isFlashMessageActive = ref(false);

const closeFlashMessage = () => {
    page.props.flash.message = null;
};

watch(flash, (newValue) => {
  if (newValue.message && !isFlashMessageActive.value) {
    isFlashMessageActive.value = true;
    setTimeout(() => {
        isFlashMessageActive.value = false;
        page.props.flash.message = null
    }, 3000);
  }
})

</script>

<template>
        <div :class="[
            'bg-white rounded-lg shadow-xl shadow-gray-600 overflow-hidden top-2 absolute min-w-48 max-w-48 sm:max-w-72 md:max-w-80 lg:max-w-96 h-fit z-[1000] transition-all ease-in-out duration-300 text-gray-900 text-sm font-thin flex items-center justify-center flex-col pt-4',
            flash.message ? ' right-1 ' : ' -right-96 ']
        ">
                <Success v-if="flash.isSuccess" />
                <Error v-if="!flash.isSuccess" />
                <p :class="'pt-2 font-semibold text-base ' + (flash.isSuccess ? 'text-green-700' : 'text-red-700')">
                    {{ flash.isSuccess ? 'SUCCESS' : 'ERROR'}}
                </p>
                <p class="px-2 py-1 text-gray-500 font-thin text-sm mb-2 text-center">{{ flash.message }}</p>
                <div class="w-full bg-gray-200 text-center p-2">
                    <CloseButton @click="closeFlashMessage" >
                        <small>CLOSE</small>
                    </CloseButton>
                </div>
        </div>
</template>
