<script setup>
import { ref } from 'vue';
import Sidebar from './components/Sidebar.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import HeaderDashboard from '@/components/HeaderDashboard.vue';
import { push } from 'notivue';
import { onClickOutside } from '@vueuse/core';
// Refs
const selectedUser = ref(null);
const fileName = ref(null);
const showModalDetail = ref(false);
const showModalTambah = ref(false);
const showModalUbah = ref(false);
const showModalHapus = ref(false);
const tambahRef = ref(null);
const ubahRef = ref(null);
const detailRef = ref(null);
const hapusRef = ref(null);

// Click Outside
onClickOutside(tambahRef, () => (showModalTambah.value = false));
onClickOutside(ubahRef, () => (showModalUbah.value = false));
onClickOutside(detailRef, () => (showModalDetail.value = false));
onClickOutside(hapusRef, () => (showModalHapus.value = false));

// Forms
const createUserForm = useForm({
  image: '',
  name: '',
  email: '',
  password: '',
  whatsapp_number: '',
  role: '',
  tanggal_lahir: '',
  jenis_kelamin: 'placeholder',
});

const editUserForm = useForm({
  id: null,
  _method: 'PATCH',
  image: '',
  name: '',
  email: '',
  password: '',
  whatsapp_number: '',
  role: '',
  tanggal_lahir: '',
  jenis_kelamin: 'placeholder',
});

const deleteForm = useForm({
  id: null,
});

// Helper function: Assign user to form
const fillFormFromUser = (form, user) => {
  Object.assign(form, {
    id: user.id,
    image: user.image,
    name: user.name,
    email: user.email,
    password: '', // kosongin password
    whatsapp_number: user.whatsapp_number,
    role: user.role,
    tanggal_lahir: user.tanggal_lahir,
    jenis_kelamin: user.jenis_kelamin,
  });
};

// Functions
const saveNewUser = () => {
  createUserForm.post('/user', {
    onSuccess: () => {
      push.success('Yey! Pengguna baru sukses ditambahin 🎉');
      router.visit(usePage().url);
    },
    onError: () => {
      push.error('Eh gagal nambah pengguna 😢 Coba lagi ya!');
    },
  });
};

const updateUser = () => {
  editUserForm.post('/user/' + editUserForm.id, {
    onSuccess: () => {
      push.success('Mantap! Data penggunanya udah di-update 🔧');
      router.visit(usePage().url);
    },
    onError: (errors) => {
      console.error(errors);
      push.error('Aduh, gak bisa update datanya 😵');
    },
  });
};

const deleteUser = () => {
  deleteForm.delete('/user/' + selectedUser.value.id, {
    data: { id: selectedUser.value.id },
    onSuccess: () => {
      push.success('Pengguna berhasil dihapus 💀 Bye bye~');
      closeModalHapus();
    },
    onError: () => {
      push.error('Oops, gagal hapus data 😬');
    },
  });
};

// Modal Detail Pengguna
const openModalDetail = (user) => {
  selectedUser.value = user;
  showModalDetail.value = true;
  fillFormFromUser(editUserForm, user);
};

const closeModalDetail = () => {
  showModalDetail.value = false;
  selectedUser.value = null;
};

// Modal Tambah Pengguna
const openModalTambah = () => {
  showModalTambah.value = true;
};

const closeModalTambah = () => {
  showModalTambah.value = false;
  createUserForm.reset();
};

// Modal Ubah Pengguna
const openModalUbah = () => {
  showModalUbah.value = true;
};

const closeModalUbah = () => {
  showModalUbah.value = false;
  editUserForm.reset();
};

// Update file input
const updateFileName = (event) => {
  const file = event.target.files ? event.target.files[0] : null;
  if (file) {
    createUserForm.image = file;
    editUserForm.image = file;
    fileName.value = file.name;
  } else {
    createUserForm.image = '';
    editUserForm.image = '';
    fileName.value = 'No file chosen';
  }
};

// Modal Hapus Pengguna
const openModalHapus = () => {
  showModalHapus.value = true;
};

const closeModalHapus = () => {
  showModalHapus.value = false;
  selectedUser.value = null;
};
</script>

<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24 md:pb-0">
    <HeaderDashboard />

    <section class="mt-4 w-full">

    </section>

    <section class="mt-6">
      <div>
        <div class="md:flex justify-between items-center">
          <h1 class="text-textDark text-lg font-semibold">Data Pengguna</h1>
          <div class="mt-4 md:mt-0">
            <button @click="openModalTambah"
              class="bg-primary py-3 md:px-8 w-full md:w-auto rounded-full flex justify-center items-center gap-2 cursor-pointer hover:brightness-90 duration-300">
              <p class="text-textDark text-sm translate-y-0.5">
                <i class="fi fi-rr-plus"></i>
              </p>
              <p class="text-textDark font-medium">Tambah Pengguna</p>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-4">
          <div v-for="userL in $page.props.users" :key="userL.id" @click="openModalDetail(userL)"
            class="relative col-span-1 bg-white p-6 mt-4 rounded-3xl flex items-center gap-4 text-start cursor-pointer">
            <!-- Badge -->
            <div class="absolute top-0 right-0 bg-primaryThin py-1.5 px-4 rounded-tr-3xl rounded-bl-3xl">
              <p class="text-sm text-primary font-medium capitalize">
                {{ userL.role }}
              </p>
            </div>
            <div class="h-14 w-14 rounded-full overflow-hidden">
              <img :src="userL.image" alt="user" />
            </div>
            <div>
              <h1 class="line-clamp-1 text-textDark font-semibold">
                {{ userL.name }}
              </h1>
              <h2 class="text-textDark">{{ userL.email }}</h2>
            </div>
            <div class="flex items-center ml-auto">
              <p class="text-textDark">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Detail Pengguna -->
    <Transition name="fade">
      <div v-if="showModalDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalDetail" ref="detailRef"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[90%] md:w-[40%] py-8 px-6 rounded-4xl shadow-lg text-center z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Detail Pengguna</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalDetail">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="flex gap-4 mt-4">
          <div class="text-start w-full">
            <div class="flex flex-col justify-center items-center">
              <div class="h-32 w-32 rounded-full overflow-hidden relative">
                <img :src="selectedUser.image" alt="user profile" />
              </div>
              <div class="text-center mt-2 space-y-1">
                <h1 class="text-textDark text-xl font-bold">
                  {{ selectedUser.name }}
                </h1>
                <p class="bg-primaryThin text-primary text-sm py-1 px-4 rounded-full mx-auto w-fit capitalize">
                  {{ selectedUser.role }}
                </p>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 mt-6 gap-y-4">
              <div class="">
                <p class="text-textGrayDark text-xs">Nomor WhatsApp</p>
                <h2 class="text-textDark">
                  {{ selectedUser.whatsapp_number || 'N/A' }}
                </h2>
              </div>
              <div class="">
                <p class="text-textGrayDark text-xs">Alamat Email</p>
                <h2 class="text-textDark break-words">
                  {{ selectedUser.email }}
                </h2>
              </div>
              <div class="">
                <p class="text-textGrayDark text-xs">Tanggal Lahir</p>
                <h2 class="text-textDark">
                  {{ selectedUser.tanggal_lahir || 'N/A' }}
                </h2>
              </div>
              <div class="">
                <p class="text-textGrayDark text-xs">Jenis Kelamin</p>
                <h2 class="text-textDark">
                  {{
                    selectedUser.jenis_kelamin != null
                      ? selectedUser.jenis_kelamin === 1
                        ? 'Laki-laki'
                        : 'Perempuan'
                      : 'N/A'
                  }}
                </h2>
              </div>
            </div>
          </div>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="openModalUbah"
            class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300">
            <div class="flex justify-center items-center gap-2">
              <p class="text-lg translate-y-0.5">
                <i class="fi fi-rr-edit"></i>
              </p>
              <p>Ubah</p>
            </div>
          </button>
          <button @click="openModalHapus" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            <div class="flex justify-center items-center gap-2">
              <p class="text-lg translate-y-0.5">
                <i class="fi fi-rr-trash"></i>
              </p>
              <p>Hapus</p>
            </div>
          </button>
        </div>
      </div>
    </Transition>

    <!-- Overlay Gelap -->
    <Transition name="fade">
      <div v-if="showModalTambah" class="fixed inset-0 bg-black/50 z-20"></div>
    </Transition>

    <!-- Modal Tambah Pengguna -->
    <Transition name="scale">
      <div v-if="showModalTambah" ref="tambahRef"
        class="fixed inset-0 z-30 overflow-y-auto flex justify-center items-start">
        <div class="bg-bgGray w-[90%] md:w-[60%] p-4 md:py-8 md:px-6 rounded-4xl shadow-lg mt-10 mb-10">
          <div class="flex justify-between">
            <h1 class="text-textDark text-lg font-semibold">Tambah Pengguna</h1>
            <p class="text-textDark text-2xl cursor-pointer" @click="closeModalTambah">
              <i class="fi fi-rr-cross-small"></i>
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Upload Foto -->
            <div class="col-span-1">
              <label for="uploadFotoProfil" class="text-textDark">
                <p>Foto Profil</p>
              </label>
              <div class="relative mt-2">
                <input type="file" id="uploadFotoProfil" class="hidden" @change="updateFileName" ref="fileInput" />
                <label for="uploadFotoProfil"
                  class="flex items-center gap-2 w-full bg-white rounded-full cursor-pointer shadow-sm">
                  <span class="bg-bgGray py-3 px-4 rounded-l-full text-textDark text-sm md:text-base w-[50%]">
                    Choose File
                  </span>
                  <span class="text-textGrayDark pr-4 line-clamp-1 w-full">{{
                    fileName || 'No file chosen'
                    }}</span>
                </label>
              </div>
            </div>

            <!-- Nama Pengguna -->
            <div class="col-span-1">
              <label for="nama-pengguna" class="text-textDark">Nama Pengguna</label>
              <div class="relative mt-2">
                <input type="text" v-model="createUserForm.name" id="nama-pengguna"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Nama Pengguna" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-user"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Email -->
            <div class="col-span-1">
              <label for="email" class="text-textDark">Alamat Email</label>
              <div class="relative mt-2">
                <input type="email" v-model="createUserForm.email" id="email"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Email Pengguna" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-envelope"></i>
                  </p>
                </div>
              </div>
            </div>
            <!-- Sandi -->
            <div class="col-span-1">
              <label for="Sandi" class="text-textDark">Sandi</label>
              <div class="relative mt-2">
                <input type="text" v-model="createUserForm.password" id="Sandi"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Sandi" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-lock"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Nomor WhatsApp -->
            <div class="col-span-1">
              <label for="nomor-whatsapp" class="text-textDark">Nomor WhatsApp</label>
              <div class="relative mt-2">
                <input type="tel" id="nomor-whatsapp" v-model="createUserForm.whatsapp_number"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Nomor WhatsApp" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-brands-whatsapp"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Peran -->
            <div class="col-span-1">
              <label for="peran-pengguna" class="text-textDark">Peran Pengguna</label>
              <div class="relative mt-2">
                <select id="peran-pengguna"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none cursor-pointer"
                  required v-model="createUserForm.role">
                  <option value="" disabled selected>
                    Pilih Peran Pengguna
                  </option>
                  <option value="admin">Admin</option>
                  <option value="kasir">Kasir</option>
                  <option value="staff">Pelayan</option>
                  <option value="customer">Customer</option>
                </select>
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-user-gear text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Tanggal Lahir -->
            <div class="col-span-1">
              <label for="tanggal-lahir" class="text-textDark">Tanggal Lahir</label>
              <div class="relative mt-2">
                <input type="date" v-model="createUserForm.tanggal_lahir" id="tanggal-lahir"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none"
                  required />
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-calendar text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-span-1">
              <label for="jenis-kelamin" class="text-textDark">Jenis Kelamin</label>
              <div class="relative mt-2">
                <select id="jenis-kelamin" v-model="createUserForm.jenis_kelamin"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none cursor-pointer"
                  required>
                  <option disabled selected value="placeholder">
                    Pilih Jenis Kelamin
                  </option>
                  <option value="0">Laki-Laki</option>
                  <option value="1">Perempuan</option>
                </select>
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-mars text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Tombol Simpan Pengguna Baru-->
            <div class="md:col-span-2 flex justify-end">
              <button type="submit" @click="saveNewUser"
                class="bg-primary px-12 py-3 rounded-full cursor-pointer translate-x-1.5 hover:brightness-90 duration-300">
                <div class="flex justify-center items-center gap-2">
                  <p class="text-textDark text-lg translate-y-0.5">
                    <i class="fi fi-rr-disk"></i>
                  </p>
                  <p class="font-semibold">Simpan</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Overlay Gelap -->
    <Transition name="fade">
      <div v-if="showModalTambah" class="fixed inset-0 bg-black/50 z-20"></div>
    </Transition>

    <!-- Modal Ubah Pengguna -->
    <Transition name="scale">
      <div v-if="showModalUbah" ref="ubahRef"
        class="fixed inset-0 z-30 overflow-y-auto flex justify-center items-start">
        <div class="bg-bgGray w-[90%] md:w-[60%] p-4 md:py-8 md:px-6 rounded-4xl shadow-lg mt-10 mb-10">
          <div class="flex justify-between">
            <h1 class="text-textDark text-lg font-semibold">Ubah Pengguna</h1>
            <p class="text-textDark text-2xl cursor-pointer" @click="closeModalUbah">
              <i class="fi fi-rr-cross-small"></i>
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Upload Foto -->
            <div class="col-span-1">
              <label for="uploadFotoProfil" class="text-textDark">
                <p>Foto Profil</p>
              </label>
              <div class="relative mt-2">
                <input type="file" id="uploadFotoProfil" class="hidden" @change="updateFileName" ref="fileInput" />
                <label for="uploadFotoProfil"
                  class="flex items-center gap-2 w-full bg-white rounded-full cursor-pointer shadow-sm">
                  <span class="bg-bgGray py-3 px-4 rounded-l-full text-textDark text-sm md:text-base w-[50%]">
                    Choose File
                  </span>
                  <span class="text-textGrayDark pr-4 line-clamp-1 w-full">{{
                    fileName || 'No file chosen'
                    }}</span>
                </label>
              </div>
            </div>

            <!-- Nama Pengguna -->
            <div class="col-span-1">
              <label for="nama-pengguna" class="text-textDark">Nama Pengguna</label>
              <div class="relative mt-2">
                <input type="text" id="nama-pengguna" v-model="editUserForm.name"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Nama Pengguna" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-user"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Email -->
            <div class="col-span-1">
              <label for="email" class="text-textDark">Alamat Email</label>
              <div class="relative mt-2">
                <input type="email" id="email" v-model="editUserForm.email"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Email Pengguna" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-envelope"></i>
                  </p>
                </div>
              </div>
            </div>
            <!-- Password -->
            <div class="col-span-1">
              <label for="email" class="text-textDark">Sandi</label>
              <div class="relative mt-2">
                <input type="text" id="password"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Sandi Pengguna" v-model="editUserForm.password" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-rr-lock"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Nomor WhatsApp -->
            <div class="col-span-1">
              <label for="nomor-whatsapp" class="text-textDark">Nomor WhatsApp</label>
              <div class="relative mt-2">
                <input type="tel" id="nomor-whatsapp" v-model="editUserForm.whatsapp_number"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                  placeholder="Masukkan Nomor WhatsApp" required />
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                  <p class="text-textDark text-xl">
                    <i class="fi fi-brands-whatsapp"></i>
                  </p>
                </div>
              </div>
            </div>

            <!-- Peran -->
            <div class="col-span-1">
              <label for="peran-pengguna" class="text-textDark">Peran Pengguna</label>
              <div class="relative mt-2">
                <select id="peran-pengguna" v-model="editUserForm.role"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none cursor-pointer"
                  required>
                  <option value="placeholder" disabled selected>
                    Pilih Peran Pengguna
                  </option>
                  <option value="admin">Admin</option>
                  <option value="kasir">Kasir</option>
                  <option value="staff">Pelayan</option>
                  <option value="customer">Customer</option>
                </select>
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-user-gear text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Tanggal Lahir -->
            <div class="col-span-1">
              <label for="tanggal-lahir" class="text-textDark">Tanggal Lahir</label>
              <div class="relative mt-2">
                <input type="date" id="tanggal-lahir" v-model="editUserForm.tanggal_lahir"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none"
                  required />
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-calendar text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Jenis Kelamin -->
            <div class="col-span-1">
              <label for="jenis-kelamin" class="text-textDark">Jenis Kelamin</label>
              <div class="relative mt-2">
                <select id="jenis-kelamin"
                  class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none appearance-none cursor-pointer"
                  v-model="editUserForm.jenis_kelamin" required>
                  <option value="placeholder" disabled selected>
                    Pilih Jenis Kelamin
                  </option>
                  <option value="1">Laki-Laki</option>
                  <option value="0">Perempuan</option>
                </select>
                <div class="absolute inset-y-0 left-0 flex items-center pointer-events-none ps-4">
                  <i class="fi fi-rr-mars text-textDark text-xl"></i>
                </div>
              </div>
            </div>

            <!-- Tombol Ubah Pengguna -->
            <div class="md:col-span-2 flex justify-end">
              <button type="submit" @click="updateUser"
                class="bg-primary px-12 py-3 rounded-full cursor-pointer translate-x-1.5 hover:brightness-90 duration-300">
                <div class="flex justify-center items-center gap-2">
                  <p class="text-textDark text-lg translate-y-0.5">
                    <i class="fi fi-rr-disk"></i>
                  </p>
                  <p class="font-semibold">Simpan</p>
                </div>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Modal Konfirmasi Hapus Pengguna -->
    <Transition name="fade">
      <div v-if="showModalHapus" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalHapus" ref="hapusRef"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-50">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin menghapus pengguna ini?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalHapus" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button @click="deleteUser"
            class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300">
            Ya, Hapus
          </button>
        </div>
      </div>
    </Transition>

    <!-- Backdrop Modal Konfirmasi Keluar -->
    <Transition name="fade">
      <div v-if="showModalKeluar" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
        @click="closeModalKeluar"></div>
    </Transition>

    <!-- Modal Konfirmasi Keluar -->
    <Transition name="scale">
      <div v-if="showModalKeluar"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-30">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin keluar?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalKeluar" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button
            class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300"
            @click="$inertia.post('/logout')">
            Keluar
          </button>
        </div>
      </div>
    </Transition>

    <!-- Sidebar -->
    <Sidebar />
  </div>
</template>
