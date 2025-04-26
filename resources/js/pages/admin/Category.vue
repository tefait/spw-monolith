<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue';
import AdminSidebar from './components/Sidebar.vue';
import PelayanSidebar from '../pelayan/components/Sidebar.vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import HeaderDashboard from '@/components/HeaderDashboard.vue';
import { push } from 'notivue';


const fileName = ref('');
const page = usePage();
// Dropdown Profil
const category = ref({});
const isDropdownOpen = ref(false);
const newCategory = useForm({
  name: '',
  image: null,
  status: true,
});

const savenewCategory = () => {
  newCategory.post('/category/store', {
    onSuccess: () => {
      newCategory.reset();
      closeModalTambah();
      fileName.value = '';

      push.success(page.props.flash.success);
    },
  });
};

const editCategoryForm = useForm({
  _method: 'PUT',
  name: null,
  image: null,
  status: null,
  id: null,
});

const submitEdit = () =>
  editCategoryForm.post('/category/update/' + editCategoryForm.id, {
    onSuccess: () => {
      editCategoryForm.reset();
      closeModalUbah();
      fileName.value = '';

      router.visit('/admin/kategori');
      push.success(page.props.flash.success);
    },
    onError: (error) => {
      console.error('Yah, gagal ngubah kategori nih :(', error);
      push.error('Oops, kategori-nya gagal diubah nih :(');
    },
  });
const dropdownRef = ref(null);
const updateFileName = (event, form = null) => {
  const file = event.target.files[0];
  if (form) {
    form.image = file;
  }
  fileName.value = file ? file.name : '';
};
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
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

const deleteCategory = () => {
  router.delete('/category/delete/' + category.value.id, {
    onSuccess: () => {
      closeModalHapus();
      closeModalDetail();
      fileName.value = '';
      router.visit('/admin/kategori');
    },
  });
};
// Modal Detail Kategori
const showModalDetail = ref(false);
const openModalDetail = (item) => {
  // Removed debug log for production
  showModalDetail.value = true;
  category.value = item;
};
const closeModalDetail = () => {
  showModalDetail.value = false;
};

// Toggle Aktif/Non Aktif Kategori
const statusText = computed(() =>
  category.value && category.value.status ? 'Aktif' : 'Nonaktif'
);
function toggle() {
  category.value.status = !category.value.status;
  router.post(
    `/category/toggle/${category.value.id}`,
    { status: category.value.status },
    {
      onSuccess: () => {
        push.success('Kategori berhasil diubah nih :)');
      },
      onError: (error) => {
        console.error('Gagal mengubah status kategori :(', error);
        push.error('Gagal mengubah status kategori :(');
      },
    }
  );
}

// Modal Tambah Kategori
const showModalTambah = ref(false);
const openModalTambah = () => {
  showModalTambah.value = true;
};
const closeModalTambah = () => {
  showModalTambah.value = false;
};

// Modal Ubah Kategori
const showModalUbah = ref(false);
const openModalUbah = () => {
  editCategoryForm.name = category.value?.name;
  editCategoryForm.status = category.value?.status;
  editCategoryForm.id = category.value?.id;
  showModalUbah.value = true;
};
const closeModalUbah = () => {
  showModalUbah.value = false;
};

// Modal Konfirmasi Hapus Kategori
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
</script>

<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24">
    <HeaderDashboard @openModalKeluar="openModalKeluar" />

    <section class="mt-4 w-full">
      <div class="md:flex justify-between">
        <div class="">
          <div class="w-full md:w-96 relative">
            <input type="search" class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
              placeholder="Cari kategori" />
            <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
              <p class="text-textDark text-xl">
                <i class="fi fi-rr-search"></i>
              </p>
            </div>
          </div>
        </div>
        <div class="flex gap-2 mt-4 md:mt-0">
          <button @click="openModalTambah"
            class="bg-primary py-3 md:px-8 w-full md:w-auto rounded-full flex justify-center items-center gap-2 cursor-pointer hover:brightness-90 duration-300">
            <p class="text-sm translate-y-0.5">
              <i class="fi fi-rr-plus"></i>
            </p>
            <p class="font-medium">Tambah Kategori</p>
          </button>
        </div>
      </div>
    </section>

    <section class="mt-6">
      <div>
        <h1 class="text-textDark text-lg font-semibold">
          Daftar Kategori
        </h1>
        <div class="grid grid-cols-1 md:grid-cols-3 md:gap-x-4">
          <div v-for="item in $page.props.categories" :key="item.id" @click="openModalDetail(item)" type="button"
            class="col-span-1 p-4 mt-4 rounded-3xl flex gap-4 text-start cursor-pointer"
            :class="item.status ? 'bg-primaryThin' : 'bg-white'">
            <div class="w-[calc(50%-56px)] h-[12vh] sm:w-[8vw] rounded-2xl overflow-hidden relative">
              <img :src="item.image" class="absolute top-0 left-0 w-full h-full object-cover" alt="" />
            </div>
            <div class="my-auto">
              <h1 class="line-clamp-1">{{ item.name }}</h1>
              <h2 class="font-bold">
                {{ item.status ? 'Aktif' : 'Nonaktif' }}
              </h2>
              <p class="text-xs text-textDark mt-1" v-if="item.items_count != null">{{ item.items_count }} Produk</p>
            </div>
            <div class="flex items-center ml-auto my-auto">
              <p class="text-textDark">
                <i class="fi fi-rr-angle-right"></i>
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Modal Detail Kategori -->
    <Transition name="fade">
      <div v-if="showModalDetail" class="fixed inset-0 bg-black/50 flex items-center justify-center z-20"
        @click="closeModalDetail"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalDetail"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[90%] md:w-[40%] py-8 px-6 rounded-4xl shadow-lg text-center z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Detail Kategori</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalDetail">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="md:flex gap-4 mt-4">
          <div
            class="relative w-36 h-36 md:w-[calc(50%-56px)] md:h-auto rounded-full md:rounded-3xl mx-auto overflow-hidden">
            <img :src="category?.image" class="absolute top-0 left-0 w-full h-full object-cover" alt="" />
          </div>
          <div class="w-[56%] text-start mt-4 md:mt-0">
            <h1 class="line-clamp-1">{{ category?.name }}</h1>
            <div class="mt-2">
              <p class="text-textGrayDark text-xs">Jumlah produk</p>
              <h2 class="text-textDark font-bold">
                {{ category?.items_count }} Produk
              </h2>
            </div>
            <div class="mt-6">
              <p class="text-textGrayDark text-xs">Status</p>
              <div class="flex items-center gap-3 mt-1">
                <button @click="toggle" :class="[
                  'w-[52px] h-7 rounded-full flex items-center transition-colors duration-300 p-1 cursor-pointer',
                  category?.status ? 'bg-green' : 'bg-textGray',
                ]">
                  <div
                    class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300 text-sm"
                    :class="category?.status ? 'translate-x-6' : 'translate-x-0'">
                    <i :class="[
                      'text-xs transition-opacity duration-200',
                      category?.status
                        ? 'fi fi-rr-check text-green'
                        : 'fi fi-rr-cross text-secondary',
                    ]"></i>
                  </div>
                </button>

                <p :class="category?.status ? 'text-green' : 'text-textGrayDark'">
                  {{ statusText }}
                </p>
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
      <div v-if="showModalTambah" class="fixed inset-0 bg-black/50 z-20" @click="closeModalTambah"></div>
    </Transition>

    <!-- Modal Tambah Kategori -->
    <Transition name="scale">
      <div v-if="showModalTambah"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-bgGray w-[90%] md:w-[35%] py-8 px-6 rounded-4xl shadow-lg z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Tambah Kategori</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalTambah">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="space-y-4 mt-4">
          <div class="col-span-1">
            <label for="nama-category" class="text-textDark">Nama Kategori</label>
            <div class="relative mt-2">
              <input type="text" id="nama-category" v-model="newCategory.name"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Masukkan Nama Kategori" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-rr-category"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="col-span-1">
            <label for="nomor-whatsapp" class="text-textDark">Lambang Kategori</label>
            <div class="relative mt-2">
              <input type="file" id="uploadFotoKategori" class="hidden" @change="updateFileName($event, newCategory)"
                ref="fileInput" />
              <label for="uploadFotoKategori"
                class="flex items-center gap-2 w-full bg-white rounded-full cursor-pointer shadow-sm">
                <span class="bg-bgGray py-3 px-4 rounded-l-full text-textDark text-sm md:text-base w-[50%]">Choose
                  File</span>
                <span class="text-textGrayDark pr-4 line-clamp-1 w-full">
                  {{ fileName || 'No file chosen' }}
                </span>
              </label>
              <span class="text-sm lowercase">* gambar harus berskala 1:1 dan dibawah 2MB</span>

            </div>
          </div>
          <div class="mt-2">
            <label class="text-textDark">Status</label>
            <div class="flex items-center gap-3 mt-1" @click.prevent="newCategory.status = !newCategory.status">
              <button :class="[
                'w-[52px] h-7 rounded-full flex items-center transition-colors duration-300 p-1 cursor-pointer',
                newCategory?.status ? 'bg-green' : 'bg-textGray',
              ]">
                <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300 text-sm"
                  :class="newCategory?.status ? 'translate-x-6' : 'translate-x-0'">
                  <i :class="[
                    'text-xs transition-opacity duration-200',
                    newCategory?.status
                      ? 'fi fi-rr-check text-green'
                      : 'fi fi-rr-cross text-secondary',
                  ]"></i>
                </div>
              </button>

              <p :class="newCategory?.status ? 'text-green' : 'text-textGrayDark'">
                {{ newCategory?.status ? 'Tampilkan' : 'Jangan tampilkan' }}
              </p>
            </div>
          </div>
          <div class="flex justify-end items-end mt-4">
            <button type="submit" @click="savenewCategory"
              class="bg-primary px-12 py-3 rounded-full cursor-pointer translate-x-1.5 hover:brightness-90 duration-300">
              <div class="flex justify-center items-center gap-2">
                <p class="text-lg translate-y-0.5">
                  <i class="fi fi-rr-disk"></i>
                </p>
                <p class="font-semibold">Simpan</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </Transition>
    <!-- Modal Ubah Kategori -->
    <Transition name="scale">
      <div v-if="showModalUbah"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-bgGray w-[90%] md:w-[35%] py-8 px-6 rounded-4xl shadow-lg z-30">
        <div class="flex justify-between">
          <h1 class="text-textDark text-lg font-semibold">Ubah Kategori</h1>
          <p class="text-textDark text-2xl cursor-pointer" @click="closeModalUbah">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <div class="space-y-4 mt-4">
          <div class="col-span-1">
            <label for="nama-category" class="text-textDark">Nama Kategori</label>
            <div class="relative mt-2">
              <input type="text" id="nama-category" v-model="editCategoryForm.name"
                class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Masukkan Nama Kategori" required />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-rr-category"></i>
                </p>
              </div>
            </div>
          </div>
          <div class="col-span-1">
            <label for="nomor-whatsapp" class="text-textDark">Lambang Kategori</label>
            <div class="relative mt-2">
              <input type="file" id="uploadFotoKategori" class="hidden"
                @change="updateFileName($event, editCategoryForm)" ref="fileInput" />
              <label for="uploadFotoKategori"
                class="flex items-center gap-2 w-full bg-white rounded-full cursor-pointer shadow-sm">
                <span class="bg-bgGray py-3 px-4 rounded-l-full text-textDark text-sm md:text-base w-[50%]">Choose
                  File</span>
                <span class="text-textGrayDark pr-4 line-clamp-1 w-full">
                  {{ fileName || 'No file chosen' }}
                </span>
              </label>
              <span class="text-sm lowercase">*gambar harus berskala 1:1 dan dibawah 2MB</span>

            </div>
          </div>
          <div class="mt-2">
            <label class="text-textDark">Status</label>
            <div class="flex items-center gap-3 mt-1"
              @click.prevent="editCategoryForm.status = !editCategoryForm.status">
              <button :class="[
                'w-[52px] h-7 rounded-full flex items-center transition-colors duration-300 p-1 cursor-pointer',
                editCategoryForm?.status ? 'bg-green' : 'bg-textGray',
              ]">
                <div class="w-5 h-5 bg-white rounded-full shadow-md transform transition-transform duration-300 text-sm"
                  :class="editCategoryForm?.status ? 'translate-x-6' : 'translate-x-0'">
                  <i :class="[
                    'text-xs transition-opacity duration-200',
                    editCategoryForm?.status
                      ? 'fi fi-rr-check text-green'
                      : 'fi fi-rr-cross text-secondary',
                  ]"></i>
                </div>
              </button>

              <p :class="editCategoryForm?.status ? 'text-green' : 'text-textGrayDark'">
                {{ editCategoryForm?.status ? 'Tampilkan' : 'Jangan tampilkan' }}
              </p>
            </div>
          </div>
          <div class="flex justify-end items-end mt-4">
            <button type="submit" @click="submitEdit"
              class="bg-primary px-12 py-3 rounded-full cursor-pointer translate-x-1.5 hover:brightness-90 duration-300">
              <div class="flex justify-center items-center gap-2">
                <p class="text-lg translate-y-0.5">
                  <i class="fi fi-rr-disk"></i>
                </p>
                <p class="font-semibold">Simpan</p>
              </div>
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- Overlay Gelap -->
    <Transition name="fade">
      <div v-if="showModalTambah" class="fixed inset-0 bg-black/50 z-20" @click="closeModalTambah"></div>
    </Transition>



    <!-- Modal Konfirmasi Hapus Kategori -->
    <Transition name="fade">
      <div v-if="showModalHapus" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40"
        @click="closeModalHapus"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalHapus"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-50">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin menghapus kategori ini?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalHapus" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button @click="deleteCategory"
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
  <AdminSidebar v-if="$page.url === '/admin/kategori'" />
  <PelayanSidebar v-else-if="$page.url === '/pelayan/dashboard'" />
</template>
