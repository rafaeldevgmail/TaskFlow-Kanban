import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue(),
    ],
    resolve: {
        alias: { '@': path.resolve(__dirname, 'resources/js') }
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true, // Simplificado, ou mantenha o seu objeto se preferir
        hmr: {
            host: process.env.CODESPACE_NAME ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}` : 'localhost',
            clientPort: 443,
            protocol: 'wss'
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});