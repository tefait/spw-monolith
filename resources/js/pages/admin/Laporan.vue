<script setup>
import { ref, computed, onMounted } from 'vue';
import AdminSidebar from './components/Sidebar.vue';
import PelayanSidebar from '../pelayan/components/Sidebar.vue';
import { router, usePage } from '@inertiajs/vue3';
import HeaderDashboard from '@/components/HeaderDashboard.vue';

const page = usePage();

/** Filters */
const period = ref('this_month');
const start = ref('');
const end = ref('');

/** Hydrate filters */
onMounted(() => {
  const f = page.props?.filters || {};
  if (f.period) period.value = f.period;
  if (f.start) start.value = f.start;
  if (f.end) end.value = f.end;
});

/** Validasi custom period */
const isCustomValid = computed(() => {
  if (period.value !== 'custom') return true;
  return !!start.value && !!end.value && start.value <= end.value;
});

/** Orders */
const orders = computed(() => page.props?.orders || []);

const q = ref('');
const filteredOrders = computed(() => {
  const term = q.value.trim().toLowerCase();
  if (!term) return orders.value;
  return orders.value.filter(o => {
    const fields = [o?.transaction_code, o?.customer_name, o?.payment_method, o?.notes]
      .filter(Boolean).map(String).join(' ').toLowerCase();
    return fields.includes(term);
  });
});

/** Format helpers */
const fmtIDR = (n) => {
  const num = typeof n === 'number' ? n : Number(n || 0);
  return `Rp${num.toLocaleString('id-ID')}`;
};
const fmtDateTimeID = (iso) => {
  if (!iso) return '-';
  const d = new Date(iso);
  return isNaN(d) ? '-' : d.toLocaleString('id-ID');
};

/** Action: filter */
const filterReport = () => {
  if (!isCustomValid.value) {
    window.alert('Tanggal custom tidak valid. Pastikan Start ≤ End.');
    return;
  }
  const params = { period: period.value };
  if (period.value === 'custom') {
    params.start = start.value;
    params.end = end.value;
  }
  router.get('/admin/laporan', params, {
    preserveState: true,
    preserveScroll: true,
  });
};
</script>


<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24">
    <HeaderDashboard />

    <!-- Top bar -->
    <section class="mt-4 w-full">
      <div class="md:flex justify-between">
        <div class="w-full md:w-96 relative">
          <input v-model="q" type="search"
            class="peer py-3 px-4 ps-12 block w-full bg-white rounded-full focus:outline-none"
            placeholder="Cari laporan (kode, nama pelanggan, catatan)" aria-label="Cari laporan" />
          <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-4 pt-1">
            <p class="text-textDark text-xl"><i class="fi fi-rr-search"></i></p>
          </div>
        </div>
      </div>
    </section>

    <!-- Content -->
    <section class="mt-6">
      <div>
        <h1 class="text-textDark text-xl font-bold">Laporan</h1>

        <div class="w-full p-6 mt-4 bg-white rounded-2xl shadow-sm">
          <!-- Filters -->
          <div class="print:hidden border-b border-textGray pb-4 mb-6">
            <h2 class="text-lg font-semibold text-textDark mb-4">Laporan Penjualan</h2>

            <div class="flex flex-col lg:flex-row lg:items-center gap-6">
              <!-- Radios -->
              <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 cursor-pointer bg-gray-100 rounded-full py-1.5 px-3">
                  <input id="today" name="notification-method" type="radio" v-model="period"
                    class="w-4 h-4 border-gray-300 text-primary focus:ring-primary" value="today"
                    aria-labelledby="label-today" />
                  <span id="label-today" class="text-sm font-medium text-textDark">Hari ini</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer bg-gray-100 rounded-full py-1.5 px-3">
                  <input id="month" name="notification-method" type="radio" v-model="period"
                    class="w-4 h-4 border-gray-300 text-primary focus:ring-primary" value="this_month"
                    aria-labelledby="label-month" />
                  <span id="label-month" class="text-sm font-medium text-textDark">Bulan Ini</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer bg-gray-100 rounded-full py-1.5 px-3">
                  <input id="period" name="notification-method" type="radio" v-model="period"
                    class="w-4 h-4 border-gray-300 text-primary focus:ring-primary" value="custom"
                    aria-labelledby="label-period" />
                  <span id="label-period" class="text-sm font-medium text-textDark">Periode</span>
                </label>
              </div>

              <!-- Custom dates -->
              <div v-if="period === 'custom'" class="flex gap-3">
                <input type="date" v-model="start" name="start"
                  class="px-3 py-2 border text-sm text-textDark bg-gray-100 rounded-full" aria-label="Tanggal mulai" />
                <input type="date" v-model="end" name="end"
                  class="px-3 py-2 border text-sm text-textDark bg-gray-100 rounded-full" aria-label="Tanggal akhir" />
              </div>

              <!-- Submit -->
              <div class="flex">
                <button type="button" @click="filterReport" :disabled="period === 'custom' && !isCustomValid"
                  class="w-full hover:cursor-pointer px-4 py-2 text-sm font-semibold text-center text-white rounded-full shadow bg-primary hover:opacity-90 transition disabled:opacity-60 disabled:cursor-not-allowed"
                  aria-disabled="period==='custom' && !isCustomValid">
                  Tampilkan Laporan
                </button>
              </div>
            </div>
          </div>

          <!-- Table -->
          <div class="flex flex-col space-y-4">
            <div class="max-w-full overflow-x-auto rounded-lg border border-textGray">
              <table class="min-w-full text-sm" id="print-content">
                <thead class="bg-bgGray text-textDark">
                  <tr>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">No</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Transaction Time</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Transaction Code</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Kasir/Pengguna</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Total</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Payment Method</th>
                    <th class="px-3 py-3 font-semibold border border-gray-200 text-left">Information</th>
                  </tr>
                </thead>

                <tbody>
                  <tr v-if="filteredOrders.length === 0" class="hover:bg-bgGray">
                    <td colspan="7" class="px-3 py-6 text-center text-textGrayDark">
                      Tidak ada data untuk ditampilkan.
                    </td>
                  </tr>

                  <tr v-for="(order, index) in filteredOrders" :key="order?.id ?? order?.transaction_code ?? index"
                    class="hover:bg-bgGray">
                    <td class="px-3 py-2 border border-gray-200 align-top">{{ index + 1 }}</td>
                    <td class="px-3 py-2 border border-gray-200 align-top">{{ fmtDateTimeID(order?.created_at) }}</td>
                    <td class="px-3 py-2 border border-gray-200 align-top">{{ order?.transaction_code }}</td>
                    <td class="px-3 py-2 border border-gray-200 align-top">
                      {{ order?.customer_name }}
                      <span v-if="order?.user_has_account">
                        (by: {{ order?.user?.name }})
                      </span>
                    </td>
                    <td class="px-3 py-2 border border-gray-200 align-top">{{ fmtIDR(order?.total_amount) }}</td>
                    <td class="px-3 py-2 border border-gray-200 align-top capitalize">{{ order?.payment_method }}</td>
                    <td class="px-3 py-2 border border-gray-200 align-top max-w-[320px]">
                      {{ order?.notes || '-' }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Action buttons (opsional) -->
            <!--
            <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-fit print:hidden">
              <button type="button"
                class="px-4 py-2 cursor-not-allowed text-sm font-semibold rounded-lg shadow bg-primaryThin text-primary hover:opacity-90 transition">
                Export to Excel
              </button>
              <button type="button"
                class="px-4 py-2 cursor-not-allowed text-sm font-semibold text-white rounded-lg shadow bg-primary hover:opacity-90 transition">
                Print PDF
              </button>
            </div>
            -->
          </div>
          <main class="mt-2 md:mt-5">
            <h2 class="text-lg font-semibold text-textDark mb-4">Laporan lain</h2>
            
            <!-- Cards row 1 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Menu Aktif</p>
                  <h1 class="text-textDark text-3xl font-bold">{{ $page.props.stats.items }}</h1>
                </div>
                <p class="text-primary text-5xl group-hover:text-primaryThin"><i class="fi fi-sr-hamburger-soda"></i></p>
              </div>
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Total Pesanan</p> <!-- ✅ bukan "Hari Ini" -->
                  <h1 class="text-textDark text-3xl font-bold">{{ $page.props.stats.orders }}</h1>
                </div>
                <p class="text-primary text-5xl group-hover:text-primaryThin"><i class="fi fi-sr-room-service"></i></p>
              </div>
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Total Pendapatan</p>
                  <h1 class="text-textDark text-3xl font-bold">{{ fmtIDR($page.props.stats.income) }}</h1>
                </div>
                <p class="text-primary text-5xl group-hover:text-primaryThin"><i class="fi fi-sr-sack-dollar"></i></p>
              </div>
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Total Keuntungan</p>
                  <h1 class="text-textDark text-3xl font-bold">Rp{{
                    Number($page.props.stats.profit).toLocaleString('id-ID') }}</h1>
                </div>
                <div>
                  <p class="text-primary text-5xl group-hover:text-primaryThin">
                    <i class="fi fi-sr-hand-holding-usd"></i>
                  </p>
                </div>
              </div>
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Jumlah Supplier</p>
                  <h1 class="text-textDark text-3xl font-bold">
                    {{ $page.props.stats.supplier }}
                  </h1>
                </div>
                <div>
                  <p class="text-primary text-5xl group-hover:text-primaryThin">
                    <i class="fi fi-ss-supplier"></i>
                  </p>
                </div>
              </div>
              <div class="h-fit bg-primaryThin p-4 rounded-2xl flex justify-between group items-center hover:bg-primary">
                <div class="space-y-2">
                  <p class="text-textDark">Pengguna Terdaftar</p>
                  <h1 class="text-textDark text-3xl font-bold">
                    {{ $page.props.stats.customer }}
                  </h1>
                </div>
                <div>
                  <p class="text-primary text-5xl group-hover:text-primaryThin">
                    <i class="fi fi-sr-users"></i>
                  </p>
                </div>
              </div>
            </div>
          </main>
        </div>

      </div>
    </section>

  </div>

  <!-- Sidebars: robust against query params -->
  <AdminSidebar v-if="$page.url.startsWith('/admin/laporan')" />
  <PelayanSidebar v-else-if="$page.url.startsWith('/pelayan/dashboard')" />
</template>
