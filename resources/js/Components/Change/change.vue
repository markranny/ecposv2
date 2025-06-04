<template>
    <div v-if="isVisible" class="modal-overlay">
      <div class="modal-content">
        <span class="close" @click="closeModal">&times;</span>
        <h2>Order Submitted Successfully!</h2>
        <p>Cash Change: {{ cashChange.toFixed(2) }}</p>
        <button @click="closeModal">OK</button>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    props: {
      isVisible: {
        type: Boolean,
        required: true,
      },
      cashChange: {
        type: Number,
        required: true,
      },
    },
    emits: ['close'],
    methods: {
      closeModal() {
        this.$emit('close');
      },
    },
  };
  </script>
  
  <style>
  .modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .modal-content {
    background: white;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
  }
  .close {
    cursor: pointer;
  }
  </style>
  