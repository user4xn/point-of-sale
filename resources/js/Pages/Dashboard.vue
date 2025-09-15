<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import Swal from 'sweetalert2';
import { ref } from 'vue'

const page = usePage();
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
  if (app.name === 'Transaksi') {
    const { open } = await checkRegister()
    if (!open) {
      await Swal.fire({
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

const apps = ref([
  {
    name: 'Produk',
    route: 'products.index',
    color: 'bg-green-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 13V7a2 2 0 00-2-2h-4V3H10v2H6a2 2 0 00-2 2v6M4 19h16a2 2 0 002-2v-2H2v2a2 2 0 002 2z" />
      </svg>
    `
  },
  {
    name: 'Supplier',
    route: 'suppliers.index',
    color: 'bg-teal-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M20 13V7a2 2 0 00-2-2h-4V3H10v2H6a2 2 0 00-2 2v6M4 19h16a2 2 0 002-2v-2H2v2a2 2 0 002 2z" />
      </svg>
    `
  },
  {
    name: 'Transaksi',
    route: 'transaction.index',
    color: 'bg-blue-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 10h18M9 21h6m-3-11V3" />
      </svg>
    `
  },
  {
    name: 'Laporan',
    route: '',
    color: 'bg-purple-600',
    cashier: true,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 17v-6h13M9 5v2h13M3 10h3M3 6h3M3 14h3M3 18h3" />
      </svg>
    `
  },
  {
    name: 'Pengaturan Pengguna',
    route: 'users.index',
    color: 'bg-yellow-600',
    cashier: false,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
      </svg>

    `
  },
  {
    name: 'Pengaturan Toko',
    route: 'settings.edit',
    color: 'bg-red-600',
    cashier: false,
    icon: `
      <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M11.049 2.927c.3-1.14 1.902-1.14 2.202 0l.27 1.028a1 1 0 00.95.69h1.118c1.178 0 1.665 1.51.707 2.207l-.905.658a1 1 0 00-.364 1.118l.27 1.027c.3 1.14-.93 2.087-1.9 1.39l-.905-.658a1 1 0 00-1.176 0l-.905.658c-.97.697-2.2-.25-1.9-1.39l.27-1.027a1 1 0 00-.364-1.118l-.905-.658c-.958-.697-.471-2.207.707-2.207h1.118a1 1 0 00.95-.69l.27-1.028z" />
      </svg>
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

    <div class="px-8 py-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 bg-white/10">
      <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1m0 16v1m18-1v1M3 12h18M12 3v18" />
        </svg>
        <h3 class="text-sm font-medium relative">Total Transaksi Hari Ini</h3>
        <p class="text-2xl font-bold mt-2 relative">{{ dashboard.total_transactions }}</p>
      </div>

      <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.5 0-3 .5-4.2 1.5S6 12 6 13.5 7.5 16 9 16h6c1.5 0 3-.5 4.2-1.5S21 12 21 10.5 19.5 8 18 8H12z" />
        </svg>
        <h3 class="text-sm font-medium relative">Total Penjualan Hari Ini</h3>
        <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.total_sales).toLocaleString() }}</p>
      </div>

      <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 6V3m0 0L8 7m4-4l4 4m-4 10v3m0 0l-4-4m4 4l4-4" />
        </svg>
        <h3 class="text-sm font-medium relative">Kas Awal</h3>
        <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.opening_amount).toLocaleString() }}</p>
      </div>

      <div class="relative bg-gray-800 p-4 shadow text-white border-b-4 border-white/20 overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/10" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <h3 class="text-sm font-medium relative">Kas Saat Ini</h3>
        <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.current_cash).toLocaleString() }}</p>
      </div>

      <div v-if="dashboard.closing_amount" class="relative bg-red-600 p-4 shadow text-white overflow-hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute -right-6 -bottom-6 w-32 h-32 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <h3 class="text-sm font-medium relative">Kas Tutup</h3>
        <p class="text-2xl font-bold mt-2 relative">Rp {{ Number(dashboard.closing_amount).toLocaleString() }}</p>
      </div>
    </div>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <Link
            v-for="app in apps"
            :key="app.name"
            :href="app.route ? route(app.route) : '#'"
            class="flex flex-col items-center justify-center h-40 rounded-lg shadow-md text-white hover:scale-105 transition-transform duration-200"
            :class="role == 'cashier' && !app.cashier ? ' opacity-50 bg-gray-600 pointer-events-none' : app.color"
            @click.prevent="handleAppClick(app)"
          >
            <div v-html="app.icon"></div>
            <span class="mt-3 font-semibold">{{ app.name }}</span>
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
