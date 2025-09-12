<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import Sidebar from './components/Sidebar.vue';
import HeaderDashboard from '@/components/HeaderDashboard.vue';
import { konversiStatus } from '../../lib/utils';
import { push } from 'notivue';
import { usePage } from '@inertiajs/vue3';
const { props } = usePage();

// Refs: Dropdown and Modal
const isDropdownOpen = ref(false);
const dropdownRef = ref(null);
const showModalDetail = ref(false);
const PrintOptions = ref(false)
// Refs: Order details
const ORDER = ref({});
const previewImage = ref(false);
// Refs: Bluetooth connection, Printer, and error handling
const error = ref('');
const text = ref('');
const bold = ref(false);
const device = ref(null);
const characteristic = ref(null);
const connected = ref(false);


// Helper methods
const formatCurrency = (num) => `Rp${Number(num).toLocaleString('id-ID')}`;

// Modal and Dropdown methods
const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
};
const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isDropdownOpen.value = false;

  }
};

const openModalDetail = (order) => {
  showModalDetail.value = true;
  ORDER.value = order;
};
const closeModalDetail = () => {
  showModalDetail.value = false;
};

const showModalKeluar = ref(false);
const openModalKeluar = () => {
  showModalKeluar.value = true;
};
const closeModalKeluar = () => {
  showModalKeluar.value = false;
};

// Bluetooth connection methods
const ensureConnected = async () => {
  if (device.value?.gatt && !device.value?.gatt?.connected) {
    try {
      const server = await device.value.gatt.connect();
      const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
      characteristic.value = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');
      connected.value = true;
      console.log('🔄 Reconnected to printer');
    } catch (err) {
      push.error({ title: '❌ Bluetooth failed', message: err })
      console.error('❌ Reconnection failed:', err);
      error.value = 'Failed to reconnect';
      return false;
    }
  }
  return true;
};

const connectPrinter = async () => {
  error.value = '';
  try {
    const dev = await navigator.bluetooth.requestDevice({
      acceptAllDevices: true,
      optionalServices: ['000018f0-0000-1000-8000-00805f9b34fb'],
    });
    device.value = dev;

    const server = await dev.gatt.connect();
    const service = await server.getPrimaryService('000018f0-0000-1000-8000-00805f9b34fb');
    characteristic.value = await service.getCharacteristic('00002af1-0000-1000-8000-00805f9b34fb');

    connected.value = true;
    console.log('✅ Connected to printer');
  } catch (err) {
    push.error({ title: '❌ Bluetooth ERROR', message: err })
    console.error(err);
    error.value = err.message || 'Failed to connect';
  }
};

// Print methods
const print = async () => {
  if (!await ensureConnected()) return;
  if (!characteristic.value) {
    error.value = 'Printer not connected';
    return;
  }

  const encoder = new TextEncoder();
  let data = text.value + '\r\n\r\n\r\n';

  if (bold.value) data = '\x1b\x45\x01' + data + '\x1b\x45\x00';

  const textBuffer = encoder.encode(data);
  const cutBuffer = new Uint8Array([0x1D, 0x56, 0x01]); // ESC/POS cut command
  const combinedBuffer = new Uint8Array(textBuffer.length + cutBuffer.length);

  combinedBuffer.set(textBuffer, 0);
  combinedBuffer.set(cutBuffer, textBuffer.length);

  try {
    // 🔎 Detect platform (Android has smaller MTU)
    const userAgent = navigator.userAgent.toLowerCase();
    const isAndroid = userAgent.includes('android');

    // Use small chunks on Android, larger on PC
    const chunkSize = isAndroid ? 20 : 512;

    for (let i = 0; i < combinedBuffer.length; i += chunkSize) {
      const chunk = combinedBuffer.slice(i, i + chunkSize);

      // Prefer writeValueWithoutResponse if available
      if (characteristic.value.writeValueWithoutResponse) {
        await characteristic.value.writeValueWithoutResponse(chunk);
      } else {
        await characteristic.value.writeValue(chunk);
      }

      // Give printer a bit more time on Android
      await new Promise(resolve => setTimeout(resolve, isAndroid ? 50 : 20));
    }

    console.log('✅ Printed and cut successfully');
  } catch (err) {
    console.error(err);
    error.value = err.message || 'Print failed';
  }
};


const cetakStruk = async () => {
  if (!connected.value) {
    await connectPrinter();
  }
  text.value =
    "SPW Gridas\n\n\n\n" +
    `Tanggal   : ${(ORDER.value?.created_at && new Date(ORDER.value.created_at).toLocaleString('id-ID')) || '-'}\n` +
    // `Kasir     : ${ORDER.value?.cashier_name || 'N/A'}\n` +
    `Transaksi : ${ORDER.value?.transaction_code || '-'}\n` +
    `Pembeli   : ${ORDER.value?.customer_name || 'N/A'}\n` +
    "------------------------------\n" +
    "Daftar Belanja:\n" +
    ORDER.value?.items?.map(item => {
      const name = item.item.name.padEnd(20, ' ').slice(0, 20);
      const qty = `x${item.quantity}`.padEnd(5, ' ');
      const price = formatCurrency(item.item.price).padStart(12, ' ');
      return `${name}${qty}${price}`;
    }).join('\n') + "\n" +
    "------------------------------\n" +
    `Total Bayar: ${formatCurrency(ORDER.value?.total_amount)}\n\n` +
    "     -- Terima Kasih --\n";

  console.log('🔄 Printing...');
  console.log(text.value);
  await print();
};

const print_with_document_print = () => {
  const printWindow = window.open('', '_blank');
  if (!printWindow) {
    push.error({ title: '❌ Error', message: 'Pop-up blocked. Please allow pop-ups for this site.' });
    return;
  }


  const htmlContent = `
    <html>
      <head>
        <title>Struk Pembelian</title>
        <style>
          body {
            font-family: monospace;
            font-size: 12px;
            white-space: pre;
            padding: 20px;
          }
          .center {
            text-align: center;
          }
          .bold {
            font-weight: bold;
          }
          .separator {
            border-top: 1px dashed #000;
            margin: 10px 0;
          }
        </style>
      </head>
      <body onload="window.print(); window.close();">
        <div class="center bold">SPW Gridas</div>

        <br>
        Tanggal     : ${ORDER.value.created_at || '-'}
        Transaksi   : ${ORDER.value.transaction_code || '-'}

        <div class="separator"></div>
        <div class="bold">Daftar Belanja:</div>

${ORDER.value.items.map(item => {
    const name = item.item.name.padEnd(20, ' ').slice(0, 20);
    const qty = `x${item.quantity}`.padEnd(5, ' ');
    const price = formatCurrency(item.item.price).padStart(12, ' ');
    return `${name} ${qty} ${price}`;
  }).join('\n')}

        <div class="separator"></div>
        Total Bayar : ${formatCurrency(ORDER.value.total_amount)}
        <br><br>
        <div class="center">-- Terima Kasih --</div>
      </body>
    </html>
  `;

  printWindow.document.open();
  printWindow.document.write(htmlContent);
  printWindow.document.close();
};

// Hooks
onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside);
});

</script>

<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24 md:pb-0">
    <HeaderDashboard @openModalKeluar="openModalKeluar" />

    <section class="mt-6">
      <div>
        <div class="md:flex justify-between items-center">
          <h1 class="text-textDark text-lg font-semibold">Riwayat Pesanan</h1>
          <div class="mt-4 md:mt-0">
            <div class="w-full md:w-96 relative">
              <input type="search" class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
                placeholder="Cari pesanan" />
              <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                <p class="text-textDark text-xl">
                  <i class="fi fi-rr-search"></i>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 min-h-fit">
          <button v-for="order in $page.props.orders" @click="openModalDetail(order)"
            class="relative block bg-white p-4 space-y-5 rounded-2xl hover:bg-primaryThin duration-300 cursor-pointer">
            <!-- Badge -->
            <div class="absolute top-0 right-0 bg-primaryThin py-1.5 px-4 rounded-tr-2xl rounded-bl-2xl">
              <p class="text-sm text-primary font-medium">
                {{ order.customer_name }}
              </p>
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
                <h2 class="text-start text-textDark line-clamp-2">
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

    <!-- Modal Detail Riwayat Pesanan -->
    <Transition name="fade">
      <div v-if="showModalDetail" class="fixed inset-0 bg-black/50 z-20" @click="closeModalDetail"></div>
    </Transition>

    <Transition name="scale">
      <div v-if="showModalDetail" class="fixed inset-0 z-30 flex items-center justify-center px-4">
        <div class="bg-bgGray w-full md:w-[60%] max-h-[90vh] md:max-h-screen overflow-y-auto rounded-4xl shadow-lg p-6"
          @click.stop>
          <div class="flex justify-between">
            <h1 class="text-textDark text-lg font-semibold">Detail Pesanan</h1>
            <p class="text-textDark text-2xl cursor-pointer" @click="closeModalDetail">
              <i class="fi fi-rr-cross-small"></i>
            </p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <!-- Kolom Kiri -->
            <div class="col-span-1">
              <h1 class="text-textDark font-semibold">Data Pemesan</h1>
              <div class="space-y-4 mt-4">
                <div>
                  <label class="text-textDark">Nama Pemesan</label>
                  <div class="relative mt-2">
                    <div class="py-3 px-4 ps-12 bg-white rounded-full">
                      <p class="text-textDark">
                        {{ ORDER.customer_name || 'N/A' }}
                      </p>
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
                      <p class="text-textDark">
                        {{ ORDER.whatsapp_number || 'N/A' }}
                      </p>
                    </div>
                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
                      <p class="text-textDark text-xl">
                        <i class="fi fi-brands-whatsapp"></i>
                      </p>
                    </div>
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
                    <p class="text-green">
                      {{ konversiStatus(ORDER.status) }}
                    </p>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-textDark">Metode Pembayaran</p>
                    <p class="text-textDark uppercase">
                      {{ ORDER.payment_method }}
                    </p>
                  </div>
                    <div v-if="ORDER.payment_method === 'cash'" class="flex justify-between">
                      <p class="text-textDark">Tunai dan Kembali</p>
                      <p class="text-textDark">
                        {{
                          "Rp. " +
                          Number(ORDER.cash_given ?? ORDER.total_amount).toLocaleString('id-ID')
                          + "  (Kembali Rp. " +
                          Number(ORDER.change ?? 0).toLocaleString('id-ID')
                          + ")" }}
                      </p>
                    </div>
                </div>
              </div>

              <div class="flex justify-between mt-6">
                <p class="text-textDark">
                  Total:
                  <span class="font-bold">Rp{{
                    Number(ORDER.total_amount).toLocaleString('id-ID')
                    }}</span>
                </p>
              </div>

              <button v-if="ORDER.payment?.proof" @click="previewImage = true"
                class="bg-primary py-3 mt-4 w-full rounded-full cursor-pointer hover:brightness-90 duration-300">
                <p class="font-semibold">Lihat Bukti Pembayaran</p>
              </button>
            </div>

            <!-- Bukti pembayaran modal -->
            <Transition name="scale">
              <div class="absolute top-0 left-0 w-full h-full bg-black/25 z-10 flex items-center justify-center"
                v-if="previewImage && ORDER.payment?.proof" @click="previewImage = false">
                <img :src="`/storage/${ORDER.payment.proof}`" class="w-[80vh]" alt="Bukti pembayaran" @click.stop />
              </div>
            </Transition>
            <!-- Kolom Kanan -->
            <div class="col-span-1 flex flex-col h-full">
              <h1 class="text-textDark font-semibold">Detail Pesanan</h1>
              <div class="flex flex-col gap-4 mt-4 bg-white p-4 rounded-2xl max-h-[274px] overflow-y-auto">
                <div v-for="i in ORDER.items" class="flex justify-between items-center">
                  <div>
                    <h1 class="line-clamp-1">
                      {{ i.item.name }}
                    </h1>
                    <h2 class="font-bold">
                      Rp{{ Number(i.item.price).toLocaleString('id-ID') }}
                    </h2>
                  </div>
                  <p class="text-xs text-textDark mt-1">x{{ i.quantity }}</p>
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
              <div class="relative h-full mt-4">
                <!-- Trigger button -->
                <button @click.prevent="PrintOptions = !PrintOptions"
                  class="absolute bottom-0 bg-primaryThin py-3 md:mt-auto w-full rounded-full cursor-pointer hover:brightness-90 duration-300">
                  <p class="font-semibold">Cetak Struk Pembelian</p>
                </button>

                <!-- Modal Dropdown -->
                <div v-if="PrintOptions"
                  class="absolute mt-2 w-full left-0 bg-white rounded-2xl shadow-lg z-10 p-4 space-y-2">

                  <button @click="cetakStruk"
                    class="bg-primaryThin py-2 w-full rounded-full hover:brightness-90 duration-300">
                    <p class="font-semibold">Cetak dengan mesin kasir</p>
                  </button>

                  <button @click="print_with_document_print"
                    class="bg-primaryThin py-2 w-full rounded-full hover:brightness-90 duration-300">
                    <p class="font-semibold">Cetak dengan printer/PDF</p>
                  </button>

                </div>
              </div>



            </div>
          </div>
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
  <Sidebar />
</template>
