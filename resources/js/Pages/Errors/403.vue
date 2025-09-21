<script setup lang="ts">
import { inject, onMounted } from 'vue'

const props = defineProps<{ message?: string }>()
const $swal = inject('swal') as any

onMounted(() => {
  $swal.fire('Akses Ditolak', props.message ?? 'Anda tidak punya izin.', 'error')
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen">
    <h1 class="text-4xl font-bold text-red-600 mb-2">Forbidden</h1>
    <p>{{ props.message }}</p>
  </div>
</template>
