<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

defineOptions({
  layout: AuthenticatedLayout,
})

const props = defineProps<{ 
  supplier: { 
    id: number; 
    name: string; 
    contact?: string; 
    email?: string; 
    address?: string 
  } 
  back?: string | null
}>()

const form = useForm({
  name: props.supplier.name,
  contact: props.supplier.contact ?? '',
  email: props.supplier.email ?? '',
  address: props.supplier.address ?? '',
})

const submit = () => {
  form.put(route('suppliers.update', { supplier: props.supplier.id, back: props.back }))
}
</script>

<template>
  <Head title="Edit Supplier" />

  <div class="p-6 text-white">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Edit Supplier</h1>
      <Link
        :href="back ?? '/suppliers'"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 font-semibold transition ease-in-out text-white rounded"
      >
        ← Kembali
      </Link>
    </div>

    <form @submit.prevent="submit" class="space-y-5 max-w-lg">
      <div>
        <label class="block text-sm font-semibold mb-1">Nama</label>
        <input
          v-model="form.name"
          type="text"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Kontak</label>
        <input
          v-model="form.contact"
          type="text"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.contact" class="text-red-400 text-xs mt-1">{{ form.errors.contact }}</p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</p>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">Alamat</label>
        <textarea
          v-model="form.address"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        ></textarea>
        <p v-if="form.errors.address" class="text-red-400 text-xs mt-1">{{ form.errors.address }}</p>
      </div>

      <button
        type="submit"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-500 font-semibold transition ease-in-out text-white rounded"
        :disabled="form.processing"
      >
        Simpan
      </button>
    </form>
  </div>
</template>