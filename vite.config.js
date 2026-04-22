import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
  server: {
    host: '127.0.0.1',
    strictPort: true,
    hmr: {
      host: '127.0.0.1',
    },
  },
  plugins: [
    laravel({
      input: ['resources/css/app.css', 'resources/js/app.js'],
      refresh: true,
    }),
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  optimizeDeps: {
    include: [
      'vue',
      'vue-router'
    ]
  },
  esbuild: {
    target: 'es2020'
  },
  resolve: {
    alias: {
      'vue': 'vue/dist/vue.esm-bundler.js' // Add this line
    }
  }
})
