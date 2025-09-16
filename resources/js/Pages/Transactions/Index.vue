<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import Modal from '@/Components/Modal.vue'
import Swal from 'sweetalert2'
import AfterPayModal from '@/Components/AfterPayModal.vue'

const props = defineProps<{
  settings: any
  cashier: any
  cashRegister: any
}>()

// realtime clock
const now = ref<Date>(new Date())
onMounted(() => setInterval(() => (now.value = new Date()), 1000))

// cart
interface CartItem {
  product_id: number
  sku: string
  name: string
  price: number
  quantity: number
  stock: number
}
const cart = ref<CartItem[]>([])

const subtotal = computed(() =>
  cart.value.reduce((t, i) => t + i.price * i.quantity, 0)
)

const tax = computed(() =>
  (subtotal.value * (props.settings.tax_rate || 0)) / 100
)

const grandTotal = computed(() => subtotal.value + tax.value)
const change = ref(0)

const form = ref({
  customer_name: '',
  items: [] as { product_id: number; quantity: number; price: number }[],
  paid_amount: 0,
})

// === Modal Bayar ===
const showPayModal = ref(false)
const cashInput = ref<number>(0)
const cashInputRef = ref<HTMLInputElement | null>(null)
const cashInputRaw = ref<number>(0)

// computed buat formatting
const formattedCashInput = computed({
  get: () =>
    cashInputRaw.value
      ? "Rp" +
        cashInputRaw.value
          .toString()
          .replace(/\B(?=(\d{3})+(?!\d))/g, ".")
      : "",
  set: (val: string) => {
    // buang semua selain angka
    const numeric = val.replace(/\D/g, "")
    cashInputRaw.value = numeric ? parseInt(numeric) : 0
  },
})

const openPayModal = () => {
  showPayModal.value = true
  cashInputRaw.value = 0
  change.value = 0
  nextTick(() => {
    cashInputRef.value?.focus()
  })
}

const showAfterPayModal = ref(false)
const lastTrxId = ref<number | null>(null)

const confirmPayment = () => {
  form.value.items = cart.value.map((i) => ({
    product_id: i.product_id,
    quantity: i.quantity,
    price: i.price,
  }))
  form.value.paid_amount = cashInputRaw.value
  change.value = cashInputRaw.value - grandTotal.value

  axios.post(route('transaction.store'), form.value)
    .then((res) => {
      if (res.data.success) {
        lastTrxId.value = res.data.trx_id
        showPayModal.value = false
        cart.value = []
        form.value = { customer_name: '', items: [], paid_amount: 0 }

        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Transaksi berhasil disimpan!',
          confirmButtonText: 'Lanjut',
        }).then(() => {
          showAfterPayModal.value = true
        })
      } else {
        Swal.fire('Error', res.data.message, 'error')
      }
    })
    .catch((err) => {
      Swal.fire('Error', err.data.message, 'error')
    })
}

const handlePrint = (id: number | null) => {
  console.log(id)
  if (!id) return
  ;(window as any).open(route('transaction.print', id), '_blank')
}

// === Search Produk ===
const search = ref<string>('')
const showProductModal = ref(false)
const searchResults = ref<any[]>([])
const searchError = ref(false)

const handleSearch = async () => {
  if (!search.value) return
  try {
    const res = await axios.get(route('transaction.search'), {
      params: { query: search.value },
    })
    const results = res.data

    if (results.length === 1) {
      addToCart(results[0])
      search.value = ''
      searchError.value = false
    } else if (results.length > 1) {
      searchResults.value = results
      showProductModal.value = true
      searchError.value = false
    } else {
      // produk tidak ditemukan → kasih border merah
      searchError.value = true
    }
  } catch (err) {
    console.error(err)
    searchError.value = true
  }
}

const addToCart = (product: any) => {
  const existing = cart.value.find((i) => i.product_id === product.id)
  if (existing) {
    if (existing.quantity < existing.stock) {
      existing.quantity++
    }
  } else {
    cart.value.push({
      product_id: product.id,
      sku: product.sku,
      name: product.name,
      price: product.sell_price,
      quantity: 1,
      stock: product.stock,
    })
  }
}

// 🚫 pastikan qty selalu valid
watch(
  cart,
  (items) => {
    items.forEach((i) => {
      if (i.quantity < 1) i.quantity = 1
      if (i.quantity > i.stock) i.quantity = i.stock
    })
  },
  { deep: true }
)

// ✅ disable tombol Bayar jika ada invalid
const canPay = computed(() => {
  if (!cart.value.length) return false
  if (grandTotal.value <= 0) return false
  for (const i of cart.value) {
    if (i.quantity < 1 || i.quantity > i.stock) return false
  }
  return true
})
</script>

<template>
  <Head title="Transaksi" />

  <div class="bg-gray-900 h-[100vh] text-white flex flex-col">
    <!-- HEADER -->
    <div class="p-4 bg-gray-600 flex gap-4 items-center h-24 justify-between">
      <div class="flex shrink-0 items-center">
        <Link :href="route('dashboard')">
          <ApplicationLogo class="block h-9 w-auto fill-current text-gray-200" />
        </Link>
      </div>
      <div class="text-xl font-bold text-center">
        Kasir <p class="font-normal">{{ props.cashier.name }}</p>
      </div>
      <div class="text-sm text-end">
        {{ now.toLocaleDateString() }} <br />
        <span class="text-4xl font-bold">{{ now.toLocaleTimeString() }}</span>
      </div>
    </div>

    <!-- BODY -->
    <div class="body flex-1">
      <div class="p-4">
        <div class="relative">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            type="text"
            :class="[
              'rounded-full text-lg font-bold px-5 w-full h-16 text-white pr-12', // kasih padding kanan biar ga ketiban icon
              searchError ? 'border-4 border-red-600 bg-gray-800' : 'border-0 bg-gray-700'
            ]"
            placeholder="Input Produk..."
          />

          <div
            v-if="searchError"
            class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center gap-2 text-red-500 px-2 py-1 rounded"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.25"
              stroke-linecap="round"
              stroke-linejoin="round"
            >
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" x2="12" y1="8" y2="12"/>
              <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span class="text-lg font-semibold">Tidak ditemukan</span>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-5 gap-5 w-full h-[60vh] ps-2 bg-gray-600/10">
        <!-- TABEL PRODUK -->
        <div class="col-span-3 overflow-y-auto max-h-[60vh]">
          <table class="w-full text-sm border-collapse">
            <thead class="bg-gray-600 sticky top-0">
              <tr>
                <th class="p-2">No</th>
                <th class="p-2">Kode Item</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Harga</th>
                <th class="p-2">Qty</th>
                <th class="p-2">Total</th>
              </tr>
            </thead>
            <tbody class="bg-gray-500/20">
              <tr
                v-for="(item, i) in cart"
                :key="item.product_id"
                class="border-b border-gray-600/20"
              >
                <td class="p-2">{{ i + 1 }}</td>
                <td class="p-2">{{ item.sku }}</td>
                <td class="p-2">{{ item.name }}</td>
                <td class="p-2">Rp{{ item.price.toLocaleString() }}</td>
                <td class="p-2">
                  <input
                    v-model="item.quantity"
                    type="number"
                    min="1"
                    :max="item.stock"
                    class="w-16 text-center rounded bg-gray-700"
                  />
                </td>
                <td class="p-2">Rp{{ (item.price * item.quantity).toLocaleString() }}</td>
              </tr>
              <tr
                v-for="i in Math.max(0, 15 - cart.length)"
                :key="'empty-' + i"
                class="border-b border-gray-600/20 h-10"
              >
                <td colspan="6"></td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- SUMMARY -->
        <div class="col-span-2 pe-4 flex flex-col justify-between">
          <div>
            <div class="text-center text-yellow-300 border-b-2 border-gray-600/20">
              <span class="text-[50px] font-bold">Rp{{ grandTotal.toLocaleString() }}</span>
            </div>
            <div class="mt-4 flex flex-col gap-1">
              <div class="flex gap-2 justify-between">
                <span>Sub Total</span>
                <span class="font-bold">Rp{{ subtotal.toLocaleString() }}</span>
              </div>
              <div class="flex gap-2 justify-between">
                <span>Pajak ({{ props.settings.tax_rate }}%)</span>
                <span class="font-bold">Rp{{ tax.toLocaleString() }}</span>
              </div>
              <div class="flex gap-2 justify-between">
                <span>Total</span>
                <span class="font-bold">Rp{{ grandTotal.toLocaleString() }}</span>
              </div>
            </div>
          </div>
          <div class="mb-4">
            <button
              type="button"
              @click="openPayModal"
              class="p-4 mt-4 bg-green-500 rounded-xl w-full text-2xl font-bold disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="!canPay"
            >
              Bayar
            </button>
            <button
              type="button"
              @click="cart = []"
              class="p-4 mt-4 bg-red-500 rounded-xl w-full text-2xl font-bold"
            >
              Reset
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="footer flex p-2 h-28 gap-2">
      <div class="bg-gray-600 p-4 flex items-center font-bold gap-2 rounded-md">
        Keluar Mode Kasir
      </div>
      <div class="bg-gray-600 p-4 flex items-center font-bold gap-2 rounded-md">
        Cetak Struk
      </div>
      <div class="bg-gray-600 p-4 flex items-center font-bold gap-2 rounded-md">
        Cek Transaksi
      </div>
    </div>

    <!-- MODAL BAYAR -->
    <Modal :show="showPayModal" maxWidth="md" @close="showPayModal = false" position="top">
      <div class="p-6 text-white text-center">
        <h2 class="text-xl font-bold mb-4">Input Uang Cash</h2>
        <div class="border-b-4 border-gray-900">
          <input
            ref="cashInputRef"
            v-model="formattedCashInput"
            type="text"
            inputmode="numeric"
            class="w-full p-3 bg-transparent text-yellow-300 font-bold text-[40px] text-center 
                  focus:outline-none focus:ring-0 border-0"
            placeholder="Masukkan nominal"
          />
        </div>
        <p class="text-yellow-300 text-[40px] font-bold mb-4">
          <span class="text-xl font-bold my-4">Kembalian</span> 
          Rp{{ (cashInputRaw - grandTotal).toLocaleString('id-ID') }}
        </p>
        <div class="flex justify-between gap-4">
          <button
            class="flex-1 py-4 bg-gray-600 rounded-xl text-2xl font-bold"
            @click="showPayModal = false"
          >
            Batal
          </button>
          <button
            class="flex-1 py-4 bg-green-600 rounded-xl text-2xl font-bold"
            @click="confirmPayment"
          >
            Konfirmasi
          </button>
        </div>
      </div>
    </Modal>

    <AfterPayModal
      :show="showAfterPayModal"
      :change="change"
      :trx-id="lastTrxId"
      @close="showAfterPayModal = false"
      @print="handlePrint(lastTrxId)"
    />

    <!-- MODAL PILIH PRODUK -->
    <Modal :show="showProductModal" maxWidth="2xl" @close="showProductModal = false" position="top">
      <div class="p-6 text-white">
        <h2 class="text-xl font-bold mb-4">Pilih Produk</h2>
        <table class="w-full text-left text-sm">
          <thead class="bg-gray-600">
            <tr>
              <th class="p-2">SKU</th>
              <th class="p-2">Nama</th>
              <th class="p-2">Harga</th>
              <th class="p-2">Stok</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in searchResults"
              :key="p.id"
              class="border-b border-gray-600 hover:bg-gray-600/20 cursor-pointer"
              @click="
                addToCart(p);
                showProductModal = false;
                search = '';
              "
            >
              <td class="p-2">{{ p.sku }}</td>
              <td class="p-2">{{ p.name }}</td>
              <td class="p-2">Rp{{ p.sell_price.toLocaleString() }}</td>
              <td class="p-2">{{ p.stock }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </Modal>
  </div>
</template>
