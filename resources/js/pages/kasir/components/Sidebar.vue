<script setup>
import { Link, usePage } from '@inertiajs/vue3';
const page = usePage();
const route = { path: page.url };
</script>

<template>
  <nav
    class="hidden md:flex fixed z-10 left-0 top-1/2 translate-x-4 -translate-y-1/2 h-[calc(100%-32px)] max-h-screen bg-white rounded-3xl flex-col items-center justify-center px-4"
  >
    <div class="space-y-3">
      <Link
        href="/kasir/dashboard"
        :class="
          route.path === '/kasir/dashboard'
            ? 'bg-primary text-textDark'
            : 'bg-transparent text-textGrayDark'
        "
        class="py-3 px-2 rounded-2xl text-center block"
      >
        <p class="text-3xl"><i class="fi fi-sc-home"></i></p>
        <p class="text-sm">Home</p>
      </Link>

      <Link
        href="/kasir/pesanan"
        :class="
          route.path === '/kasir/pesanan'
            ? 'bg-primary text-textDark'
            : 'bg-transparent text-textGrayDark'
        "
        class="py-3 px-2 rounded-2xl text-center block"
      >
        <p class="text-3xl"><i class="fi fi-sr-journal-alt"></i></p>
        <p class="text-sm">Pesanan</p>
      </Link>

      <Link
        href="/kasir/riwayat"
        :class="
          route.path === '/kasir/riwayat'
            ? 'bg-primary text-textDark'
            : 'bg-transparent text-textGrayDark'
        "
        class="py-3 px-2 rounded-2xl text-center block"
      >
        <p class="text-3xl"><i class="fi fi-sr-pending"></i></p>
        <p class="text-sm">Riwayat</p>
      </Link>

      <!-- <Link
        href="/kasir/pengaturan"
        :class="
          route.path === '/kasir/pengaturan'
            ? 'bg-primary text-textDark'
            : 'bg-transparent text-textGrayDark'
        "
        class="py-3 px-1 rounded-2xl text-center"
      >
        <p class="text-3xl"><i class="fi fi-sr-settings"></i></p>
        <p class="text-sm">Pengaturan</p>
      </Link> -->
    </div>
  </nav>

  <nav
    class="flex md:hidden fixed z-10 bottom-0 left-1/2 -translate-y-4 -translate-x-1/2 w-[calc(100%-32px)] bg-white shadow-sm rounded-3xl justify-around py-2"
  >
    <Link
      href="/kasir/dashboard"
      :class="
        route.path === '/kasir/dashboard'
          ? 'text-secondary'
          : 'text-textGrayDark'
      "
      class="w-20 text-center"
    >
      <p class="text-2xl"><i class="fi fi-sc-home"></i></p>
      <p class="text-sm">Home</p>
    </Link>

    <Link
      href="/kasir/pesanan"
      :class="
        route.path === '/kasir/pesanan' ? 'text-secondary' : 'text-textGrayDark'
      "
      class="w-20 text-center"
    >
      <p class="text-2xl"><i class="fi fi-sr-journal-alt"></i></p>
      <p class="text-sm">Pesanan</p>
    </Link>

    <Link
      href="/kasir/riwayat"
      :class="
        route.path === '/kasir/riwayat' ? 'text-secondary' : 'text-textGrayDark'
      "
      class="w-20 text-center"
    >
      <p class="text-2xl"><i class="fi fi-sr-pending"></i></p>
      <p class="text-sm">Riwayat</p>
    </Link>

    <Link
      href="/kasir/pengaturan"
      :class="
        route.path === '/kasir/pengaturan'
          ? 'text-secondary'
          : 'text-textGrayDark'
      "
      class="w-20 text-center"
    >
      <p class="text-2xl"><i class="fi fi-sr-settings"></i></p>
      <p class="text-sm">Pengaturan</p>
    </Link>
  </nav>
</template>
