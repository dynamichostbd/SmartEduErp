import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        tailwindcss(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/admin_app.js',
                'resources/js/login_app.js',
                'resources/js/frontend_app.js',
            ],
            refresh: true,
        }),
        vue(),
    ],
})