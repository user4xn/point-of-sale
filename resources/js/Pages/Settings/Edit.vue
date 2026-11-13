<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { inject, ref, onMounted } from 'vue'
import { buildReceipt } from '@/utils/print-formatter'

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

const $swal = inject('swal') as any
const printTesting = ref(false)

const testPrint = async () => {
  if (printTesting.value) return
  printTesting.value = true
  try {
    if (!qz.websocket.isActive()) {
      await qz.websocket.connect()
    }

    const content = buildReceipt({
      store_name: form.store_name || 'NAMA TOKO',
      store_address: form.store_address || 'Alamat Toko',
      store_contact: form.store_contact || 'Kontak',
      invoice: 'TEST-PRINT',
      date: new Date().toLocaleString('id-ID'),
      cashier: 'Test Print',
      items: [
        { name: 'Produk Contoh A', qty: '1 pcs', unit: 'pcs', price: 10000, subtotal: 10000 },
        { name: 'Produk Contoh B', qty: '2 pcs', unit: 'pcs', price: 5000, subtotal: 10000 },
      ],
      total_price: 20000,
      discount: 0,
      tax: 0,
      grand_total: 20000,
      paid_amount: 50000,
      change_amount: 30000,
      footer: form.receipt_template || 'Terima kasih telah berbelanja',
    })

    let printer = await qz.printers.getDefault()
    if (!printer) {
      const printers = await qz.printers.find()
      printer = printers[0]
    }
    if (!printer) {
      throw new Error('Printer tidak ditemukan. Pastikan QZ Tray berjalan dan printer thermal terpasang.')
    }

    const config = qz.configs.create(printer)
    await qz.print(config, [content])
    $swal.fire('Berhasil', 'Struk test berhasil dikirim ke printer', 'success')
  } catch (e: any) {
    $swal.fire('Gagal', 'Test print gagal: ' + (e?.message || 'QZ Tray tidak terhubung'), 'error')
  } finally {
    printTesting.value = false
  }
}

const qzConnected = ref(false)
const printerName = ref<string | null>(null)
const checking = ref(false)

const checkQz = async () => {
  checking.value = true
  try {
    if (!qz.websocket.isActive()) {
      await qz.websocket.connect()
    }
    qzConnected.value = qz.websocket.isActive()

    let printer: string | null = null
    try {
      printer = await qz.printers.getDefault()
      if (!printer) {
        const printers = await qz.printers.find()
        printer = Array.isArray(printers) ? printers[0] ?? null : printers ?? null
      }
    } catch {
      printer = null
    }
    printerName.value = printer
  } catch {
    qzConnected.value = false
    printerName.value = null
  } finally {
    checking.value = false
  }
}

onMounted(checkQz)
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
      <!-- Status Printer (QZ Tray) -->
      <div class="w-full p-4 bg-gray-800/50 rounded border border-gray-700">
        <div class="flex flex-wrap items-center gap-3">
          <h3 class="font-semibold text-white mr-2">Status Printer</h3>
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium" :class="qzConnected ? 'bg-green-600/20 text-green-300' : 'bg-red-600/20 text-red-300'">
            <span class="size-2 rounded-full" :class="qzConnected ? 'bg-green-400' : 'bg-red-400'"></span>
            QZ Tray: {{ checking ? 'Mengecek…' : (qzConnected ? 'Terhubung' : 'Tidak terhubung') }}
          </span>
          <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium" :class="printerName ? 'bg-green-600/20 text-green-300' : 'bg-yellow-600/20 text-yellow-300'">
            <span class="size-2 rounded-full" :class="printerName ? 'bg-green-400' : 'bg-yellow-400'"></span>
            Printer: {{ printerName || 'Tidak terdeteksi' }}
          </span>
          <button type="button" @click="checkQz" :disabled="checking" class="ml-auto px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded transition">
            {{ checking ? 'Mengecek…' : 'Cek Ulang' }}
          </button>
        </div>
        <p class="text-xs text-gray-400 mt-2">Pastikan QZ Tray berjalan di komputer ini dan printer thermal sudah terpasang &amp; terdeteksi.</p>
      </div>

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
        <div class="flex flex-wrap gap-3">
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold transition ease-in-out text-white rounded"
            :disabled="form.processing"
          >
            Simpan
          </button>
          <button
            type="button"
            @click="testPrint"
            class="px-4 py-2 bg-green-600 hover:bg-green-600/80 font-semibold transition ease-in-out text-white rounded inline-flex items-center gap-2"
            :disabled="printTesting"
          >
            <svg v-if="!printTesting" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" /></svg>
            <span v-if="printTesting" class="inline-block animate-spin size-5 border-2 border-white border-t-transparent rounded-full"></span>
            {{ printTesting ? 'Mencetak...' : 'Test Print' }}
          </button>
        </div>
      </form>

      <!-- Receipt Preview -->
      <div class="bg-white text-black border border-gray-400 rounded p-3 w-[280px] font-mono text-[12px] leading-tight">
        <!-- Header -->
        <div class="text-right mb-2">
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
