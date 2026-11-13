<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Modal from '@/Components/Modal.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { inject, ref } from 'vue'

const page = usePage();
const $swal = inject('swal') as any
const role = page.props.auth.user?.role || 'cashier';

const props = defineProps<{
  dashboard: any
  owner_summary?: {
    as_of: string
    month_start: string
    previous_start: string
    previous_end: string
    net_sales: number
    previous_net_sales: number
    change_percent: number | null
    transactions: number
    average_order_value: number
    expenses: number
    estimated_stock_value: number
    trend: { date: string; net_sales: number }[]
  }
}>()

const money = (value: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 2 }).format(value)

const dashboard = ref(props.dashboard)
const showOwnerSummary = ref(false)

const checkRegister = async () => {
  const res = await fetch(route('cash-register.check'))
  return await res.json()
}

const handleAppClick = async (app: any) => {
  if (app.name === 'Mode Kasir') {
    const { open } = await checkRegister()
    if (!open) {
      await $swal.fire({
        title: 'Kas belum dibuka',
        text: 'Silakan buka kas terlebih dahulu sebelum transaksi.',
        icon: 'warning',
        confirmButtonText: 'OK'
      })
      return
    }
  }

  if (app.route) {
    router.visit(route(app.route))
  }
}

const styleIconMetrics = 'absolute -right-6 -top-6 w-64 h-64 text-white/10'

const apps = ref([
  {
    name: 'Produk',
    route: 'products.index',
    color: 'bg-green-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package-search-icon lucide-package-search"><path d="M21 10V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l2-1.14"/><path d="m7.5 4.27 9 5.15"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/><circle cx="18.5" cy="15.5" r="2.5"/><path d="M20.27 17.27 22 19"/></svg>
    `
  },
  {
    name: 'Supplier',
    route: 'suppliers.index',
    color: 'bg-teal-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-container-icon lucide-container"><path d="M22 7.7c0-.6-.4-1.2-.8-1.5l-6.3-3.9a1.72 1.72 0 0 0-1.7 0l-10.3 6c-.5.2-.9.8-.9 1.4v6.6c0 .5.4 1.2.8 1.5l6.3 3.9a1.72 1.72 0 0 0 1.7 0l10.3-6c.5-.3.9-1 .9-1.5Z"/><path d="M10 21.9V14L2.1 9.1"/><path d="m10 14 11.9-6.9"/><path d="M14 19.8v-8.1"/><path d="M18 17.5V9.4"/></svg>
    `
  },
  {
    name: 'Mode Kasir',
    route: 'transaction.cashier',
    color: 'bg-blue-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-basket-icon lucide-shopping-basket"><path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8a2 2 0 0 0 2-1.6l1.7-7.4"/><path d="M4.5 15.5h15"/><path d="m5 11 4-7"/><path d="m9 11 1 9"/></svg>
    `
  },
  {
    name: 'Transaksi',
    route: 'transaction.index',
    color: 'bg-orange-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-badge-dollar-sign-icon lucide-badge-dollar-sign"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 18V6"/></svg>
    `
  },
  {
    name: 'Stok Opname',
    route: 'stockopname.index',
    color: 'bg-cyan-800',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-calculator-icon lucide-calculator"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>
    `
  },
  {
    name: 'Laporan',
    route: 'reports.index',
    color: 'bg-purple-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text-icon lucide-file-text"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
    `
  },
  {
    name: 'Purchase Order (PO)',
    route: 'purchase-orders.index',
    color: 'bg-indigo-700',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-bag-icon lucide-shopping-bag"><path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.2V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6.667a2 2 0 0 0-.4-1.2l-2-2.667A2 2 0 0 0 17 2H7a2 2 0 0 0-1.6.8z"/></svg>
    `
  },
  {
    name: 'Purchase Retur (PR)',
    route: 'purchase-returns.index',
    color: 'bg-amber-400',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-archive-x-icon lucide-archive-x"><rect width="20" height="5" x="2" y="3" rx="1"/><path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/><path d="m9.5 17 5-5"/><path d="m9.5 12 5 5"/></svg>
    `
  },
  {
    name: 'Pengaturan Pengguna',
    route: 'users.index',
    color: 'bg-blue-400',
    cashier: false,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users-round-icon lucide-users-round"><path d="M18 21a8 8 0 0 0-16 0"/><circle cx="10" cy="8" r="5"/><path d="M22 20c0-3.37-2-6.5-4-8a5 5 0 0 0-.45-8.3"/></svg>

    `
  },
  {
    name: 'Pengaturan Toko',
    route: 'settings.edit',
    color: 'bg-red-600',
    cashier: false,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-sliders-horizontal-icon lucide-sliders-horizontal"><path d="M10 5H3"/><path d="M12 19H3"/><path d="M14 3v4"/><path d="M16 17v4"/><path d="M21 12h-9"/><path d="M21 19h-5"/><path d="M21 5h-7"/><path d="M8 10v4"/><path d="M8 12H3"/></svg>
    `
  },
  {
    name: 'Data Customer',
    route: 'customer.index',
    color: 'bg-purple-800',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-user-icon lucide-book-user"><path d="M15 13a3 3 0 1 0-6 0"/><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"/><circle cx="12" cy="8" r="2"/></svg>
    `
  },
   {
    name: 'Transaksi Pengeluaran',
    route: 'expenditures.index',
    color: 'bg-yellow-700',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-blocks-icon lucide-blocks"><path d="M10 22V7a1 1 0 0 0-1-1H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-5a1 1 0 0 0-1-1H2"/><rect x="14" y="2" width="8" height="8" rx="1"/></svg>
    `
  }
])
</script>

<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-200">Dashboard Aplikasi</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <button
          v-if="role === 'admin' && owner_summary"
          type="button"
          class="mb-6 min-h-14 w-full rounded-md bg-red-700 px-6 py-4 text-lg font-bold text-white hover:bg-red-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-400"
          aria-haspopup="dialog"
          :aria-expanded="showOwnerSummary"
          @click="showOwnerSummary = true"
        >
          CEK RINGKASAN OWNER
        </button>
        <Modal v-if="role === 'admin' && owner_summary" :show="showOwnerSummary" max-width="2xl" @close="showOwnerSummary = false">
        <section aria-labelledby="owner-summary-title" class="max-h-[80dvh] overflow-y-auto bg-gray-800 p-4 text-white sm:p-6">
          <div class="flex items-center justify-between gap-4">
            <h2 id="owner-summary-title" class="text-xl font-semibold">Ringkasan owner</h2>
            <button type="button" autofocus class="min-h-11 px-4 py-2 font-bold text-gray-200 hover:bg-gray-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-white" @click="showOwnerSummary = false">Tutup</button>
          </div>
          <p class="mt-2 text-sm text-gray-300">{{ owner_summary.month_start }} sampai {{ owner_summary.as_of }} (waktu aplikasi). Penjualan hanya transaksi paid, setelah diskon dan sebelum pajak.</p>
          <dl class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div><dt class="text-sm text-gray-300">Penjualan bersih bulan ini</dt><dd class="text-2xl font-semibold break-words">{{ money(owner_summary.net_sales) }}</dd></div>
            <div><dt class="text-sm text-gray-300">Pengeluaran bulan ini</dt><dd class="text-2xl font-semibold break-words">{{ money(owner_summary.expenses) }}</dd></div>
            <div><dt class="text-sm text-gray-300">Estimasi nilai persediaan</dt><dd class="text-2xl font-semibold break-words">{{ money(owner_summary.estimated_stock_value) }}</dd><dd class="text-sm text-gray-300">Stok × harga beli saat ini, bukan HPP historis.</dd></div>
            <div><dt class="text-sm text-gray-300">Jumlah transaksi paid bulan ini</dt><dd class="text-2xl font-semibold">{{ owner_summary.transactions }}</dd></div>
            <div><dt class="text-sm text-gray-300">Rata-rata penjualan bersih per transaksi</dt><dd class="text-2xl font-semibold break-words">{{ money(owner_summary.average_order_value) }}</dd></div>
          </dl>
          <p class="mt-4 text-sm text-gray-300">Pembanding {{ owner_summary.previous_start }} sampai {{ owner_summary.previous_end }} (hari dijepit ke akhir bulan): {{ money(owner_summary.previous_net_sales) }}.
            <span v-if="owner_summary.change_percent !== null">Perubahan {{ owner_summary.change_percent > 0 ? '+' : '' }}{{ owner_summary.change_percent }}%.</span>
            <span v-else>Persentase tidak tersedia karena penjualan periode pembanding nol atau negatif.</span>
          </p>
          <p class="mt-2 text-sm text-gray-300">Nilai ini bukan laba. Pengeluaran mengikuti tanggal pencatatan, bukan mutasi kas shift.</p>
          <table class="mt-6 w-full text-sm">
            <caption class="pb-2 text-left font-semibold">Tren penjualan bersih 14 hari, termasuk hari ini. Hari tanpa transaksi ditampilkan nol.</caption>
            <thead><tr class="border-b border-gray-600"><th scope="col" class="py-2 text-left">Tanggal</th><th scope="col" class="py-2 text-right">Penjualan bersih</th></tr></thead>
            <tbody><tr v-for="day in owner_summary.trend" :key="day.date" class="border-b border-gray-700"><th scope="row" class="py-2 text-left font-normal">{{ day.date }}</th><td class="py-2 text-right tabular-nums">{{ money(day.net_sales) }}</td></tr></tbody>
          </table>
        </section>
        </Modal>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <Link
            v-for="app in apps"
            :key="app.name"
            class="flex flex-col items-center justify-center h-40 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-200"
            :class="role == 'cashier' && !app.cashier ? ' opacity-50 bg-gray-600 pointer-events-none' : app.color"
            @click.prevent="handleAppClick(app)"
          >
            <div v-html="app.icon"></div>
            <span class="mt-3 font-semibold">{{ app.name }}</span>
          </Link>
        </div>
      </div>

      <!-- Metrics Section -->
      <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 bg-white/10 mt-6">

        <!-- Total Transaksi Hari Ini -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Total Transaksi Hari Ini</h3>
          <p class="text-2xl font-bold mt-2 relative">{{ dashboard.total_transactions }}</p>
        </div>

        <!-- Gross Sales -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Gross Sales</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.gross_sales).toLocaleString() }}</p>
        </div>

        <!-- Net Sales -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Net Sales</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.net_sales).toLocaleString() }}</p>
        </div>

        <!-- Average Transaction -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Rata-rata Transaksi</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.avg_transaction).toLocaleString() }}</p>
        </div>
      </div>

       <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 bg-white/10 mt-6">
        <!-- Kas Awal -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Kas Awal</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.opening_amount).toLocaleString() }}</p>
        </div>

        <!-- Kas Saat Ini -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Kas Saat Ini</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.current_cash).toLocaleString() }}</p>
        </div>

        <!-- Bank Saat Ini -->
        <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium relative">Kas Non Tunai Hari Ini</h3>
          <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.current_bank).toLocaleString() }}</p>
        </div>
      </div>

      <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Stock Alerts -->
        <div class="bg-red-800/50 p-4 shadow text-white border-b-4 border-red-500 overflow-hidden">
          <h3 class="text-sm font-medium">Stok Hampir Habis (!!!)</h3>
          <ul class="mt-2 text-sm space-y-1">
            <li v-for="(s, i) in dashboard.stock_alerts" :key="i" class="flex justify-between">
              <span>{{ s.name }}</span>
              <span class="font-bold text-red-400">{{ s.stock }}</span>
            </li>
          </ul>
        </div>
        <!-- Top Produk -->
        <div class="bg-blue-800/50 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
          <h3 class="text-sm font-medium">Top Produk (3 Besar)</h3>
          <ul class="mt-2 text-sm space-y-1">
            <li v-for="(p, i) in dashboard.top_products" :key="i" class="flex justify-between">
              <span>{{ i+1 }}. {{ p.name }}</span>
              <span class="font-bold">{{ p.qty_sold }}x</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
