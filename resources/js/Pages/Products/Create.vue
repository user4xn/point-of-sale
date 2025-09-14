<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{ categories: any[], units: any[], suppliers: any[] }>()
const fileInput = ref<HTMLInputElement | null>(null)

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
          <input v-model="form.name" type="text" class="w-full border bg-gray-700 rounded p-2" />
        </div>

        <!-- SKU -->
        <div>
          <label class="block text-sm font-semibold mb-1">SKU</label>
          <input v-model="form.sku" type="text" class="w-full border bg-gray-700 rounded p-2" />
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-semibold mb-1">Kategori</label>
          <Multiselect
            v-model="form.category_id"
            :options="props.categories.map(c => ({ value: c.id, label: c.name }))"
            placeholder="Pilih / ketik kategori baru"
            track-by="value"
            label="label"
            :taggable="true"
            @tag="form.category_id = null; form.new_category = $event"
          />
        </div>

        <!-- Unit -->
        <div>
          <label class="block text-sm font-semibold mb-1">Unit</label>
          <Multiselect
            v-model="form.unit_id"
            :options="props.units.map(u => ({ value: u.id, label: u.name }))"
            placeholder="Pilih / ketik unit baru"
            track-by="value"
            label="label"
            :taggable="true"
            @tag="form.unit_id = null; form.new_unit = $event"
          />
        </div>

        <!-- Supplier -->
        <div>
          <label class="block text-sm font-semibold mb-1">Supplier</label>
          <select v-model="form.supplier_id" class="w-full border bg-gray-700 rounded p-2">
            <option :value="null">-- Pilih Supplier --</option>
            <option v-for="s in props.suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
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
          <textarea v-model="form.description" class="w-full border bg-gray-700 rounded p-2"></textarea>
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
