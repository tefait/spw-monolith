<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import BottomNavbar from '@/components/BottomNavbar.vue';
import { push } from 'notivue';
const form = useForm({
  email: '',
  password: '',
  remember: false,
});
const submit = () => {
  form.post('/login', {
    onFinish: () => {
      form.reset('password');
    },
    onError: (errors) => {
      Object.values(errors).forEach((error) => {
        push.error(error);
      });
    },
  });
};
const showPassword = ref(false);
</script>

<template>
  <main class="bg-bgGray min-h-screen pb-36">
    <Head title="Login" />

    <section>
      <div class="w-full">
        <img
          src="/assets/images/SPANDUK SPW SECONDARY.webp"
          alt="SPANDUK SPW"
        />
      </div>
      <form @submit.prevent="submit" class="mt-4 p-4">
        <h1 class="text-textDark text-center text-xl font-bold">Masuk</h1>
        <div class="mt-4">
          <label for="email" class="text-textDark">Email</label>
          <div class="relative mt-2">
            <input
              type="email"
              id="email"
              v-model="form.email"
              autofocus
              class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Masukkan Email"
              required
            />
            <div
              class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1"
            >
              <p class="text-textDark text-xl">
                <i class="fi fi-rr-envelope"></i>
              </p>
            </div>
          </div>
        </div>
        <div class="mt-4">
          <label for="kata-sandi" class="text-textDark">Kata Sandi</label>
          <div class="relative mt-2">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              id="kata-sandi"
              class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Masukkan Kata Sandi"
              required
            />
            <div
              class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4"
            >
              <i class="fi fi-rr-lock text-textDark text-xl"></i>
            </div>
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-textDark cursor-pointer"
            >
              <i
                :class="
                  showPassword
                    ? 'fi fi-rr-eye text-xl'
                    : 'fi fi-rr-eye-crossed text-xl'
                "
              ></i>
            </button>
          </div>
        </div>
        <div class="mt-4">
          <button
            type="submit"
            class="bg-primary w-full py-3 mt-4 rounded-full cursor-pointer hover:brightness-90 duration-300"
          >
            <p class="text-textDark font-bold">Masuk</p>
          </button>
        </div>
        <div class="text-center text-sm mt-4">
          <p>
            Belum punya akun?
            <Link href="/daftar" class="text-secondary hover:underline">
              Daftar
            </Link>
          </p>
        </div>
        <Link
          href="/pusat-bantuan"
          class="fixed z-10 bottom-0 right-0 -translate-y-24 -translate-x-4 py-4 px-6 bg-white shadow-sm rounded-full hover:brightness-90 duration-300"
        >
          <p>Perlu Bantuan?</p>
        </Link>
      </form>
    </section>

    <BottomNavbar />
  </main>
</template>
