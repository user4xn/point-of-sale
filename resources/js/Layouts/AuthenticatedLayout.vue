<script setup lang="ts">
import { inject, ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const $swal = inject('swal') as any

watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) {
      $swal.fire('Berhasil', flash.success, 'success')
    }
    if (flash?.error) {
      $swal.fire('Gagal', flash.error, 'error')
    }
  },
  { deep: true, immediate: true }
)

const checkRegister = async () => {
  const res = await fetch(route('cash-register.check'))
  return await res.json()
}

const openCash = async () => {
  const { open } = await checkRegister()
  if (open) {
    return $swal.fire('Peringatan', 'Kas sudah dibuka, tutup dulu sebelum buka baru.', 'warning')
  }

  const { value: opening } = await $swal.fire({
    title: 'Buka Kas',
    input: 'number',
    inputLabel: 'Saldo awal',
    inputPlaceholder: 'Masukkan saldo kas awal',
    confirmButtonText: 'Buka!',
    cancelButtonText: 'Batal',
    showCancelButton: true,
  })

  if (opening !== undefined) {
    router.post(route('cash-register.open'), { opening_amount: opening }, {
      preserveState: false,
    })
  }
}

const closeCash = async () => {
  const { open } = await checkRegister()
  if (!open) {
    return $swal.fire('Peringatan', 'Tidak ada kas terbuka untuk ditutup.', 'warning')
  }
  
  const { value: closing } = await $swal.fire({
    title: 'Tutup Kas',
    input: 'number',
    inputLabel: 'Saldo akhir',
    inputPlaceholder: 'Masukkan saldo kas akhir',
    confirmButtonText: 'Tutup',
    cancelButtonText: 'Batal',
    showCancelButton: true,
  })

  if (closing !== undefined) {
    router.post(route('cash-register.close'), { closing_amount: closing }, {
      preserveState: false,
    })
  }
}
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Aplikasi
                                </NavLink>

                                 <!-- Tombol Buka Kas -->
                                <button
                                  @click="openCash"
                                  class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out"
                                >
                                  Buka Kas
                                </button>

                                <!-- Tombol Tutup Kas -->
                                <button
                                  @click="closeCash"
                                  class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700 focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out"
                                >
                                  Tutup Kas
                                </button>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {{ $page.props.auth.user?.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profil Saya
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Keluar
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Aplikasi
                        </ResponsiveNavLink>

                        <!-- Tombol Buka Kas -->
                        <button
                          @click="openCash"
                          class="w-full text-left px-4 py-2 text-sm rounded bg-green-600 hover:bg-green-500 text-white"
                        >
                          Buka Kas
                        </button>

                        <!-- Tombol Tutup Kas -->
                        <button
                          @click="closeCash"
                          class="w-full text-left px-4 py-2 text-sm rounded bg-red-600 hover:bg-red-500 text-white"
                        >
                          Tutup Kas
                        </button>>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user?.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user?.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil Saya
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Keluar
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow dark:bg-gray-800" v-if="$slots.header">
              <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center justify-between">
                <div>
                  <slot name="header" />
                </div>
              </div>
            </header>

            <!-- Page Content -->
            <main>
                <div class="py-12">
                  <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800 text-white">
                        <slot />
                      </div>
                  </div>
                </div>
            </main>
        </div>
    </div>
</template>
