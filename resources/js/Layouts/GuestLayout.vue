<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-900"
        style="background-image: url('/storage/img-assets/patterns.png'); background-size: cover; background-repeat: repeat;"
    >
          <!-- Overlay gelap biar pattern ga ganggu -->
          <div class="absolute inset-0 bg-gray-900/70"></div>

        <div class="relative z-10 flex justify-center gap-3 flex-col items-center">
            <h1 class="text-3xl font-bold text-white">(POS) Point Of Sale</h1>
            <Link href="/">
                <ApplicationLogo class="fill-current text-gray-500" />
            </Link>
        </div>

        <div
            class="relative z-10 mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-800"
        >
            <slot />
        </div>
    </div>
</template>
