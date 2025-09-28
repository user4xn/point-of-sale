<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, watch } from 'vue'

const props = defineProps<{
  customers: any,
  filters: { search?: string },
  metrics: {
    total_customers: number,
    total_points: number,
    total_spent: number,
    total_trx: number,
  },
  topCustomers: any[],
}>()

const search = ref(props.filters.search || '')

watch(search, (val) => {
  router.get(
    route('customers.index'),
    { search: val },
    { preserveState: true, replace: true }
  )
})
</script>

<template>
  <Head title="Customer" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Customer</h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <h1 class="text-xl font-bold mb-4">Customer</h1>

      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Customer</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_customers }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Poin</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_points }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Belanja</h3>
          <p class="text-2xl font-bold mt-1">Rp {{ Number(metrics.total_spent).toLocaleString() }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Transaksi</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_trx }}</p>
        </div>
      </div>

      <!-- Top Customers -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Top 3 Customer</h3>
        <ul class="divide-y divide-gray-700">
          <li v-for="c in topCustomers" :key="c.id" class="flex justify-between py-2">
            <span>{{ c.name }} <span class="text-gray-400">({{ c.phone }})</span></span>
            <span class="font-bold">Rp {{ Number(c.transactions_sum_grand_total).toLocaleString() }}</span>
          </li>
        </ul>
      </div>

      <!-- Filter -->
      <div class="filters mb-4 flex gap-2">
        <input
          v-model="search"
          type="text"
          placeholder="Cari nama/phone..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Nama</th>
            <th class="p-2">Phone</th>
            <th class="p-2">Email</th>
            <th class="p-2">Total Belanja</th>
            <th class="p-2">Total Transaksi</th>
            <th class="p-2">Points</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(c, i) in props.customers.data"
            :key="c.id"
            class="hover:bg-white/10 transition"
          >
            <td class="p-2">{{ props.customers.from + i }}</td>
            <td class="p-2">{{ c.name }}</td>
            <td class="p-2">{{ c.phone || '-' }}</td>
            <td class="p-2">{{ c.email || '-' }}</td>
            <td class="p-2">Rp {{ Number(c.transactions_sum_grand_total || 0).toLocaleString() }}</td>
            <td class="p-2">{{ c.transactions_count }}</td>
            <td class="p-2">{{ c.points }}</td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.customers.links" :data="props.customers" />
    </div>
  </AuthenticatedLayout>
</template>
