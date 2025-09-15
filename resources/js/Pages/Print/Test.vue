<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const props = defineProps<{ struk: string }>();

const printNow = () => {
  window.location.href = `rawbt://print?text=${encodeURIComponent(props.struk)}`;
};
</script>

<template>
  <Head title="Test Print" />

  <div class="p-6 text-white">
    <h1 class="text-xl font-bold mb-4">Test Print Struk</h1>
    <pre class="bg-gray-800 p-4 rounded mb-4">{{ props.struk }}</pre>
    <button
      @click="printNow"
      class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white"
    >
      Cetak Struk Test
    </button>
  </div>
</template>