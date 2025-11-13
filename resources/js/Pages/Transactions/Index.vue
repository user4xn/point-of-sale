<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import { ref, watch } from 'vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'
import { buildReceipt } from "@/utils/print-formatter"

const props = defineProps<{
  transactions: any,
  filters: { search?: string },
  dashboard: {
    today : {
      total_transactions: number,
      total_sales: number,
      total_items: number,
      avg_transaction: number,
      payment: {
        cash: number,
        noncash: number,
      }
    },
    week : {
      total_transactions: number,
      total_sales: number,
      payment: {
        cash: number,
        noncash: number,
      }
    },
    month : {
      total_transactions: number,
      total_sales: number,
      payment: {
        cash: number,
        noncash: number,
      }
    },
    alltime : {
      total_transactions: number,
      total_sales: number,
      payment: {
        cash: number,
        noncash: number,
      }
    }
  }
}>()

const search = ref(props.filters.search || '')

watch(search, (val) => {
  router.get(
    '/transaction',
    { search: val },
    { preserveState: true, replace: true }
  )
})

const handlePrint = async (id: number | null) => {
  if (!id) return;

  try {
    if (!qz.websocket.isActive()) {
      await qz.websocket.connect()
      console.log("✅ Connected to QZ Tray")
    }

    const res = await axios.post(route("transaction.print.direct", id))
    const data = res.data

    const content = buildReceipt(data)

    let printer = await qz.printers.getDefault()
    if (!printer) {
      const printers = await qz.printers.find()
      console.log("📋 Printers detected:", printers)
      printer = printers[0]
    }
    const config = qz.configs.create(printer)
    await qz.print(config, [content])

    console.log("✅ Print sukses ke:", printer)
  } catch (e) {
    console.error("❌ Print gagal", e)
  }
}

const showDetail = ref(false)
const detailTrx = ref<any>(null)

const fetchTransaction = async (id: number) => {
  const res = await fetch(`/transaction/detail/${id}`)
  const data = await res.json()
  if (data.success) {
    detailTrx.value = data.transaction
    showDetail.value = true
  }
}
</script>

<template>
  <Head title="Transaksi" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Transaksi</h2>
      </div>
    </template>
    <div class="p-6 text-white">
      <h1 class="text-xl font-bold mb-4">Kasir - Transaksi</h1>

      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        <!-- Item Terjual -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white col-span-4">
          <h3 class="text-sm font-medium">Item Terjual Hari Ini</h3>
          <p class="text-2xl font-bold mt-2">{{ dashboard.today.total_items }}</p>
          <p class="text-sm text-gray-400">
            Rata-rata: Rp {{ Number(dashboard.today.avg_transaction).toLocaleString() }}
          </p>
        </div>

        <!-- Hari ini -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Transaksi Hari Ini</h3>
          <p class="text-lg mt-2">
            {{ dashboard.today.total_transactions }} trx
          </p>
          <p class="text-2xl font-bold">
            Rp {{ Number(dashboard.today.total_sales).toLocaleString() }}
          </p>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Tunai</h3>
            <p class="text-2xl font-bold mt-2">
              Rp {{ Number(dashboard.today.payment.cash).toLocaleString() }}
            </p>
          </div>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Non Tunai</h3>
            <p class="text-2xl font-bold mt-2 text-red-400">
              Rp {{ Number(dashboard.today.payment.noncash).toLocaleString() }}
            </p>
          </div>
        </div>

        <!-- Mingguan -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Minggu Ini</h3>
          <p class="text-lg mt-2">
            {{ dashboard.week.total_transactions }} trx
          </p>
          <p class="text-2xl font-bold">
            Rp {{ Number(dashboard.week.total_sales).toLocaleString() }}
          </p>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Tunai</h3>
            <p class="text-2xl font-bold mt-2">
              Rp {{ Number(dashboard.week.payment.cash).toLocaleString() }}
            </p>
          </div>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Non Tunai</h3>
            <p class="text-2xl font-bold mt-2 text-red-400">
              Rp {{ Number(dashboard.week.payment.noncash).toLocaleString() }}
            </p>
          </div>
        </div>

        <!-- Bulanan -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Bulan Ini</h3>
          <p class="text-lg mt-2">
            {{ dashboard.month.total_transactions }} trx
          </p>
          <p class="text-2xl font-bold">
            Rp {{ Number(dashboard.month.total_sales).toLocaleString() }}
          </p>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Tunai</h3>
            <p class="text-2xl font-bold mt-2">
              Rp {{ Number(dashboard.month.payment.cash).toLocaleString() }}
            </p>
          </div>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Non Tunai</h3>
            <p class="text-2xl font-bold mt-2 text-red-400">
              Rp {{ Number(dashboard.month.payment.noncash).toLocaleString() }}
            </p>
          </div>
        </div>

        <!-- All Time -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">All Time</h3>
          <p class="text-lg mt-2">
            {{ dashboard.alltime.total_transactions }} trx
          </p>
          <p class="text-2xl font-bold">
            Rp {{ Number(dashboard.alltime.total_sales).toLocaleString() }}
          </p>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Tunai</h3>
            <p class="text-2xl font-bold mt-2">
              Rp {{ Number(dashboard.alltime.payment.cash).toLocaleString() }}
            </p>
          </div>

          <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
            <h3 class="text-sm font-medium">Non Tunai</h3>
            <p class="text-2xl font-bold mt-2 text-red-400">
              Rp {{ Number(dashboard.alltime.payment.noncash).toLocaleString() }}
            </p>
          </div>
        </div>
      </div>

      <!-- Filter -->
      <div class="filters mb-4 flex gap-2">
        <input
          v-model="search"
          type="text"
          placeholder="Cari No. Invoice..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2 text-start">#</th>
            <th class="p-2 text-start">Invoice</th>
            <th class="p-2 text-start">Metode Bayar</th>
            <th class="p-2 text-start">Tanggal</th>
            <th class="p-2 text-start">Kasir</th>
            <th class="p-2 text-start">Customer</th>
            <th class="p-2 text-start">Total</th>
            <th class="p-2 text-start">Status</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(trx, i) in props.transactions.data"
            :key="trx.id"
            class="hover:bg-white/10 transition"
            @click="fetchTransaction(trx.id)"
          >
            <td class="p-2">{{ props.transactions.from + i }}</td>
            <td class="p-2 font-mono">{{ trx.invoice_number }}</td>
            <td class="p-2 font-mono" :class="trx.payment_method === 'cash' ? 'text-green-400' : 'text-red-400'">{{ trx.payment_method == 'cash' ? 'Tunai' : 'Non Tunai' }}</td>
            <td class="p-2">{{ new Date(trx.created_at).toLocaleString() }}</td>
            <td class="p-2">{{ trx.user?.name }}</td>
            <td class="p-2">{{ trx.customer_name || '-' }}</td>
            <td class="p-2">Rp {{ Number(trx.grand_total).toLocaleString() }}</td>
            <td class="p-2">
              <span
                :class="trx.status === 'paid' ? 'text-green-400' : 'text-red-400'"
              >
                {{ trx.status }}
              </span>
            </td>
            <td class="p-2">
              <div class="flex justify-end">
                <button
                    @click="fetchTransaction(trx.id)"
                    class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 rounded text-white"
                  >
                    Detail
                  </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.transactions.links" :data="props.transactions" />
    </div>
  </AuthenticatedLayout>

  <!-- Modal Detail -->
  <Modal :show="showDetail" max-width="2xl" @close="showDetail = false">
    <div class="p-6 text-white">
      <h2 class="text-xl font-bold mb-4">Detail Transaksi</h2>

      <div v-if="detailTrx">
        <p><strong>Invoice:</strong> {{ detailTrx.invoice_number }}</p>
        <p><strong>Kasir:</strong> {{ detailTrx.user?.name }}</p>
        <p><strong>Customer:</strong> {{ detailTrx.customer_name || '-' }}</p>
        <p><strong>Total:</strong> Rp {{ Number(detailTrx.grand_total).toLocaleString() }}</p>
        <p><strong>Status:</strong> {{ detailTrx.status }}</p>
        <p><strong>Pembayaran:</strong> {{ detailTrx.payment_method == 'cash' ? 'Tunai' : 'Non Tunai' }}</p>

        <h3 class="mt-4 font-semibold">Items</h3>
        <table class="w-full text-sm mt-2 border border-gray-700">
          <thead>
            <tr class="bg-gray-700">
              <th class="p-2 text-left">Produk</th>
              <th class="p-2 text-right">Qty</th>
              <th class="p-2 text-right">Satuan</th>
              <th class="p-2 text-right">Harga</th>
              <th class="p-2 text-right">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in detailTrx.items"
              :key="item.id"
              class="border-t border-gray-700"
            >
              <td class="p-2">{{ item.product?.name }}</td>
              <td class="p-2 text-right">{{ item.quantity }}</td>
              <td class="p-2 text-right uppercase">{{ item.unit_name }}</td>
              <td class="p-2 text-right">Rp {{ Number(item.price).toLocaleString() }}</td>
              <td class="p-2 text-right">Rp {{ Number(item.subtotal).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 justify-end flex gap-2">
        <button
          @click="handlePrint(detailTrx.id)"
          class="px-3 py-1 bg-blue-600 hover:bg-blue-500 rounded text-white"
        >
          Print
        </button>
        <button
          @click="showDetail = false"
          class="px-3 py-1 bg-red-600 hover:bg-red-500 rounded text-white"
        >
          Tutup
        </button>
      </div>
    </div>
  </Modal>
</template>