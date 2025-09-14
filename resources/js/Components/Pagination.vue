<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import type { PaginationLink } from '@/types'

defineProps<{
  links: PaginationLink[]
  users: { from: number; to: number; total: number }
}>()
</script>

<template>
  <div :class="links.length > 3 ? 'justify-between' : 'justify-end'" class="flex  items-center text-white mt-4">
    <div v-if="links.length > 3" class="flex gap-2 flex-wrap">
      <Link
        v-for="link in links"
        :key="link.label"
        :href="link.url ?? ''"
        v-html="link.label"
        class="px-3 py-1 border border-white/40 rounded-full text-white hover:bg-bllue-600/80 transition ease-in-out"
        :class="{
          'bg-blue-600 text-white': link.active,
          'text-gray-500 cursor-not-allowed': !link.url,
          'hover:bg-blue-600/80': link.url
        }"
      />
    </div>

    <div>
      Menampilkan {{ users.from }} s/d {{ users.to }} dari
      {{ users.total }} data
    </div>
</div>
</template>
