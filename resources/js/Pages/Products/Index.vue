<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{ products: any }>()

const deleteProduct = async (id: number) => {
  const result = await Swal.fire({
    title: 'Konfirmasi Hapus',
    text: 'Data produk yang dihapus tidak bisa dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })
  if (result.isConfirmed) {
    router.delete(`/products/${id}`)
  }
}
</script>

<template>
  <Head title="Produk" />

  <div class="p-6 text-white">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-bold">Daftar Produk</h1>
      <Link
        href="/products/create"
        class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white"
      >
        + Tambah Produk
      </Link>
    </div>

    <table class="w-full text-sm border border-gray-600 rounded">
      <thead>
        <tr class="bg-gray-600/50">
          <th class="p-2 text-start">#</th>
          <th class="p-2 text-start">Gambar</th>
          <th class="p-2 text-start">Nama</th>
          <th class="p-2 text-start">SKU</th>
          <th class="p-2 text-start">Kategori</th>
          <th class="p-2 text-start">Unit</th>
          <th class="p-2 text-start">Supplier</th>
          <th class="p-2 text-start">Harga Beli</th>
          <th class="p-2 text-start">Harga Jual</th>
          <th class="p-2 text-start">Stok</th>
          <th class="p-2 text-start">Status</th>
          <th class="p-2 text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(product, i) in props.products.data"
          :key="product.id"
          class="hover:bg-white/10 transition"
        >
          <td class="p-2">{{ props.products.from + i }}</td>
          <td class="p-2">
            <img
              v-if="product.image"
              :src="`/storage/${product.image}`"
              alt="product"
              class="h-12 w-12 object-contain rounded bg-gray-700"
            />
            <span v-else class="text-gray-400">-</span>
          </td>
          <td class="p-2">{{ product.name }}</td>
          <td class="p-2">{{ product.sku }}</td>
          <td class="p-2">{{ product.category?.name || '-' }}</td>
          <td class="p-2">{{ product.unit?.name || '-' }}</td>
          <td class="p-2">{{ product.supplier?.name || '-' }}</td>
          <td class="p-2">Rp {{ Number(product.purchase_price).toLocaleString() }}</td>
          <td class="p-2">Rp {{ Number(product.sell_price).toLocaleString() }}</td>
          <td class="p-2">{{ product.stock }}</td>
          <td class="p-2">
            <span
              :class="product.status === 'active' ? 'text-green-400' : 'text-red-400'"
            >
              {{ product.status === 'active' ? 'Aktif' : 'Nonaktif' }}
            </span>
          </td>
          <td class="p-2 flex gap-2 justify-end">
            <Link
              :href="`/products/${product.id}/edit`"
              class="px-2 py-1 bg-yellow-500 hover:bg-yellow-500/80 rounded text-white"
            >
              Edit
            </Link>
            <button
              @click="deleteProduct(product.id)"
              class="px-2 py-1 bg-red-600 hover:bg-red-600/80 rounded text-white"
            >
              Delete
            </button>
          </td>
        </tr>
      </tbody>
    </table>

    <Pagination :links="props.products.links" :users="props.products" />
  </div>
</template>
