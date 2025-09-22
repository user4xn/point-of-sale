<script setup lang="ts">
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { computed } from 'vue'
import Swal from 'sweetalert2'

const props = defineProps<{
  order: any,
  suppliers: any[],
  products: any[]
}>()

const form = useForm({
  supplier_id: props.order.supplier_id,
  order_date: props.order.order_date,
  status: props.order.status,
  items: props.order.items.map((it: any) => ({
    id: it.id,
    product_id: it.product_id,
    unit_conversion_id: it.unit_conversion_id,
    quantity: it.quantity,
    price: it.price,
    subtotal: it.subtotal,
  })),
})

const filteredProducts = computed(() => {
  if (!form.supplier_id) return props.products
  return props.products.filter(p => p.supplier_id === form.supplier_id)
})

const updateSubtotal = (i: number) => {
  const item = form.items[i]
  item.subtotal = (item.price || 0) * (item.quantity || 0)
}

const total = computed(() => {
  return form.items.reduce((sum: number, it: any) => sum + (it.subtotal || 0), 0)
})


const completeOrder = () => {
  form.transform(data => ({
    ...data,
    action: 'complete'
  })).put(`/purchase-orders/${props.order.id}`)
}

const voidOrder = () => {
  Swal.fire({
    title: 'Batalkan PO?',
    text: 'Stok akan dikembalikan, status menjadi VOID',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Void',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'bg-gray-800 text-white',
      confirmButton: 'bg-red-600 px-3 py-2 rounded',
      cancelButton: 'bg-gray-600 px-3 py-2 rounded'
    }
  }).then((res) => {
    if (res.isConfirmed) {
      router.post(`/purchase-orders/${props.order.id}/void`)
    }
  })
}

const handlePrint = () => {
  window.open(`/purchase-orders/${props.order.id}/print`, '_blank')
}
</script>

<template>
  <Head title="Proses Purchase Order" />

  <AuthenticatedLayout>
    <div class="p-6 text-white">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Proses Purchase Order</h1>
        <Link href="/purchase-orders" class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white">
          ← Kembali
        </Link>
      </div>

      <!-- Info PO -->
      <div class="mb-4 flex flex-col gap-1 text-sm">
        <div><span class="font-semibold">No. PO:</span> {{ props.order.po_number }}</div>
        <div><span class="font-semibold">Status:</span>&nbsp;
          <span
            :class="{
              'text-yellow-400': form.status === 'draft',
              'text-green-400': form.status === 'completed',
              'text-red-400': form.status === 'void'
            }"
          >
            {{ form.status }}
          </span>
        </div>
      </div>

      <!-- Form -->
      <form class="space-y-6">
        <!-- Supplier -->
        <div>
          <label class="block mb-1 font-semibold">Supplier</label>
          <select disabled v-model="form.supplier_id" class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white cursor-not-allowed">
            <option value="">Pilih Supplier</option>
            <option v-for="s in props.suppliers" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <!-- Tanggal -->
        <div>
          <label class="block mb-1 font-semibold">Tanggal</label>
          <input type="date" v-model="form.order_date" disabled class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-gray-400" />
        </div>

        <!-- Items -->
        <div>
          <label class="block mb-2 font-semibold">Detail Produk</label>
          <table class="w-full text-sm border border-gray-600 rounded">
            <thead class="bg-gray-700">
              <tr>
                <th class="p-2 text-left">Produk</th>
                <th class="p-2 text-center">Satuan</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in form.items" :key="i">
                <!-- Produk -->
                <td class="pl-2 w-[220px]">
                  {{ filteredProducts.find(p => p.id === item.product_id)?.name ?? '-' }}
                </td>

                <!-- Unit Conversion -->
                <td class="p-2 px-1 w-[200px] text-center">
                  <span v-if="item.unit_conversion_id">
                    {{
                      props.products.find(p => p.id === item.product_id)
                        ?.unit_conversions.find((uc: any) => uc.id === item.unit_conversion_id)
                        ?.unit_name ?? '-'
                    }}
                  </span>
                  <span v-else>
                    {{ filteredProducts.find(p => p.id === item.product_id)?.unit?.name ?? '-' }}
                  </span>
                </td>

                <!-- Qty -->
                <td class="p-2 px-1 w-[100px] text-center">
                  {{ item.quantity }}
                </td>

                <!-- Price -->
                <td class="p-2 px-1 w-[200px]">
                  <input
                    v-model.number="item.price"
                    type="number"
                    :disabled="form.status !== 'draft'"
                    class="w-full py-1.5 rounded bg-gray-700 border border-gray-600 text-white disabled:text-gray-400"
                    @input="updateSubtotal(i)"
                  />
                </td>

                <!-- Subtotal -->
                <td class="p-2 text-center w-[200px]">
                  Rp {{ Number(item.subtotal || 0).toLocaleString() }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Total -->
        <div class="text-right font-bold text-lg">
          Total: Rp {{ Number(total).toLocaleString() }}
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
            <button
              type="button"
              @click="handlePrint()"
              class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white font-semibold"
            >
              Print / Save PDF
            </button>
          <button
            type="button"
            @click="completeOrder"
            class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded text-white font-semibold disabled:bg-gray-500"
            :disabled="form.items.some((item: any) => item.price < 1) || form.processing || form.status !== 'draft'"
          >
            Tandai Sudah Selesai
          </button>
          <button
            type="button"
            @click="voidOrder"
            class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-white font-semibold disabled:bg-gray-500"
            :disabled="form.processing || form.status !== 'completed'"
          >
            Void PO
          </button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
