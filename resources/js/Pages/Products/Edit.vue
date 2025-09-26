<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed,ref } from 'vue'
import VueSelect from 'vue3-select-component'
import BlankImage from '@/Images/assets/blank-image.jpg'

defineOptions({ layout: AuthenticatedLayout })

const selectedSupplier = computed(() => {
  return props.suppliers.find(s => s.id === form.supplier_id) || null
})

const props = defineProps<{
  product: any
  categories: any[]
  units: any[]
  suppliers: any[]
}>()

const form = useForm({
  name: props.product.name,
  sku: props.product.sku,
  category_id: props.product.category_id,
  unit_id: props.product.unit_id,
  supplier_id: props.product.supplier_id,
  purchase_price: props.product.purchase_price,
  sell_price: props.product.sell_price,
  stock: props.product.stock,
  description: props.product.description ?? '',
  status: props.product.status,
  image: null as File | null,
  unit_conversions: props.product.unit_conversions ?? [],
  new_category: '',
  new_unit: '',
  new_supplier: '',
})

const addUnitConversion = () => {
  form.unit_conversions.push({ unit_name: '', conversion: 1, purchase_price: null })
}

const removeUnitConversion = (i: number) => {
  form.unit_conversions.splice(i, 1)
}

const imagePreview = ref<string | null>(
  props.product.image ? `/storage/${props.product.image}` : null
)
const fileInput = ref<HTMLInputElement | null>(null)

const handleImageChange = (e: Event) => {
  const file = (e.target as HTMLInputElement).files?.[0] ?? null
  form.image = file
  if (file) {
    const reader = new FileReader()
    reader.onload = (ev) => {
      imagePreview.value = ev.target?.result as string
    }
    reader.readAsDataURL(file)
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


const triggerFilePicker = () => {
  fileInput.value?.click()
}

const submit = () => {
  const payload: any = { ...form.data(), _method: 'PUT' }

  if (!payload.image) delete payload.image

  form.transform(() => payload).post(`/products/${props.product.id}`, {
    forceFormData: true,
  })
}
</script>

<template>
  <Head title="Edit Produk" />

  <div class="p-6 text-white">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Edit Produk</h1>
      <Link
        href="/products"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        ← Kembali
      </Link>
    </div>

    <!-- Konversi Satuan -->
    <div class="mb-4 p-4 bg-white/10 rounded-md">
      <div>
        <label class="block text-lg font-semibold mb-1">Satuan Konversi</label>
        <table class="w-full text-sm border border-gray-600 rounded">
          <thead class="bg-gray-700">
            <tr>
              <th class="p-2 text-start">Nama Unit</th>
              <th class="p-2 text-start">Konversi</th>
              <th class="p-2 text-start">Harga Beli (opsional)</th>
              <th class="p-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(uc, i) in form.unit_conversions" :key="i">
              <td class="p-2">
                <input v-model="uc.unit_name" type="text" class="w-full border bg-gray-700 rounded p-2"/>
              </td>
              <td class="p-2">
                <input v-model.number="uc.conversion" type="number" min="1" class="w-full border bg-gray-700 rounded p-2"/>
              </td>
              <td class="p-2">
                <input v-model.number="uc.purchase_price" type="number" class="w-full border bg-gray-700 rounded p-2"/>
              </td>
              <td class="p-2">
                <button @click="removeUnitConversion(i)" type="button" class="px-2 py-1 bg-red-600 rounded text-white">Hapus</button>
              </td>
            </tr>
          </tbody>
        </table>
        <button @click="addUnitConversion" type="button" class="mt-2 px-3 py-1 bg-green-600 rounded text-white">+ Tambah Satuan</button>
      </div>
    </div>

    <hr class="border-t border-gray-600 my-4">

    <form @submit.prevent="submit" class="grid grid-cols-2 gap-6 mt-4">
      <!-- Form Kiri -->
      <div class="space-y-5">
        <!-- Nama -->
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
          <input disabled v-model="form.stock" type="number" class="w-full border bg-gray-700 rounded p-2" />
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
      <div class="flex flex-col items-center p-4 pr-0">
        <div
          class="w-full flex items-center justify-center bg-gray-700 rounded-lg overflow-hidden mb-3 cursor-pointer hover:opacity-80 transition flex-col"
          @click="triggerFilePicker"
        >
          <img
            :src="imagePreview ?? BlankImage"
            class="object-cover h-[400px] w-full border-b border-gray-600"
          />
          <span class="py-2 text-gray-400">Klik untuk mengganti gambar</span>
        </div>
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
              :href="`/suppliers/${selectedSupplier.id}/edit?back=/products/${product.id}/edit`"
              class="px-2 py-1 bg-yellow-500 font-semibold hover:bg-yellow-400 transition ease-in-out text-white rounded"
            >
              Edit Data Supplier
            </Link>
            <span v-else class="px-2 py-1 bg-blue-500 font-semibold transition ease-in-out text-white rounded">BARU</span>
          </div>
          <p class="p-2 mt-4"><span class="font-semibold">Nama:</span> {{ selectedSupplier.name }}</p>
          <p class="p-2" v-if="selectedSupplier.email"><span class="font-semibold">Email:</span> {{ selectedSupplier.email }}</p>
          <p class="p-2" v-if="selectedSupplier.phone"><span class="font-semibold">Telepon:</span> {{ selectedSupplier.phone }}</p>
          <p class="p-2" v-if="selectedSupplier.address"><span class="font-semibold">Alamat:</span> {{ selectedSupplier.address }}</p>
          <p class="p-2" v-if="selectedSupplier.created_at"><span class="font-semibold">Terdaftar:</span> {{ (selectedSupplier.created_at) }}</p>
        </div>
      </div>
    </form>

    <!-- Save -->
    <div class="mt-6">
      <button
        type="button"
        @click="submit"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 font-semibold rounded text-white"
        :disabled="form.processing"
      >
        Simpan Perubahan
      </button>
    </div>
  </div>
</template>
