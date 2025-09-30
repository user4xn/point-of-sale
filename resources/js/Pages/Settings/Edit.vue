<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

interface Setting {
  store_name: string
  store_address?: string
  store_contact?: string
  store_logo?: string
  receipt_template?: string
  tax_rate?: number
}

const props = defineProps<{ setting: Setting | null }>()

const form = useForm({
  store_name: props.setting?.store_name ?? '',
  store_address: props.setting?.store_address ?? '',
  store_contact: props.setting?.store_contact ?? '',
  receipt_template: props.setting?.receipt_template ?? 'Terima kasih telah berbelanja di [store_name]',
  store_logo: null as File | null,
  tax_rate: props.setting?.tax_rate ?? 0,
})

const submit = () => {
  form.post('/settings', {
    forceFormData: true,
  })
}

const rupiah = (val: number) =>
  "Rp " + val.toLocaleString("id-ID")

const renderTemplate = () => {
  return form.receipt_template
    .replace(/\[store_name\]/g, form.store_name || '[Nama Toko]')
    .replace(/\[store_address\]/g, form.store_address || '[Alamat]')
    .replace(/\[store_contact\]/g, form.store_contact || '[Kontak]')
}
</script>

<template>
  <Head title="Pengaturan Toko" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Pengaturan Toko</h2>
      </div>
    </template>

    <div class="flex flex-wrap gap-3 p-1">
      <form @submit.prevent="submit" class="space-y-5 flex-1 p-4">
        <!-- Store Name -->
        <div>
          <label class="block text-sm font-semibold mb-1">Nama Toko</label>
          <input
            v-model="form.store_name"
            type="text"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2 focus:ring focus:ring-blue-600/50"
          />
          <p v-if="form.errors.store_name" class="text-red-400 text-xs mt-1">{{ form.errors.store_name }}</p>
        </div>
  
        <!-- Address -->
        <div>
          <label class="block text-sm font-semibold mb-1">Alamat</label>
          <textarea
            v-model="form.store_address"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2 focus:ring focus:ring-blue-600/50"
          ></textarea>
          <p v-if="form.errors.store_address" class="text-red-400 text-xs mt-1">{{ form.errors.store_address }}</p>
        </div>
  
        <!-- Contact -->
        <div>
          <label class="block text-sm font-semibold mb-1">Kontak</label>
          <input
            v-model="form.store_contact"
            type="text"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2 focus:ring focus:ring-blue-600/50"
          />
          <p v-if="form.errors.store_contact" class="text-red-400 text-xs mt-1">{{ form.errors.store_contact }}</p>
        </div>
  
        <!-- Store Logo -->
        <div>
          <label class="block text-sm font-semibold mb-1">Logo</label>
          <input
            type="file"
            @change="e => form.store_logo = (e.target as HTMLInputElement).files?.[0] ?? null"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer"
          />
          <div v-if="props.setting?.store_logo" class="mt-3">
            <img :src="`/storage/${props.setting.store_logo}`" alt="Logo" class="h-16 rounded shadow" />
          </div>
          <p v-if="form.errors.store_logo" class="text-red-400 text-xs mt-1">{{ form.errors.store_logo }}</p>
        </div>
  
        <!-- Receipt Template -->
        <div>
          <label class="block text-sm font-semibold mb-1">Template Nota (Akhiran)</label>
          <textarea
            v-model="form.receipt_template"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2 h-32 focus:ring focus:ring-blue-600/50"
          ></textarea>
          <p class="text-xs text-gray-400 mt-1">
            Variabel: <code v-html="'[store_name]'"></code>, <code v-html="'[store_address]'"></code>, <code v-html="'[store_contact]'"></code>
          </p>
          <p v-if="form.errors.receipt_template" class="text-red-400 text-xs mt-1">{{ form.errors.receipt_template }}</p>
        </div>

        <div>
          <label class="block text-sm font-semibold mb-1">Pajak (%)</label>
          <input
            v-model="form.tax_rate"
            type="number"
            step="0.01"
            class="w-full border border-gray-500/50 bg-gray-700 text-white rounded p-2"
          />
          <p v-if="form.errors.tax_rate" class="text-red-400 text-xs mt-1">{{ form.errors.tax_rate }}</p>
        </div>
  
        <!-- Save Button -->
        <button
          type="submit"
          class="px-4 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold transition ease-in-out text-white rounded"
          :disabled="form.processing"
        >
          Simpan
        </button>
      </form>

      <!-- Receipt Preview -->
      <div class="bg-white text-black border border-gray-400 rounded p-3 w-[280px] font-mono text-[12px] leading-tight">
        <!-- Header -->
        <div class="text-left mb-2">
          <div class="uppercase font-bold text-[14px]">
            {{ form.store_name || 'NAMA TOKO' }}
          </div>
          <div>{{ form.store_address || 'Alamat Toko' }}</div>
          <div>Telp: {{ form.store_contact || 'Kontak' }}</div>
        </div>
        <br>
        <div class="border-t border-dashed border-gray-500 my-1"></div>

        <!-- Info Transaksi -->
        <div class="space-y-[2px]">
          <div class="flex justify-between"><span>No. Order</span><span>INV-20250915-001123</span></div>
          <div class="flex justify-between"><span>Tanggal</span><span>{{ new Date().toLocaleString() }}</span></div>
          <div class="flex justify-between"><span>Kasir</span><span>superadmin</span></div>
        </div>

        <div class="border-t border-dashed border-gray-500 my-1"></div>

        <!-- Items -->
        <div class="space-y-1">
          <div>
            <div class="flex justify-between">
              <span>Indomie Goreng</span><span>2 pcs</span>
            </div>
            <div class="flex justify-between">
              <span class="pl-4">x Rp3.500</span><span>Rp7.000</span>
            </div>
          </div>
          <div>
            <div class="flex justify-between">
              <span>Aqua Botol</span><span>1 pcs</span>
            </div>
            <div class="flex justify-between">
              <span class="pl-4">x Rp5.000</span><span>Rp5.000</span>
            </div>
          </div>
          <div>
            <div class="flex justify-between">
              <span>Teh Kotak</span><span>3 pcs</span>
            </div>
            <div class="flex justify-between">
              <span class="pl-4">x Rp4.500</span><span>Rp13.500</span>
            </div>
          </div>
        </div>

        <div class="border-t border-dashed border-gray-500 my-1"></div>

        <!-- Summary -->
        <div class="space-y-[2px]">
          <div class="flex justify-between"><span>Subtotal</span><span>{{ rupiah(26500) }}</span></div>
          <div class="flex justify-between"><span>Diskon</span><span>{{ rupiah(1500) }}</span></div>
          <div class="flex justify-between"><span>PPN</span><span>{{ rupiah(((26500-1500)*(form.tax_rate/100))||0) }}</span></div>
          <div class="flex justify-between font-bold"><span>TOTAL</span><span>{{ rupiah(25000 + ((26500-1500)*(form.tax_rate/100))||0) }}</span></div>
          <div class="flex justify-between mt-1"><span>Tunai</span><span>{{ rupiah(30000) }}</span></div>
          <div class="flex justify-between"><span>Kembali</span><span>{{ rupiah(5000) }}</span></div>
        </div>

        <div class="border-t border-dashed border-gray-500 my-1"></div>

        <!-- Footer -->
        <div class="text-center text-xs mt-1">
          <p>*** {{ renderTemplate() || 'Terima kasih telah berbelanja di [store_name]' }} ***</p>
          <p>Terima Kasih & Sampai Jumpa</p>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
