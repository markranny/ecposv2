<script setup>
import { Head } from '@inertiajs/vue3';
import Main from "@/Layouts/AdminPanel.vue";
import ImportComponent from "@/Components/ImportComponent.vue";

defineProps({
  auth: {
    type: Object,
    required: true,
  },
});
</script>

<template>
  <Head title="Import Products" />

  <Main active-tab="RETAILITEMS">
    <template v-slot:main>
      <div class="container mx-auto py-8">
        <div class="bg-white shadow rounded-lg p-6">
          <h1 class="text-2xl font-bold mb-6">Import Products</h1>
          
          <p class="mb-4">
            Upload an Excel file to import products to the system. Make sure your file contains the following columns:
          </p>
          
          <div class="mb-6 p-4 bg-gray-100 rounded">
            <ul class="list-disc pl-5">
              <li>PRODUCTCODE - Product code/ID (required)</li>
              <li>DESCRIPTION - Product name/description (required)</li>
              <li>BARCODE - Product barcode (optional)</li>
              <li>CATEGORY - Product category</li>
              <li>RETAILGROUP - Retail group</li>
              <li>SRP - Selling price</li>
            </ul>
          </div>
          
          <ImportComponent />
        </div>
      </div>
    </template>
  </Main>
</template>