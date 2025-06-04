
<script setup>
import { ref } from "vue";
import Title from "@/Components/Modals/Title.vue";
import SecondaryButton from "@/Components/Buttons/SecondaryButton.vue";

const animateShake = ref(false);

const props = defineProps({
    buttonTitle: {
        type: String,
        default: "Modal Button"
    },
    title: {
        type: String,
        default: "Modal Title"
    },
    showModal: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits();

const callAnimateShake = () => {
    if(!animateShake.value){
        animateShake.value = true;

        setTimeout(() => {
            animateShake.value = false;
        }, 300);
    }
};

const toggleActive = () => {
    emit('toggleActive');
};
</script>

<template>
        <transition
            enter-active-class="ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div class="absolute inset-0 h-screen w-screen z-[900]" v-if="showModal">
                <div class="relative inset-0 h-full w-full flex justify-center overflow-hidden">
                    <div class="absolute inset-0 h-full w-full bg-gray-300 opacity-80 z-[41]"  @click="callAnimateShake" />
                    <div class="relative z-[42] mt-5 h-fit w-fit w-2/12 max-w-5xl">
                        <div :class="['bg-white rounded-lg shadow-md shadow-blue-600 overflow-hidden', animateShake ? 'animate-shake' : '']">
                            <div class="w-full border-b border-gray-400 p-2">
                                <Title :title="title" />
                            </div>
                            <div class="p-2">
                                <slot name="content" />
                            </div>
                            <div class="bg-gray-200 flex justify-end items-center p-3 gap-2">
                                <SecondaryButton @click="toggleActive">CLOSE</SecondaryButton>
                                <slot name="buttons" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
</template>

<style scoped>
@keyframes shake {
  0% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  50% { transform: translateX(5px); }
  75% { transform: translateX(-5px); }
  100% { transform: translateX(0); }
}
.animate-shake {
  animation: shake 0.5s infinite;
}
</style>
