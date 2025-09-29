<script setup>
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import BottomOffcanvas from '@/components/BottomOffcanvas.vue';
import BottomNavbar from '@/components/BottomNavbar.vue';
import { Head, Link } from '@inertiajs/vue3';

const { props, url } = usePage();
const initialSearch = props.filters?.search ?? '';
const initialCategory = props.filters?.category ?? '';
let debounceTimeout = null;

// Refs
const search = ref(initialSearch);
const displayedSearch = ref(initialSearch);
const selectedCategory = ref(initialCategory);
const products = ref(props.items);
const offcanvasRef = ref(null);

// Handler
const showOffcanvas = (product) => {
  offcanvasRef.value?.openOffcanvas(product);
};
const blockBackButton = () => {
  if (url === '/') {
    window.history.pushState(null, '', window.location.href);
  }
};
const changeCategory = (id) => {
  router.get('/', { category: id }, { preserveState: false, replace: true });
};
const categoryName = computed(() => {
  if (!selectedCategory.value) return '';
  const cat = props.categories?.find(c => c.id == selectedCategory.value);
  return cat ? cat.name : '';
});

const headingText = computed(() => {
  if (!displayedSearch.value && !selectedCategory.value) {
    return 'Semua Menu';
  }
  if (!displayedSearch.value && selectedCategory.value) {
    return `Menu untuk kategori ${categoryName.value}`;
  }
  if (displayedSearch.value && !selectedCategory.value) {
    return `Hasil pencarian untuk "${displayedSearch.value}"`;
  }
  return `Hasil pencarian untuk "${displayedSearch.value}" di kategori ${categoryName.value}`;
});

// Hooks
watch(search, (value) => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    router.get('/', { search: value, category: selectedCategory.value }, { preserveState: false, replace: true });
    displayedSearch.value = value;
  }, 500);
});


onMounted(() => {
  if (url === '/') {
    window.history.pushState(null, '', window.location.href);
    window.addEventListener('popstate', blockBackButton);
  }
});
onUnmounted(() => {
  window.removeEventListener('popstate', blockBackButton);
  clearTimeout(debounceTimeout);
});
</script>

<template>
  <main class="bg-bgGray min-h-screen">

    <Head title="Beranda" />

    <!-- Hero Section -->
    <!-- <section class="bg-primary py-6 px-4 space-y-7">
      <div class="relative flex items-center justify-between w-full">
        <div class="w-10"></div>
        <h1
          class="absolute w-80 leading-6 left-1/2 transform -translate-x-1/2 text-lg font-semibold text-textDark text-center">
          SiPEKA<br>
          Sistem Penjualan Karya Siswa<br>
          <b>SMKN 2 SUMEDANG</b>
        </h1>

        <div>
          <Link href="/keranjang" class="text-textDark text-2xl">
          <i class="fi fi-rr-shopping-cart"></i>
          </Link>
        </div>
      </div>


      <div class="relative">
        <input v-model="search" type="search"
          class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
          placeholder="Cari menu hari ini" />
        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
          <i class="fi fi-rr-search text-textDark text-xl"></i>
        </div>
      </div>
    </section> -->

    <!-- Banner -->
    <!-- <section>
      <img src="/assets/images/SPANDUK SPW SECONDARY.webp" alt="SPANDUK SPW" />
    </section> -->


    <!-- Hero Section -->
    <section class="py-6 px-4 space-y-5">
      <div class="flex justify-around items-center">
        <div class="w-1/4"></div>
        <div class="w-2/4">
          <h1 class="leading-6 text-base font-semibold text-textDark text-center">
            <span class="font-bold text-xl">SiPEKA</span><br>
            <span class="font-normal">Sistem Penjualan Karya Siswa</span><br>
            <b>SMKN 2 SUMEDANG</b>
          </h1>
        </div>
        <!-- <img src="/assets/images/logo.png" alt="Logo ASoleh" class="w-18 h-18"> -->
        <div class="w-1/4 flex justify-end items-center">
          <Link href="/keranjang" class="text-textDark bg-primaryThin px-3 py-2.5 rounded-xl text-2xl">
          <p class="translate-y-0.5"><i class="fi fi-rr-shopping-cart"></i></p>
          </Link>
        </div>
      </div>
    </section>

    <!-- Banner -->
    <section class="px-4">
      <img src="/assets/images/SPANDUK SPW SECONDARY.webp" alt="BANNER ASOLEH" class="rounded-2xl" />
    </section>

    <div class="relative mt-4 px-4">
      <input v-model="search" type="search"
        class="peer py-3 px-4 ps-14 block w-full bg-white rounded-2xl focus:outline-none shadow-lg"
        placeholder="Cari produk" />
      <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-8 pt-1">
        <i class="fi fi-rr-search text-textDark text-xl"></i>
      </div>
    </div>

    <section class="bg-bgGray py-5 px-4 rounded-t-4xl space-y-4">
      <!-- Category -->
      <h1 class="text-textDark text-lg font-bold mb-2">Kategori</h1>
      <div class="overflow-x-auto flex gap-3 pb-4 scroll-smooth">

        <div class="flex gap-2.5 w-max">
          <button
            class="flex-shrink-0 flex flex-col items-center p-2 rounded-2xl min-w-[70px] max-w-[90px] gap-2 hover:bg-primary active:bg-primary focus:bg-primary duration-300 cursor-pointer"
            @click="changeCategory('')" :class="{
              'bg-primary': !selectedCategory || selectedCategory === '',
              'bg-white': selectedCategory !== ''
            }">
            <div class="h-10 w-10">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 153 153" fill="none">
                <path
                  d="M69.2908 17.353C72.8491 11.9718 80.7468 11.9718 84.3051 17.353L106.01 50.1777C109.967 56.1608 105.676 64.1418 98.5032 64.1418H55.0927C47.9198 64.1418 43.6292 56.1608 47.5856 50.1777L69.2908 17.353Z"
                  fill="#68CA44" />
                <ellipse cx="116.039" cy="106.858" rx="26.557" ry="26.5" fill="#FED2A4" />
                <rect x="11" y="80.3582" width="53.114" height="53" rx="9" fill="#017B4E" />
              </svg>
            </div>
            <p class="text-textDark text-xs text-center">Semua</p>
          </button>
          <button v-for="category in $page.props?.categories"
            class="flex-shrink-0 flex flex-col items-center p-2 rounded-2xl min-w-[70px] max-w-[90px] gap-2 hover:bg-primary active:bg-primary focus:bg-primary duration-300 cursor-pointer"
            @click="changeCategory(category.id)" :class="{
              'bg-primary': selectedCategory == category.id,
              'bg-white': selectedCategory != category.id
            }">
            <img class="h-10 w-10" @error="$event.target.src = '/assets/images/categoryFallback.svg'"
              :src="category?.image" />
            <p class="text-textDark text-xs text-center">{{ category.name }}</p>
          </button>
        </div>
      </div>
      <!-- Category -->


      <!-- Product List -->
      <h1 class="text-textDark text-lg font-bold">
        {{
headingText
        }}
      </h1>

      <div class="grid grid-cols-2 gap-4 pb-20">
        <div v-for="product in products" :key="product.id"
          class="flex flex-col bg-white p-3 rounded-2xl w-full cursor-pointer max-w-[480px] h-[315px]"
          @click="showOffcanvas(product)">
          <div class="h-[50%] w-full rounded-2xl overflow-hidden relative">
            <img :src="product.image" class="absolute top-0 left-0 w-full h-full object-cover" alt="" />
          </div>
          <div class="flex flex-col justify-between flex-1">
            <div class="my-2">
              <h1 class="line-clamp-2">{{ product.name }}</h1>
              <h2 class="font-bold">
                Rp{{ product.price.toLocaleString('id-ID') }}
              </h2>
              <p class="text-xs text-secondary mt-1">
                Sisa {{ product.stock }}
              </p>
            </div>
            <button class="bg-primary cursor-pointer w-full py-2 rounded-full hover:brightness-90 duration-300">
              Beli
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Bottom Components -->
    <BottomNavbar />
    <BottomOffcanvas ref="offcanvasRef" />
  </main>
</template>
