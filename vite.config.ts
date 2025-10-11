import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import vueDevTools from 'vite-plugin-vue-devtools';
import { defineConfig } from 'vite';
import fs from 'fs';

export default defineConfig((command) => {
    const baseConfig = {
        build: {
            chunkSizeWarningLimit: 1000
        },
        plugins: [
            laravel({
                input: ['resources/js/app.ts'],
                refresh: true
            }),
            tailwindcss(),
            wayfinder({
                formVariants: true
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false
                    }
                }
            }),
            vueDevTools()
        ]
    };
    if (command === 'build') {
        return baseConfig;
    }
    const host = 'app.test';
    return {
        ...baseConfig,
        server: {
            host,
            cors: true,
            hmr: {
                host
            },
            https: {
                key: fs.readFileSync('./docker.copy/nginx/conf/docker.key'),
                cert: fs.readFileSync('./docker.copy/nginx/conf/docker.crt')
            }
        }
    };
});