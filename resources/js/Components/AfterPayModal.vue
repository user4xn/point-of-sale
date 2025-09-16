<script setup lang="ts">
const props = defineProps<{
  show: boolean
  change: number
  trxId: number | null
}>()

const emit = defineEmits(['close', 'print'])
</script>

<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/60 flex items-center justify-center z-50"
  >
    <div class="bg-gray-800 text-white p-8 rounded-lg w-[400px] text-center">
      <h2 class="text-2xl font-bold mb-4">Transaksi Berhasil 🎉</h2>
      <p class="text-yellow-300 text-[40px] font-bold mb-6">
        Kembalian: Rp{{ change.toLocaleString('id-ID') }}
      </p>

      <div class="flex gap-4 justify-center">
        <button
          class="px-6 py-3 bg-green-600 rounded-xl text-lg font-bold"
          @click="$emit('print', trxId)"
        >
          Print Struk
        </button>
        <button
          class="px-6 py-3 bg-gray-600 rounded-xl text-lg font-bold"
          @click="$emit('close')"
        >
          Tutup
        </button>
      </div>
    </div>
  </div>
</template>
