<script setup lang="ts">
import { onMounted } from 'vue'

const props = defineProps<{
  trx: any
  settings: any
}>()

onMounted(() => {
  window.print()
})

</script>

<template>
  <div class="w-[280px] font-mono text-[12px] text-black mx-auto">
    <!-- Header -->
    <div class="text-center mb-2">
      <img
        v-if="props.settings.store_logo"
        :src="`/storage/${props.settings.store_logo}`"
        alt="Logo"
        class="mx-auto h-12 mb-1"
      />
      <strong class="block uppercase text-[14px]">{{ props.settings.store_name }}</strong>
      <span class="block">{{ props.settings.store_address }}</span>
      <span class="block">{{ props.settings.store_contact }}</span>
    </div>

    <hr class="border-t border-dashed border-gray-400 my-1" />

    <!-- Order Info -->
    <div class="mb-2 text-[12px] leading-tight">
      <div>No. Order : <strong>{{ props.trx.invoice_number }}</strong></div>
      <div>Tanggal   : {{ new Date(props.trx.created_at).toLocaleString() }}</div>
      <div>Kasir     : {{ props.trx.user?.name || '-' }}</div>
    </div>

    <hr class="border-t border-dashed border-gray-400 my-1" />

    <!-- Items -->
    <table class="w-full text-[12px]">
      <tbody>
        <template v-for="item in props.trx.items" :key="item.id">
          <tr class="py-[2px]">
            <td colspan="2">{{ item.product?.name }}</td>
            <td class="text-right">{{ item.quantity }}</td>
            <td class="text-right uppercase">{{ item.unit_name }}</td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td class="text-right pr-3">{{ item.price.toLocaleString('id-ID') }}</td>
            <td class="text-right">{{ item.subtotal.toLocaleString('id-ID') }}</td>
          </tr>
        </template>
      </tbody>
    </table>

    <hr class="border-t border-dashed border-gray-400 my-1" />

    <!-- Summary -->
    <div class="text-[12px] leading-tight space-y-1">
      <div class="flex justify-between">
        <span>Subtotal</span>
        <span>{{props.trx.total_price}}</span>
      </div>
      <div class="flex justify-between">
        <span>Diskon</span>
        <span>{{props.trx.discount}}</span>
      </div>
      <div class="flex justify-between">
        <span>PPN ({{ props.settings.tax_rate }}%)</span>
        <span>{{props.trx.tax}}</span>
      </div>
      <div class="flex justify-between font-bold">
        <span>Total</span>
        <span>{{props.trx.grand_total}}</span>
      </div>
      <div class="flex justify-between mt-2">
        <span>Tunai</span>
        <span>{{props.trx.paid_amount}}</span>
      </div>
      <div class="flex justify-between">
        <span>Kembali</span>
        <span>{{props.trx.change_amount}}</span>
      </div>
    </div>

    <hr class="border-t border-dashed border-gray-400 my-1" />

    <!-- Footer -->
    <div class="text-center text-xs mt-2">
      <p>
        *** {{
          props.settings.receipt_template
            ?.replace(/\[store_name\]/g, props.settings.store_name)
            ?.replace(/\[store_address\]/g, props.settings.store_address)
            ?.replace(/\[store_contact\]/g, props.settings.store_contact)
        }} ***
      </p>
      <p>Terima Kasih & Sampai Jumpa</p>
    </div>
  </div>
</template>
