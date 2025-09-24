<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref } from 'vue'

const props = defineProps<{
  registers: any,
  filters: { from: string, to: string },
  metrics: {
    opening_cash: number,
    closing_cash: number,
    current_cash: number,
    total_transactions: number
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
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
          <Link :href="route('reports.index')">
            <h2 class="text-xl font-semibold text-gray-200 hover:text-yellow-500 transition">Laporan</h2>
          </Link>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
          <h2 class="text-xl font-semibold text-gray-200 underline">Laporan Kas</h2>
        </div>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Kas Awal</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.opening_cash).toLocaleString() }}</p>
          <p class="text-sm">Total kas awal sesuai filter</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Kas Saat Ini</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.current_cash).toLocaleString() }}</p>
          <p class="text-sm">Total kas saat ini sesuai filter</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Kas Akhir</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.closing_cash).toLocaleString() }}</p>
          <p class="text-sm">Total kas akhir sesuai filter</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Jumlah Transaksi</h3>
          <p class="text-2xl font-bold">{{ metrics.total_transactions }}</p>
          <p class="text-sm">Jumlah semua transaksi sesuai filter</p>
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

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Kasir</th>
            <th class="p-2">Kas Awal</th>
            <th class="p-2">Kas Akhir</th>
            <th class="p-2">Total Penjualan</th>
            <th class="p-2">Total Kas</th>
            <th class="p-2">Buka Kas</th>
            <th class="p-2">Tutup Kas</th>
            <th class="p-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r,i) in props.registers.data" :key="r.id" class="hover:bg-white/10 transition">
            <td class="p-2">{{ props.registers.from + i }}</td>
            <td class="p-2">{{ r.user?.name }}</td>
            <td class="p-2">Rp {{ Number(r.opening_amount).toLocaleString() }}</td>
            <td class="p-2">Rp {{ Number(r.closing_amount).toLocaleString() }}</td>
            <td class="p-2">Rp {{ Number(r.total_sales).toLocaleString() }}</td>
            <td class="p-2">Rp {{ Number(r.total_amount).toLocaleString() }}</td>
            <td class="p-2">{{ new Date(r.opened_at).toLocaleString() }}</td>
            <td class="p-2">{{ r.closed_at ? new Date(r.closed_at).toLocaleString() : '-' }}</td>
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
