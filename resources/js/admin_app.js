import './bootstrap'

import { createApp } from 'vue'
import AdminApp from './apps/admin/AdminAppFixed.vue'

const app = createApp(AdminApp)

app.config.globalProperties.$toast = {
    success(message, opts = {}) {
        window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'success', message, ...opts } }))
    },
    error(message, opts = {}) {
        window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'error', message, ...opts } }))
    },
    info(message, opts = {}) {
        window.dispatchEvent(new CustomEvent('app-toast', { detail: { type: 'info', message, ...opts } }))
    },
}

app.mount('#app')
