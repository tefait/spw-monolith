<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';

const showPasswordOld = ref(false);
const showPasswordNew = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.put('/password/update', {
    preserveScroll: true,
    onSuccess: () =>
      form.reset('current_password', 'password', 'password_confirmation'),
  });
};
</script>

<template>
  <div class="bg-bgGray min-h-screen pb-4">
    <Head title="Pengaturan akun" />

    <section class="bg-primary w-full p-4">
      <div class="flex items-center py-3.5">
        <h1
          class="text-textDark text-lg font-semibold absolute left-1/2 -translate-x-1/2"
        >
          Pengaturan Akun
        </h1>
      </div>
    </section>

    <section class="mt-4 p-4">
      <div class="flex justify-center items-center gap-2">
        <i class="fi fi-br-user-pen text-textDark text-lg translate-y-0.5"></i>
        <h1 class="text-textDark text-lg font-semibold">Ubah Kata Sandi</h1>
      </div>

      <!-- Form -->
      <form class="space-y-4 mt-4" @submit.prevent="submit">
        <!-- Kata Sandi Saat Ini -->
        <div>
          <label for="current_password" class="text-textDark"
            >Kata Sandi Saat Ini</label
          >
          <div class="relative mt-2">
            <input
              :type="showPasswordOld ? 'text' : 'password'"
              v-model="form.current_password"
              id="current_password"
              class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Masukkan Kata Sandi Saat Ini"
            />
            <div
              class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4"
            >
              <i class="fi fi-rr-lock text-textDark text-xl"></i>
            </div>
            <button
              type="button"
              @click="showPasswordOld = !showPasswordOld"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-textDark cursor-pointer"
            >
              <i
                :class="
                  showPasswordOld
                    ? 'fi fi-rr-eye text-xl'
                    : 'fi fi-rr-eye-crossed text-xl'
                "
              ></i>
            </button>
          </div>
          <span
            v-if="form.errors.current_password"
            class="text-red-500 text-sm"
            >{{ form.errors.current_password }}</span
          >
        </div>

        <!-- Kata Sandi Baru -->
        <div>
          <label for="password" class="text-textDark">Kata Sandi Baru</label>
          <div class="relative mt-2">
            <input
              :type="showPasswordNew ? 'text' : 'password'"
              v-model="form.password"
              id="password"
              class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Masukkan Kata Sandi Baru"
            />
            <div
              class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4"
            >
              <i class="fi fi-rr-lock text-textDark text-xl"></i>
            </div>
            <button
              type="button"
              @click="showPasswordNew = !showPasswordNew"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-textDark cursor-pointer"
            >
              <i
                :class="
                  showPasswordNew
                    ? 'fi fi-rr-eye text-xl'
                    : 'fi fi-rr-eye-crossed text-xl'
                "
              ></i>
            </button>
          </div>
          <span v-if="form.errors.password" class="text-red-500 text-sm">{{
            form.errors.password
          }}</span>
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div>
          <label for="password_confirmation" class="text-textDark"
            >Konfirmasi Kata Sandi Baru</label
          >
          <div class="relative mt-2">
            <input
              :type="showPasswordConfirm ? 'text' : 'password'"
              v-model="form.password_confirmation"
              id="password_confirmation"
              class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Ketik Ulang Kata Sandi Baru"
            />
            <div
              class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4"
            >
              <i class="fi fi-rr-lock text-textDark text-xl"></i>
            </div>
            <button
              type="button"
              @click="showPasswordConfirm = !showPasswordConfirm"
              class="absolute inset-y-0 right-0 flex items-center pr-4 text-textDark cursor-pointer"
            >
              <i
                :class="
                  showPasswordConfirm
                    ? 'fi fi-rr-eye text-xl'
                    : 'fi fi-rr-eye-crossed text-xl'
                "
              ></i>
            </button>
          </div>
        </div>

        <p class="text-textDark text-sm mt-2">
          <span class="text-secondary">Catatan:</span> Kata sandi minimal 6
          karakter
        </p>

        <button
          type="submit"
          class="bg-primary w-full py-3 rounded-full cursor-pointer hover:brightness-90 duration-300"
          :disabled="form.processing"
        >
          <p class="text-textDark font-bold">Simpan Perubahan</p>
        </button>
      </form>
    </section>
  </div>
</template>
