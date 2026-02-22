<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const showSurvey = ref(false)
const showIframe = ref(false);
const iframeURL = ref('');
const confirmGoHome = () => {
  const confirmExit = window.confirm(
    'Apakah Anda yakin ingin kembali ke Home?'
  )
  if (confirmExit) {
    router.visit('/')
  } else {
    window.history.pushState(null, '', window.location.href)
  }
}

const openSurvey = () => {
  const transactionCode = page.props.order.transaction_code
  localStorage.setItem(
    "survey_done_" + page.props.order.transaction_code,
    "1"
  )
  const baseUrl = "https://docs.google.com/forms/d/e/1FAIpQLSf6LR4zkF85QmlFa1zTwM8MmODC-pjA_qjDmC5WFdoX-u7s4A/viewform?embedded=true&usp=pp_url&entry.1164870403="
  iframeURL.value = baseUrl + transactionCode
  showIframe.value = true
}

onMounted(() => {
  const key = "survey_done_" + page.props.order.transaction_code
  if (!localStorage.getItem(key)) {
    setTimeout(() => {
      showSurvey.value = true
    }, 800)
  }
  const transactionCode = page.props.order.transaction_code
  window.history.replaceState(
    {},
    '',
    `/detail-transaksi/${transactionCode}`
  )
  window.history.pushState(null, '', window.location.href)
  window.addEventListener('popstate', confirmGoHome)
  setTimeout(() => {
    showSurvey.value = true
  }, 800)
})

onUnmounted(() => {
  window.removeEventListener('popstate', confirmGoHome)
})

</script>

<style scoped>
body {
  overscroll-behavior-y: none;
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
  /* Chrome, Safari */
}

.no-scrollbar {
  -ms-overflow-style: none;
  /* IE & Edge */
  scrollbar-width: none;
  /* Firefox */
}
</style>

<template>
  <div v-if="showSurvey" class="fixed inset-0 bg-black/15 flex items-center justify-center z-20"
    @click="showSurvey = !showSurvey" />
  <!-- Survey Modal -->
  <Transition name="fade">
    <div v-if="showSurvey" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
      <div class="w-[90%] max-w-md bg-white rounded-2xl p-6 shadow-lg"
        :class="showIframe && 'h-10/12  overflow-hidden'">
        <div class="flex justify-between h-1/12">
          <h2 :class="'text-lg' + (showIframe ? 'text-black font-semibold' : 'font-bold text-primary')"
            v-text="showIframe ? 'Survei Kegunaan Platform' : 'Bantu Kami Tingkatkan SiPEKA'">
          </h2>
          <p class="text-textDark text-2xl cursor-pointer" @click="showIframe = false; showSurvey = false">
            <i class="fi fi-rr-cross-small"></i>
          </p>
        </div>
        <section v-if="!showIframe">

          <p class="text-sm text-textDark mt-2">
            Survey singkat (±30 detik). Masukan Anda sangat berarti bagi kami.
          </p>

          <div class="flex gap-3 mt-6">
            <button @click="openSurvey" class="flex-1 bg-primary py-2 rounded-full hover:brightness-90">
              Isi Feedback
            </button>

            <button @click="showSurvey = false" class="flex-1 border border-gray-300 py-2 rounded-full">
              Lewati
            </button>
          </div>
        </section>
        <section v-else class="h-11/12 w-full overflow-hidden">
          <div class="w-full h-full overflow-y-scroll no-scrollbar">
            <iframe :src="iframeURL" class="w-full h-full" frameborder="0">
            </iframe>
          </div>
        </section>
      </div>
    </div>
  </Transition>

  <main class="bg-white min-h-screen">

    <Head title="Transaksi berhasil" />

    <section class="max-w-[480px] p-4">
      <div class="flex flex-col justify-center items-center translate-y-[20vh]">
        <div class="w-[40%] overflow-hidden relative">
          <img src="/assets/images/check.webp" alt="berhasil" />
        </div>
        <div class="text-center mt-8 px-8">
          <h1 class="text-primary text-xl font-bold">
            Pesanan Berhasil Dibuat
          </h1>
          <p class="text-textDark text-sm">
            Silahkan lakukan pembayaran dan ambil pesananmu ditempat
          </p>
        </div>
        <div class="w-full mt-4">
          <button @click="
            $inertia.visit(
              '/detail-transaksi/' + $page.props.order.transaction_code
            )
            " class="bg-primary w-full py-3 mt-4 rounded-full cursor-pointer hover:brightness-90 duration-300">
            <p class="font-bold">Lihat Detail Transaksi</p>
          </button>
          <p class="text-textDark text-sm text-center mt-4">
            Ada pertanyaan?
            <Link href="/pusat-bantuan" class="text-primary hover:underline cursor-pointer">
              Hubungi Penjual
            </Link>
          </p>
        </div>
      </div>
    </section>
  </main>
</template>
