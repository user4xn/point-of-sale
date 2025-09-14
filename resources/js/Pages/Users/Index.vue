<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Paginated, User } from '@/types'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import Swal from 'sweetalert2'

const props = defineProps<{
  users: Paginated<User>
}>()

const deleteUser = async (id: number) => {
  const result = await Swal.fire({
    title: 'Konfimasi Hapus',
    text: 'data yang dihapus tidak dapat dikembalikan',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
  })

  if (result.isConfirmed) {
    router.delete(`/users/${id}`)
  }
}
</script>

<template>
  <Head title="User Management" />

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

    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <Link href="/users/create" class="px-3 py-2 bg-blue-600 hover:bg-blue-600/80 font-semibold transition ease-in-out text-white rounded">+ Tambah User</Link>
      </div>

      <table class="w-full table-auto text-sm text-white">
        <thead>
          <tr class="bg-gray-500/80">
            <th class="p-2 text-start">No</th>
            <th class="p-2 text-start">Nama</th>
            <th class="p-2 text-start">Email</th>
            <th class="p-2 text-start">Role</th>
            <th class="p-2 text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(user, i) in props.users.data" :key="i" class="hover:bg-white/10 transition ease-in-out">
            <td class="p-2">{{ props.users.from + i }}.</td>
            <td class="p-2">{{ user.name }}</td>
            <td class="p-2">{{ user.email }}</td>
            <td class="p-2">{{ user.role }}</td>
            <td class="p-2 flex gap-2 justify-end">
              <Link :href="`/users/${user.id}/edit`" class="px-2 py-1 bg-yellow-500 font-semibold hover:bg-yellow-500/80 transition ease-in-out text-white rounded">
                Edit
              </Link>
              <button @click="deleteUser(user.id)" class="px-2 py-1 bg-red-600 font-semibold hover:bg-red-600/80 transition ease-in-out text-white rounded">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <Pagination :links="props.users.links" :users="props.users" />
    </div>
  </AuthenticatedLayout>
</template>
