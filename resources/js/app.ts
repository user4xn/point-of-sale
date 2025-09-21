import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { App, createApp, DefineComponent, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.min.css';
import Swal from 'sweetalert2';

const SwalPlugin = {
  install(app: App) {
    const customSwal = Swal.mixin({
      customClass: {
        container: 'bg-gray-800 text-white',
        popup: 'bg-gray-800 text-white',
        input: 'bg-gray-600 border border-gray-500 text-white rounded-full',
        confirmButton: 'bg-green-600 hover:bg-green-500 text-white text-md font-semibold',
        cancelButton: 'bg-red-600 hover:bg-red-500 text-white text-md font-semibold',
      },
    });

    app.config.globalProperties.$swal = customSwal;
    app.provide('swal', customSwal);
  },
};

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
    ),
  setup({ el, App, props, plugin }) {
    createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(ZiggyVue)
      .use(SwalPlugin)
      .component('Multiselect', Multiselect)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});