import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                entryFileNames: `assets/[name]-[hash].js`,
                chunkFileNames: `assets/[name]-[hash].js`,
                assetFileNames: `assets/[name]-[hash].[ext]`
            }
        }
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pages.css',
                'resources/css/login.css',
                'resources/css/register.css',
                'resources/js/app.js',
                'resources/js/script.js',
                'resources/js/global.js',
                'resources/js/cart.js',
                'resources/js/carrinho.js',
                'resources/js/dashboard.js',
                'resources/js/detalhes.js',
                'resources/js/viagens.js'
            ],
            refresh: true,
        }),
    ],
});
