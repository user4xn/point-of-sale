<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import { ref, watch } from 'vue'

const props = defineProps<{
  products: any,
  filters: { search?: string },
  metrics: {
    total_products: number,
    active_products: number,
    inactive_products: number,
    low_stock: number,
    dead_products: number
  }
}>()

const search = ref(props.filters.search || '')

watch(search, (val) => {
  router.get(route('reports.products.index'), { search: val }, { preserveState: true, replace: true })
})

const handleExport = () => {
  window.open(route('reports.products.export'), '_blank')
}
</script>

<template>
  <Head title="Laporan Produk" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-col gap-2">
        <div class="flex gap-2 items-center">
          <Link :href="route('dashboard')">
            <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition">Dashboard Aplikasi</h2>
          </Link>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
          <Link :href="route('reports.index')">
            <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition">Laporan</h2>
          </Link>
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
          <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Laporan Produk</h2>
        </div>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Produk</h3>
          <p class="text-2xl font-bold">{{ metrics.total_products }}</p>
          <p class="text-sm">Jumlah semua produk</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Produk Aktif</h3>
          <p class="text-2xl font-bold">{{ metrics.active_products }}</p>
          <p class="text-sm">Semua produk yang aktif dijual</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Produk Nonaktif</h3>
          <p class="text-2xl font-bold">{{ metrics.inactive_products }}</p>
          <p class="text-sm">Semua produk yang tidak dijual</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Produk Stok Rendah</h3>
          <p class="text-2xl font-bold">{{ metrics.low_stock }}</p>
          <p class="text-sm">Produk dengan stok (&lt;=5)</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Produk Stok Mati</h3>
          <p class="text-2xl font-bold">{{ metrics.dead_products }}</p>
          <p class="text-sm">Produk yang tidak ada penjualan</p>
        </div>
      </div>

      <!-- Filter + Export -->
      <div class="flex justify-between items-center mb-4">
        <input v-model="search" type="text" placeholder="Cari produk / SKU..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white w-64"/>
        <button @click="handleExport" class="px-4 font-bold py-2 bg-blue-600 hover:bg-blue-500 rounded text-white">
          Export Excel
        </button>
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Produk</th>
            <th class="p-2">SKU</th>
            <th class="p-2">Kategori</th>
            <th class="p-2">Supplier</th>
            <th class="p-2 text-right">Stok</th>
            <th class="p-2 text-right">Harga Beli</th>
            <th class="p-2 text-right">Harga Jual</th>
            <th class="p-2 text-right">Margin</th>
            <th class="p-2">Status</th>
            <th class="p-2">Terjual</th>
            <th class="p-2">Stok Mati</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(p, i) in props.products.data" :key="p.id" class="hover:bg-white/10 transition">
            <td class="p-2">{{ props.products.from + i }}</td>
            <td class="p-2">{{ p.name }}</td>
            <td class="p-2 font-mono">{{ p.sku }}</td>
            <td class="p-2">{{ p.category?.name || '-' }}</td>
            <td class="p-2">{{ p.supplier?.name || '-' }}</td>
            <td class="p-2 text-right">
              <span :class="p.stock <= 5 ? 'text-red-400 font-bold' : ''">{{ p.stock }}</span>
            </td>
            <td class="p-2 text-right">Rp {{ Number(p.purchase_price).toLocaleString() }}</td>
            <td class="p-2 text-right">Rp {{ Number(p.sell_price).toLocaleString() }}</td>
            <td class="p-2 text-right">
              Rp {{ Number(p.sell_price - p.purchase_price).toLocaleString() }}
              ({{ (((p.sell_price - p.purchase_price) / p.purchase_price) * 100).toFixed(1) }}%)
            </td>
            <td class="p-2">
              <span :class="p.status === 'active' ? 'text-green-400' : 'text-red-400'">
                {{ p.status === 'active' ? 'Aktif' : 'Nonaktif' }}
              </span>
            </td>
            <td class="p-2">
                {{ p.transaction_items_count }}
            </td>
            <td class="p-2">
              <span :class="p.transaction_items_count == 0 && p.stock > 0 ? 'text-red-400 font-bold' : 'text-gray-400'">
                {{ p.transaction_items_count == 0 && p.stock > 0 ? 'Ya' : 'Tidak' }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.products.links" :data="props.products" />
    </div>
  </AuthenticatedLayout>
</template>
