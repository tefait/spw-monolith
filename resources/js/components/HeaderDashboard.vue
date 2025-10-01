<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import DashboardFlyout from './DashboardFlyout.vue';

const isDropdownOpen = ref(false);
const dropdownRef = ref(null);

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
</script>

<template>
  <div class="bg-white p-4 rounded-2xl flex justify-between items-center">
    <div>
      <h1 class="text-lg font-semibold">SIPEKA</h1>
    </div>
    <!-- Dropdown -->
    <div class="relative" ref="dropdownRef">
      <button @click="toggleDropdown" class="flex items-center gap-4 cursor-pointer">
        <div class="h-12 w-12 rounded-full overflow-hidden">
          <img :src="$page.props.auth.user.image" :alt="$page.props.auth.user.name + ' profile picture'" />
        </div>
        <div class="hidden md:inline-flex flex-col text-left">
          <h2 class="text-textDark font-semibold">
            {{ $page.props.auth.user.name }}
          </h2>
          <p class="text-textDark text-sm">
            {{ $page.props.auth.user.email }}
          </p>
        </div>
        <div>
          <p class="hidden md:block text-textDark transition-transform duration-200"
            :class="isDropdownOpen ? 'rotate-180' : ''">
            <i class="fi fi-sr-angle-down"></i>
          </p>
        </div>
      </button>

      <DashboardFlyout v-model="isDropdownOpen" />
    </div>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.1s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
