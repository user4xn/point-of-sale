<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref } from 'vue'

const props = defineProps<{
  registers: any,
  filters: { from: string, to: string },
  metrics: {
    total_selisih: number,
    amount_selisih: number,
    selisih_bulan: number,
    late_close: number
  }
}>()

const from = ref(props.filters.from)
const to = ref(props.filters.to)

const handleFilter = () => {
  router.get(route('reports.cash.index'), { from: from.value, to: to.value }, { preserveState: true, replace: true })
}

const handleExport = () => {
  window.open(route('reports.cash.export', { from: from.value, to: to.value }), '_blank')
}

const toYMD = (dateString: string) => {
  const d = new Date(dateString);
  return d.toISOString().split("T")[0];
}
</script>

<template>
  <Head title="Laporan Kas" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-2">
        <!-- Breadcrumb -->
        <div class="flex gap-2 items-center">
          <Link :href="route('dashboard')">
            <h2 class="text-xl font-semibold text-gray-200 hover:text-yellow-500 transition">Dashboard Aplikasi</h2>
          </Link>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
          </svg>
          <Link :href="route('reports.index')">
            <h2 class="text-xl font-semibold text-gray-200 hover:text-yellow-500 transition">Laporan</h2>
          </Link>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
          </svg>
          <h2 class="text-xl font-semibold text-gray-200 underline">Laporan Kas</h2>
        </div>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Kas Selisih</h3>
          <p class="text-2xl font-bold">{{ metrics.total_selisih }}</p>
          <p class="text-sm">Jumlah register dengan selisih</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Jumlah Selisih Keseluruhan</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.amount_selisih).toLocaleString() }}</p>
          <p class="text-sm">Akumulasi selisih</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Selisih Bulan Ini</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.selisih_bulan).toLocaleString() }}</p>
          <p class="text-sm">Total selisih bulan berjalan</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Kas Terlambat Ditutup</h3>
          <p class="text-2xl font-bold">{{ metrics.late_close }}</p>
          <p class="text-sm">Ditutup > 1 hari</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="flex justify-between mb-4">
        <div class="flex gap-2">
          <input type="date" v-model="from" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <input type="date" v-model="to" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <button @click="handleFilter" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-white">Filter</button>
        </div>
        <button @click="handleExport" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 font-bold rounded text-white">Export Excel</button>
      </div>

      <p class="text-sm mb-2 text-gray-400">
        *Selisih (- *jika ada) kas akan muncul ketika kas sudah tertutup, non tunai tidak masuk hitungan selisih.
      </p>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">ID/Kasir</th>
            <th class="p-2">Input Kas Awal</th>
            <th class="p-2">Input Kas Akhir</th>
            <th class="p-2">Total Transaksi</th>
            <th class="p-2">Penjualan Masuk</th>
            <th class="p-2">Selisih Kas</th>
            <th class="p-2">Total Penjualan</th>
            <th class="p-2">Keterangan</th>
            <th class="p-2">Buka/Tutup Kas</th>
            <th class="p-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r,i) in props.registers.data" :key="r.id" class="hover:bg-white/10 transition">
            <td class="p-2">{{ props.registers.from + i }}</td>
            <td class="p-2">
              <span class="p-2 bg-white/10 font-bold rounded-full">
                #{{ r.id }}
              </span>
              {{ r.user?.name }}
            </td>
            <td class="p-2">Rp {{ Number(r.opening_amount).toLocaleString() }}</td>
            <td class="p-2">Rp {{ Number(r.closing_amount).toLocaleString() }}</td>
            <td class="p-2">
              Tunai: {{ parseInt(r.total_sales) }}
              <div class="text-gray-400 text-sm">
                Non Tunai: {{ parseInt(r.total_sales_noncash) }}
              </div>
            </td>
            <td class="p-2">
              Tunai: Rp {{ Number(r.total_amount).toLocaleString() }}
              <div class="text-gray-400 text-sm">
                Non Tunai: Rp {{ Number(r.total_amount_noncash).toLocaleString() }}
              </div>
            </td>
            <td class="p-2" :class="Number((r.selisih_amount)) < 0 ? 'text-red-400' : 'text-green-400'">
              <div v-if="r.status === 'closed'">
                {{ Number(Math.min(r.selisih_amount, 0)).toLocaleString() }}
              </div>
              <div v-else>
                
              </div>
            </td>
            <td>Rp {{ Number((r.total_all_amount ?? 0)).toLocaleString() }}</td>
            <td class="p-2">
              <span v-if="r.status === 'open'" class="text-yellow-400">Belum Ditutup</span>
              <span v-else-if="toYMD(r.closed_at) !== toYMD(r.opened_at)" class="text-red-400">Terlambat</span>
              <span v-else class="text-green-400">Tepat Waktu</span>
            </td>
            <td class="p-2 text-sm">
              {{ toYMD(r.opened_at) }}
              <div class="text-gray-400">
                {{ r.closed_at ? toYMD(r.closed_at).toLocaleString() : '-' }}
              </div>
            </td>
            <td class="p-2">
              <span :class="r.status === 'open' ? 'text-green-400' : 'text-red-400'">
                {{ r.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.registers.links" :data="props.registers" />
    </div>
  </AuthenticatedLayout>
</template>
