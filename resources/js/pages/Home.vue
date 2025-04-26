<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
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
    <section class="bg-primary py-6 px-4 space-y-5">
      <div class="flex justify-between">
        <h1 class="text-lg font-semibold text-textDark">Selamat Datang</h1>
        <Link href="/keranjang" class="text-textDark text-2xl">
        <i class="fi fi-rr-shopping-cart"></i>
        </Link>
      </div>

      <div class="relative">
        <input v-model="search" type="search"
          class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
          placeholder="Cari menu hari ini" />
        <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
          <i class="fi fi-rr-search text-textDark text-xl"></i>
        </div>
      </div>
    </section>

    <!-- Banner -->
    <section>
      <img src="/assets/images/SPANDUK SPW SECONDARY.webp" alt="SPANDUK SPW" />
    </section>

    <section class="bg-bgGray py-5 px-4 rounded-t-4xl -translate-y-7 space-y-4">
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
            <img class="h-10 w-10" :src="category.image">
            <p class="text-textDark text-xs text-center">{{ category.name }}</p>
          </button>
        </div>
      </div>
      <!-- Category -->


      <!-- Product List -->
      <h1 class="text-textDark text-lg font-bold">
        {{
          !displayedSearch
            ? 'Semua Menu'
            : `Hasil pencarian untuk \"${displayedSearch}\"`
        }}
      </h1>

      <div class="grid grid-cols-2 gap-4 pb-20">
        <div v-for="product in products" :key="product.id"
          class="flex flex-col bg-white p-3 rounded-2xl w-full max-w-[480px] h-[315px]">
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
            <button @click="showOffcanvas(product)"
              class="bg-primary w-full py-2 rounded-full cursor-pointer hover:brightness-90 duration-300">
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
