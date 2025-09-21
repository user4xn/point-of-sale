<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { ref, watch } from 'vue'

const props = defineProps<{ 
  opnames: any,
  metrics: {
    last_opname: string|null,
    opname_count_month: number,
    not_opname_this_month: number,
    never_opname: number,
    diff_this_month: number
  },
  filters: { search?: string }
}>()

const search = ref(props.filters.search || '')

watch(search, (val) => {
  router.get(
    '/stock-opnames',
    { search: val },
    { preserveState: true, replace: true }
  )
})

const deleteOpname = async (id: number) => {
  const result = await Swal.fire({
    customClass: {
      container: 'bg-gray-800 text-white',
      popup: 'bg-gray-800 text-white',
      input: 'bg-gray-600 border border-gray-500 text-white rounded-full',
      confirmButton: 'bg-green-600 hover:bg-green-500 text-white text-md font-semibold',
      cancelButton: 'bg-red-600 hover:bg-red-500 text-white text-md font-semibold',
    },
    title: 'Konfirmasi Hapus',
    text: 'Data opname yang dihapus tidak bisa dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })
  if (result.isConfirmed) {
    router.delete(`/stock-opnames/${id}`)
  }
}
</script>

<template>
  <Head title="Stok Opname" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2
            class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out"
          >
            Dashboard Aplikasi
          </h2>
        </Link>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
          stroke-width="1.5"
          stroke="currentColor"
          class="size-6 text-white"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2
          class="text-xl font-semibold leading-tight text-gray-200 underline"
        >
          Stok Opname
        </h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <!-- Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-6">
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Terakhir Opname</h3>
          <p class="text-2xl font-bold mt-2">
            {{ props.metrics.last_opname ? new Date(props.metrics.last_opname).toLocaleDateString() : '-' }}
          </p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Jumlah Opname Bulan Ini</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.opname_count_month }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Produk Belum Opname Bulan Ini</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.not_opname_this_month }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Produk Belum Pernah Opname</h3>
          <p class="text-2xl font-bold mt-2">{{ props.metrics.never_opname }}</p>
        </div>
        <div class="bg-gray-900 border-b-4 border-gray-500 p-4 shadow text-white">
          <h3 class="text-sm font-medium">Selisih Bulan Ini</h3>
          <p class="text-2xl font-bold mt-2"
             :class="props.metrics.diff_this_month === 0 ? 'text-gray-200' : (props.metrics.diff_this_month > 0 ? 'text-green-400' : 'text-red-400')">
            {{ props.metrics.diff_this_month }}
          </p>
        </div>
      </div>

      <!-- Action & Filter -->
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Daftar Stock Opname</h1>
        <Link
          href="/stock-opnames/create"
          class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white"
        >
          + Tambah Opname
        </Link>
      </div>

      <div class="filters mb-4 flex gap-2 flex-wrap">
        <input
          v-model="search"
          type="text"
          placeholder="Cari catatan opname..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />
      </div>

      <!-- Table -->
      <table class="w-full text-sm border border-gray-600 rounded">
        <thead>
          <tr class="bg-gray-600/50">
            <th class="p-2 text-start">#</th>
            <th class="p-2 text-start">Tanggal</th>
            <th class="p-2 text-start">User</th>
            <th class="p-2 text-start">Tipe</th>
            <th class="p-2 text-start">Status</th>
            <th class="p-2 text-start">Catatan</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(opname, i) in props.opnames.data"
            :key="opname.id"
            class="hover:bg-white/10 transition"
          >
            <td class="p-2">{{ props.opnames.from + i }}</td>
            <td class="p-2">{{ new Date(opname.created_at).toLocaleString() }}</td>
            <td class="p-2">{{ opname.user?.name }}</td>
            <td class="p-2 capitalize">{{ opname.type }}</td>
            <td class="p-2">
              <span
                :class="[
                  opname.status === 'confirmed'
                    ? 'text-green-400'
                    : new Date().getTime() - new Date(opname.created_at).getTime() >
                      7 * 24 * 60 * 60 * 1000
                    ? 'text-red-400'
                    : 'text-yellow-400'
                ]"
              >
                {{ opname.status }}
              </span>
            </td>
            <td class="p-2">{{ opname.note || '-' }}</td>
            <td class="p-2">
              <div class="flex gap-2 justify-end">
                <Link
                  :href="`/stock-opnames/${opname.id}`"
                  class="px-2 py-1 bg-indigo-600 hover:bg-indigo-500 rounded text-white"
                >
                  Detail
                </Link>
                <button
                  v-if="opname.status === 'draft'"
                  @click="deleteOpname(opname.id)"
                  class="px-2 py-1 bg-red-600 hover:bg-red-500 rounded text-white"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.opnames.links" :data="props.opnames" />
    </div>
  </AuthenticatedLayout>
</template>
