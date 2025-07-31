<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import BottomOffcanvas from '@/components/BottomOffcanvas.vue';
import BottomNavbar from '@/components/BottomNavbar.vue';
import { Head, Link } from '@inertiajs/vue3';

// === Setup State ===
const { props, url } = usePage();
const initialSearch = props.filters?.search ?? '';

const search = ref(initialSearch);
const displayedSearch = ref(initialSearch);
const products = ref(props.items);

// === Debounced Search ===
let debounceTimeout = null;
watch(search, (value) => {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(() => {
    router.get('/', { search: value }, { preserveState: false, replace: true });
    displayedSearch.value = value;
  }, 500);
});

// === Offcanvas Handler ===
const offcanvasRef = ref(null);
const showOffcanvas = (product) => {
  offcanvasRef.value?.openOffcanvas(product);
};

// === Back Button Lock ===
const blockBackButton = () => {
  if (url === '/') {
    window.history.pushState(null, '', window.location.href);
  }
};
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
        <input
          v-model="search"
          type="search"
          class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
          placeholder="Cari menu hari ini"
        />
        <div
          class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1"
        >
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
          <button class="flex-shrink-0 flex flex-col items-center bg-white p-2 rounded-2xl min-w-[70px] max-w-[90px] gap-2 hover:bg-primary active:bg-primary focus:bg-primary duration-300">
            <img class="h-10 w-10" src="/assets/images/makanan.png" alt="Makanan">
            <p class="text-textDark text-xs text-center">Nasi</p>
          </button>
          <button class="flex-shrink-0 flex flex-col items-center bg-white p-2 rounded-2xl min-w-[70px] max-w-[90px] gap-2 hover:bg-primary active:bg-primary focus:bg-primary duration-300">
            <img class="h-10 w-10" src="/assets/images/makanan.png" alt="Makanan">
            <p class="text-textDark text-xs text-center">Snack</p>
          </button>
          <button class="flex-shrink-0 flex flex-col items-center bg-white p-2 rounded-2xl min-w-[70px] max-w-[90px] gap-2 hover:bg-primary active:bg-primary focus:bg-primary duration-300">
            <img class="h-10 w-10" src="/assets/images/makanan.png" alt="Makanan">
            <p class="text-textDark text-xs text-center">Minuman</p>
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
        <div
          v-for="product in products"
          :key="product.id"
          class="flex flex-col bg-white p-3 rounded-2xl w-full max-w-[480px] h-[315px]"
        >
          <div class="h-[50%] w-full rounded-2xl overflow-hidden relative">
            <img
              :src="product.image"
              class="absolute top-0 left-0 w-full h-full object-cover"
              alt=""
            />
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
            <button
              @click="showOffcanvas(product)"
              class="bg-primary w-full py-2 rounded-full cursor-pointer hover:brightness-90 duration-300"
            >
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
