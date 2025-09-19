<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed, ref } from 'vue'
import VueSelect from 'vue3-select-component'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{ categories: any[], units: any[], suppliers: any[] }>()
const fileInput = ref<HTMLInputElement | null>(null)

const selectedSupplier = computed(() => {
  return props.suppliers.find(s => s.id === form.supplier_id) || null
})

const form = useForm({
  name: '',
  sku: '',
  category_id: null as number | null,
  unit_id: null as number | null,
  supplier_id: null as number | null,
  purchase_price: 0,
  sell_price: 0,
  stock: 0,
  description: '',
  status: 'active',
  image: null as File | null,
  new_category: '',
  new_unit: '',
  new_supplier: '',
})

const imagePreview = ref<string | null>(null)

const handleImageChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  form.image = file
  if (file) {
    const reader = new FileReader()
    reader.onload = (ev) => {
      imagePreview.value = ev.target?.result as string
    }
    reader.readAsDataURL(file)
  } else {
    imagePreview.value = null
  }
}

const addCategory = (newTag: string) => {
  form.category_id = null
  form.new_category = newTag
  props.categories.push({ id: null, name: newTag })
  console.log(form)
}

const addUnit = (newTag: string) => {
  form.category_id = null
  form.new_unit = newTag
  props.units.push({ id: null, name: newTag })
}

const addSupplier = (newTag: string) => {
  form.supplier_id = null
  form.new_supplier = newTag
  props.suppliers.push({ id: null, name: newTag })
}

const submit = () => {
  form.post('/products', { forceFormData: true })
}

const triggerFilePicker = () => {
  fileInput.value?.click()
}
</script>

<template>
  <Head title="Tambah Produk" />

  <div class="p-6 text-white">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Tambah Produk</h1>
      <Link
        href="/products"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        ← Kembali
      </Link>
    </div>

    <form @submit.prevent="submit" class="grid grid-cols-2 gap-6">
      <!-- Form Kiri -->
      <div class="space-y-5">
        <!-- Nama Produk -->
        <div>
          <label class="block text-sm font-semibold mb-1">Nama Produk</label>
          <input v-model="form.name" type="text" class="w-full border bg-gray-700 rounded p-2" placeholder="Indomie Goreng" />
        </div>

        <!-- SKU -->
        <div>
          <label class="block text-sm font-semibold mb-1">SKU</label>
          <input v-model="form.sku" type="text" class="w-full border bg-gray-700 rounded p-2" placeholder="1102412512" />
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-semibold mb-1">Kategori</label>
          <div class="w-full bg-gray-700 text-white border border-gray-600 rounded"></div>
          <VueSelect
            class="dark-select"
            v-model="form.category_id"
            :options="props.categories.map(c => ({ value: c.id, label: c.name }))"
            placeholder="Pilih / Ketik Kategori Baru"
            :is-taggable="true"
            @option-created="addCategory"
          />
        </div>

        <!-- Unit -->
        <div>
          <label class="block text-sm font-semibold mb-1">Unit (Satuan)</label>
          <VueSelect
            class="dark-select"
            v-model="form.unit_id"
            :options="props.units.map(u => ({ value: u.id, label: u.name }))"
            placeholder="Pilih / Ketik Unit Baru"
            :is-taggable="true"
            @option-created="addUnit"
          />
        </div>

        <!-- Supplier -->
        <div>
          <label class="block text-sm font-semibold mb-1">Supplier</label>
          <VueSelect
            class="dark-select"
            v-model="form.supplier_id"
            :options="props.suppliers.map(s => ({ value: s.id, label: s.name }))"
            placeholder="Pilih / Ketik Supplier Baru"
            :is-taggable="true"
            @option-created="addSupplier"
          />
        </div>

        <!-- Harga -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold mb-1">Harga Beli</label>
            <input v-model="form.purchase_price" type="number" class="w-full border bg-gray-700 rounded p-2" />
          </div>
          <div>
            <label class="block text-sm font-semibold mb-1">Harga Jual</label>
            <input v-model="form.sell_price" type="number" class="w-full border bg-gray-700 rounded p-2" />
          </div>
        </div>

        <!-- Stok -->
        <div>
          <label class="block text-sm font-semibold mb-1">Stok</label>
          <input v-model="form.stock" type="number" class="w-full border bg-gray-700 rounded p-2" />
        </div>

        <!-- Status -->
        <div>
          <label class="block text-sm font-semibold mb-1">Status</label>
          <select v-model="form.status" class="w-full border bg-gray-700 rounded p-2">
            <option value="active">Aktif</option>
            <option value="inactive">Nonaktif</option>
          </select>
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-sm font-semibold mb-1">Deskripsi</label>
          <textarea v-model="form.description" class="w-full border bg-gray-700 rounded p-2" placeholder="Indomie Goreng Bundling Telur"></textarea>
        </div>
      </div>

      <!-- Upload Gambar -->
      <div class="flex flex-col items-center p-4">
        <div
          class="w-full flex items-center justify-center bg-gray-700 rounded-lg overflow-hidden mb-3 cursor-pointer hover:opacity-80 transition flex-col"
          @click="triggerFilePicker"
        >
          <img
            :src="imagePreview ?? '/storage/img-assets/blank-image.jpg'"
            class="object-cover h-[400px] w-full border-b border-gray-600"
          />
          <span class="text-gray-400 py-2">Klik untuk mengganti gambar</span>
        </div>

        <!-- Hidden input -->
        <input
          ref="fileInput"
          type="file"
          class="hidden"
          accept="image/*"
          @change="handleImageChange"
        />

        <!-- Detail Supplier -->
        <div v-if="selectedSupplier" class="mt-2 p-3 rounded bg-gray-800 border border-gray-600 text-sm w-full h-auto">
          <div class="flex gap-2 justify-between flex-wrap">
            <span class="text-2xl font-bold">Detail Supplier</span>
            <Link
              v-if="selectedSupplier.created_at != null"
              :href="`/suppliers/${selectedSupplier.id}/edit?back=/products/create`"
              class="px-2 py-1 bg-yellow-500 font-semibold hover:bg-yellow-400 transition ease-in-out text-white rounded"
            >
              Edit Data Supplier
            </Link>
            <span v-else class="px-2 py-1 bg-blue-500 font-semibold transition ease-in-out text-white rounded">BARU</span>
          </div>
          <p class="p-2 mt-4"><span class="font-semibold">Nama:</span> {{ selectedSupplier.name }}</p>
          <p class="p-2"><span class="font-semibold">Email:</span> {{ selectedSupplier.email ?? '-' }}</p>
          <p class="p-2"><span class="font-semibold">Telepon:</span> {{ selectedSupplier.phone ?? '-'}}</p>
          <p class="p-2"><span class="font-semibold">Alamat:</span> {{ selectedSupplier.address ?? '-' }}</p>
          <p class="p-2" v-if="selectedSupplier.created_at"><span class="font-semibold">Terdaftar:</span> {{ (selectedSupplier.created_at) }}</p>
        </div>
      </div>
    </form>

    <!-- Button Save -->
    <div class="mt-6">
      <button
        type="button"
        @click="submit"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 font-semibold rounded text-white"
        :disabled="form.processing"
      >
        Simpan
      </button>
    </div>
  </div>
</template>