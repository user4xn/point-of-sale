<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'
import { ref, watch } from 'vue'
import Checkbox from '@/Components/Checkbox.vue'
import Modal from '@/Components/Modal.vue'
import { CategoryUnit } from '@/types'

const props = defineProps<{ 
  products: any, 
  categories: any[], 
  units: any[], 
  suppliers: any[],
  filters: { 
    search?: string,
    kategori?: string,
    supplier?: string
    status?: string
    empty_stock?: boolean
  } 
}>()

const search = ref(props.filters.search || '')
const kategori = ref(props.filters.kategori || '')
const supplier = ref(props.filters.supplier || '')
const status = ref(props.filters.status || '')
const emptyStock = ref(props.filters.empty_stock || false)
const showModalUnit = ref(false)
const showModalCategory = ref(false)

watch([search, kategori, supplier, status, emptyStock], (value) => {
  router.get(
    '/products',
    { 
      search: search.value, 
      kategori: kategori.value, 
      supplier: supplier.value,
      status: status.value,
      empty_stock: emptyStock.value ? 1 : 0
    },
    { preserveState: true, replace: true }
  )
})

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

const resetFilters = () => {
  search.value = ''
  kategori.value = ''
  supplier.value = ''
  status.value = ''
  emptyStock.value = false

  router.get('/products', {}, { replace: true })
}

const deleteCategory = async (c: CategoryUnit) => {
  showModalCategory.value = false
  const result = await Swal.fire({
    title: 'Hapus '+ c.name + '?',
    text: 'Data kategori yang dihapus tidak bisa dikembalikan, dan produk yang menggunakan kategori ini akan menjadi non kategori!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })
  if (result.isConfirmed) {
    router.delete(`products/category/${c.id}`)
  }
}

const deleteUnit = async (c: CategoryUnit) => {
  showModalUnit.value = false
  const result = await Swal.fire({
    title: 'Hapus '+ c.name + '?',
    text: 'Data unit yang dihapus tidak bisa dikembalikan, dan produk yang menggunakan unit ini akan menjadi non unit!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })
  if (result.isConfirmed) {
    router.delete(`products/unit/${c.id}`)
  }
}
</script>

<template>
  <Head title="Produk" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Produk</h2>
      </div>
    </template>

    <div class="p-6 text-white">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Daftar Produk</h1>
        <div class="flex gap-2">
          <button type="button" @click="showModalCategory = true" class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white">
            Kelola Kategori
          </button>
          <button type="button" @click="showModalUnit = true" class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white">
            Kelola Unit
          </button>
          <Link
            href="/products/create"
            class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold rounded text-white"
          >
            + Tambah Produk
          </Link>
        </div>
      </div>

      <div class="filters mb-4 flex gap-2 flex-wrap w-full justify-start flex-stretch">
        <input
          v-model="search"
          type="text"
          placeholder="Cari produk..."
          class="px-4 py-2 rounded-full bg-gray-700 border border-gray-600 text-white"
        />

        <select v-model="kategori" class="px-3 py-2 rounded-full bg-gray-700 border border-gray-600 text-white">
          <option value=""> Semua Kategori </option>
          <option v-for="category in categories" :value="category.id">{{ category.name }}</option>
        </select>

        <select v-model="supplier" class="px-3 py-2 rounded-full bg-gray-700 border border-gray-600 text-white">
          <option value=""> Semua Supplier </option>
          <option v-for="supplier in suppliers" :value="supplier.id">{{ supplier.name }}</option>
        </select>

        <select v-model="status" class="px-3 py-2 rounded-full bg-gray-700 border border-gray-600 text-white">
          <option value=""> Semua Status </option>
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>

        <label class="flex items-center gap-2 text-sm">
          <Checkbox v-model="emptyStock" :checked="false"/>
          Stok Kosong
        </label>

        <button 
          type="button"
          @click="resetFilters"
          class="px-3 py-2 rounded-full bg-red-500 hover:bg-red-600 text-white transition"
        >
          Hapus Filter
        </button>
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
            <td class="p-2">
              <div class="flex gap-2 justify-end">
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
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.products.links" :data="props.products" />
    </div>
  </AuthenticatedLayout>

  <Modal :show="showModalCategory" maxWidth="2xl" @close="showModalCategory = false" position="top">
    <div class="p-6 text-white">
      <h2 class="text-xl font-bold mb-4">Kelola Kategori</h2>
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-600">
          <tr>
            <th class="p-2">Nama</th>
            <th class="p-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="c in categories" :key="c.id" class="border-b border-gray-600 hover:bg-gray-600/20 cursor-pointer"
          >
            <td class="p-2">{{ c.name }}</td>
            <td class="p-2 text-right">
              <button @click="deleteCategory(c)" class="px-2 py-1 bg-red-600 hover:bg-red-600/80 rounded text-white">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
      <span class="block mt-1 italic text-gray-400 text-sm">*anda bisa menambahkan kategori langsung di halaman tambah produk</span>
    </div>
  </Modal>

  <Modal :show="showModalUnit" maxWidth="2xl" @close="showModalUnit = false" position="top">
    <div class="p-6 text-white">
      <h2 class="text-xl font-bold mb-4">Kelola Unit</h2>
      <table class="w-full text-left text-sm">
        <thead class="bg-gray-600">
          <tr>
            <th class="p-2">Nama</th>
            <th class="p-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="u in units" :key="u.id" class="border-b border-gray-600 hover:bg-gray-600/20 cursor-pointer"
          >
            <td class="p-2">{{ u.name }}</td>
            <td class="p-2 text-right">
              <button @click="deleteUnit(u)" class="px-2 py-1 bg-red-600 hover:bg-red-600/80 rounded text-white">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
      <span class="block mt-1 italic text-gray-400 text-sm">*anda bisa menambahkan unit langsung di halaman tambah produk</span>
    </div>
  </Modal>
</template>
