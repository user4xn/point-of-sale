<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref, watch } from 'vue'

const props = defineProps<{
  transactions: any,
  filters: { from?: string, to?: string, cashier?: string },
  dashboard: any
}>()

const from = ref(props.filters.from || '')
const to = ref(props.filters.to || '')
const cashier = ref(props.filters.cashier || '')

watch([from, to, cashier], () => {
  router.get(
    route('reports.sales.index'),
    { from: from.value, to: to.value, cashier: cashier.value },
    { preserveState: true, replace: true }
  )
})
</script>

<template>
  <Head title="Laporan Penjualan" />

  <AuthenticatedLayout>
    <!-- Header -->
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
        <Link :href="route('reports.index')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition">
            Laporan
          </h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">
          Laporan Penjualan
        </h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <h1 class="text-xl font-bold mb-4">Laporan Penjualan</h1>

      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Hari Ini</h3>
          <p class="text-2xl font-bold mt-1">{{ dashboard.today.total_transactions }} trx</p>
          <p class="text-sm">Rp {{ Number(dashboard.today.total_sales).toLocaleString() }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Minggu Ini</h3>
          <p class="text-2xl font-bold mt-1">{{ dashboard.week.total_transactions }} trx</p>
          <p class="text-sm">Rp {{ Number(dashboard.week.total_sales).toLocaleString() }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Bulan Ini</h3>
          <p class="text-2xl font-bold mt-1">{{ dashboard.month.total_transactions }} trx</p>
          <p class="text-sm">Rp {{ Number(dashboard.month.total_sales).toLocaleString() }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">All Time</h3>
          <p class="text-2xl font-bold mt-1">{{ dashboard.alltime.total_transactions }} trx</p>
          <p class="text-sm">Rp {{ Number(dashboard.alltime.total_sales).toLocaleString() }}</p>
        </div>
      </div>

      <!-- Filter -->
      <div class="filters mb-4 flex flex-wrap gap-2 items-center">
        <input
          type="date"
          v-model="from"
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />
        <input
          type="date"
          v-model="to"
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />

        <!-- Export Excel -->
        <a
          :href="(!from || !to) ? '#' : route('reports.sales.export', { from, to })"
          :class="!from || !to ? 'bg-gray-500' : 'bg-green-600 hover:bg-green-500'"
          class="px-4 py-2 font-semibold rounded text-white transition"
        >
          Export Excel
        </a>
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Invoice</th>
            <th class="p-2">Tanggal</th>
            <th class="p-2">Kasir</th>
            <th class="p-2">Customer</th>
            <th class="p-2">Metode Bayar</th>
            <th class="p-2">Grand Total</th>
            <th class="p-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(trx, i) in props.transactions.data"
            :key="trx.id"
            class="hover:bg-white/10 transition"
          >
            <td class="p-2">{{ props.transactions.from + i }}</td>
            <td class="p-2 font-mono">{{ trx.invoice_number }}</td>
            <td class="p-2">{{ new Date(trx.created_at).toLocaleString() }}</td>
            <td class="p-2">{{ trx.user?.name }}</td>
            <td class="p-2">{{ trx.customer_name || '-' }}</td>
            <td class="p-2 font-mono" :class="trx.payment_method === 'cash' ? 'text-green-400' : 'text-red-400'">{{ trx.payment_method == 'cash' ? 'Tunai' : 'Non Tunai' }}</td>
            <td class="p-2">Rp {{ Number(trx.grand_total).toLocaleString() }}</td>
            <td class="p-2">
              <span :class="trx.status === 'paid' ? 'text-green-400' : 'text-red-400'">
                {{ trx.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.transactions.links" :data="props.transactions" />
    </div>
  </AuthenticatedLayout>
</template>
