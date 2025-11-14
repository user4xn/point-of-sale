<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref } from 'vue'

const props = defineProps<{
  registers: any,
  filters: { from: string, to: string },
  metrics: {
    total_transaksi_bank: number,
    total_amount_bank: number,
    register_ada_transaksi: number
  }
}>()

const from = ref(props.filters.from)
const to = ref(props.filters.to)

const handleFilter = () => {
  router.get(route('reports.bank.index'), { from: from.value, to: to.value }, { preserveState: true, replace: true })
}
</script>

<template>
  <Head title="Laporan Non Tunai" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-2">
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

          <h2 class="text-xl font-semibold text-gray-200 underline">Laporan Non Tunai</h2>
        </div>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Transaksi</h3>
          <p class="text-2xl font-bold">{{ metrics.total_transaksi_bank }}</p>
        </div>

        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Penjualan Non Tunai</h3>
          <p class="text-2xl font-bold">Rp {{ Number(metrics.total_amount_bank).toLocaleString() }}</p>
        </div>

        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Register kas yang Ada Transaksi</h3>
          <p class="text-2xl font-bold">{{ metrics.register_ada_transaksi }}</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="flex justify-between mb-4">
        <div class="flex gap-2">
          <input type="date" v-model="from" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <input type="date" v-model="to" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <button @click="handleFilter" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-white">Filter</button>
        </div>
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50 text-left">
            <th class="p-2">#</th>
            <th class="p-2">Kasir</th>
            <th class="p-2">Total Transaksi Non Tunai</th>
            <th class="p-2">Total Penjualan</th>
            <th class="p-2">Detail Transaksi</th>
            <th class="p-2">Tanggal</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(r,i) in props.registers.data" :key="r.id" class="hover:bg-white/10 transition">
            <td class="p-2">{{ props.registers.from + i }}</td>
            <td class="p-2">{{ r.user?.name }}</td>

            <td class="p-2">
              {{ r.total_sales_noncash }}
            </td>

            <td class="p-2">
              Rp {{ Number(r.total_amount_noncash).toLocaleString() }}
            </td>

            <td class="p-2">
              <ul class="list-disc ml-4 text-gray-300 text-xs">
                <li v-for="t in r.bank_transactions" :key="t.id">
                  Rp {{ Number(t.amount).toLocaleString() }} — Trx #{{ t.transaction_id }}
                </li>
              </ul>
            </td>

            <td class="p-2">
              {{ new Date(r.opened_at).toLocaleString() }}
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.registers.links" :data="props.registers" />
    </div>
  </AuthenticatedLayout>
</template>
