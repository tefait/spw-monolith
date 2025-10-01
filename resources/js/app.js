import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import AdminLayout from './layouts/AdminLayout.vue';
import UserLayout from './layouts/UserLayout.vue';
import 'notivue/notification.css';
import 'notivue/animations.css';
import { createNotivue } from 'notivue';
const appName = import.meta.env.VITE_APP_NAME || 'SIPEKA';

const notivue = createNotivue({
  position: 'top-center',
  duration: 3000,
  max: 3,
  theme: 'material',
  closeButton: true,
  draggable: true,
  pauseOnHover: true,
});
createInertiaApp({
  title: (title) => `${title} | ${appName}`,

  resolve: async (name) => {
    const pages = import.meta.glob('./pages/**/*.vue', { eager: false });
    const page = await resolvePageComponent(`./pages/${name}.vue`, pages);

    // 💡 Set layout based on folder name
    if (
      name.startsWith('admin/') ||
      name.startsWith('kasir/') ||
      name.startsWith('pelayan/')
    ) {
      page.default.layout ??= AdminLayout;
    } else {
      page.default.layout ??= UserLayout;
    }

    return page;
  },

  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(notivue)
      .mount(el);
  },
  progress: {
    color: '#f1bd2c',
    showSpinner: true,
    delay: 0,
  },
}).then(() => {
  if (document) {
    document.getElementById('app').removeAttribute('data-page');
    document.getElementById('app').removeAttribute('data-v-app');
  }
});
