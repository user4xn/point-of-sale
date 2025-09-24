<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import VueSelect from 'vue3-select-component';
import { computed, watch } from 'vue';

const props = defineProps<{
  suppliers: any[],
  products: any[]
}>()

const form = useForm({
  supplier_id: '',
  order_date: new Date().toISOString().split('T')[0],
  items: [] as any[],
})

watch(() => form.supplier_id, () => {
  form.items = []
})

const filteredProducts = computed(() => {
  if (!form.supplier_id) return props.products
  return props.products.filter(p => p.supplier_id === form.supplier_id)
})

const addItem = () => {
  form.items.push({
    product_id: '',
    unit_conversion_id: null,
    quantity: 1,
    price: 0,
    subtotal: 0,
  })
}

const removeItem = (i: number) => {
  form.items.splice(i, 1)
}

const updateSubtotal = (i: number) => {
  const item = form.items[i]
  item.subtotal = item.quantity * item.price
}

const submit = () => {
  form.post('/purchase-orders')
}
</script>

<template>
  <Head title="Buat Purchase Order" />

  <AuthenticatedLayout>
    <div class="p-6 text-white">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Buat Purchase Order</h1>
        <Link href="/purchase-orders" class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white">
          ← Kembali
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Supplier -->
        <div>
          <label class="block mb-1 font-semibold">Supplier</label>
          <VueSelect
            class="dark-select"
            v-model="form.supplier_id"
            :options="props.suppliers.map(s => ({ value: s.id, label: s.name }))"
            placeholder="Pilih / Ketik Supplier"
          />
        </div>

        <!-- Tanggal -->
        <div>
          <label class="block mb-1 font-semibold">Tanggal</label>
          <input type="date" v-model="form.order_date" class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white" />
        </div>

        <!-- Items -->
        <div>
          <label class="block mb-2 font-semibold">Detail Produk</label>
          <table class="w-full text-sm border border-gray-600 rounded">
            <thead class="bg-gray-700">
              <tr>
                <th class="p-2">Produk</th>
                <th class="p-2">Satuan</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Subtotal</th>
                <th class="p-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in form.items" :key="i">
                <!-- Produk -->
                <td class="pl-2">
                  <VueSelect
                    v-model="item.product_id"
                    class="dark-select pr-1"
                    :options="filteredProducts.map(s => ({ value: s.id, label: s.name }))"
                    placeholder="Pilih / Ketik Supplier"
                    @option-selected="item.unit_conversion_id = null"
                  />
                </td>

                <!-- Unit Conversion -->
                <td class="p-2 px-1 w-[200px]">
                  <select
                    v-model="item.unit_conversion_id"
                    class="w-full py-1.5 rounded bg-gray-700 border border-gray-600 text-white"
                    :disabled="!item.product_id"
                  >
                    <option :value="null">Default ({{ (filteredProducts.find(p => p.id === item.product_id)?.unit?.name) ?? '-' }})</option>
                    <option
                      v-for="uc in filteredProducts.find(p => p.id === item.product_id)?.unit_conversions || []"
                      :key="uc.id"
                      :value="uc.id"
                    >
                      {{ uc.unit_name }} ({{ uc.conversion }}x)
                    </option>
                  </select>
                </td>

                <!-- Qty -->
                <td class="p-2 px-1 w-[100px]">
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    class="w-full py-1.5 rounded bg-gray-700 border border-gray-600 text-white"
                    @keyup="updateSubtotal(i)"
                  />
                </td>

                <!-- Price -->
                <td class="p-2 px-1 w-[200px]">
                  <input
                    type="text"
                    placeholder="(menunggu invoice)"
                    disabled
                    class="w-full py-1.5 rounded bg-gray-700 border border-gray-600 text-white"
                    @keyup="updateSubtotal(i)"
                  />
                </td>

                <!-- Subtotal -->
                <td class="p-2 text-center w-[100px]">
                  <span class="text-gray-400">(?)</span>
                </td>

                <!-- Remove -->
                <td class="p-2 text-center">
                  <button type="button" @click="removeItem(i)" class="px-2 py-1 bg-red-600 rounded text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash2-icon lucide-trash-2"><path d="M10 11v6"/><path d="M14 11v6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <button type="button" :disabled="form.supplier_id == ''" @click="addItem" class="mt-2 px-3 font-semibold py-1 bg-green-600 rounded text-white disabled:bg-gray-500">
            + Tambah Item
          </button>
        </div>

        <!-- Submit -->
        <div>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white font-semibold"
            :disabled="form.processing"
          >
            Simpan
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
