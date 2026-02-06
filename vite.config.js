import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import { compression } from 'vite-plugin-compression2';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
                compilerOptions: {
                    // Remove whitespace for smaller bundle
                    whitespace: 'condense',
                },
            },
        }),
        // Gzip compression for production
        compression({
            algorithm: 'gzip',
            exclude: [/\.(br)$/, /\.(gz)$/],
        }),
    ],
    server: {
        watch: {
            ignored: [
                '**/storage/framework/views/**',
                '**/vendor/**',
                '**/node_modules/**'
            ],
        },
        hmr: {
            overlay: false, // Disable error overlay for better performance
        },
    },
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm-bundler.js',
            '@': '/resources/js',
            '@components': '/resources/js/components',
            '@pages': '/resources/js/pages',
            '@stores': '/resources/js/stores',
            '@composables': '/resources/js/composables',
        },
    },
    build: {
        // Optimize chunk splitting
        rollupOptions: {
            output: {
                manualChunks: {
                    // Vendor chunks
                    'vendor-vue': ['vue', 'vue-router', 'pinia'],
                    'vendor-utils': ['axios', 'flatpickr'],
                    // Component chunks
                    'components-heavy': [
                        './resources/js/components/TaskDetails.vue',
                        './resources/js/components/TaskDeliveryModal.vue',
                    ],
                    'pages-dashboard': ['./resources/js/pages/Dashboard.vue'],
                },
                // Optimize chunk file names
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
        // Optimize minification
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.logs in production
                drop_debugger: true,
                pure_funcs: ['console.log', 'console.info', 'console.debug'],
            },
        },
        // Increase chunk size warning limit
        chunkSizeWarningLimit: 1000,
        // Enable CSS code splitting
        cssCodeSplit: true,
        // Source maps only for development
        sourcemap: false,
        // Optimize assets
        assetsInlineLimit: 4096, // 4kb - inline small assets
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['vue', 'vue-router', 'pinia', 'axios'],
        exclude: ['@vite/client', '@vite/env'],
    },
});

