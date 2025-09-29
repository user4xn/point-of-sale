<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({ layout: AuthenticatedLayout })

const form = useForm({
  date: new Date().toISOString().slice(0, 10), // default hari ini
  amount: '',
  note: '',
})

const submit = () => {
  form.post(route('expenditures.store'))
}
</script>

<template>
  <Head title="Tambah Pengeluaran" />

  <div class="p-6 text-white pb-20">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Tambah Pengeluaran</h1>
      <Link
        href="/expenditures"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white"
      >
        ← Kembali
      </Link>
    </div>

    <!-- Form -->
    <form @submit.prevent="submit" class="space-y-6 max-w-lg">
      <!-- Tanggal -->
      <div>
        <label class="block text-sm font-semibold mb-1">Tanggal</label>
        <input
          v-model="form.date"
          type="date"
          class="w-full border bg-gray-700 rounded p-2"
        />
        <div v-if="form.errors.date" class="text-red-400 text-sm mt-1">
          {{ form.errors.date }}
        </div>
      </div>

      <!-- Nominal -->
      <div>
        <label class="block text-sm font-semibold mb-1">Nominal</label>
        <input
          v-model="form.amount"
          type="number"
          min="0"
          step="100"
          class="w-full border bg-gray-700 rounded p-2 text-left"
          placeholder="Contoh: 100000"
        />
        <div v-if="form.errors.amount" class="text-red-400 text-sm mt-1">
          {{ form.errors.amount }}
        </div>
      </div>

      <!-- Catatan -->
      <div>
        <label class="block text-sm font-semibold mb-1">Catatan</label>
        <input
          v-model="form.note"
          type="text"
          class="w-full border bg-gray-700 rounded p-2"
          placeholder="Contoh: Bayar listrik, beli ATK ..."
        />
        <div v-if="form.errors.note" class="text-red-400 text-sm mt-1">
          {{ form.errors.note }}
        </div>
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
