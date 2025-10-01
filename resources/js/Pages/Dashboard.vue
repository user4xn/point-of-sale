<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { inject, ref } from 'vue'

const page = usePage();
const $swal = inject('swal') as any
const role = page.props.auth.user?.role || 'cashier';

const props = defineProps<{
  dashboard: any
}>()

const dashboard = ref(props.dashboard)

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
          <h3 class="text-sm font-medium">Top Produk (5 Besar)</h3>
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
