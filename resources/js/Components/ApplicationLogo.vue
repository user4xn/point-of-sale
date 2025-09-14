<template>
    <div v-if="$page.props.store" class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
      <img
        v-if="$page.props.store.logo"
        :src="$page.props.store.logo"
        alt="Logo"
        class="h-10 w-10 rounded-full border border-gray-400 dark:border-gray-600 object-cover"
      />
      <div class="leading-tight">
        <div class="font-bold text-gray-900 dark:text-white">
          {{ $page.props.store.name || 'Nama Toko' }}
        </div>
        <div class="text-xs">
          {{ $page.props.store.address || '' }}
        </div>
        <div class="text-xs">
          {{ $page.props.store.contact || '' }}
        </div>
      </div>
    </div>
</template>
