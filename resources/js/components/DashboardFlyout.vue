<template>
  <!-- Backdrop Modal Dropdown -->
  <Transition name="fade">
    <div v-if="show" class="fixed inset-0 bg-black/15 flex items-center justify-center z-20" @click="show = !show" />
  </Transition>

  <!-- Dropdown Menu -->
  <Transition name="scale">
    <div v-if="show" class="py-3 px-2 absolute right-0 z-50 mt-2 w-56 bg-white rounded-2xl shadow-lg">

      <!-- Hak Admin Section -->
      <template v-if="$page.props.auth?.user?.role == 'admin'">
        <div class="px-4 py-2 text-xs font-semibold text-textGray-dark uppercase tracking-wide">
          Hak Admin
        </div>
        <Link href="/admin/dashboard" v-if="!isAdminPage"
          class="flex items-center py-2.5 px-4 gap-4 hover:bg-bgGray rounded-2xl duration-300">
        <p class="text-textDark text-lg"><i class="fi fi-rr-exchange"></i></p>
        <p class="text-textDark">Menjadi Admin</p>
        </Link>
        <Link href="/kasir/dashboard" v-if="!isKasirPage"
          class="flex items-center py-2.5 px-4 gap-4 hover:bg-bgGray rounded-2xl duration-300">
        <p class="text-textDark text-lg"><i class="fi fi-rr-exchange"></i></p>
        <p class="text-textDark">Menjadi Kasir</p>
        </Link>
        <Link href="/pelayan/dashboard" v-if="!isPelayanPage"
          class="flex items-center py-2.5 px-4 gap-4 hover:bg-bgGray rounded-2xl duration-300">
        <p class="text-textDark text-lg"><i class="fi fi-rr-exchange"></i></p>
        <p class="text-textDark">Menjadi Pelayan</p>
        </Link>


        <!-- Divider -->
        <div class="border-t border-textGray mt-2 mb-1 md:mx-4"></div>
      </template>

      <!-- Akun Section -->
      <div class="px-4 py-2 text-xs font-semibold text-textGray-dark uppercase tracking-wide">
        Akun
      </div>
      <Link href="/pengaturan-akun"
        class="flex items-center py-2.5 px-4 gap-4 hover:bg-bgGray rounded-2xl duration-300">
      <p class="text-textDark text-lg"><i class="fi fi-rr-users"></i></p>
      <p class="text-textDark">Pengaturan Akun</p>
      </Link>
      <Link href="/profil" class="flex items-center py-2.5 px-4 gap-4 hover:bg-bgGray rounded-2xl duration-300">
      <p class="text-textDark text-lg"><i class="fi fi-rr-settings"></i></p>
      <p class="text-textDark">Pengaturan Profil</p>
      </Link>
      <button @click="() => { confirmLogout = true; show = false }"
        class="flex items-center px-4 hover:bg-bgGray p-2 mt-1 rounded-2xl gap-4 w-full duration-300 cursor-pointer">
        <p class="text-secondary text-lg"><i class="fi fi-rr-sign-out-alt"></i></p>
        <p class="text-secondary">Keluar</p>
      </button>
    </div>
  </Transition>


  <!-- Backdrop Konfirmasi Logout -->
  <Transition name="fade">
    <div v-if="confirmLogout" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
      @click="closeModalKeluar" />
  </Transition>

  <!-- Modal Konfirmasi Logout -->
  <Transition name="scale">
    <div v-if="confirmLogout"
      class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-30">
      <p class="text-center text-textDark text-xl font-semibold">
        Apakah Anda yakin ingin keluar?
      </p>
      <div class="flex justify-between mt-4 gap-2">
        <button @click="confirmLogout = false"
          class="cursor-pointer w-full text-secondary py-3 rounded-full font-medium">
          Batal
        </button>
        <button @click="$inertia.post('/logout')"
          class="cursor-pointer w-full bg-primary text-textDark py-3 rounded-full font-medium hover:brightness-90 duration-300">
          Keluar
        </button>
      </div>
    </div>
  </Transition>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { defineModel, ref } from 'vue';

const show = defineModel();
const confirmLogout = ref(false);

const closeModalKeluar = () => {
  confirmLogout.value = false;
};

// akses current url
const currentUrl = computed(() => usePage().url);

// cek posisi halaman
const isKasirPage = computed(() => currentUrl.value.startsWith('/kasir'));
const isAdminPage = computed(() => currentUrl.value.startsWith('/admin'));
const isPelayanPage = computed(() => currentUrl.value.startsWith('/pelayan'));
</script>
