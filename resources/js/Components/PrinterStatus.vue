<script setup lang="ts">
import { inject, ref, onMounted } from 'vue'
import { buildReceipt } from '@/utils/print-formatter'

const props = withDefaults(defineProps<{
    store?: {
        store_name?: string
        store_address?: string
        store_contact?: string
        receipt_template?: string
    }
    compact?: boolean
}>(), {
    store: () => ({}),
    compact: false,
})

const $swal = inject('swal') as any
const qzConnected = ref(false)
const printerName = ref<string | null>(null)
const checking = ref(false)
const printTesting = ref(false)

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

const testPrint = async () => {
    if (printTesting.value) return
    printTesting.value = true
    try {
        if (!qz.websocket.isActive()) {
            await qz.websocket.connect()
        }

        const content = buildReceipt({
            store_name: props.store?.store_name || 'NAMA TOKO',
            store_address: props.store?.store_address || 'Alamat Toko',
            store_contact: props.store?.store_contact || 'Kontak',
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
            footer: props.store?.receipt_template || 'Terima kasih telah berbelanja',
        })

        let printer = await qz.printers.getDefault()
        if (!printer) {
            const printers = await qz.printers.find()
            printer = Array.isArray(printers) ? printers[0] ?? null : printers ?? null
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

onMounted(checkQz)
</script>

<template>
    <div :class="compact ? 'flex flex-wrap items-center gap-2' : 'w-full p-4 bg-gray-800/50 rounded border border-gray-700'">
        <span v-if="!compact" class="font-semibold text-white mr-2">Status Printer</span>

        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium" :class="qzConnected ? 'bg-green-600/20 text-green-300' : 'bg-red-600/20 text-red-300'">
            <span class="size-2 rounded-full" :class="qzConnected ? 'bg-green-400' : 'bg-red-400'"></span>
            QZ: {{ checking ? '…' : (qzConnected ? 'Terhubung' : 'Off') }}
        </span>

        <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium" :class="printerName ? 'bg-green-600/20 text-green-300' : 'bg-yellow-600/20 text-yellow-300'">
            <span class="size-2 rounded-full" :class="printerName ? 'bg-green-400' : 'bg-yellow-400'"></span>
            {{ printerName || 'Printer tidak terdeteksi' }}
        </span>

        <button
            type="button"
            @click="testPrint"
            :disabled="printTesting"
            class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-600 hover:bg-green-500 text-white text-xs font-medium rounded transition disabled:opacity-50"
        >
            <span v-if="printTesting" class="inline-block animate-spin size-3 border-2 border-white border-t-transparent rounded-full"></span>
            {{ printTesting ? 'Mencetak…' : 'Test Print' }}
        </button>

        <button
            type="button"
            @click="checkQz"
            :disabled="checking"
            class="px-2.5 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded transition disabled:opacity-50"
        >
            {{ checking ? '…' : 'Cek Ulang' }}
        </button>

        <p v-if="!compact" class="text-xs text-gray-400 w-full mt-2">Pastikan QZ Tray berjalan di komputer ini dan printer thermal sudah terpasang &amp; terdeteksi.</p>
    </div>
</template>
