<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { inject, ref, watch } from 'vue'

const props = defineProps<{ 
  expenditures: any,
  metrics: {
    last_expenditure: string|null,
    total_this_month: number,
    total_this_year: number
  },
  filters: { search?: string }
}>()

const search = ref(props.filters.search || '')
const $swal = inject('swal') as any

watch(search, (val) => {
  router.get(
    '/expenditures',
    { search: val },
    { preserveState: true, replace: true }
  )
})

const deleteExpenditure = async (id: number) => {
  const result = await $swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Data pengeluaran yang dihapus tidak bisa dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })
  if (result.isConfirmed) {
    router.delete(`/expenditures/${id}`)
  }
}
</script>

<template>
  <Head title="Transaksi Pengeluaran" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition">
            Dashboard Aplikasi
          </h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">
          Transaksi Pengeluaran
        </h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Terakhir Pengeluaran</h3>
          <p class="text-2xl font-bold mt-2">
            {{ props.metrics.last_expenditure ? new Date(props.metrics.last_expenditure).toLocaleDateString() : '-' }}
          </p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Total Bulan Ini</h3>
          <p class="text-2xl font-bold mt-2 text-green-400">
            Rp {{ Number(props.metrics.total_this_month).toLocaleString() }}
          </p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Total Tahun Ini</h3>
          <p class="text-2xl font-bold mt-2 text-yellow-400">
            Rp {{ Number(props.metrics.total_this_year).toLocaleString() }}
          </p>
        </div>
      </div>

      <!-- Action & Filter -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Daftar Pengeluaran</h1>
        <Link
          href="/expenditures/create"
          class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white"
        >
          + Tambah Pengeluaran
        </Link>
      </div>

      <div class="filters mb-4 flex gap-2 flex-wrap">
        <input
          v-model="search"
          type="text"
          placeholder="Cari catatan pengeluaran..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2 text-start">#</th>
            <th class="p-2 text-start">Tanggal</th>
            <th class="p-2 text-start">User</th>
            <th class="p-2 text-end">Nominal</th>
            <th class="p-2 text-start">Catatan</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(exp, i) in props.expenditures.data"
            :key="exp.id"
            class="hover:bg-white/10 transition"
          >
            <td class="p-2">{{ props.expenditures.from + i }}</td>
            <td class="p-2">{{ new Date(exp.date).toLocaleDateString() }}</td>
            <td class="p-2">{{ exp.user?.name }}</td>
            <td class="p-2 text-end text-red-400">
              Rp {{ exp.amount.toLocaleString('id-ID') }}
            </td>
            <td class="p-2">{{ exp.note || '-' }}</td>
            <td class="p-2">
              <div class="flex gap-2 justify-end">
                <button
                  @click="deleteExpenditure(exp.id)"
                  class="px-2 py-1 bg-red-600 hover:bg-red-500 rounded text-white"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="props.expenditures.data.length === 0">
            <td colspan="6" class="text-center text-gray-400 p-4">
              Belum ada data pengeluaran
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.expenditures.links" :data="props.expenditures" />
    </div>
  </AuthenticatedLayout>
</template>
