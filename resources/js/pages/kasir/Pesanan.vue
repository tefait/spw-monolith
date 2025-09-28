<script setup>
import { ref, onMounted, onBeforeUnmount, watchEffect, watch } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import { push } from 'notivue';
import PusherJS from 'pusher-js';
import { konversiStatus } from '../../lib/utils';
import Sidebar from './components/Sidebar.vue';
import HeaderDashboard from '@/components/HeaderDashboard.vue';

const { props } = usePage();
const orders = ref(props.orders);
const lastOrderId = ref(orders.value?.[0]?.id || 0);
const previewImage = ref(false);
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);
const ORDER = ref({});
const showModalDetail = ref(false);
const showModalPesanan = ref(false);
const showModalKeluar = ref(false);
const sound = new Audio('/assets/NewOrder.opus');
const form = useForm({
  status: '',
  cash_given: null,
  change: null,
  proof: null, // This will hold the file object for upload
});
let isSoundAllowed = ref(false);

watchEffect(() => {
  if (ORDER.value.total_amount && form.cash_given >= ORDER.value.total_amount) {
    form.change = form.cash_given - ORDER.value.total_amount;
  } else {
    form.change = null;
  }
});

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false;
  }
};
onMounted(() => {
  document.addEventListener('click', () => {
    isSoundAllowed.value = true;
  }, { once: true });
  const Pusher = new PusherJS(import.meta.env.VITE_PUSHER_APP_KEY, {
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1'
  });
  const channel = Pusher.subscribe(`orders`)
  channel.bind('NewOrderCreated', (e) => {
    let newOrder = e.order;
    if (isSoundAllowed.value) {
      sound.play().catch((e) => {
        console.error('Gagal play audio:', e);
      });
    }
    orders.value.unshift(newOrder);
    push.info(`Pesanan baru masuk: #${newOrder.transaction_code}`);
  });
  document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});
const openModalDetail = (order) => {
  ORDER.value = order;
  let defaultCashGiven = order.cash_given; // Start with the existing value, if any.
  if (!defaultCashGiven || defaultCashGiven == 0) {
    defaultCashGiven = order.total_amount;
  }
  form.defaults({
    status: order.status,
    cash_given: defaultCashGiven, // Use our new calculated default value
    change: order.change,
    proof: null,
  }).reset();

  showModalDetail.value = true;
};
const closeModalDetail = () => {
  showModalDetail.value = false;
};


// Modal Konfirmasi Pesanan
const openModalPesanan = () => {
  showModalPesanan.value = true;
};
const closeModalPesanan = () => {
  showModalPesanan.value = false;
};

// Modal Konfirmasi Keluar
const openModalKeluar = () => {
  showModalKeluar.value = true;
};
const closeModalKeluar = () => {
  showModalKeluar.value = false;
};
const submitUpdate = (newStatus) => {
  form.status = newStatus; // Set the status right before submitting

  // Use .post for multipart/form-data (file uploads)
  form.post(`/pesanan/${ORDER.value.id}`, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => {
      closeModalPesanan();
      closeModalDetail();
      // Manually update the order in the local list to see changes instantly
      const index = orders.value.findIndex(o => o.id === ORDER.value.id);
      if (index !== -1) {
        // This is a simple update, for a full update you might need to refetch the order
        orders.value[index].status = newStatus;
        orders.value[index].cash_given = form.cash_given;
        orders.value[index].change = form.change;
      }
      push.success('Status pesanan berhasil diubah');
    },
    onError: (errors) => {
      push.error(errors.proof || 'Gagal mengubah status pesanan.');
      console.error('Gagal mengubah order status:', errors);
    },
  });
};
</script>

<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24 md:pb-0">
    <HeaderDashboard @openModalKeluar="openModalKeluar" />

    <section class="mt-6">
      <div>
        <h1 class="text-textDark text-lg font-semibold">Pesanan Masuk</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <button v-for="order in orders" @click="openModalDetail(order)"
            class="relative block bg-primaryThin p-4 space-y-5 rounded-2xl hover:bg-white duration-300 cursor-pointer">
            <!-- Badge -->
            <div class="absolute top-0 right-0 bg-primary py-1.5 px-4 rounded-tr-2xl rounded-bl-2xl">
              <p class="text-sm text-textDark font-medium">Pesanan Baru</p>
            </div>
            <div class="flex justify-between">
              <div class="text-start">
                <h2 class="text-textDark font-semibold">
                  {{ order.transaction_code }}
                </h2>
                <p class="text-textDark text-sm">
                  {{ new Date(order.created_at).toLocaleString('id-ID') }}
                </p>
              </div>
            </div>
            <div class="flex justify-between">
              <div>
                <h2 class="text-textDark line-clamp-2">
                  {{order.items.map((item) => item.item.name).join(', ')}}
                </h2>
              </div>
              <div class="text-end">
                <h2 class="text-textDark font-bold">
                  Rp{{ Number(order.total_amount).toLocaleString('id-ID') }}
                </h2>
                <p class="text-textDark text-sm">
                  {{
                    order.items.reduce((sum, item) => sum + item.quantity, 0)
                  }}
                  Item
                </p>
              </div>
            </div>
          </button>
        </div>
      </div>
    </section>

    <Transition name="fade">
      <div v-if="showModalDetail" class="fixed inset-0 bg-black/50 z-20" @click="closeModalDetail"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalDetail" class="fixed inset-0 z-30 flex items-center justify-center px-4">
        <div class="bg-bgGray w-full md:w-[60%] max-h-[90vh] md:max-h-screen overflow-y-auto rounded-4xl shadow-lg p-6">
          <div class="flex justify-between">
            <h1 class="text-textDark text-lg font-semibold">Detail Pesanan</h1>
            <p class="text-textDark text-2xl cursor-pointer" @click="closeModalDetail">
              <i class="fi fi-rr-cross-small"></i>
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="col-span-1">
              <h1 class="text-textDark font-semibold">Data Pemesan</h1>
              <div class="space-y-4 mt-4">
                <div>
                  <label class="text-textDark">Nama Pemesan</label>
                  <div class="relative mt-2">
                    <div class="py-3 px-4 ps-12 bg-white rounded-full">
                      <p class="text-textDark" v-text="ORDER.customer_name || 'N/A'"></p>
                    </div>
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                      <p class="text-textDark text-xl">
                        <i class="fi fi-rr-user"></i>
                      </p>
                    </div>
                  </div>
                </div>
                <div>
                  <label class="text-textDark">Nomor WhatsApp</label>
                  <div class="relative mt-2">
                    <div class="py-3 px-4 ps-12 bg-white rounded-full">
                      <p class="text-textDark" v-text="ORDER.whatsapp_number || 'N/A'"></p>
                    </div>
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                      <p class="text-textDark text-xl">
                        <i class="fi fi-brands-whatsapp"></i>
                      </p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-4 mt-4">
                <div>
                  <label class="text-textDark">Uang Diberikan (Rp)</label>
                  <input type="number" v-model="form.cash_given" placeholder="e.g. 50000"
                    class="mt-2 py-3 px-4 block w-full bg-white rounded-full focus:outline-none text-textDark" />
                <span class="text-xs">*Silakan input bila berbeda</span>
                  </div>
                <div>
                  <label class="text-textDark">Kembalian (Rp)</label>
                  <div class="relative mt-2">
                    <div class="py-3 px-4 bg-red-700/10 rounded-full cursor-not-allowed">
                      <p class="text-textDark font-medium"
                        v-text="'Rp.'+Number(form.change || 0).toLocaleString('id-ID')"></p>
                    </div><span class="text-xs">*Dikalkulasi oleh sistem</span>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <div class="bg-white mt-4 p-4 rounded-2xl space-y-2">
                  <div class="flex justify-between">
                    <p class="text-textDark">Kode Transaksi</p>
                    <p class="text-textDark font-semibold">
                      {{ ORDER.transaction_code }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-textDark">Waktu Pemesanan</p>
                    <p class="text-textDark">
                      {{ new Date(ORDER.created_at).toLocaleString('id-ID') }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-textDark">Status</p>
                    <p class="text-primary">
                      {{ konversiStatus(ORDER.status) }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-textDark">Metode Pembayaran</p>
                    <p class="text-textDark uppercase">
                      {{ ORDER.payment_method }}
                    </p>
                  </div>
                </div>
              </div>

              <div v-if="ORDER.payment_method === 'qris' && !ORDER.payment?.proof" class="mt-4">
                <label class="text-textDark">Upload Bukti Pembayaran (jika ada)</label>
                <input type="file" @input="form.proof = $event.target.files[0]"
                  class="mt-2 block w-full text-sm text-textDark file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primaryThin file:text-primary hover:file:bg-primary/30" />
                <progress v-if="form.progress" :value="form.progress.percentage" max="100" class="w-full mt-1">
                  {{ form.progress.percentage }}%
                </progress>
              </div>
              <div class="flex justify-between mt-6">
                <p class="text-textDark">
                  Total:
                  <span class="font-bold">Rp{{ Number(ORDER.total_amount).toLocaleString('id-ID') }}</span>
                </p>
              </div>
              <button v-if="String(ORDER.status) == 'under-review' && ORDER.payment?.proof" @click="previewImage = true"
                class="flex font-semibold bg-primary py-3 justify-center mt-4 w-full rounded-full cursor-pointer hover:brightness-90 duration-300">
                Lihat Bukti Pembayaran
              </button>
            </div>

            <Transition name="scale">
              <div class="absolute top-0 left-0 w-full h-full bg-black/25 z-10 flex items-center justify-center"
                v-if="previewImage && ORDER.status == 'under-review'" @click="previewImage = false">
                <img :src="`/storage/${ORDER.payment.proof}`" class="w-[80vh]" alt="Bukti pembayaran" @click.stop />
              </div>
            </Transition>

            <div class="col-span-1 flex flex-col h-full">
              <h1 class="text-textDark font-semibold">Detail Pesanan</h1>
              <div class="flex flex-col gap-4 mt-4 bg-white p-4 rounded-2xl max-h-[274px] overflow-y-auto">
                <div class="flex justify-between items-center" v-for="item in ORDER.items" :key="item.id">
                  <div>
                    <h1 class="line-clamp-1">
                      {{ item.item.name }}
                    </h1>
                    <h2 class="font-bold">
                      Rp{{ Number(item.item.price).toLocaleString('id-ID') }}
                    </h2>
                  </div>
                  <p class="text-xs text-textDark mt-1">x{{ item.quantity }}</p>
                </div>
              </div>

              <div class="mt-4">
                <label class="text-textDark">Catatan</label>
                <div class="relative mt-2">
                  <div class="py-3 px-4 ps-12 bg-white rounded-full">
                    <p class="text-textDark">
                      {{ ORDER.notes || 'N/A' }}
                    </p>
                  </div>
                  <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                    <p class="text-textDark text-xl">
                      <i class="fi fi-rr-edit"></i>
                    </p>
                  </div>
                </div>
              </div>

              <button @click="openModalPesanan" :disabled="form.processing"
                class="bg-primaryThin py-3 mt-4 md:mt-auto w-full rounded-full cursor-pointer hover:brightness-90 duration-300 disabled:opacity-50 disabled:cursor-wait">
                <p class="font-semibold">Konfirmasi Pesanan</p>
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalPesanan"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-50">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin konfirmasi pesanan ini?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalPesanan" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button @click="submitUpdate('done')" :disabled="form.processing"
            class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300 disabled:opacity-50">
            Ya, Konfirmasi
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

    <!-- Backdrop Modal Konfirmasi Pesanan -->
    <Transition name="fade">
      <div v-if="showModalPesanan" class="fixed inset-0 bg-black/50 flex items-center justify-center z-40"
        @click="closeModalPesanan"></div>
    </Transition>

    <!-- Modal Konfirmasi Pesanan -->
    <Transition name="scale">
      <div v-if="showModalPesanan"
        class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 scale-100 bg-white w-[80%] max-w-[480px] py-8 px-6 rounded-4xl shadow-lg text-center z-50">
        <div class="">
          <p class="text-center text-textDark text-xl font-semibold">
            Apakah Anda yakin ingin konfirmasi pesanan ini?
          </p>
        </div>
        <div class="flex justify-between mt-4 gap-2">
          <button @click="closeModalPesanan" class="w-full text-secondary py-3 rounded-full font-medium cursor-pointer">
            Batal
          </button>
          <button @click="submitUpdate('done')"
            class="w-full bg-primary text-textDark py-3 rounded-full font-medium cursor-pointer hover:brightness-90 duration-300">
            Ya, Konfirmasi
          </button>
        </div>
      </div>
    </Transition>
  </div>

  <!-- Sidebar -->
  <Sidebar />
</template>
