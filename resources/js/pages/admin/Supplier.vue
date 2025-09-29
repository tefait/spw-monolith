<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import AdminSidebar from './components/Sidebar.vue';
import PelayanSidebar from '../pelayan/components/Sidebar.vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import HeaderDashboard from '@/components/HeaderDashboard.vue';
import { push } from 'notivue';

// Dropdown Profil
const selectedSupplier = ref({});
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);
const newSupplier = useForm({
  name: '',
  whatsapp_number: '',
});
const saveNewSupplier = () => {
  newSupplier.post('/supplier/store', {
    onSuccess: () => {
      newSupplier.reset();
      closeModalTambah();
      push.success(usePage().props.flash.success);
    },
    onError: (error) => {
      push.error('Gagal menyimpan supplier');
      console.error('Error saving supplier', error);
    },
  });
};

const editSupplierForm = useForm({
  name: selectedSupplier.value.name,
  whatsapp_number: selectedSupplier.value.whatsapp_number,
});
const saveEditedSupplier = () => {
  editSupplierForm.put(`/supplier/update/${selectedSupplier.value.id}`, {
    onSuccess: () => {
      editSupplierForm.reset();
      closeModalUbah();
      push.success(usePage().props.flash.success);
    },
    onError: (error) => {
      push.error('Gagal menyimpan perubahan supplier');
      console.error('Error saving edited supplier', error);
    },
  });
};
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false;
  }
};
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

// Modal Detail Supplier
const showModalDetail = ref(false);
const openModalDetail = (supplier) => {
  selectedSupplier.value = supplier;
  showModalDetail.value = true;
};
const closeModalDetail = () => {
  showModalDetail.value = false;
};

// Modal Tambah Supplier
const showModalTambah = ref(false);
const openModalTambah = () => {
  showModalTambah.value = true;
};
const closeModalTambah = () => {
  showModalTambah.value = false;
};

// Modal Ubah Supplier
const showModalUbah = ref(false);
const openModalUbah = () => {
  editSupplierForm.name = selectedSupplier.value.name;
  editSupplierForm.whatsapp_number = selectedSupplier.value.whatsapp_number;
  showModalUbah.value = true;
};
const closeModalUbah = () => {
  showModalUbah.value = false;
};

// Modal Hapus Supplier
const showModalHapus = ref(false);
const openModalHapus = () => {
  showModalHapus.value = true;
};
const closeModalHapus = () => {
  showModalHapus.value = false;
};

// Modal Konfirmasi Keluar
const showModalKeluar = ref(false);
const openModalKeluar = () => {
  showModalKeluar.value = true;
};
const closeModalKeluar = () => {
  showModalKeluar.value = false;
};

const deleteSupplier = () => {
  router.delete('/supplier/delete/' + selectedSupplier.value.id, {
    onSuccess: () => {
      closeModalHapus();
      closeModalDetail();
      push.success(usePage().props.flash.success);
      router.visit('/admin/supplier');
    },
  });
};
</script>

<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24 md:pb-0">
    <HeaderDashboard/>

    <section class="mt-4 w-full">
      <div class="md:flex justify-between">
        <div class="">
          <div class="w-full md:w-96 relative">
            <input type="search" class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Cari supplier" />
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
              <p class="text-textDark text-xl">
                <i class="fi fi-rr-search"></i>
              </p>
            </div>
          </div>
        </div>
        <div class="mt-4 md:mt-0">
          <button @click="openModalTambah"
            class="bg-primary py-3 md:px-8 w-full md:w-auto rounded-full flex justify-center items-center gap-2 cursor-pointer hover:brightness-90 duration-300">
            <p class="text-textDark text-sm translate-y-0.5">
              <i class="fi fi-rr-plus"></i>
            </p>
            <p class="text-textDark font-medium">Tambah Supplier</p>
          </button>
        </div>
      </div>
    </section>

    <section class="mt-6">
      <div>
        <h1 class="text-textDark text-lg font-semibold">Data Supplier</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 md:gap-x-4">
          <div v-for="supplier in $page.props.suppliers" @click="openModalDetail(supplier)"
            class="col-span-1 bg-white p-6 mt-4 rounded-3xl flex items-center gap-4 text-start cursor-pointer">
            <div class="h-14 w-14 rounded-full overflow-hidden">
              <img src="/assets/images/user.webp" alt="user" />
            </div>
            <div>
              <h1 class="line-clamp-1 text-textDark font-semibold">
                {{ supplier?.name }}
              </h1>
              <h2 class="text-textDark">
                {{ supplier?.whatsapp_number }}
              </h2>
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

    <!-- Modal Detail Supplier -->
    <Transition name="fade">
      <div v-if="showModalDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
        @click="closeModalDetail"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalDetail"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[90%] md:w-[35%] py-8 px-6 rounded-4xl shadow-lg text-center z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Detail Supplier</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalDetail">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="flex gap-4 mt-4">
          <div class="text-start">
            <div>
              <p class="text-textGrayDark text-xs">Nama Supplier</p>
              <h1 class="text-textDark line-clamp-1">
                {{ selectedSupplier?.name }}
              </h1>
            </div>
            <div class="mt-2">
              <p class="text-textGrayDark text-xs">Nomor WhatsApp</p>
              <h2 class="text-textDark">
                {{ selectedSupplier?.whatsapp_number }}
              </h2>
            </div>
            <div class="mt-2">
              <p class="text-textGrayDark text-xs">Produk</p>
              <h2 class="text-textDark">
                {{ selectedSupplier?.items || 'Belum ada produk' }}
              </h2>
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

    <!-- Modal Tambah Supplier -->
    <Transition name="fade">
      <div v-if="showModalTambah" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
        @click="closeModalTambah"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalTambah"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-bgGray w-[90%] md:w-[35%] py-8 px-6 rounded-4xl shadow-lg z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Tambah Supplier</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalTambah">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="space-y-4 mt-4">
          <div class="col-span-1">
            <label for="nama-supplier" class="text-textDark">Nama Supplier</label>
            <div class="relative mt-2">
              <input type="text" id="nama-supplier" v-model="newSupplier.name"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Masukkan Nama Supplier" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-rr-supplier"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="col-span-1">
            <label for="nomor-whatsapp" class="text-textDark">Nomor WhatsApp</label>
            <div class="relative mt-2">
              <input type="tel" id="nomor-whatsapp"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                v-model="newSupplier.whatsapp_number" placeholder="Masukkan Nomor WhatsApp" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-brands-whatsapp"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="flex justify-end items-end mt-4">
            <button type="submit" @click="saveNewSupplier"
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
    </Transition>

    <!-- Modal Ubah Supplier -->
    <Transition name="fade">
      <div v-if="showModalUbah" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
        @click="closeModalUbah"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalUbah"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-bgGray w-[90%] md:w-[35%] py-8 px-6 rounded-4xl shadow-lg z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Ubah Supplier</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalUbah">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="space-y-4 mt-4">
          <div class="col-span-1">
            <label for="nama-supplier" class="text-textDark">Nama Supplier</label>
            <div class="relative mt-2">
              <input type="name" id="nama-supplier" v-model="editSupplierForm.name"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Masukkan Nama Supplier" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-rr-supplier"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="col-span-1">
            <label for="nomor-whatsapp" class="text-textDark">Nomor WhatsApp</label>
            <div class="relative mt-2">
              <input type="tel" id="nomor-whatsapp" v-model="editSupplierForm.whatsapp_number"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Masukkan Nomor WhatsApp" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-brands-whatsapp"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="flex justify-end items-end mt-4">
            <button type="submit" @click="saveEditedSupplier"
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
    </Transition>

    <!-- Modal Konfirmasi Hapus Supplier -->
    <Transition name="fade">
      <div v-if="showModalHapus" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40"
        @click="closeModalHapus"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalHapus"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-50">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin menghapus supplier ini?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalHapus" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button @click="deleteSupplier"
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
  </div>

  <!-- Sidebar -->
  <AdminSidebar v-if="$page.url === '/admin/supplier'" />
  <PelayanSidebar v-else-if="$page.url === '/pelayan/supplier'" />
</template>
