import { defineConfig } from 'vite';
import { ViteMinifyPlugin } from 'vite-plugin-minify'
import { viteStaticCopy } from 'vite-plugin-static-copy'
import alias from '@rollup/plugin-alias'
import path from 'path'
import commonjs from "vite-plugin-commonjs";
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    plugins: [
        alias(),
        commonjs(),
        tailwindcss(),
        viteStaticCopy({
            targets: [
                {
                    src: 'Assets/images/*',
                    dest: 'images/'
                },
            ],
        }),
        ViteMinifyPlugin({}),
    ],
    // Set the root directory for Vite
    root: path.resolve(__dirname, 'src'),
    server: {
        cors: {
            origin: 'http://localhost:8080',
        },
    },
    resolve: {
        alias: {
        },
    },
    build: {
        css: {
            postcss: {
                plugins: [tailwindcss()],
            },
        },
        emptyOutDir: false,
        outDir: '../public',
        manifest: true,
        modulePreload: {
            polyfill: true,
        },
        rollupOptions: {
            input: {
                // Main JavaScript entry point
                main: path.resolve(__dirname, './src/Assets/js/main.js'),
            },
            output: {
                manualChunks: undefined,
                entryFileNames: "js/main.js",
            },
        },
    },
})
