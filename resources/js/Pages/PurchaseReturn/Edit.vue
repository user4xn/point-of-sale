<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Swal from 'sweetalert2'
import { computed, onMounted } from 'vue'

const props = defineProps<{
  retur: any,
  suppliers: any[],
  products: any[]
}>()

const form = useForm({
  supplier_id: props.retur.supplier_id,
  return_date: props.retur.return_date,
  reason: props.retur.reason,
  status: props.retur.status,
  items: props.retur.items.map((it: any) => ({
    id: it.id,
    product_id: it.product_id,
    unit_conversion_id: it.unit_conversion_id,
    quantity: it.quantity,
    price: it.price,
    subtotal: it.subtotal,
    note: it.note,
  })),
})

onMounted(() => {
  props.retur.items.forEach((_: any, i: number) => {
    form.items[i].subtotal = (form.items[i].quantity || 0) * (form.items[i].price || 0)
  })
})

const updateSubtotal = (i: number) => {
  const item = form.items[i]
  item.subtotal = (item.quantity || 0) * (item.price || 0)
}

const total = computed(() => {
  return form.items.reduce((sum: number, it: any) => sum + (it.subtotal || 0), 0)
})

const confirmReturn = () => {
  Swal.fire({
    title: 'Konfirmasi Retur?',
    text: 'Stok akan dikurangi sesuai barang retur.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Konfirmasi',
    cancelButtonText: 'Batal',
    customClass: {
      popup: 'bg-gray-800 text-white',
      confirmButton: 'bg-green-600 px-3 py-2 rounded',
      cancelButton: 'bg-gray-600 px-3 py-2 rounded'
    }
  }).then((res) => {
    if (res.isConfirmed) {
      router.post(`/purchase-returns/${props.retur.id}/confirm`)
    }
  })
}

const voidReturn = () => {
  Swal.fire({
    title: 'Batalkan Retur?',
    text: 'Stok akan dikembalikan, status jadi VOID.',
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
      router.post(`/purchase-returns/${props.retur.id}/void`)
    }
  })
}

const handlePrint = () => {
  window.open(`/purchase-returns/${props.retur.id}/print`, '_blank')
}
</script>

<template>
  <Head title="Proses Purchase Return" />

  <AuthenticatedLayout>
    <div class="p-6 text-white">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">Proses Retur Pembelian</h1>
        <Link href="/purchase-returns" class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white">
          ← Kembali
        </Link>
      </div>

      <!-- Info Retur -->
      <div class="mb-4 flex flex-col gap-1 text-sm">
        <div><span class="font-semibold">No. Retur:</span> {{ props.retur.return_number }}</div>
        <div><span class="font-semibold">Tanggal:</span> {{ props.retur.return_date }}</div>
        <div><span class="font-semibold">Supplier:</span> {{ props.retur.supplier?.name }}</div>
        <div><span class="font-semibold">Reason:</span> {{ form.reason || '-' }}</div>
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

      <!-- Items -->
      <div>
        <table class="w-full text-sm border border-gray-600 rounded">
          <thead class="bg-gray-700">
            <tr>
              <th class="p-2">Produk</th>
              <th class="p-2">Qty</th>
              <th class="p-2">Harga</th>
              <th class="p-2 text-center">Subtotal</th>
              <th class="p-2 text-center">Note</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in form.items" :key="i">
              <!-- Produk -->
              <td class="pl-2 w-[250px]">
                {{ props.products.find(p => p.id === item.product_id)?.name ?? '-' }}
              </td>

              <!-- Qty + Unit -->
              <td class="p-2 text-center">
                {{ item.quantity }}
                <span>
                  <template v-if="item.unit_conversion_id">
                    {{
                      props.products.find(p => p.id === item.product_id)
                        ?.unit_conversions.find((uc: any) => uc.id === item.unit_conversion_id)
                        ?.unit_name ?? ''
                    }}
                  </template>
                  <template v-else>
                    {{ props.products.find(p => p.id === item.product_id)?.unit?.name ?? '' }}
                  </template>
                </span>
              </td>

              <!-- Price -->
              <td class="p-2 w-[200px]">
                <input
                  v-model.number="item.price"
                  type="number"
                  :disabled="form.status !== 'draft'"
                  class="w-full py-1.5 rounded bg-gray-700 border border-gray-600 text-white disabled:text-gray-400"
                  @input="updateSubtotal(i)"
                />
              </td>

              <!-- Subtotal -->
              <td class="p-2 text-center w-[100px]">
                Rp {{ Number(item.subtotal || 0).toLocaleString() }}
              </td>

              <!-- Note -->
              <td class="p-2 text-center">{{ item.note || '-' }}</td>
            </tr>
          </tbody>

        </table>
      </div>

      <!-- Total -->
      <div class="text-right font-bold text-lg mt-3">
        Total: Rp {{ Number(total).toLocaleString() }}
      </div>

      <!-- Actions -->
      <div class="flex gap-3 mt-6">
        <button
          type="button"
          @click="handlePrint()"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded text-white font-semibold"
        >
          Print / Save PDF
        </button>
        <button
          type="button"
          @click="confirmReturn"
          class="px-4 py-2 bg-green-600 hover:bg-green-500 rounded text-white font-semibold disabled:bg-gray-500"
          :disabled="form.status !== 'draft'"
        >
          Konfirmasi Retur
        </button>
        <button
          type="button"
          @click="voidReturn"
          class="px-4 py-2 bg-red-600 hover:bg-red-500 rounded text-white font-semibold disabled:bg-gray-500"
          :disabled="form.status !== 'completed'"
        >
          Void Retur
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
