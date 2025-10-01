<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { Notification, Notivue, NotivueSwipe, push } from 'notivue';
import { onMounted, watch } from 'vue';

const page = usePage();

// fungsi supaya reusable
const showErrors = (errors) => {
  if (!errors) return;
  console.warn(errors)
  Object.entries(errors).forEach(([field, message]) => {
    push.error({
      title: 'System',
      message: `${field}: ${message}`
    });
  });
};

onMounted(() => {
  // Success flash message
  if (page.props.flash?.success) {
    push.success(page.props.flash.success);
  }

  // Errors on mount
  showErrors(page.props.errors);
});

// Jika mau otomatis muncul juga kalau props errors berubah setelah navigation
watch(
  () => page.props.errors,
  (errors) => showErrors(errors),
  { deep: true }
);

// Dynamic page title
const title = page.props.title ?? 'Dashboard';
</script>

<template>
  <div class="font-display antialiased">

    <Head :title="title" />

    <Notivue v-slot="item">
      <NotivueSwipe :item="item">
        <Notification :item="item" />
      </NotivueSwipe>
    </Notivue>

    <slot />
  </div>
</template>
