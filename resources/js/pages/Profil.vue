<script setup>
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { push } from 'notivue';
import { ref } from 'vue';

const page = usePage();
const user = page.props.auth.user;
const fileName = ref('');

const form = useForm({
  _method: 'PUT',
  image: null,
  name: user?.name,
  email: user?.email,
  whatsapp_number: user?.whatsapp_number,
  tanggal_lahir: user?.tanggal_lahir,
  jenis_kelamin: user?.jenis_kelamin,
});



const updateFileName = (event) => {
  const file = event.target.files[0];
  form.image = file || null;
  fileName.value = file ? file.name : '';
};

const submitForm = () => {
  form.post('/profil',
    {
      onSuccess: () => {
        push.success({
          title: 'Sistem',
          message: 'Profil berhasil diperbarui',
        });
      },
      onError: (errors) => {
        push.error({
          title: 'Sistem',
          message: 'Yah! Gagal ubah profil nih',
        });
        console.error(errors);
      },
      onFinish: () => {
        fileName.value = '';
        form.reset();
        router.visit(page.url);
      }
    });
};

</script>

<template>

  <Head title="Edit Profil" />

  <div class="bg-bgGray min-h-screen pb-4">
    <section class="bg-primary w-full p-4">
      <div class="flex items-center py-3.5">
        <h1 class="text-textDark text-lg font-semibold absolute left-1/2 -translate-x-1/2">
          Profil Saya
        </h1>
      </div>
    </section>
    <section class="mt-4 p-4">
      <div class="flex justify-center items-center gap-2">
        <p class="text-textDark text-lg translate-y-0.5">
          <i class="fi fi-br-user-pen"></i>
        </p>
        <h1 class="text-textDark text-lg font-semibold">Ubah Profil</h1>
      </div>
      <div class="mt-4">
        <label for="uploadFotoProfil" class="text-textDark">
          <p>Foto Profil</p>
        </label>
        <div class="relative mt-2">
          <input type="file" id="uploadFotoProfil" class="hidden" @change="updateFileName" ref="fileInput" />
          <label for="uploadFotoProfil"
            class="flex items-center gap-2 w-full bg-white rounded-full cursor-pointer shadow-sm">
            <span class="bg-bgGray py-3 px-4 rounded-l-full text-textDark w-[50%]">Choose File</span>
            <span class="text-textGrayDark pr-4 line-clamp-1 w-full">{{
              fileName || 'No file chosen'
            }}</span>
          </label>
        </div>
      </div>
      <div class="mt-4">
        <label for="nama-pengguna" class="text-textDark">Nama Pengguna</label>
        <div class="relative mt-2">
          <input type="name" v-model="form.name" id="nama-pengguna"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
            placeholder="Masukkan nama lengkap" required />
          <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
            <p class="text-textDark text-xl">
              <i class="fi fi-rr-user"></i>
            </p>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <label for="alamat-email" class="text-textDark">Alamat Email</label>
        <div class="relative mt-2">
          <input type="email" v-model="form.email" id="alamat-email"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
            placeholder="Masukkan alamat email" required />
          <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
            <p class="text-textDark text-xl">
              <i class="fi fi-rr-envelope"></i>
            </p>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <label for="nomor-whatsapp" class="text-textDark">Nomor WhatsApp</label>
        <div class="relative mt-2">
          <input type="tel" v-model="form.whatsapp_number" id="nomor-whatsapp"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
            placeholder="Masukkan nomor whatsapp" required />
          <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
            <p class="text-textDark text-xl">
              <i class="fi fi-brands-whatsapp"></i>
            </p>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <label for="tanggal-lahir" class="text-textDark">Tanggal Lahir</label>
        <div class="relative mt-2">
          <input type="date" v-model="form.tanggal_lahir" id="tanggal-lahir"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none"
            required />
          <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
            <i class="fi fi-rr-calendar text-textDark text-xl"></i>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <label for="jenis-kelamin" class="text-textDark">Jenis Kelamin</label>
        <div class="relative mt-2">
          <select id="jenis-kelamin" v-model="form.jenis_kelamin"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none" required>
            <option value="" disabled selected>Pilih Jenis Kelamin</option>
            <option value="1">Laki-laki</option>
            <option value="0">Perempuan</option>
          </select>
          <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
            <i class="fi fi-rr-mars text-textDark text-xl"></i>
          </div>
        </div>
      </div>
      <div class="mt-4">
        <button @click="submitForm"
          class="bg-primary w-full py-3 mt-4 rounded-full cursor-pointer hover:brightness-90 duration-300">
          <p class="text-textDark font-bold">Simpan Perubahan</p>
        </button>
      </div>
    </section>
  </div>
</template>
