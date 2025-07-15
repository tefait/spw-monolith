<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { usePage, router, Head } from '@inertiajs/vue3';
import BottomNavbar from '@/components/BottomNavbar.vue';
import { Link } from '@inertiajs/vue3';

const route = { path: usePage().url };
const showModal = ref(false);
const page = usePage();
const user = page.props.auth.user || null; // Check if user is logged in

// Function to handle back button navigation
const handleBackButton = () => {
  if (route.path === '/akun') {
    router.visit('/'); // Redirect to Home
  }
};

onMounted(() => {
  if (route.path === '/akun') {
    window.history.pushState(null, '', window.location.href);
    window.addEventListener('popstate', handleBackButton);
  }
});

onUnmounted(() => {
  window.removeEventListener('popstate', handleBackButton);
});

// Functions to control modal visibility
const openModal = () => {
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};
</script>

<template>
  <main class="bg-bgGray min-h-screen pt-16 pb-24">
    <Head title="Akun" />
    <div
      class="bg-primary w-full h-[50vh] rounded-b-[100px] absolute -top-[30vh] left-1/2 -translate-x-1/2"
    ></div>
    <section class="max-w-[480px] p-4">
      <div class="flex flex-col justify-center items-center">
        <div class="h-36 w-36 rounded-full overflow-hidden relative">
          <img :src="$page.props.auth.user.image || '/assets/images/user.webp'" alt="user profile" />
        </div>
        <div class="text-center mt-4">
          <h1 class="text-textDark text-xl font-bold">
            {{ user ? user.name : 'Kamu belum punya akun' }}
          </h1>
          <p
            v-if="user"
            class="text-textGrayDark text-sm"
            v-text="user.email"
          />
        </div>
      </div>
    </section>
    <section class="p-4">
      <div class="bg-white p-5 rounded-2xl">
        <div class="flex flex-col gap-5">
          <template v-if="user">
            <Link
              href="/profil"
              class="flex justify-between items-center group cursor-pointer"
            >
              <p
                class="flex gap-6 text-textDark text-xl group-hover:text-primary duration-300"
              >
                <i class="fi fi-rr-user"></i>
                <span class="text-base">Profil Saya</span>
              </p>
              <p class="text-textDark group-hover:text-primary duration-300">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </Link>
            <Link
              href="/pengaturan-akun"
              class="flex justify-between items-center group cursor-pointer"
            >
              <p
                class="flex gap-6 text-textDark text-xl group-hover:text-primary duration-300"
              >
                <i class="fi fi-rr-settings"></i>
                <span class="text-base">Pengaturan Akun</span>
              </p>
              <p class="text-textDark group-hover:text-primary duration-300">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </Link>
          </template>
          <template v-else>
            <Link
              href="/daftar"
              class="flex justify-between items-center group cursor-pointer"
            >
              <p
                class="flex gap-6 text-textDark text-xl group-hover:text-primary duration-300"
              >
                <i class="fi fi-rr-user-add"></i>
                <span class="text-base">Daftar</span>
              </p>
              <p class="text-textDark group-hover:text-primary duration-300">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </Link>
            <Link
              href="/login"
              class="flex justify-between items-center group cursor-pointer"
            >
              <p
                class="flex gap-6 text-textDark text-xl group-hover:text-primary duration-300"
              >
                <i class="fi fi-rr-sign-in-alt"></i>
                <span class="text-base">Masuk</span>
              </p>
              <p class="text-textDark group-hover:text-primary duration-300">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </Link>
          </template>
          <Link
            href="/pusat-bantuan"
            class="flex justify-between items-center group cursor-pointer"
          >
            <p
              class="flex gap-6 text-textDark text-xl group-hover:text-primary duration-300"
            >
              <i class="fi fi-rr-info"></i>
              <span class="text-base">Pusat Bantuan</span>
            </p>
            <p class="text-textDark group-hover:text-primary duration-300">
              <i class="fi fi-rr-angle-right"></i>
            </p>
          </Link>
          <button
            v-if="user"
            type="button"
            class="ps-1 cursor-pointer"
            @click="openModal"
          >
            <p
              class="flex gap-5 text-secondary text-xl hover:text-primary duration-300"
            >
              <i class="fi fi-rr-sign-out-alt"></i>
              <span class="text-base">Keluar</span>
            </p>
          </button>
        </div>
      </div>
    </section>

  </main>

  <!-- Background Hitam dengan Opacity -->
  <Transition name="fade">
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
      @click="closeModal"
    ></div>
  </Transition>

  <!-- Modal dengan Scale dan Posisi Tengah -->
  <Transition name="scale">
    <div
      v-if="showModal"
      class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-30"
    >
      <div class="">
        <p class="text-center text-textDark text-xl font-semibold">
          Apakah Anda yakin ingin keluar?
        </p>
      </div>
      <div class="flex justify-between mt-4 gap-2">
        <button
          @click="closeModal"
          class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer"
        >
          Batal
        </button>
        <button
          @click="$inertia.post('/logout')"
          class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300"
        >
          Keluar
        </button>
      </div>
    </div>
  </Transition>

  <!-- Bottom Navbar -->
  <BottomNavbar />
</template>
