<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import type { User } from '@/types'

defineOptions({
  layout: AuthenticatedLayout,
})

const props = defineProps<{ user: User }>()

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  password: '',
  role: props.user.role,
})

const submit = () => {
  form.put(`/users/${props.user.id}`)
}
</script>

<template>
  <Head title="Edit User" />

  <div class="p-6 text-white">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-xl font-bold">Edit Pengguna</h1>
      <Link
        href="/users"
        class="px-3 py-2 bg-gray-600 hover:bg-gray-500 font-semibold transition ease-in-out text-white rounded"
      >
        ← Kembali
      </Link>
    </div>

    <form @submit.prevent="submit" class="space-y-5 max-w-lg">
      <!-- Name -->
      <div>
        <label class="block text-sm font-semibold mb-1">Nama</label>
        <input
          v-model="form.name"
          type="text"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.name" class="text-red-400 text-xs mt-1">{{ form.errors.name }}</p>
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-semibold mb-1">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.email" class="text-red-400 text-xs mt-1">{{ form.errors.email }}</p>
      </div>

      <!-- Password (opsional) -->
      <div>
        <label class="block text-sm font-semibold mb-1">Password (kosongkan jika tidak diganti)</label>
        <input
          v-model="form.password"
          type="password"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        />
        <p v-if="form.errors.password" class="text-red-400 text-xs mt-1">{{ form.errors.password }}</p>
      </div>

      <!-- Role -->
      <div>
        <label class="block text-sm font-semibold mb-1">Role</label>
        <select
          v-model="form.role"
          class="w-full border border-gray-500/50 bg-gray-700 rounded p-2 text-white"
        >
          <option value="admin">Admin</option>
          <option value="cashier">Kasir</option>
        </select>
        <p v-if="form.errors.role" class="text-red-400 text-xs mt-1">{{ form.errors.role }}</p>
      </div>

      <!-- Save -->
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
