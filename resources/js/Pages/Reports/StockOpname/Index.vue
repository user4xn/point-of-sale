<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

const props = defineProps<{
  filters: { from: string, to: string },
  metrics: { total_opname: number, total_items: number, total_loss: number, total_note: number },
  opnames: any
}>()

const from = ref(props.filters.from)
const to   = ref(props.filters.to)

watch([from, to], ([f, t]) => {
  router.get(
    route('reports.stockopname.index'),
    { from: f, to: t },
    { preserveState: true, replace: true }
  )
})
</script>

<template>
  <Head title="Laporan Stock Opname" />

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
        <Link :href="route('reports.index')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">
            Laporan
          </h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Laporan Stock Opname</h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <h1 class="text-xl font-bold mb-4">Laporan Stock Opname</h1>

      <!-- Filter -->
      <div class="flex gap-2 mb-6">
        <input type="date" v-model="from" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" />
        <input type="date" v-model="to" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white" />
        
        <!-- Export Excel -->
        <a
          :href="route('reports.stockopname.export', { from, to })"
          :disabled="!from || !to"
          class="px-4 py-2 bg-green-600 hover:bg-green-500 font-semibold rounded text-white transition"
        >
          Export Excel
        </a>
      </div>

      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Opname</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_opname }}</p>
          <p class="text-sm">Periode {{ from }} s/d {{ to }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Item Dicek</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_items }}</p>
          <p class="text-sm">Semua produk yang diopname</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Total Selisih</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_loss }}</p>
          <p class="text-sm">Qty Sistem - Qty Fisik</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm font-medium">Item dengan Catatan</h3>
          <p class="text-2xl font-bold mt-1">{{ metrics.total_note }}</p>
          <p class="text-sm">Butuh perhatian khusus</p>
        </div>
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">Tanggal</th>
            <th class="p-2 text-left">Petugas</th>
            <th class="p-2">Produk</th>
            <th class="p-2">Qty Sistem</th>
            <th class="p-2">Qty Fisik</th>
            <th class="p-2">Selisih</th>
            <th class="p-2">Catatan</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="opname in opnames.data" :key="opname.id" class="hover:bg-white/10 transition">
            <td class="p-2 text-center">{{ new Date(opname.created_at).toLocaleDateString() }}</td>
            <td class="p-2 text-left">{{ opname.user?.name }}</td>
            <td class="p-2">
              <ul>
                <li v-for="it in opname.items" :key="it.id">{{ it.product?.name }}</li>
              </ul>
            </td>
            <td class="p-2 text-center">
              <ul>
                <li v-for="it in opname.items" :key="it.id">{{ it.system_qty }}</li>
              </ul>
            </td>
            <td class="p-2 text-center">
              <ul>
                <li v-for="it in opname.items" :key="it.id">{{ it.physical_qty }}</li>
              </ul>
            </td>
            <td class="p-2 text-center">
              <ul>
                <li v-for="it in opname.items" :key="it.id">{{ it.difference }}</li>
              </ul>
            </td>
            <td class="p-2 text-right">
              <ul>
                <li v-for="it in opname.items" :key="it.id">{{ it.note || '-' }}</li>
              </ul>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>
</template>
