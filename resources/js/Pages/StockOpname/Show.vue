<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, inject } from 'vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{ opname: any }>()
const $swal = inject('swal') as any

const isExpired = computed(() => {
  const created = new Date(props.opname.created_at)
  const now = new Date()
  const diffDays = Math.floor((now.getTime() - created.getTime()) / (1000 * 60 * 60 * 24))
  return props.opname.status === 'draft' && diffDays >= 7
})

const confirmOpname = (id: number) => {
  $swal.fire({
    title: 'Konfirmasi Opname?',
    text: 'Stok produk akan diperbarui sesuai hasil opname!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#16a34a',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Konfirmasi',
    cancelButtonText: 'Batal',
  }).then((res: any) => {
    if (res.isConfirmed) {
      router.post(`/stock-opnames/${id}/confirm`)
    }
  })
}
</script>

<template>
  <Head title="Detail Stock Opname" />

  <div class="p-6 text-white">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Detail Stock Opname</h1>
      <Link
        href="/stock-opnames"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        ← Kembali
      </Link>
    </div>

    <!-- Info -->
    <div class="grid grid-cols-2 gap-4 mb-6">
      <div>
        <p><strong>Tanggal:</strong> {{ new Date(props.opname.created_at).toLocaleString() }}</p>
        <p><strong>Petugas:</strong> {{ props.opname.user?.name }}</p>
      </div>
      <div>
        <p><strong>Tipe:</strong> {{ props.opname.type }}</p>
        <p><strong>Status:</strong>&nbsp;
          <span
            :class="[
              props.opname.status === 'confirmed'
                ? 'text-green-400'
                : isExpired
                ? 'text-red-400'
                : 'text-yellow-400'
            ]"
          >
            {{ isExpired ? 'expired' : props.opname.status }}
          </span>
        </p>
      </div>
    </div>

    <p class="mb-4"><strong>Catatan:</strong> {{ props.opname.note || '-' }}</p>
    <p class="mb-4">
      <span class="bg-red-500/10 text-red-500 px-3 py-1 rounded-full">Data opname telah melewati masa expired (7 hari), silahkan lakukan stok opname kembali!</span>
    </p>

    <!-- Items Table -->
    <table class="w-full text-sm border border-gray-600 rounded mb-6">
      <thead class="bg-gray-700">
        <tr>
          <th class="p-2 text-left">Produk</th>
          <th class="p-2 text-center">Stok Sistem</th>
          <th class="p-2 text-center">Stok Fisik</th>
          <th class="p-2 text-center">Selisih</th>
          <th class="p-2 text-left">Catatan</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in props.opname.items"
          :key="item.id"
          class="border-t border-gray-600"
        >
          <td class="p-2">{{ item.product?.name }}</td>
          <td class="p-2 text-center">{{ item.system_qty }}</td>
          <td class="p-2 text-center">{{ item.physical_qty }}</td>
          <td
            class="p-2 text-center"
            :class="item.difference !== 0 ? 'text-yellow-400' : ''"
          >
            {{ item.difference }}
          </td>
          <td class="p-2">{{ item.note || '-' }}</td>
        </tr>
      </tbody>
    </table>

    <span
      class="italic text-gray-400"
    >Note: konfirmasi opname akan memperbarui stok produk, harap periksa kembali sebelum konfirmasi</span>

    <!-- Action -->
    <div class="flex justify-end gap-3 mt-4">
      <button
        v-if="props.opname.status !== 'confirmed'"
        @click="confirmOpname(props.opname.id)"
        class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded text-white disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="isExpired"
      >
        Konfirmasi
      </button>
      <Link
        href="/stock-opnames"
        class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        Tutup
      </Link>
    </div>
  </div>
</template>
