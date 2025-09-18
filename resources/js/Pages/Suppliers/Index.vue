<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

const props = defineProps<{ suppliers: any }>()

const deleteSupplier = async (id: number) => {
  const result = await Swal.fire({
    title: 'Konfimasi Hapus',
    text: 'data yang dihapus tidak dapat dikembalikan',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })

  if (result.isConfirmed) {
    router.delete(`/suppliers/${id}`)
  }
}
</script>

<template>
  <Head title="Supplier" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Supplier</h2>
      </div>
    </template>
    <div class="p-6 text-white">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Daftar Supplier</h1>
        <Link
          href="/suppliers/create"
          class="px-3 py-2 bg-blue-600 hover:bg-blue-500 font-semibold transition ease-in-out text-white rounded"
        >
          + Tambah Supplier
        </Link>
      </div>

      <table class="w-full table-auto text-sm text-white">
        <thead>
          <tr class="bg-gray-500/80">
            <th class="p-2 text-start">#</th>
            <th class="p-2 text-start">Nama</th>
            <th class="p-2 text-start">Kontak</th>
            <th class="p-2 text-start">Email</th>
            <th class="p-2 text-start">Alamat</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(supplier, i) in props.suppliers.data"
            :key="supplier.id"
            class="hover:bg-white/10 transition ease-in-out"
          >
            <td class="p-2">{{ props.suppliers.from + i }}</td>
            <td class="p-2">{{ supplier.name }}</td>
            <td class="p-2">{{ supplier.contact }}</td>
            <td class="p-2">{{ supplier.email }}</td>
            <td class="p-2">{{ supplier.address }}</td>
            <td class="p-2">
              <div class="flex gap-2 justify-end">
                <Link
                  :href="`/suppliers/${supplier.id}/edit`"
                  class="px-2 py-1 bg-yellow-500 font-semibold hover:bg-yellow-400 transition ease-in-out text-white rounded"
                >
                  Edit
                </Link>
                <button
                  @click="deleteSupplier(supplier.id)"
                  class="px-2 py-1 bg-red-600 font-semibold hover:bg-red-500 transition ease-in-out text-white rounded"
                >
                  Hapus
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.suppliers.links" :data="props.suppliers" />
    </div>
  </AuthenticatedLayout>
</template>