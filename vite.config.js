import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/identity-access/session-timer.js',
                'resources/css/identity-access/session.css',
                'resources/js/reporting/dashboard-charts.js',
            ],
            refresh: true,
        }),
    ],
});
