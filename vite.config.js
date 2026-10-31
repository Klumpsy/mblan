import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    // Vite 7+ raised the default browser target to Safari 16.4 / Chrome 111.
    // Keep Vite 5's previous default so older phones (iOS < 16.4) keep
    // working: without this, Lightning CSS rewrites every `min-width`
    // breakpoint to `(width>=…)` range syntax that those browsers ignore.
    build: {
        target: ['es2020', 'edge88', 'firefox78', 'chrome87', 'safari14'],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
