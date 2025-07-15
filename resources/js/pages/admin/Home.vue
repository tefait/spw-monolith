<template>
  <div class="bg-bgGray min-h-screen md:ps-[150px] p-4 md:pe-4 pt-[18px] pb-24 md:pb-0">
    <HeaderDashboard/>

    <section class="bg-white mt-4 p-4 rounded-2xl">
      <div>
        <h1 class="text-textDark text-lg font-semibold">Statistik Hari Ini</h1>
        <!-- Chart -->
        <div class="col-span-2 md:hidden">
          <VueApexCharts type="bar" height="320" :options="chartOptions" :series="series" />
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 space-y-4 md:space-x-4 mt-4">
          <div class="col-span-1 bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
            <div class="space-y-2">
              <p class="text-textDark">Menu Aktif</p>
              <h1 class="text-textDark text-3xl font-bold">
                {{ $page.props.stats.items }}
              </h1>
            </div>
            <div>
              <p class="text-primary text-5xl">
                <i class="fi fi-sr-hamburger-soda"></i>
              </p>
            </div>
          </div>
          <div class="col-span-1 bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
            <div class="space-y-2">
              <p class="text-textDark">Jumlah Pesanan Hari Ini</p>
              <h1 class="text-textDark text-3xl font-bold">
                {{ $page.props.stats.orders }}
              </h1>
            </div>
            <div>
              <p class="text-primary text-5xl">
                <i class="fi fi-sr-room-service"></i>
              </p>
            </div>
          </div>
          <div class="col-span-1 bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
            <div class="space-y-2">
              <p class="text-textDark">Total Pendapatan Hari Ini</p>
              <h1 class="text-textDark text-3xl font-bold">
                Rp{{ Number($page.props.stats.income || 0).toLocaleString('id-ID') }}
              </h1>
            </div>
            <div>
              <p class="text-primary text-5xl">
                <i class="fi fi-sr-sack-dollar"></i>
              </p>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 space-y-4 md:space-x-4 mt-4">
          <!-- Chart -->
          <div class="col-span-2 hidden md:block">
            <VueApexCharts type="bar" height="320" :options="chartOptions" :series="series" />
          </div>
          <div class="col-span-1 flex flex-col justify-between gap-4 md:gap-0">
            <div class="bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
              <div class="space-y-2">
                <p class="text-textDark">Total Keuntungan Hari Ini</p>
                <h1 class="text-textDark text-3xl font-bold">Rp{{
                  Number($page.props.stats.profit).toLocaleString('id-ID') }}</h1>
              </div>
              <div>
                <p class="text-primary text-5xl">
                  <i class="fi fi-sr-hand-holding-usd"></i>
                </p>
              </div>
            </div>
            <div class="bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
              <div class="space-y-2">
                <p class="text-textDark">Jumlah Supplier</p>
                <h1 class="text-textDark text-3xl font-bold">
                  {{ $page.props.stats.supplier }}
                </h1>
              </div>
              <div>
                <p class="text-primary text-5xl">
                  <i class="fi fi-ss-supplier"></i>
                </p>
              </div>
            </div>
            <div class="bg-primaryThin p-4 rounded-3xl flex justify-between items-center">
              <div class="space-y-2">
                <p class="text-textDark">Pengguna Terdaftar</p>
                <h1 class="text-textDark text-3xl font-bold">
                  {{ $page.props.stats.customer }}
                </h1>
              </div>
              <div>
                <p class="text-primary text-5xl">
                  <i class="fi fi-sr-users"></i>
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Sidebar -->
  <Sidebar />
</template>

<script setup>
import { ref } from 'vue';
import Sidebar from './components/Sidebar.vue';
import VueApexCharts from 'vue3-apexcharts';
import HeaderDashboard from '@/components/HeaderDashboard.vue';
import { usePage } from '@inertiajs/vue3';


const page = usePage();
// Modal Konfirmasi Keluar
const showModalKeluar = ref(false);

// Grafik Pesanan Tahunan
const series = ref([
  {
    name: 'Pesanan',
    data: page.props.stats.monthlyCounts,
  },
]);

const chartOptions = ref({
  chart: {
    height: 320,
    type: 'bar',
  },
  colors: ['#F1BD2C'],
  plotOptions: {
    bar: {
      borderRadius: 10,
      dataLabels: {
        position: 'top',
      },
    },
  },
  dataLabels: {
    enabled: true,
    // formatter: (val) => val + '%',
    offsetY: -20,
    style: {
      fontSize: '12px',
      colors: ['#2D3134'],
    },
  },
  xaxis: {
    categories: [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec',
    ],
    position: 'top',
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
    tooltip: {
      enabled: true,
    },
  },
  yaxis: {
    labels: {
      show: false,
      //   formatter: (val) => val + '%',
    },
  },
  title: {
    text: 'Grafik Pesanan Tahun 2025',
    floating: true,
    offsetY: 300,
    align: 'center',
    style: {
      color: '#2D3134',
    },
  },
});
</script>
