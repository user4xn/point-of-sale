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
  receipt_template: props.setting?.receipt_template ?? 'Terima kasih telah berbelanja di {{store_name}}',
  store_logo: null as File | null,
  tax_rate: props.setting?.tax_rate ?? 0,
})

const submit = () => {
  form.post('/settings', {
    forceFormData: true,
  })
}


const renderTemplate = () => {
  return form.receipt_template
    .replace(/\[store_name\]/g, form.store_name || '[Nama Toko]')
    .replace(/\[store_address\]/g, form.store_address || '[Alamat]')
    .replace(/\[store_contact\]/g, form.store_contact || '[Kontak]')
}
</script>

<template>
  <Head title="Store Settings" />

  <AuthenticatedLayout>
    <template #header>
      <div class="flex gap-2">
        <Link :href="route('dashboard')">
          <h2 class="text-xl font-semibold leading-tight text-gray-200 hover:text-yellow-500 transition ease-in-out">Dashboard Aplikasi</h2>
        </Link>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-white">
          <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
        <h2 class="text-xl font-semibold leading-tight text-gray-200 underline">Pengaturan Pengguna</h2>
      </div>
    </template>

    <div class="flex flex-wrap gap-3 p-1">
      <form @submit.prevent="submit" class="space-y-5 max-w-xl flex-1 p-4">
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
      <div class="flex-1 bg-white text-black border border-gray-400 rounded p-4 text-sm font-mono w-[280px]">
        <!-- Header -->
        <div class="text-center mb-3">
          <img
            v-if="props.setting?.store_logo"
            :src="`/storage/${props.setting.store_logo}`"
            alt="Logo"
            class="mx-auto h-12 mb-2"
          />
          <strong class="block uppercase">{{ form.store_name || 'Nama Toko' }}</strong>
          <span class="block">{{ form.store_address || 'Alamat Toko' }}</span>
          <span class="block">{{ form.store_contact || 'Kontak' }}</span>
        </div>

        <hr class="border-gray-400 my-2" />

        <!-- Order Info -->
        <div class="mb-2">
          <div>No. Order : <strong>#INV001</strong></div>
          <div>Tanggal   : {{ new Date().toLocaleString() }}</div>
          <div>Kasir     : Admin</div>
        </div>

        <hr class="border-gray-400 my-2" />

        <!-- Order Items -->
        <table class="w-full text-xs">
          <thead>
            <tr>
              <th class="text-left">Item</th>
              <th class="text-right">Qty</th>
              <th class="text-right">Harga</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in [
              { name: 'Indomie Goreng', qty: 2, price: 3500 },
              { name: 'Aqua Botol', qty: 1, price: 5000 },
              { name: 'Teh Kotak', qty: 3, price: 4500 }
            ]" :key="item.name">
              <td>{{ item.name }}</td>
              <td class="text-right">{{ item.qty }}</td>
              <td class="text-right">{{ item.price.toLocaleString() }}</td>
              <td class="text-right">{{ (item.qty * item.price).toLocaleString() }}</td>
            </tr>
          </tbody>
        </table>

        <hr class="border-gray-400 my-2" />

        <!-- Summary -->
        <div class="text-sm">
          <div class="flex justify-between">
            <span>Subtotal</span>
            <span>Rp 26.500</span>
          </div>
          <div class="flex justify-between">
            <span>Diskon</span>
            <span>Rp 1.500</span>
          </div>
          <div class="flex justify-between font-bold">
            <span>Grand Total</span>
            <span>Rp 25.000</span>
          </div>
          <div class="flex justify-between mt-2">
            <span>Cash</span>
            <span>Rp 30.000</span>
          </div>
          <div class="flex justify-between">
            <span>Kembali</span>
            <span>Rp 5.000</span>
          </div>
        </div>

        <hr class="border-gray-400 my-2" />

        <!-- Footer -->
        <div class="text-center text-xs">
          <p>*** {{ renderTemplate() }} ***</p>
          <p>Terima Kasih & Sampai Jumpa</p>
        </div>
      </div>

    </div>

  </AuthenticatedLayout>
</template>
