<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VueSelect from 'vue3-select-component'
import { computed } from 'vue';

defineOptions({ layout: AuthenticatedLayout })

const props = defineProps<{ products: any[] }>()

const form = useForm({
  type: 'partial',
  note: '',
  items: [] as {
    product_id: number | null
    system_qty: number
    physical_qty: number
    difference: number
    note: string
  }[],
})

const addItem = () => {
  form.items.push({
    product_id: null,
    system_qty: 0,
    physical_qty: 0,
    difference: 0,
    note: '',
  })
}

const removeItem = (i: number) => {
  form.items.splice(i, 1)
}

const updateProduct = (index: number) => {
  const product = props.products.find(
    (p) => p.id === form.items[index].product_id
  )

  if (product) {
    form.items[index].system_qty = product.stock
    form.items[index].physical_qty = 0
    form.items[index].difference = 0
  }
}

const updateDifference = (index: number) => {
  const item = form.items[index]
  item.difference = item.physical_qty - item.system_qty
}

const submit = () => {
  form.post(route('stockopname.store'))
}

const updateType = () => {
  if (form.type === 'full') {
    form.items = props.products.map((p) => ({
      product_id: p.id,
      system_qty: p.stock,
      physical_qty: 0,
      difference: 0,
      note: '',
    }))

    console.log(form.items)
  } else {
    form.items = []
  }
}

const availableProducts = computed(() => {
  const usedIds = form.items.map((i) => i.product_id)
  return props.products.map((p) => ({
    value: p.id,
    label: p.name,
    disabled: usedIds.includes(p.id),
  }))
})
</script>

<style scoped>
  .dark-select{
    --vs-menu-height: 200px !important;
  }
</style>

<template>
  <Head title="Tambah Stock Opname" />

  <div class="p-6 text-white pb-20 min-h-[80vh]">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Tambah Stock Opname</h1>
      <Link
        href="/stock-opnames"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        ← Kembali
      </Link>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6">
      <!-- Type & Note -->
      <div class="grid grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-semibold mb-1">Tipe Opname</label>
          <select
            v-model="form.type"
            @change="updateType"
            class="w-full border bg-gray-700 rounded p-2"
          >
            <option value="full">Full</option>
            <option value="partial">Sebagian</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-semibold mb-1">Catatan</label>
          <input
            v-model="form.note"
            type="text"
            class="w-full border bg-gray-700 rounded p-2"
            placeholder="Contoh: Opname Gudang Belakang ..."
          />
        </div>
      </div>

      <!-- Items -->
      <div>
        <div class="flex justify-between items-center mb-2">
          <h2 class="text-lg font-bold">Daftar Item</h2>
          <button
            type="button"
            @click="addItem"
            :disabled="form.type == 'full'"
            class="px-3 py-1 bg-green-600 hover:bg-green-500 rounded text-white disabled:bg-gray-500"
          >
            + Tambah Item
          </button>
        </div>

        <table class="w-full text-sm border border-gray-600 rounded">
          <thead class="bg-gray-700">
            <tr>
              <th class="p-2 text-left">Produk</th>
              <th class="p-2 text-center">Stok Sistem</th>
              <th class="p-2 text-center">Stok Fisik</th>
              <th class="p-2 text-center">Selisih</th>
              <th class="p-2 text-left">Catatan</th>
              <th class="p-2"></th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(item, i) in form.items"
              :key="i"
              class="border-t border-gray-600"
            >
              <td class="p-2">
                <VueSelect
                  v-model="item.product_id"
                  :options="availableProducts"
                  placeholder="Pilih Produk"
                  @option-selected="updateProduct(i)"
                  class="dark-select"
                  :disabled="form.type === 'full'"
                />
              </td>
              <td class="p-2 text-center w-[100px]">{{ item.system_qty }}</td>
              <td class="p-2 w-[100px]">
                <input
                  v-model="item.physical_qty"
                  type="number"
                  min="0"
                  class="bg-transparent border-0 w-full p-1 text-right underline border-b"
                  @input="updateDifference(i)"
                />
              </td>
              <td
                class="p-2 text-center w-[100px]"
                :class="item.difference !== 0 ? 'text-yellow-400' : ''"
              >
                {{ item.difference }}
              </td>
              <td class="p-2">
                <input
                  v-model="item.note"
                  type="text"
                  class="w-[300px] border bg-gray-700 rounded p-1"
                  placeholder="Keterangan"
                />
              </td>
              <td class="p-2 text-right">
                <button
                  type="button"
                  @click="removeItem(i)"
                  :disabled="form.type == 'full'"
                  class="px-2 py-1 bg-red-600 hover:bg-red-500 rounded text-white disabled:bg-gray-500"
                >
                  Hapus
                </button>
              </td>
            </tr>
            <tr v-if="form.items.length === 0">
              <td colspan="6" class="text-center text-gray-400 p-4">
                Belum ada item opname
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Button Save -->
      <div>
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-500 font-semibold rounded text-white"
          :disabled="form.processing"
        >
          Simpan
        </button>
      </div>
    </form>
  </div>
</template>
