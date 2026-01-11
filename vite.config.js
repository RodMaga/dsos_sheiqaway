import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
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
                'resources/js/viagens.js'
            ],
            refresh: true,
        }),
    ],
});
