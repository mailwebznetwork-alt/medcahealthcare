import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/public/public.css',
                'resources/css/admin/admin.css',
                'resources/js/app.js',
                'resources/js/dashboard.js',
            ],
            refresh: true,
        }),
    ],
});
