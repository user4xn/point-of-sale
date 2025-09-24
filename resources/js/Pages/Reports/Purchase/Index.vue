<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'

const props = defineProps<{
  orders: any,
  returns: any,
  filters: { from: string, to: string },
  metrics: {
    total_purchase: number,
    total_return: number,
    supplier_count: number,
    po_count: number,
    top_suppliers: any[]
  }
}>()

const from = ref(props.filters.from)
const to = ref(props.filters.to)

const showDetail = ref(false)
const detailOrder = ref<any>(null)

const fetchDetail = async (id:number) => {
  const res = await fetch(`/reports/purchase/${id}/detail`)
  const data = await res.json()
  if (data.success) {
    detailOrder.value = data.order
    showDetail.value = true
  }
}

const handleFilter = () => {
  router.get(route('reports.purchase.index'), { from: from.value, to: to.value }, { preserveState: true, replace: true })
}

const handleExport = () => {
  window.open(route('reports.purchase.export', { from: from.value, to: to.value }), '_blank')
}
</script>

<template>
  <Head title="Laporan Pembelian" />
  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2 items-center">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold text-gray-200 hover:text-yellow-500 transition">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <Link :href="route('reports.index')">
          <h2 class="text-xl font-semibold text-gray-200 hover:text-yellow-500 transition">Laporan</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h2 class="text-xl font-semibold text-gray-200 underline">Laporan Pembelian</h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Pembelian</h3>
          <p class="text-2xl font-bold">Rp {{ Number(props.metrics.total_purchase).toLocaleString() }}</p>
          <p class="text-sm">Nominal total pembelian barang</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Total Retur</h3>
          <p class="text-2xl font-bold">Rp {{ Number(props.metrics.total_return).toLocaleString() }}</p>
          <p class="text-sm">Nominal total barang retur</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Jumlah Supplier</h3>
          <p class="text-2xl font-bold">{{ props.metrics.supplier_count }}</p>
          <p class="text-sm">Semua supplier</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow">
          <h3 class="text-sm">Jumlah PO</h3>
          <p class="text-2xl font-bold">{{ props.metrics.po_count }}</p>
          <p class="text-sm">Akumulasi semua PO</p>
        </div>
      </div>

      <!-- Top Supplier -->
      <div class="mb-6">
        <h3 class="text-lg font-semibold mb-2">Top Supplier</h3>
        <table class="w-full text-sm border border-gray-600 rounded">
          <thead>
            <tr class="bg-gray-600/50">
              <th class="p-2">#</th>
              <th class="p-2">Supplier</th>
              <th class="p-2">Jumlah PO</th>
              <th class="p-2">Total Pembelian</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(s,i) in props.metrics.top_suppliers" :key="s.id" class="hover:bg-white/10">
              <td class="p-2">{{ i+1 }}</td>
              <td class="p-2">{{ s.supplier?.name }}</td>
              <td class="p-2">{{ s.total_po }}</td>
              <td class="p-2">Rp {{ Number(s.total_amount).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Filter -->
      <div class="flex justify-between mb-4">
        <div class="flex gap-2">
          <input type="date" v-model="from" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <input type="date" v-model="to" class="px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white"/>
          <button @click="handleFilter" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-white font-bold">Filter</button>
        </div>
        <button @click="handleExport" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white font-bold">Export Excel</button>
      </div>

      <!-- Table PO -->
      <h3 class="font-semibold mb-2">Riwayat Purchase Order</h3>
      <table class="w-full text-sm border border-gray-600 rounded mb-6">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Nomor PO</th>
            <th class="p-2">Tanggal</th>
            <th class="p-2">Supplier</th>
            <th class="p-2">Total</th>
            <th class="p-2">Status</th>
            <th class="p-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(o,i) in props.orders.data" :key="o.id" class="hover:bg-white/10">
            <td class="p-2">{{ props.orders.from + i }}</td>
            <td class="p-2">{{ o.po_number }}</td>
            <td class="p-2">{{ o.order_date }}</td>
            <td class="p-2">{{ o.supplier?.name }}</td>
            <td class="p-2">Rp {{ Number(o.total).toLocaleString() }}</td>
            <td class="p-2">{{ o.status }}</td>
            <td class="p-2 text-right">
              <button @click="fetchDetail(o.id)" class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 rounded text-white">Detail</button>
            </td>
          </tr>
        </tbody>
      </table>
      <Pagination :links="props.orders.links" :data="props.orders" />

      <!-- Retur -->
      <h3 class="font-semibold mb-2">Riwayat Retur Pembelian</h3>
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2">#</th>
            <th class="p-2">Nomor Retur</th>
            <th class="p-2">Tanggal</th>
            <th class="p-2">Supplier</th>
            <th class="p-2">Total</th>
            <th class="p-2">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(r,i) in props.returns" :key="r.id" class="hover:bg-white/10">
            <td class="p-2">{{ i+1 }}</td>
            <td class="p-2">{{ r.return_number }}</td>
            <td class="p-2">{{ r.return_date }}</td>
            <td class="p-2">{{ r.supplier?.name }}</td>
            <td class="p-2">Rp {{ Number(r.total).toLocaleString() }}</td>
            <td class="p-2">{{ r.status }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AuthenticatedLayout>

  <!-- Modal Detail -->
  <Modal :show="showDetail" max-width="2xl" @close="showDetail = false">
    <div class="p-6 text-white">
      <h2 class="text-xl font-bold mb-4">Detail Purchase Order</h2>
      <div v-if="detailOrder">
        <p><strong>Nomor PO:</strong> {{ detailOrder.po_number }}</p>
        <p><strong>Supplier:</strong> {{ detailOrder.supplier?.name }}</p>
        <p><strong>Tanggal:</strong> {{ detailOrder.order_date }}</p>
        <p><strong>Total:</strong> Rp {{ Number(detailOrder.total).toLocaleString() }}</p>
        <p><strong>Status:</strong> {{ detailOrder.status }}</p>

        <h3 class="mt-4 font-semibold">Items</h3>
        <table class="w-full text-sm mt-2 border border-gray-700">
          <thead>
            <tr class="bg-gray-700">
              <th class="p-2 text-left">Produk</th>
              <th class="p-2 text-right">Qty</th>
              <th class="p-2 text-right">Harga Beli</th>
              <th class="p-2 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in detailOrder.items" :key="item.id" class="border-t border-gray-700">
              <td class="p-2">{{ item.product?.name }}</td>
              <td class="p-2 text-right">{{ item.quantity }}</td>
              <td class="p-2 text-right">Rp {{ Number(item.price).toLocaleString() }}</td>
              <td class="p-2 text-right">Rp {{ Number(item.subtotal).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="mt-4 flex justify-end">
        <button @click="showDetail = false" class="px-3 py-1 bg-red-600 hover:bg-red-500 rounded text-white">Tutup</button>
      </div>
    </div>
  </Modal>
</template>
