<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { ref, watch } from 'vue';

const props = defineProps<{
  returns: any,
  suppliers: any[],
  filters: { supplier_id?: string, status?: string, search?: string },
  metrics: { total: number, draft: number, confirmed: number, void: number }
}>()

const supplier = ref(props.filters.supplier_id || '')
const status = ref(props.filters.status || '')
const search = ref(props.filters.search || '')

watch([supplier, status, search], () => {
  router.get('/purchase-returns', {
    supplier_id: supplier.value,
    status: status.value,
    search: search.value,
  }, { preserveState: true, replace: true })
})

const resetFilters = () => {
  supplier.value = ''
  status.value = ''
  search.value = ''
  router.get('/purchase-returns', {}, { replace: true })
}

const deleteReturn = async (id: number) => {
  const result = await Swal.fire({
    title: 'Hapus Retur?',
    text: 'Data yang dihapus tidak bisa dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'bg-gray-800 text-white',
      confirmButton: 'bg-red-600 px-3 py-2 rounded',
      cancelButton: 'bg-gray-600 px-3 py-2 rounded'
    }
  })
  if (result.isConfirmed) {
    router.delete(`/purchase-returns/${id}`)
  }
}
</script>

<template>
  <Head title="Purchase Return" />

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
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Purchase Return</h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Total Retur</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.total }}</p>
        </div>

        <!-- Draft -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Draft</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.draft }}</p>
          <p class="text-sm text-gray-400">Belum diproses</p>
        </div>

        <!-- Confirmed -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Confirmed</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.confirmed }}</p>
          <p class="text-sm text-gray-400">Sudah kurangi stok</p>
        </div>

        <!-- Void -->
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Voided</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.void }}</p>
          <p class="text-sm text-gray-400">Dibatalkan</p>
        </div>
      </div>

      <!-- Header + Create -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Daftar Retur Pembelian</h1>
        <Link
          href="/purchase-returns/create"
          class="px-3 py-2 bg-blue-600 hover:bg-blue-500 font-semibold rounded text-white"
        >
          + Tambah Retur
        </Link>
      </div>

      <!-- Filters -->
      <div class="filters mb-4 flex gap-2 flex-wrap w-full justify-start flex-stretch">
        <input
          v-model="search"
          type="text"
          placeholder="Cari no retur..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />

        <select v-model="supplier" class="px-3 py-2 rounded-full bg-gray-700 border border-gray-600 text-white">
          <option value="">Semua Supplier</option>
          <option v-for="s in props.suppliers" :value="s.id">{{ s.name }}</option>
        </select>

        <select v-model="status" class="px-3 py-2 rounded-full bg-gray-700 border border-gray-600 text-white">
          <option value="">Semua Status</option>
          <option value="draft">Draft</option>
          <option value="confirmed">Confirmed</option>
          <option value="void">Void</option>
        </select>

        <button 
          type="button"
          @click="resetFilters"
          class="px-3 py-2 rounded-full bg-red-500 hover:bg-red-600 text-white transition"
        >
          Hapus Filter
        </button>
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead class="bg-gray-600/50">
          <tr>
            <th class="p-2 text-start">#</th>
            <th class="p-2 text-start">No. Retur</th>
            <th class="p-2 text-start">Tanggal</th>
            <th class="p-2 text-start">Supplier</th>
            <th class="p-2 text-start">Total</th>
            <th class="p-2 text-start">Reason</th>
            <th class="p-2 text-start">Status</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(retur, i) in props.returns.data" :key="retur.id" class="hover:bg-white/10 transition">
            <td class="p-2">{{ props.returns.from + i }}</td>
            <td class="p-2">{{ retur.return_number }}</td>
            <td class="p-2">{{ retur.return_date }}</td>
            <td class="p-2">{{ retur.supplier?.name }}</td>
            <td class="p-2">Rp {{ Number(retur.total).toLocaleString() }}</td>
            <td class="p-2">{{ retur.reason ?? '-' }}</td>
            <td class="p-2">
              <span
                :class="{
                  'text-yellow-400': retur.status === 'draft',
                  'text-green-400': retur.status === 'completed',
                  'text-red-400': retur.status === 'void'
                }"
              >
                {{ retur.status }}
              </span>
            </td>
            <td class="p-2 text-end">
              <div class="flex gap-2 justify-end">
                <Link :href="`/purchase-returns/${retur.id}/edit`"
                  class="px-2 py-1 bg-green-500 hover:bg-green-400 rounded text-white"
                >{{ retur.status === 'draft' ? 'Proses' : 'Lihat' }}</Link>
                <button :disabled="retur.status !== 'draft'" @click="deleteReturn(retur.id)" class="px-2 py-1 bg-red-600 hover:bg-red-500 rounded text-white disabled:bg-gray-500">
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.returns.links" :data="props.returns" />
    </div>
  </AuthenticatedLayout>
</template>
